<?php

namespace App\Models\Concerns;

trait PresentsOrderItemDetails
{
    /**
     * Format harga satuan item ke format mata uang Rupiah
     */
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->unit_price ?? 0), 0, ',', '.');
    }

    /**
     * Format subtotal item ke format mata uang Rupiah
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->subtotal ?? 0), 0, ',', '.');
    }

    /**
     * Format kuantitas pemesanan dengan satuan kg
     */
    public function getFormattedOrderedQtyAttribute(): string
    {
        return number_format((float) ($this->ordered_qty ?? 0), 1, ',', '.') . ' kg';
    }
}
