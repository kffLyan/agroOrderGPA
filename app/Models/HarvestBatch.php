<?php

namespace App\Models;

use App\Models\Concerns\PresentsHarvestBatchDetails;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HarvestBatch extends Model
{
    use HasFactory, PresentsHarvestBatchDetails;

    protected $fillable = [
        'product_id', 'source_type', 'supplier_name',
        'batch_date', 'initial_quantity', 'grade', 'qc_notes',
        'available_quantity', 'inputted_by',
    ];

    protected function casts(): array
    {
        return [
            'batch_date' => 'date',
            'initial_quantity' => 'decimal:2',
            'available_quantity' => 'decimal:2',
        ];
    }

    protected $appends = [
        'ticket',
        'time_formatted',
        'gross',
        'tare',
        'net',
        'qc',
        'note',
        'source_label',
        'name',
        'commodity',
    ];

    // Relasi Many-to-One: Batch panen merujuk ke data master sayur (Product)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi Many-to-One: Batch panen diinput oleh Koordinator Lapangan (User)
    public function inputter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inputted_by');
    }
}
