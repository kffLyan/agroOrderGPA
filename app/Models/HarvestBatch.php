<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'source_type', 'supplier_name',
        'batch_date', 'initial_quantity', 'available_quantity', 'inputted_by',
    ];

    protected function casts(): array
    {
        return [
            'batch_date' => 'date',
            'initial_quantity' => 'decimal:2',
            'available_quantity' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inputter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inputted_by');
    }
}
