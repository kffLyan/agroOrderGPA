<?php

namespace App\Models\Concerns;

use App\Enums\OrderStatus;
use App\Models\Payment;

trait PresentsOrderDetails
{
    /**
     * Mengambil instance enum OrderStatus yang bertipe kuat
     */
    public function getStatusEnumAttribute(): ?OrderStatus
    {
        return OrderStatus::tryFrom((string) $this->status);
    }

    /**
     * Mengambil alasan penolakan pesanan dari catatan verifikasi pembayaran yang tersimpan
     */
    public function getRejectionReasonAttribute(): ?string
    {
        return $this->payments->where('status', 'DITOLAK')->first()?->verification_note
            ?? $this->payments->whereNotNull('verification_note')->first()?->verification_note;
    }

    /**
     * Memeriksa apakah pesanan ini sudah memiliki bukti pembayaran yang sedang diverifikasi
     */
    public function getHasPendingProofAttribute(): bool
    {
        return $this->payments
            ->where('status', 'MENUNGGU_VERIFIKASI')
            ->filter(fn ($p) => !empty($p->proof_url))
            ->isNotEmpty();
    }

    /**
     * Mengambil entitas pembayaran dengan bukti yang sedang menunggu verifikasi
     */
    public function getPendingProofPaymentAttribute(): ?Payment
    {
        return $this->payments
            ->where('status', 'MENUNGGU_VERIFIKASI')
            ->filter(fn ($p) => !empty($p->proof_url))
            ->first();
    }

    /**
     * Memeriksa apakah pesanan ini memiliki bukti pembayaran terakhir yang ditolak oleh verifikator
     */
    public function getHasRejectedProofAttribute(): bool
    {
        $latestPayment = $this->payments->sortByDesc('id')->first();

        return $latestPayment && in_array($latestPayment->status, ['DITOLAK', 'REJECTED']);
    }

    /**
     * Mengambil entitas pembayaran dengan bukti yang ditolak
     */
    public function getRejectedProofPaymentAttribute(): ?Payment
    {
        $latestPayment = $this->payments->sortByDesc('id')->first();

        return ($latestPayment && in_array($latestPayment->status, ['DITOLAK', 'REJECTED']))
            ? $latestPayment
            : null;
    }

    /**
     * Memformat path file foto menjadi URL aset yang valid
     */
    protected function formatPhotoAssetUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $trimmed = trim($path);
        if (empty($trimmed)) {
            return null;
        }

        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        if (str_starts_with($trimmed, 'uploads/') || str_starts_with($trimmed, 'images/')) {
            return asset($trimmed);
        }

        if (str_starts_with($trimmed, 'storage/')) {
            return asset($trimmed);
        }

        return asset('storage/' . $trimmed);
    }

    /**
     * Mengambil daftar URL aset seluruh foto bukti serah terima (PoD).
     * Mendukung format single URL maupun 2 foto dipisahkan koma (surat jalan & dock).
     *
     * @return array<int, string>
     */
    public function getPodPhotoAssetUrlsAttribute(): array
    {
        if (!$this->pod_photo_url) {
            return [];
        }

        $paths = explode(',', $this->pod_photo_url);
        $urls = [];

        foreach ($paths as $path) {
            $formatted = $this->formatPhotoAssetUrl($path);
            if ($formatted) {
                $urls[] = $formatted;
            }
        }

        return $urls;
    }

    /**
     * Mengambil URL aset foto bukti serah terima utama (kompatibilitas backward)
     */
    public function getPodPhotoAssetUrlAttribute(): ?string
    {
        $urls = $this->pod_photo_asset_urls;
        return $urls[0] ?? null;
    }

    /**
     * Foto Lembar Surat Jalan Berstempel & Tanda Tangan (Foto 1)
     */
    public function getPodManifestPhotoUrlAttribute(): ?string
    {
        $urls = $this->pod_photo_asset_urls;
        return $urls[0] ?? null;
    }

    /**
     * Foto Fisik Muatan & Pembongkaran di Dock (Foto 2)
     */
    public function getPodDockPhotoUrlAttribute(): ?string
    {
        $urls = $this->pod_photo_asset_urls;
        return $urls[1] ?? ($urls[0] ?? null);
    }

    /**
     * Format nilai grand total ke format mata uang Rupiah
     */
    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->grand_total ?? 0), 0, ',', '.');
    }

    /**
     * Mengambil seluruh string catatan verifikasi pembayaran terkait
     */
    public function getAllVerificationNotesAttribute(): string
    {
        if (!$this->relationLoaded('payments') && method_exists($this, 'payments')) {
            $this->load('payments');
        }
        return $this->payments ? $this->payments->pluck('verification_note')->filter()->implode(' • ') : '';
    }

    /**
     * Hitung subtotal komoditas yang dipesan
     */
    public function getSubtotalAmountAttribute(): float
    {
        $items = $this->orderItems ?? $this->items;
        if ($items && $items->isNotEmpty()) {
            $sum = (float) $items->sum(fn ($i) => ($i->subtotal ?: ($i->ordered_qty * $i->unit_price)));
            if ($sum > 0) {
                return $sum;
            }
        }

        return (float) ($this->invoice?->subtotal_amount ?? $this->grand_total);
    }

    /**
     * Biaya pengiriman / ongkir armada chiller
     */
    public function getShippingCostAttribute(): float
    {
        $notes = $this->all_verification_notes;
        if (preg_match('/Ongkir[^\d]*Rp\s*([\d\.,]+)/i', $notes, $m)) {
            $clean = str_replace(['.', ','], ['', '.'], $m[1]);
            return (float) $clean;
        }

        $taxAmount = 0.0;
        if (preg_match('/PPN \([^\)]+\) Rp\s*([\d\.,]+)/i', $notes, $mTax)) {
            $cleanTax = str_replace(['.', ','], ['', '.'], $mTax[1]);
            $taxAmount = (float) $cleanTax;
        } else {
            $taxAmount = $this->tax_amount;
        }

        $diff = (float) $this->grand_total - (float) $this->subtotal_amount - $taxAmount;
        if ($diff > 0.5) {
            return round($diff, 2);
        }

        return 0.0;
    }

    /**
     * Tarif Pajak PPN (persentase)
     */
    public function getTaxRateAttribute(): float
    {
        $notes = $this->all_verification_notes;
        if (preg_match('/PPN \((\d+(\.\d+)?)%\)/i', $notes, $m)) {
            return (float) $m[1];
        }

        if (class_exists(\App\Services\GovernanceService::class)) {
            $gov = app(\App\Services\GovernanceService::class);
            $tax = $gov->getTaxSettings();
            return $tax['enabled'] ? $tax['percent'] : 0.0;
        }

        return 0.0;
    }

    /**
     * Nilai nominal Pajak PPN
     */
    public function getTaxAmountAttribute(): float
    {
        $notes = $this->all_verification_notes;
        if (preg_match('/PPN \([^\)]+\) Rp\s*([\d\.,]+)/i', $notes, $m)) {
            $clean = str_replace(['.', ','], ['', '.'], $m[1]);
            return (float) $clean;
        }

        $rate = $this->tax_rate;
        if ($rate > 0) {
            return round($this->subtotal_amount * ($rate / 100), 2);
        }

        return 0.0;
    }

    /**
     * Label keterangan Pajak PPN
     */
    public function getTaxLabelAttribute(): string
    {
        return $this->tax_rate > 0
            ? "PPN {$this->tax_rate}%"
            : 'Bebas PPN 0% (DTP Pertanian)';
    }

    /**
     * Hitung total kuantitas seluruh komoditas yang dipesan dalam kilogram
     */
    public function getTotalOrderedQtyAttribute(): float
    {
        return (float) $this->orderItems->sum('ordered_qty');
    }

    /**
     * Format total kuantitas pesanan dengan satuan kg
     */
    public function getFormattedTotalOrderedQtyAttribute(): string
    {
        return number_format($this->total_ordered_qty, 1, ',', '.') . ' kg';
    }

    /**
     * Nama entitas pembeli/klien yang representatif
     */
    public function getClientNameAttribute(): string
    {
        return $this->user?->company_name ?: ($this->user?->name ?: 'Mitra Klien');
    }

    public function getPoAttribute(): string
    {
        return $this->order_number;
    }

    public function getStageAttribute(): string
    {
        return match ($this->status) {
            'MENUNGGU_VERIFIKASI'                      => 'verifikasi',
            'MENUNGGU_PEMBAYARAN', 'LUNAS'             => 'bayar',
            'SIAP_KIRIM'                               => 'kemas',
            'DALAM_PENGIRIMAN'                         => 'kirim',
            'SELESAI', 'SELESAI_CATATAN'               => 'selesai',
            'BATAL'                                    => 'batal',
            default                                    => 'verifikasi',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'MENUNGGU_PEMBAYARAN') {
            if ($this->has_rejected_proof) {
                return 'Bukti Bayar Ditolak';
            }
            if ($this->has_pending_proof) {
                return 'Menunggu Verifikasi Pembayaran';
            }
            return 'Menunggu Pembayaran';
        }

        return match ($this->status) {
            'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
            'LUNAS'               => 'Lunas Terverifikasi',
            'SIAP_KIRIM'          => 'Surat Jalan Terbit',
            'DALAM_PENGIRIMAN'    => 'Armada Menuju Dock',
            'SELESAI'             => 'Selesai Diterima Dock',
            'SELESAI_CATATAN'     => 'Selesai (Ada Catatan Retur)',
            'BATAL'               => 'Pesanan Dibatalkan',
            default               => (string) $this->status,
        };
    }

    public function getStatusToneAttribute(): string
    {
        if ($this->status === 'MENUNGGU_PEMBAYARAN') {
            return $this->has_rejected_proof ? 'danger' : ($this->has_pending_proof ? 'warning' : 'brand');
        }

        return match ($this->status) {
            'MENUNGGU_VERIFIKASI'        => 'warning',
            'LUNAS'                      => 'accent',
            'SIAP_KIRIM'                 => 'accent',
            'DALAM_PENGIRIMAN'           => 'brand',
            'SELESAI', 'SELESAI_CATATAN' => 'pill',
            'BATAL'                      => 'track',
            default                      => 'track',
        };
    }

    public function getStatusNoteAttribute(): string
    {
        if ($this->status === 'BATAL') {
            return 'Alasan: ' . ($this->rejection_reason ?: 'Ditolak Verifikasi Sekretariat');
        }
        if ($this->status === 'MENUNGGU_PEMBAYARAN') {
            if ($this->has_rejected_proof) {
                return 'Ditolak: ' . ($this->rejected_proof_payment?->verification_note ?: 'Bukti transfer tidak valid/ditolak. Silakan unggah ulang.');
            }
            if ($this->has_pending_proof) {
                return 'Bukti bayar terkirim ke Sekretaris • Menunggu verifikasi';
            }
            return 'Menunggu unggah bukti transfer';
        }
        if ($this->status === 'MENUNGGU_VERIFIKASI') {
            return 'Verifikasi < 2 Jam';
        }
        return 'Proses Operasional Berjalan';
    }

    public function getCommoditiesSummaryAttribute(): string
    {
        $items = $this->orderItems ?? $this->items;
        if (!$items || $items->isEmpty()) {
            return 'Komoditas Pertanian Segar';
        }
        return $items->map(function ($item) {
            return ($item->product?->name ?: 'Komoditas') . ' (' . number_format((float) $item->ordered_qty, 0, ',', '.') . ' kg)';
        })->join(', ');
    }

    public function getCommoditiesAttribute(): string
    {
        return $this->commodities_summary;
    }

    public function getDateAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d/m/Y') : '-';
    }

    public function getDateFormattedAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d M Y') : '-';
    }

    public function getTimeFormattedAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('H:i') . ' WIB' : '-';
    }

    public function getHubAttribute(): string
    {
        return 'lembang';
    }

    public function getHubChipAttribute(): string
    {
        return 'Sentra Lembang';
    }

    public function getSjAttribute(): ?string
    {
        return $this->surat_jalan_number;
    }

    public function getVehicleAttribute(): ?string
    {
        return $this->vehicle_plate_number ?: ($this->driver ? 'Truk Chiller GPA' : null);
    }

    public function getDriverNoteAttribute(): string
    {
        return $this->driver ? ("Supir: " . $this->driver->name) : 'Armada belum ditugaskan';
    }

    public function getShipmentNoteAttribute(): string
    {
        return $this->status === 'DALAM_PENGIRIMAN' ? 'ETA < 45 Menit Dock' : '';
    }

    public function getTotalChipAttribute(): string
    {
        return $this->invoice_id ? 'Faktur Terbit' : 'Belum Ditagihkan';
    }

    public function getTotalChipToneAttribute(): string
    {
        return $this->invoice_id ? 'success' : 'default';
    }

    public function getUrlAttribute(): string
    {
        return route('klien.pesanan.show', $this->id);
    }

    public function getDeviationPercentAttribute(): float
    {
        return 0.0;
    }

    public function getDeviationLabelAttribute(): string
    {
        return '0%';
    }

    public function getWeightLabelValueAttribute(): ?string
    {
        return number_format((float) $this->total_ordered_qty, 1, ',', '.');
    }

    public function getWeightChipAttribute(): ?string
    {
        return in_array($this->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'Netto Sah' : null;
    }

    public function getScaleNoteAttribute(): string
    {
        return in_array($this->status, ['SELESAI', 'SELESAI_CATATAN'])
            ? 'Sertifikat Timbangan Pasundan'
            : (in_array($this->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN']) ? 'Netto Sah Siap Kirim' : 'Menunggu sortir packing house');
    }

    public function getPackagingAttribute(): string
    {
        return $this->delivery_address ?: 'Dock Logistik Mitra';
    }

    public function getWeightAttribute(): ?string
    {
        return ((int) $this->total_ordered_qty > 0) ? (number_format((float) $this->total_ordered_qty, 0, ',', '.') . ' kg') : null;
    }

    public function getWeightNoteAttribute(): string
    {
        return in_array($this->status, ['SELESAI', 'SELESAI_CATATAN']) ? '(Netto Sah)' : '(Estimasi PO)';
    }
}
