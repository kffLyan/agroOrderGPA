<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuratJalanResource extends JsonResource
{
    // Mengubah model Order menjadi representasi data konsol Surat Jalan, manifest muatan, dan legalitas distribusi untuk UI Sekretaris
    public function toArray(Request $request): array
    {
        $order = $this->resource;
        $clientName = $order->user?->company_name ?: ($order->user?->name ?: 'Mitra Klien GPA');
        $deliveryAddress = $order->delivery_address ?: 'Gudang Utama Klien';
        $totalWeight = (float) $order->orderItems->sum('ordered_qty');

        $driver = $order->driver;
        $driverName = $driver?->name ?: 'Belum Ditugaskan';
        $plateNumber = $order->vehicle_plate_number ?: '—';
        $fleetName = $driver ? 'Armada Logistik GPA' : 'Menunggu Penugasan';

        $suratJalanNumber = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT));

        $isContractClient = $order->user && (
            $order->user->client_type === 'B2B_KONTRAK' ||
            ($order->user->relationLoaded('contracts') && $order->user->contracts->where('status', 'ACTIVE')->isNotEmpty()) ||
            $order->user->contracts()->where('status', 'ACTIVE')->exists()
        );
        $isPaid = ($order->status === 'LUNAS');
        $isSuratJalanIssued = in_array($order->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']);
        $isReadyToIssue = $isPaid || ($isContractClient && $order->status === 'MENUNGGU_PEMBAYARAN');

        // Bentuk rincian baris muatan surat jalan
        $cargoLines = $order->orderItems->map(function ($item, $rowIndex) {
            $quantity = (float) $item->ordered_qty;
            return [
                'no'        => $rowIndex + 1,
                'commodity' => $item->product?->name ?: 'Komoditas Sayur',
                'lot'       => 'Wadah Krat Standar GPA',
                'estimate'  => number_format($quantity, 1, ',', '.') . ' kg',
                'netto'     => number_format($quantity, 1, ',', '.') . ' kg',
                'delta'     => '0.0 kg (Tagihan Pasti)',
            ];
        })->all();

        return [
            'order_id'     => $order->id,
            'id'           => $order->order_number,
            'client'       => $clientName,
            'warehouse'    => 'Gudang Pusat Sentra GPA',
            'dock'         => $deliveryAddress,
            'state'        => ($isReadyToIssue && !$isSuratJalanIssued) ? 'ready' : ($isSuratJalanIssued ? 'issued' : 'locked'),
            'order_status' => $order->status,
            'estimate'     => number_format($totalWeight, 1, ',', '.') . ' kg',
            'netto'        => number_format($totalWeight, 1, ',', '.') . ' kg',
            'deviation'    => $isContractClient ? 'Kredit Tempo B2B (Sah Rilis)' : 'Lunas Terverifikasi',
            'revision'     => null,
            'status_chip'  => [
                'label' => $isSuratJalanIssued ? 'Surat Jalan Terbit (SIAP KIRIM)' : ($isPaid ? 'Pembayaran LUNAS (Siap Rilis SJ)' : ($isContractClient ? 'Kontrak Tempo B2B (Siap Rilis SJ)' : 'Menunggu Pembayaran Lunas')),
                'class' => $isSuratJalanIssued ? 'bg-surface-pill text-ink-body' : (($isPaid || $isContractClient) ? 'bg-accent text-success-ink outline outline-1 outline-success-deep font-bold' : 'bg-warning-soft text-warning-deep'),
            ],
            'tera'         => 'Verifikator: ' . ($order->verifier?->name ?: 'Sekretariat GPA'),
            'armada'       => $fleetName,
            'plate'        => $plateNumber,
            'driver'       => $driverName,
            'driver_id'    => $driver?->id,
            'sj'           => $suratJalanNumber,
            'receipt_url'  => route('prints.thermal-receipt', ['invoice' => $order->order_number]),
            'issued_at'    => ($order->updated_at ?: now())->format('d F Y (H:i') . ' WIB)',
            'handover_at'  => 'Gudang Pusat Sentra GPA',
            'auth_key'     => 'GPA-AUTH-' . substr(hash('sha256', $suratJalanNumber), 0, 8),
            'document'     => [
                'client'       => $clientName,
                'drop_point'   => $deliveryAddress,
                'address'      => $deliveryAddress,
                'pic'          => 'PIC Klien: ' . ($order->user?->name ?: 'Bagian Penerimaan') . ($order->user?->phone ? ' (' . $order->user->phone . ')' : ''),
                'armada'       => $fleetName,
                'plate'        => $plateNumber,
                'driver'       => $driverName,
                'sim'          => 'SIM: ' . ($driver?->phone ?: 'Terdata di Sistem'),
                'chiller'      => 'Cold Chain: 4°C - 6°C',
                'officer'      => $order->verifier?->name ?: 'Sekretaris Operasional',
                'officer_role' => 'Sekretariat / Otorisasi Legalitas',
                'officer_note' => 'Pembayaran lunas terverifikasi. Surat jalan sah untuk pengantaran.',
                'estimate'     => number_format($totalWeight, 1, ',', '.') . ' kg',
                'netto'        => number_format($totalWeight, 1, ',', '.') . ' kg',
                'delta'        => '0.0 kg',
                'lines'        => $cargoLines,
            ],
        ];
    }
}
