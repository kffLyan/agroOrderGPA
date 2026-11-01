<?php

namespace App\Models\Concerns;

use App\Enums\ContractStatus;

trait PresentsContractDetails
{
    // Mengonversi status string kontrak ke tipe ContractStatus Enum resmi
    public function getStatusEnumAttribute(): ?ContractStatus
    {
        return ContractStatus::tryFrom((string) $this->status);
    }

    // Mengambil nama tampilan identitas mitra atau badan usaha klien kontrak
    public function getClientDisplayNameAttribute(): string
    {
        return $this->user?->company_name ?: ($this->user?->name ?: 'Mitra B2B');
    }

    // Mengambil nama komoditas sayur yang diikat dalam kontrak perjanjian kerja sama
    public function getProductNameAttribute(): string
    {
        return $this->product ? strtoupper($this->product->name) : 'SENTRA PRODUKSI';
    }

    // Mengonversi komitmen volume pasokan per siklus dari kilogram ke satuan ton
    public function getCommittedVolumeTonAttribute(): float
    {
        $volumeKg = (float) $this->committed_volume_per_cycle;
        $volumeTon = round($volumeKg / 1000, 2);
        return $volumeTon > 0 ? $volumeTon : round($volumeKg, 2);
    }

    // Memformat nilai harga tetap per kg kontrak ke format mata uang Rupiah
    public function getFixedPriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->fixed_price_per_kg ?? 0), 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'ACTIVE'     => 'Aktif',
            'PENDING'    => 'Menunggu Persetujuan',
            'TERMINATED' => 'Dihentikan',
            'REJECTED'   => 'Ditolak',
            default      => (string) $this->status,
        };
    }

    public function getNameAttribute(): string
    {
        return $this->client_display_name;
    }

    public function getSentraAttribute(): string
    {
        return $this->product_name;
    }

    public function getCommitmentAttribute(): float
    {
        return $this->committed_volume_ton;
    }

    public function getPriorityAttribute(): string
    {
        return 'KONTRAK AKTIF';
    }
}
