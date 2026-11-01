<?php

namespace App\Models;

use App\Models\Concerns\PresentsPaymentDetails;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, PresentsPaymentDetails;

    protected $fillable = [
        'order_id', 'invoice_id', 'payment_reference',
        'payment_method', 'amount', 'proof_url', 'status',
        'verified_by', 'paid_at', 'verification_note',
    ];

    protected $appends = [
        'code',
        'status_label',
        'status_tone',
        'receipt_code',
        'date_formatted',
        'amount_formatted',
        'receipt_pending',
        'row_tone',
        'amount_class',
        'status_class',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // Relasi Many-to-One: Pembayaran melunasi transaksi pesanan (Order)
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi Many-to-One: Pembayaran melunasi faktur tagihan (Invoice)
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // Relasi Many-to-One: Pembayaran diverifikasi oleh Sekretaris (User)
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
