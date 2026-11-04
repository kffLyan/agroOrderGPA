<?php

namespace App\Models\Concerns;

trait HasProductStock
{
    // Menghitung total akumulasi stok fisik komoditas tersedia dari seluruh batch panen.
    public function getStockAttribute(): float
    {
        if ($this->relationLoaded('harvestBatches')) {
            return (float) $this->harvestBatches->sum('available_quantity');
        }
        return (float) $this->harvestBatches()->sum('available_quantity');
    }

    // Menghitung ketersediaan stok panen asal petani binaan koperasi di wilayah Ciwidey.
    public function getStockBudAttribute(): float
    {
        if ($this->relationLoaded('harvestBatches')) {
            return (float) $this->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        }
        return (float) $this->harvestBatches()->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
    }

    // Menghitung ketersediaan stok cadangan dari buffer luar konsolidasi mitra agribisnis.
    public function getStockBufferAttribute(): float
    {
        if ($this->relationLoaded('harvestBatches')) {
            return (float) $this->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        }
        return (float) $this->harvestBatches()->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
    }

    // Mengidentifikasi nama kelompok tani (poktan) pemasok panen komoditas.
    public function getSupplierNameAttribute(): string
    {
        if ($this->relationLoaded('harvestBatches')) {
            return $this->harvestBatches->first()?->supplier_name ?: 'Kelompok Tani Mitra';
        }
        return $this->harvestBatches()->first()?->supplier_name ?: 'Kelompok Tani Mitra';
    }

    // Alias atribut pasokan untuk kompatibilitas tampilan dashboard koordinator
    public function getSupplyAttribute(): float
    {
        return $this->stock_bud;
    }

    public function getBufferAttribute(): float
    {
        return $this->stock_buffer;
    }

    public function getTotalStockAttribute(): float
    {
        return $this->stock;
    }

    public function getGroupAttribute(): string
    {
        return $this->supplier_name;
    }

    public function getOrderAttribute(): float
    {
        return 0.0;
    }
}
