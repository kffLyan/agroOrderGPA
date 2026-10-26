<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceClientResource extends JsonResource
{
    /**
     * Transformasi data Klien dan dokumen pengirimannya untuk modal penerbitan faktur
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $c = $this->resource;

        $docs = [];
        if ($c->relationLoaded('orders')) {
            foreach ($c->orders as $ord) {
                $netWeight = (float) $ord->orderItems->sum(fn ($it) => $it->ordered_qty);
                $val = (float) $ord->grand_total;
                $commodity = $ord->orderItems->first()?->product?->name ?: 'Sayur Segar Grade A';
                $sjNum = $ord->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad((string) $ord->id, 4, '0', STR_PAD_LEFT));

                $docs[] = [
                    'sj'           => $sjNum,
                    'date'         => ($ord->created_at ?: now())->format('d M Y'),
                    'commodity'    => $commodity,
                    'weight'       => $netWeight,
                    'weight_label' => number_format($netWeight, 0, ',', '.') . ' kg',
                    'pod'          => 'Telah Diterima & Sah',
                    'value'        => $val,
                    'value_label'  => 'Rp ' . number_format($val, 0, ',', '.'),
                ];
            }
        }

        $contractObj = method_exists($c, 'contracts') ? $c->contracts()->where('status', 'ACTIVE')->first() : null;
        $topDays = $contractObj?->top_days ?: 30;
        $plafon = $contractObj ? (float) ($contractObj->fixed_price_per_kg * $contractObj->committed_volume_per_cycle * 2) : 0;
        $usedPlafon = (float) $c->orders()->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])->sum('grand_total');

        return [
            'id'          => (string) $c->id,
            'name'        => $c->company_name ?: ($c->name ?: 'Mitra Klien'),
            'terms_label' => 'Tempo ' . $topDays . ' Hari',
            'terms_days'  => $topDays,
            'npwp'        => '—',
            'contract'    => $contractObj?->contract_number ?: ('KONTRAK-' . str_pad((string) $c->id, 3, '0', STR_PAD_LEFT)),
            'plafon'      => $plafon,
            'used'        => $usedPlafon,
            'issue_date'  => now()->format('Y-m-d'),
            'documents'   => $docs,
        ];
    }
}
