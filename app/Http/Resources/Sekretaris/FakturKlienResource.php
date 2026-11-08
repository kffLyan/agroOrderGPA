<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FakturKlienResource extends JsonResource
{
    // Mengubah data Klien dan riwayat dokumen pengirimannya untuk formulir modal penerbitan faktur tagihan Sekretaris
    public function toArray(Request $request): array
    {
        $client = $this->resource;

        // Ambil daftar dokumen pengiriman untuk modal penerbitan faktur
        $documents = [];
        if ($client->relationLoaded('orders')) {
            foreach ($client->orders as $order) {
                // Lewati jika pesanan ini sudah menjadi bagian dari faktur konsolidasi resmi
                if ($order->invoice && $order->invoice->orders && $order->invoice->orders->count() > 1) {
                    continue;
                }

                $netWeight = (float) $order->orderItems->sum(fn ($item) => $item->ordered_qty);
                $totalOrderAmount = (float) $order->grand_total;
                $commodityName = $order->orderItems->first()?->product?->name ?: 'Sayur Segar Grade A';
                $suratJalanNumber = $order->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT));

                $documents[] = [
                    'order_id'     => $order->id,
                    'sj'           => $suratJalanNumber,
                    'date'         => ($order->created_at ?: now())->format('d M Y'),
                    'commodity'    => $commodityName,
                    'weight'       => $netWeight,
                    'weight_label' => number_format($netWeight, 0, ',', '.') . ' kg',
                    'pod'          => 'Telah Diterima & Sah',
                    'value'        => $totalOrderAmount,
                    'value_label'  => 'Rp ' . number_format($totalOrderAmount, 0, ',', '.'),
                ];
            }
        }

        // Ambil data kontrak aktif untuk menentukan batas TOP dan plafon kredit
        $activeContract = method_exists($client, 'contracts') ? $client->contracts()->where('status', 'ACTIVE')->first() : null;
        $isContractClient = ($client->client_type === 'B2B_KONTRAK' || $activeContract !== null);
        $topDays = $activeContract?->top_days ?: 30;
        $creditLimit = (float) ($activeContract?->credit_limit ?? 0);
        
        $unpaidInvoices = (float) \App\Models\Invoice::where('user_id', $client->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('grand_total');
        $uninvoicedOrders = (float) $client->orders()
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])
            ->whereNull('invoice_id')
            ->sum('grand_total');
        $usedCredit = $unpaidInvoices + $uninvoicedOrders;

        return [
            'id'          => (string) $client->id,
            'name'        => $client->company_name ?: ($client->name ?: 'Mitra Klien'),
            'terms_label' => 'Tempo ' . $topDays . ' Hari',
            'terms_days'  => $topDays,
            'npwp'        => '—',
            'is_contract' => $isContractClient,
            'contract'    => $activeContract?->contract_number ?: 'Non-Kontrak (Reguler)',
            'plafon'      => $creditLimit,
            'used'        => $usedCredit,
            'issue_date'  => now()->format('Y-m-d'),
            'documents'   => $documents,
        ];
    }
}
