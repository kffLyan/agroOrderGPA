<?php

namespace App\Models;

use App\Models\Concerns\PresentsOrderItemDetails;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, PresentsOrderItemDetails;

    protected $fillable = [
        'order_id', 'product_id', 'ordered_qty', 'unit_price',
        'subtotal', 'returned_weight', 'return_reason',
    ];

    protected function casts(): array
    {
        return [
            'ordered_qty' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'returned_weight' => 'decimal:2',
        ];
    }

    // Relasi Many-to-One: Item terikat pada pesanan induk (Order)
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi Many-to-One: Item merujuk ke data master sayur (Product)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
