<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;
    use \App\Models\Concerns\PresentsOrderDetails;

    protected $fillable = [
        'order_number', 'user_id', 'invoice_id', 'order_source',
        'target_delivery_date', 'delivery_address',
        'grand_total', 'status', 'verified_by', 'surat_jalan_number',
        'driver_id', 'vehicle_plate_number', 'departure_time',
        'arrival_time', 'pod_photo_url', 'received_by_name',
    ];

    protected $appends = [
        'stage',
        'status_label',
        'status_tone',
        'status_note',
        'commodities_summary',
        'total_ordered_qty',
        'po',
        'date_formatted',
        'time_formatted',
        'hub',
        'hub_chip',
        'sj',
        'vehicle',
        'driver_note',
        'shipment_note',
        'total_chip',
        'total_chip_tone',
        'url',
        'deviation_percent',
        'deviation_label',
        'weight_label_value',
        'weight_chip',
        'scale_note',
    ];

    protected function casts(): array
    {
        return [
            'target_delivery_date' => 'date',
            'departure_time' => 'datetime',
            'arrival_time' => 'datetime',
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

    /**
     * Alias untuk relasi orderItems() demi kompatibilitas antarmuka
     */
    public function items(): HasMany
    {
        return $this->orderItems();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}

