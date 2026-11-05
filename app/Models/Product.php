<?php

namespace App\Models;

use App\Models\Concerns\HasProductStock;
use App\Models\Concerns\PresentsProductCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasProductStock, PresentsProductCatalog;

    protected $fillable = [
        'sku', 'name', 'unit', 'grade', 'base_price',
        'minimum_order', 'image_url', 'description', 'is_active',
    ];

    protected $appends = [
        'key',
        'category',
        'category_label',
        'latin',
        'image_path',
        'formatted_base_price',
        'formatted_minimum_order',
        'moq',
        'step',
        'initial_qty',
        'badges',
        'packaging',
        'botanical',
        'subtitle',
        'price',
        'price_label',
        'hub_code',
        'origin',
        'franco',
        'cold_chain',
        'crate_kg',
        'pack_label',
        'extra',
        'supply',
        'buffer',
        'total_stock',
        'group',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Relasi One-to-Many: Komoditas memiliki banyak catatan pasokan panen (HarvestBatch)
    public function harvestBatches(): HasMany
    {
        return $this->hasMany(HarvestBatch::class);
    }

    // Relasi One-to-Many: Komoditas dapat muncul di banyak detail pesanan (OrderItem)
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi One-to-Many: Komoditas dapat diikat dalam kontrak kemitraan B2B (Contract)
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
