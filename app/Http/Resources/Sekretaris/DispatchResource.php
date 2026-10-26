<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DispatchResource extends JsonResource
{
    /**
     * Transformasi pesanan ke format baris konsol Surat Jalan untuk Alpine.js
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $order = $this->resource;
        $clientName = $order->user?->company_name ?: ($order->user?->name ?: 'Mitra Klien GPA');
        $address = $order->delivery_address ?: 'Gudang Utama Klien';
        $totalWeight = (float) $order->orderItems->sum('ordered_qty');

        $driver = $order->driver;
        $driverName = $driver?->name ?: 'Belum Ditugaskan';
        $plateNumber = $order->vehicle_plate_number ?: '—';
        $armadaName = $driver ? 'Armada Logistik GPA' : 'Menunggu Penugasan';

        $sjNumber = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT));

        $isPaid = ($order->status === 'LUNAS');
        $isIssued = in_array($order->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']);

        $itemLines = $order->orderItems->map(function ($it, $lineIdx) {
            $qty = (float) $it->ordered_qty;
            return [
                'no'        => $lineIdx + 1,
                'commodity' => $it->product?->name ?: 'Komoditas Sayur',
                'lot'       => 'Wadah Krat Standar GPA',
                'estimate'  => number_format($qty, 1, ',', '.') . ' kg',
                'netto'     => number_format($qty, 1, ',', '.') . ' kg',
                'delta'     => '0.0 kg (Tagihan Pasti)',
            ];
        })->all();

        return [
            'order_id'     => $order->id,
            'id'           => $order->order_number,
            'client'       => $clientName,
            'warehouse'    => 'Gudang Pusat Sentra GPA',
            'dock'         => $address,
            'state'        => $isPaid ? 'ready' : ($isIssued ? 'issued' : 'locked'),
            'order_status' => $order->status,
            'estimate'     => number_format($totalWeight, 1, ',', '.') . ' kg',
            'netto'        => number_format($totalWeight, 1, ',', '.') . ' kg',
            'deviation'    => 'Lunas Terverifikasi',
            'revision'     => null,
            'status_chip'  => [
                'label' => $isIssued ? 'Surat Jalan Terbit (SIAP KIRIM)' : ($isPaid ? 'Pembayaran LUNAS (Siap Rilis SJ)' : 'Menunggu Pembayaran Lunas'),
                'class' => $isIssued ? 'bg-surface-pill text-ink-body' : ($isPaid ? 'bg-accent text-success-ink outline outline-1 outline-success-deep font-bold' : 'bg-warning-soft text-warning-deep'),
            ],
            'tera'         => 'Verifikator: ' . ($order->verifier?->name ?: 'Sekretariat GPA'),
            'armada'       => $armadaName,
            'plate'        => $plateNumber,
            'driver'       => $driverName,
            'driver_id'    => $driver?->id,
            'sj'           => $sjNumber,
            'issued_at'    => ($order->updated_at ?: now())->format('d F Y (H:i') . ' WIB)',
            'handover_at'  => 'Gudang Pusat Sentra GPA',
            'auth_key'     => 'GPA-AUTH-' . substr(hash('sha256', $sjNumber), 0, 8),
            'document'     => [
                'client'       => $clientName,
                'drop_point'   => $address,
                'address'      => $address,
                'pic'          => 'PIC Klien: ' . ($order->user?->name ?: 'Bagian Penerimaan') . ($order->user?->phone ? ' (' . $order->user->phone . ')' : ''),
                'armada'       => $armadaName,
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
                'lines'        => $itemLines,
            ],
        ];
    }
}
