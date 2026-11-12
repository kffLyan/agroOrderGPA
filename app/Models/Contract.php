<?php

namespace App\Models;

use App\Models\Concerns\PresentsContractDetails;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory, PresentsContractDetails;

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
        'credit_limit',
    ];

    protected $casts = [
        'top_days' => 'integer',
        'fixed_price_per_kg' => 'decimal:2',
        'committed_volume_per_cycle' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Relasi Many-to-One: Kontrak kemitraan dimiliki oleh Klien B2B (User)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi Many-to-One: Komoditas panen yang diikat dalam perjanjian kontrak (Product)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi Many-to-One: Pejabat direksi yang mengesahkan kontrak kemitraan (User)
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
