<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'invoice_id', 'payment_reference',
        'payment_method', 'amount', 'proof_url', 'status',
        'verified_by', 'paid_at', 'verification_note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusEnumAttribute(): ?\App\Enums\PaymentStatus
    {
        return \App\Enums\PaymentStatus::tryFrom((string) $this->status);
    }

    public function getMethodEnumAttribute(): ?\App\Enums\PaymentMethod
    {
        return \App\Enums\PaymentMethod::tryFrom((string) $this->payment_method);
    }

    /**
     * Format nominal pembayaran ke format mata uang Rupiah
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->amount ?? 0), 0, ',', '.');
    }
}
