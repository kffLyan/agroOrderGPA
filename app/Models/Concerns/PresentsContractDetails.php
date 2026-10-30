<?php

namespace App\Models\Concerns;

use App\Enums\ContractStatus;

trait PresentsContractDetails
{
    public function getStatusEnumAttribute(): ?ContractStatus
    {
        return ContractStatus::tryFrom((string) $this->status);
    }

    public function getClientDisplayNameAttribute(): string
    {
        return $this->user?->company_name ?: ($this->user?->name ?: 'Mitra B2B');
    }

    public function getProductNameAttribute(): string
    {
        return $this->product ? strtoupper($this->product->name) : 'SENTRA PRODUKSI';
    }

    public function getCommittedVolumeTonAttribute(): float
    {
        $volKg = (float) $this->committed_volume_per_cycle;
        $volTon = round($volKg / 1000, 2);
        return $volTon > 0 ? $volTon : round($volKg, 2);
    }

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
