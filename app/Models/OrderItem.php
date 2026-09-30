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
        'actual_net_weight', 'subtotal_final', 'weighed_by',
        'weighed_at', 'returned_weight', 'return_reason',
    ];

    protected function casts(): array
    {
        return [
            'ordered_qty' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'actual_net_weight' => 'decimal:2',
            'subtotal_final' => 'decimal:2',
            'returned_weight' => 'decimal:2',
            'weighed_at' => 'datetime',
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

    public function weigher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'weighed_by');
    }
}
