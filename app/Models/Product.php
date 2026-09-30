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
}
