<?php

namespace App\Models;

use App\Models\Concerns\PresentsInvoiceDetails;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, PresentsInvoiceDetails;

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

    // Relasi Many-to-One: Faktur ditujukan kepada Klien (User)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi One-to-Many: Faktur mencakup transaksi pesanan terkait (Order)
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Relasi One-to-Many: Faktur memiliki riwayat pembayaran (Payment)
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
