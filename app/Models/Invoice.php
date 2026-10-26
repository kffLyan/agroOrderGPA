<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'user_id', 'period_start', 'period_end',
        'subtotal_amount', 'grand_total', 'due_date', 'status', 'pdf_file_path',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'subtotal_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Memeriksa apakah faktur ini sudah memiliki bukti pembayaran yang sedang diverifikasi
     */
    public function getHasPendingProofAttribute(): bool
    {
        return $this->payments
            ->where('status', 'MENUNGGU_VERIFIKASI')
            ->filter(fn ($p) => !empty($p->proof_url))
            ->isNotEmpty();
    }

    /**
     * Memeriksa apakah faktur ini memiliki bukti pembayaran terakhir yang ditolak oleh verifikator
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
     * Format total tagihan faktur ke format mata uang Rupiah
     */
    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->grand_total ?? 0), 0, ',', '.');
    }

    /**
     * Format subtotal tagihan ke format mata uang Rupiah
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->subtotal_amount ?? 0), 0, ',', '.');
    }
}
