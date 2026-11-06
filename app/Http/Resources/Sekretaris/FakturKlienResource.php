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
                $netWeight = (float) $order->orderItems->sum(fn ($item) => $item->ordered_qty);
                $totalOrderAmount = (float) $order->grand_total;
                $commodityName = $order->orderItems->first()?->product?->name ?: 'Sayur Segar Grade A';
                $suratJalanNumber = $order->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT));

                $documents[] = [
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
        $creditLimit = $activeContract ? (float) ($activeContract->fixed_price_per_kg * $activeContract->committed_volume_per_cycle * 2) : 0;
        $usedCredit = (float) $client->orders()->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])->sum('grand_total');

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
