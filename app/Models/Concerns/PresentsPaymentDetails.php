<?php

namespace App\Models\Concerns;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;

trait PresentsPaymentDetails
{
    public function getStatusEnumAttribute(): ?PaymentStatus
    {
        return PaymentStatus::tryFrom((string) $this->status);
    }

    public function getMethodEnumAttribute(): ?PaymentMethod
    {
        return PaymentMethod::tryFrom((string) $this->payment_method);
    }

    /**
     * Format nominal pembayaran ke format mata uang Rupiah
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->amount ?? 0), 0, ',', '.');
    }

    public function getAmountFormattedAttribute(): string
    {
        return $this->formatted_amount;
    }

    public function getCodeAttribute(): string
    {
        return 'PAY-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'LUNAS', 'VERIFIED'   => 'Terverifikasi Sah',
            'DITOLAK', 'REJECTED' => 'Ditolak',
            default               => 'Menunggu Verifikasi',
        };
    }

    public function getStatusToneAttribute(): string
    {
        return match ($this->status) {
            'LUNAS', 'VERIFIED'   => 'success',
            'DITOLAK', 'REJECTED' => 'danger',
            default               => 'warning',
        };
    }

    public function getReceiptCodeAttribute(): string
    {
        return in_array($this->status, ['LUNAS', 'VERIFIED'])
            ? ('KWT-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT))
            : 'Menunggu Approval';
    }

    public function getDateFormattedAttribute(): string
    {
        return $this->paid_at ? $this->paid_at->format('d/m/Y H:i') : ($this->created_at ? $this->created_at->format('d/m/Y') : '-');
    }

    public function getInvoiceCodeAttribute(): string
    {
        return $this->invoice?->invoice_number ?: ($this->order?->order_number ?: '-');
    }

    public function getVerifierNameAttribute(): string
    {
        return $this->verifier?->name ?: 'Antrean Sistem';
    }

    public function getVerifierPendingAttribute(): bool
    {
        return ! (bool) $this->verifier_id;
    }

    public function getReceiptPendingAttribute(): bool
    {
        return ! in_array($this->status, ['LUNAS', 'VERIFIED']);
    }

    public function getRowToneAttribute(): string
    {
        return in_array($this->status, ['LUNAS', 'VERIFIED']) ? 'plain' : 'muted';
    }

    public function getAmountClassAttribute(): string
    {
        return in_array($this->status, ['DITOLAK', 'FAILED']) ? 'text-danger' : 'text-ink';
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'LUNAS', 'VERIFIED', 'SUCCESS' => 'bg-accent text-ink outline outline-1 outline-success-deep',
            'DITOLAK', 'FAILED'           => 'bg-danger-soft text-danger outline outline-1 outline-danger/60',
            default                        => 'bg-surface-track text-ink-body outline outline-1 outline-line-board',
        };
    }

    public function getOperatorAttribute(): string
    {
        return $this->verifier?->name ?: 'SK-001';
    }

    public function getTimestampAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d/m H:i') : date('d/m H:i');
    }

    public function getClientNameAttribute(): string
    {
        $clientUser = $this->order?->user ?: ($this->invoice?->user ?: null);
        return $clientUser?->company_name ?: ($clientUser?->name ?: 'Mitra Klien');
    }
}
