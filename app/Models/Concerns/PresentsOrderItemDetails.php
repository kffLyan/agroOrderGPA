<?php

namespace App\Models\Concerns;

trait PresentsOrderItemDetails
{
    // Memformat harga satuan komoditas item ke format mata uang resmi Rupiah.
    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->unit_price ?? 0), 0, ',', '.');
    }

    // Memformat subtotal nilai belanja item ke format mata uang resmi Rupiah.
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->subtotal ?? 0), 0, ',', '.');
    }

    // Memformat kuantitas pemesanan item komoditas dengan satuan kilogram.
    public function getFormattedOrderedQtyAttribute(): string
    {
        return number_format((float) ($this->ordered_qty ?? 0), 1, ',', '.') . ' kg';
    }
}
