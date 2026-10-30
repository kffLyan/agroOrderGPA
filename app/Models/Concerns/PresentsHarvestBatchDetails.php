<?php

namespace App\Models\Concerns;

trait PresentsHarvestBatchDetails
{
    public function getTicketAttribute(): string
    {
        $year = $this->created_at ? $this->created_at->format('Y') : date('Y');
        return 'TKT-' . $year . '-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getTimeFormattedAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('H:i') . ' WIB' : date('H:i') . ' WIB';
    }

    public function getGrossAttribute(): float
    {
        return (float) $this->initial_quantity + 20.0;
    }

    public function getTareAttribute(): float
    {
        return 20.0;
    }

    public function getNetAttribute(): float
    {
        return (float) $this->initial_quantity;
    }

    public function getQcAttribute(): string
    {
        return 'QC PASS';
    }

    public function getNoteAttribute(): string
    {
        return $this->source_type === 'BUFFER_LUAR' ? 'Buffer Luar' : 'Panen Binaan';
    }

    public function getSourceLabelAttribute(): string
    {
        return $this->note;
    }

    public function getNameAttribute(): string
    {
        return $this->supplier_name ?: 'Mitra Tani Binaan';
    }

    public function getCommodityAttribute(): string
    {
        return $this->product?->name ?? '—';
    }
}
