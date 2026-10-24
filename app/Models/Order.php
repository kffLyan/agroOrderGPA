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
        'target_delivery_date', 'delivery_address',
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

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Mengambil alasan penolakan pesanan dari catatan verifikasi pembayaran yang tersimpan
     */
    public function getRejectionReasonAttribute(): ?string
    {
        return $this->payments->where('status', 'DITOLAK')->first()?->verification_note
            ?? $this->payments->whereNotNull('verification_note')->first()?->verification_note;
    }

    /**
     * Memeriksa apakah pesanan ini sudah memiliki bukti pembayaran yang sedang diverifikasi
     */
    public function getHasPendingProofAttribute(): bool
    {
        return $this->payments
            ->where('status', 'MENUNGGU_VERIFIKASI')
            ->filter(fn ($p) => !empty($p->proof_url))
            ->isNotEmpty();
    }

    /**
     * Mengambil entitas pembayaran dengan bukti yang sedang menunggu verifikasi
     */
    public function getPendingProofPaymentAttribute(): ?Payment
    {
        return $this->payments
            ->where('status', 'MENUNGGU_VERIFIKASI')
            ->filter(fn ($p) => !empty($p->proof_url))
            ->first();
    }

    /**
     * Memeriksa apakah pesanan ini memiliki bukti pembayaran terakhir yang ditolak oleh verifikator
     */
    public function getHasRejectedProofAttribute(): bool
    {
        $latestPayment = $this->payments->sortByDesc('id')->first();

        return $latestPayment && in_array($latestPayment->status, ['DITOLAK', 'REJECTED']);
    }

    /**
     * Mengambil entitas pembayaran dengan bukti yang ditolak
     */
    public function getRejectedProofPaymentAttribute(): ?Payment
    {
        $latestPayment = $this->payments->sortByDesc('id')->first();

        return ($latestPayment && in_array($latestPayment->status, ['DITOLAK', 'REJECTED']))
            ? $latestPayment
            : null;
    }

    /**
     * Mengambil URL aset gambar bukti serah terima (PoD) yang valid dan dapat diakses publik
     */
    public function getPodPhotoAssetUrlAttribute(): ?string
    {
        if (!$this->pod_photo_url) {
            return null;
        }

        if (str_starts_with($this->pod_photo_url, 'http://') || str_starts_with($this->pod_photo_url, 'https://')) {
            return $this->pod_photo_url;
        }

        if (str_starts_with($this->pod_photo_url, 'uploads/') || str_starts_with($this->pod_photo_url, 'images/')) {
            return asset($this->pod_photo_url);
        }

        if (str_starts_with($this->pod_photo_url, 'storage/')) {
            return asset($this->pod_photo_url);
        }

        return asset('storage/' . $this->pod_photo_url);
    }
}

