<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

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
