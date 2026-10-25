<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'unit', 'grade', 'base_price',
        'minimum_order', 'image_url', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function harvestBatches(): HasMany
    {
        return $this->hasMany(HarvestBatch::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Resolusi URL/Path gambar komoditas secara dinamis murni dari database.
     * Menggunakan file unggahan atau preset yang tersimpan di kolom image_url.
     * Jika belum ada gambar, menggunakan default placeholder resmi GPA (gpaleaves.png).
     */
    public function getImagePathAttribute(): string
    {
        if (!empty($this->image_url)) {
            $clean = ltrim($this->image_url, '/');
            if (file_exists(public_path($clean))) {
                return $clean;
            }
            if (file_exists(public_path('storage/' . $clean))) {
                return 'storage/' . $clean;
            }
            if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
                return $this->image_url;
            }
        }

        return 'gpaleaves.png';
    }

    /**
     * Format harga dasar komoditas ke format mata uang Rupiah
     */
    public function getFormattedBasePriceAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->base_price ?? 0), 0, ',', '.');
    }

    /**
     * Format minimal order komoditas dengan satuan (kg/unit)
     */
    public function getFormattedMinimumOrderAttribute(): string
    {
        return number_format((float) ($this->minimum_order ?? 0), 0, ',', '.') . ' ' . ($this->unit ?: 'kg');
    }
}
