<?php

namespace App\Models\Concerns;

trait HasCreditProfile
{
    /**
     * Hitung plafon batas kredit belanja klien (B2B)
     */
    public function getCreditLimitAttribute(): float
    {
        if ($this->relationLoaded('contracts')) {
            $limit = (float) $this->contracts->where('status', 'ACTIVE')->sum(
                fn ($ct) => ($ct->committed_volume_per_cycle ?: 0) * ($ct->fixed_price_per_kg ?: 0)
            );
            return $limit > 0 ? $limit : 150000000.0;
        }

        $limit = (float) $this->contracts()->where('status', 'ACTIVE')->get()->sum(
            fn ($ct) => ($ct->committed_volume_per_cycle ?: 0) * ($ct->fixed_price_per_kg ?: 0)
        );

        return $limit > 0 ? $limit : 150000000.0;
    }

    /**
     * Total nilai kredit/tagihan yang sedang berjalan
     */
    public function getCreditUsedAttribute(): float
    {
        if ($this->relationLoaded('invoices')) {
            return (float) $this->invoices->whereIn('status', ['UNPAID', 'OVERDUE'])->sum('grand_total');
        }

        return (float) $this->invoices()->whereIn('status', ['UNPAID', 'OVERDUE'])->sum('grand_total');
    }
}
