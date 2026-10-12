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

    public function getImagePathAttribute(): string
    {
        $nameLower = strtolower($this->name ?? '');

        if (str_contains($nameLower, 'selada') || str_contains($nameLower, 'lettuce') || str_contains($nameLower, 'romaine')) {
            return 'lettuce.jpg';
        } elseif (str_contains($nameLower, 'brokoli') || str_contains($nameLower, 'broccoli')) {
            return 'broccoli.jpg';
        } elseif (str_contains($nameLower, 'tomat') || str_contains($nameLower, 'tomato')) {
            return 'tomato.jpg';
        } elseif (str_contains($nameLower, 'stroberi') || str_contains($nameLower, 'strawberry') || str_contains($nameLower, 'berry')) {
            return 'strawberry.jpg';
        } elseif (str_contains($nameLower, 'kol') || str_contains($nameLower, 'cabbage') || str_contains($nameLower, 'kubis')) {
            return 'cabbage.jpg';
        } elseif ($this->image_url && file_exists(public_path($this->image_url))) {
            return $this->image_url;
        } elseif ($this->image_url && file_exists(public_path('storage/' . $this->image_url))) {
            return 'storage/' . $this->image_url;
        }

        return 'gpaleaves.png';
    }
}
