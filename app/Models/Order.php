<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'invoice_id', 'order_source',
        'target_delivery_date', 'delivery_address', 'estimated_total',
        'grand_total', 'status', 'verified_by', 'surat_jalan_number',
        'driver_id', 'vehicle_plate_number', 'departure_time',
        'arrival_time', 'pod_photo_url', 'received_by_name',
    ];

    protected function casts(): array
    {
        return [
            'target_delivery_date' => 'date',
            'departure_time' => 'datetime',
            'arrival_time' => 'datetime',
            'estimated_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
