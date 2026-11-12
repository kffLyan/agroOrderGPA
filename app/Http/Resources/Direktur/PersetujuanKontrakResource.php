<?php

namespace App\Http\Resources\Direktur;

use App\Models\Contract;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class PersetujuanKontrakResource extends JsonResource
{
    // Konstruktor resource persetujuan kontrak dengan parameter pengaturan margin dan tata kelola
    public function __construct(
        $resource,
        protected array $settings = []
    ) {
        parent::__construct($resource);
    }

    // Mengubah data pengajuan draf kontrak kemitraan menjadi format kartu evaluasi persetujuan Direktur
    public function toArray(Request $request): array
    {
        $contract = $this->resource;

        // Ambil data klien, produk, dan parameter harga
        $clientUser            = $contract->user;
        $clientName            = $clientUser ? ($clientUser->company_name ?: $clientUser->name) : 'Klien Mitra';
        $productName           = $contract->product ? $contract->product->name : 'Komoditas Inti';
        $committedVolume       = (float) ($contract->committed_volume_per_cycle ?: 0);
        $catalogPrice          = (float) ($contract->product?->base_price ?: 0);
        $agreedPrice           = (float) ($contract->fixed_price_per_kg ?: $catalogPrice);
        $estimatedContractValue = $committedVolume * $agreedPrice;
        $topDays               = (int) ($contract->top_days ?: 30);
        $minimumMarginThreshold = (float) ($this->settings['min_margin_percent'] ?? 15.0);

        // Identifikasi wilayah pengiriman dan PIC klien
        $deliveryRegion = $clientUser?->address
            ? strtoupper(trim(explode(',', $clientUser->address)[0]))
            : 'BANDUNG RAYA';
        $applicantName = $clientUser?->pic_name ?: ($clientUser?->name ?: 'Mitra Pemohon');
        $clientSegment = ($clientUser?->client_type === 'B2B_KONTRAK') ? 'Kemitraan B2B Prioritas' : 'Pengajuan Kemitraan Baru';

        // Hitung persentase diskon dari harga katalog
        $hasDiscount     = ($catalogPrice > 0 && $agreedPrice < $catalogPrice);
        $discountPercent = $hasDiscount ? round((($catalogPrice - $agreedPrice) / $catalogPrice) * 100, 1) : null;

        // Hitung estimasi margin keuntungan Koperasi (asumsi COGS 75%)
        $estimatedCogs = $catalogPrice > 0 ? ($catalogPrice * 0.75) : ($agreedPrice * 0.75);
        $marginValue   = $agreedPrice > 0 ? round((($agreedPrice - $estimatedCogs) / $agreedPrice) * 100, 1) : $minimumMarginThreshold;
        $marginDelta   = round($marginValue - $minimumMarginThreshold, 1);
        $marginStatus  = $marginDelta >= 0 ? 'Safe Margin Pass' : 'Margin Review Required';

        // Hitung skor kelayakan kredit dari riwayat pesanan selesai
        $completedOrdersCount = $clientUser ? $clientUser->orders()->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count() : 0;
        $creditScore          = min(95, 70 + ($completedOrdersCount * 5));
        $creditGrade          = $creditScore >= 85 ? 'A' : ($creditScore >= 75 ? 'B' : 'C');
        $creditNote           = $completedOrdersCount > 0 ? "Riwayat Transaksi: {$completedOrdersCount} Pesanan Selesai" : 'Klien Baru / Verifikasi Pertama';

        // Kelompokkan kategori filter persetujuan kontrak
        $filterCategory = 'annual';
        if ($agreedPrice < 14000 || $hasDiscount) {
            $filterCategory = 'discount';
        } elseif ($topDays > 30) {
            $filterCategory = 'top';
        }

        // Format nomor telepon untuk tautan WhatsApp konfirmasi
        $rawPhoneNumber   = $clientUser?->pic_phone ?: ($clientUser?->phone ?: '');
        $cleanPhoneNumber = preg_replace('/[^0-9]/', '', (string) $rawPhoneNumber);
        if (str_starts_with($cleanPhoneNumber, '0')) {
            $whatsappNumber = '62' . substr($cleanPhoneNumber, 1);
        } elseif (str_starts_with($cleanPhoneNumber, '62')) {
            $whatsappNumber = $cleanPhoneNumber;
        } else {
            $whatsappNumber = $cleanPhoneNumber ? ('62' . $cleanPhoneNumber) : null;
        }

        $whatsappMessage = urlencode("Halo Bapak/Ibu {$applicantName} ({$clientName}), kami dari Manajemen Green Pasundan Agriculture terkait verifikasi pengajuan Kontrak Kemitraan B2B {$contract->contract_number} ({$productName}).");
        $whatsappLink    = $whatsappNumber ? "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}" : null;

        return [
            'id'               => $contract->id,
            'contract'         => $contract->contract_number,
            'region'           => $deliveryRegion,
            'client'           => $clientName,
            'client_tone'      => 'success',
            'submitted_by'     => $applicantName,
            'pic_phone'        => $rawPhoneNumber ?: null,
            'wa_link'          => $whatsappLink,
            'submitted_at'     => $contract->created_at ? $contract->created_at->translatedFormat('d M Y') : date('d M Y'),
            'commodity'        => $productName,
            'volume'           => number_format($committedVolume, 0, ',', '.') . ' kg / siklus',
            'segment'          => $clientSegment,
            'price'            => 'Rp ' . number_format($agreedPrice, 0, ',', '.') . ' / kg',
            'price_strike'     => $hasDiscount ? ('Rp ' . number_format($catalogPrice, 0, ',', '.') . ' / kg') : null,
            'list_price'       => $catalogPrice > 0 ? ('Rp ' . number_format($catalogPrice, 0, ',', '.')) : null,
            'offer_price'      => 'Rp ' . number_format($agreedPrice, 0, ',', '.'),
            'discount_percent' => $discountPercent ? ($discountPercent . '%') : null,
            'discount_ceiling' => 'Maks 15%',
            'exception'        => $topDays > 30 ? "Ekstensi TOP {$topDays} Hari" : 'Pengajuan Standar',
            'exception_tone'   => $topDays > 30 ? 'caution' : 'neutral',
            'term'             => "TOP {$topDays} Hari",
            'top_days'         => $topDays,
            'top_default'      => (int) ($this->settings['default_top_days'] ?? 30),
            'margin'           => $marginValue . '% Net Margin',
            'margin_value'     => $marginValue,
            'margin_minimum'   => $minimumMarginThreshold,
            'margin_delta'     => $marginDelta,
            'margin_flag'      => $marginStatus,
            'score'            => "{$creditGrade} ({$creditScore}/100)",
            'score_note'       => $creditNote,
            'approve_label'    => 'Setujui Kontrak',
            'approve_tone'     => 'solid',
            'filter_key'       => $filterCategory,
            'value'            => $estimatedContractValue,
            'value_label'      => 'Rp ' . number_format($estimatedContractValue, 0, ',', '.'),
            'exceeds_plafon'   => $topDays > 30 || $agreedPrice < 14000,
        ];
    }
}
