<?php

namespace App\Models\Concerns;

trait HasCreditProfile
{
    // Menghitung batas plafon kredit belanja klien B2B berdasarkan komitmen kontrak kerja sama aktif.
    public function getCreditLimitAttribute(): float
    {
        $activeContracts = $this->relationLoaded('contracts')
            ? $this->contracts->where('status', 'ACTIVE')
            : $this->contracts()->where('status', 'ACTIVE')->get();

        $limit = (float) $activeContracts->sum(
            fn ($contract) => ($contract->committed_volume_per_cycle ?: 0) * ($contract->fixed_price_per_kg ?: 0)
        );

        return $limit > 0 ? $limit : 150000000.0;
    }

    // Menghitung akumulasi nilai faktur tagihan yang sedang berjalan atau jatuh tempo.
    public function getCreditUsedAttribute(): float
    {
        $unpaidInvoices = $this->relationLoaded('invoices')
            ? $this->invoices->whereIn('status', ['UNPAID', 'OVERDUE'])
            : $this->invoices()->whereIn('status', ['UNPAID', 'OVERDUE'])->get();

        return (float) $unpaidInvoices->sum('grand_total');
    }
}
