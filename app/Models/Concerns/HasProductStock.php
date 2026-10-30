<?php

namespace App\Models\Concerns;

trait HasProductStock
{
    /**
     * Total stok fisik tersedia (gabungan petani binaan & buffer luar)
     */
    public function getStockAttribute(): float
    {
        if ($this->relationLoaded('harvestBatches')) {
            return (float) $this->harvestBatches->sum('available_quantity');
        }
        return (float) $this->harvestBatches()->sum('available_quantity');
    }

    /**
     * Ketersediaan stok dari petani binaan (BUD)
     */
    public function getStockBudAttribute(): float
    {
        if ($this->relationLoaded('harvestBatches')) {
            return (float) $this->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        }
        return (float) $this->harvestBatches()->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
    }

    /**
     * Ketersediaan stok dari buffer luar konsolidasi
     */
    public function getStockBufferAttribute(): float
    {
        if ($this->relationLoaded('harvestBatches')) {
            return (float) $this->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        }
        return (float) $this->harvestBatches()->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
    }

    /**
     * Nama kelompok tani pemasok utama
     */
    public function getSupplierNameAttribute(): string
    {
        if ($this->relationLoaded('harvestBatches')) {
            return $this->harvestBatches->first()?->supplier_name ?: 'Kelompok Tani Mitra';
        }
        return $this->harvestBatches()->first()?->supplier_name ?: 'Kelompok Tani Mitra';
    }
}
