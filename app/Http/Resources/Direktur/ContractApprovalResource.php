<?php

namespace App\Http\Resources\Direktur;

use App\Models\Contract;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ContractApprovalResource extends JsonResource
{
    /**
     * @param Contract $resource
     * @param array<string, mixed> $settings
     */
    public function __construct(
        $resource,
        protected array $settings = []
    ) {
        parent::__construct($resource);
    }

    /**
     * Transform the contract resource into an approval card representation for Direktur.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Contract $c */
        $c = $this->resource;

        $clientUser = $c->user;
        $clientName = $clientUser ? ($clientUser->company_name ?: $clientUser->name) : 'Klien Mitra';
        $prodName = $c->product ? $c->product->name : 'Komoditas Inti';
        $vol = (float) ($c->committed_volume_per_cycle ?: 0);
        $basePrice = (float) ($c->product?->base_price ?: 0);
        $price = (float) ($c->fixed_price_per_kg ?: $basePrice);
        $contractVal = $vol * $price;
        $top = (int) ($c->top_days ?: 30);
        $minMarginSetting = (float) ($this->settings['min_margin_percent'] ?? 15.0);

        $region = $clientUser?->address
            ? strtoupper(trim(explode(',', $clientUser->address)[0]))
            : 'BANDUNG RAYA';
        $submittedBy = $clientUser?->pic_name ?: ($clientUser?->name ?: 'Mitra Pemohon');
        $segment = ($clientUser?->client_type === 'B2B_KONTRAK') ? 'Kemitraan B2B Prioritas' : 'Pengajuan Kemitraan Baru';

        $hasDiscount = ($basePrice > 0 && $price < $basePrice);
        $discountPercent = $hasDiscount ? round((($basePrice - $price) / $basePrice) * 100, 1) : null;

        $estimatedCost = $basePrice > 0 ? ($basePrice * 0.75) : ($price * 0.75);
        $marginValue = $price > 0 ? round((($price - $estimatedCost) / $price) * 100, 1) : $minMarginSetting;
        $marginDelta = round($marginValue - $minMarginSetting, 1);
        $marginFlag = $marginDelta >= 0 ? 'Safe Margin Pass' : 'Margin Review Required';

        $settledOrdersCount = $clientUser ? $clientUser->orders()->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count() : 0;
        $scoreValue = min(95, 70 + ($settledOrdersCount * 5));
        $scoreGrade = $scoreValue >= 85 ? 'A' : ($scoreValue >= 75 ? 'B' : 'C');
        $scoreNote = $settledOrdersCount > 0 ? "Riwayat Transaksi: {$settledOrdersCount} Pesanan Selesai" : 'Klien Baru / Verifikasi Pertama';

        $filterKey = 'annual';
        if ($price < 14000 || $hasDiscount) {
            $filterKey = 'discount';
        } elseif ($top > 30) {
            $filterKey = 'top';
        }

        $rawPhone = $clientUser?->pic_phone ?: ($clientUser?->phone ?: '');
        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$rawPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $waPhone = '62' . substr($cleanPhone, 1);
        } elseif (str_starts_with($cleanPhone, '62')) {
            $waPhone = $cleanPhone;
        } else {
            $waPhone = $cleanPhone ? ('62' . $cleanPhone) : null;
        }

        $waText = urlencode("Halo Bapak/Ibu {$submittedBy} ({$clientName}), kami dari Manajemen Green Pasundan Agriculture terkait verifikasi pengajuan Kontrak Kemitraan B2B {$c->contract_number} ({$prodName}).");
        $waLink = $waPhone ? "https://wa.me/{$waPhone}?text={$waText}" : null;

        return [
            'id' => $c->id,
            'contract' => $c->contract_number,
            'region' => $region,
            'client' => $clientName,
            'client_tone' => 'success',
            'submitted_by' => $submittedBy,
            'pic_phone' => $rawPhone ?: null,
            'wa_link' => $waLink,
            'submitted_at' => $c->created_at ? $c->created_at->translatedFormat('d M Y') : date('d M Y'),
            'commodity' => $prodName,
            'volume' => number_format($vol, 0, ',', '.') . ' kg / siklus',
            'segment' => $segment,
            'price' => 'Rp ' . number_format($price, 0, ',', '.') . ' / kg',
            'price_strike' => $hasDiscount ? ('Rp ' . number_format($basePrice, 0, ',', '.') . ' / kg') : null,
            'list_price' => $basePrice > 0 ? ('Rp ' . number_format($basePrice, 0, ',', '.')) : null,
            'offer_price' => 'Rp ' . number_format($price, 0, ',', '.'),
            'discount_percent' => $discountPercent ? ($discountPercent . '%') : null,
            'discount_ceiling' => 'Maks 15%',
            'exception' => $top > 30 ? "Ekstensi TOP {$top} Hari" : 'Pengajuan Standar',
            'exception_tone' => $top > 30 ? 'caution' : 'neutral',
            'term' => "TOP {$top} Hari",
            'top_days' => $top,
            'top_default' => (int) ($this->settings['default_top_days'] ?? 30),
            'margin' => $marginValue . '% Net Margin',
            'margin_value' => $marginValue,
            'margin_minimum' => $minMarginSetting,
            'margin_delta' => $marginDelta,
            'margin_flag' => $marginFlag,
            'score' => "{$scoreGrade} ({$scoreValue}/100)",
            'score_note' => $scoreNote,
            'approve_label' => 'Setujui Kontrak',
            'approve_tone' => 'solid',
            'filter_key' => $filterKey,
            'value' => $contractVal,
            'value_label' => 'Rp ' . number_format($contractVal, 0, ',', '.'),
            'exceeds_plafon' => $top > 30 || $price < 14000,
        ];
    }
}
