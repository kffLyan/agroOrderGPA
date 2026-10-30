<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    use \App\Models\Concerns\PresentsContractDetails;

    protected $fillable = [
        'user_id',
        'product_id',
        'contract_number',
        'fixed_price_per_kg',
        'top_days',
        'committed_volume_per_cycle',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $appends = [
        'client_display_name',
        'product_name',
        'committed_volume_ton',
        'fixed_price_formatted',
        'status_label',
        'name',
        'sentra',
        'commitment',
        'priority',
    ];

    protected $casts = [
        'top_days' => 'integer',
        'fixed_price_per_kg' => 'decimal:2',
        'committed_volume_per_cycle' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
