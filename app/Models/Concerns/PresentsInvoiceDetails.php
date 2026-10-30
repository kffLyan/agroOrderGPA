<?php

namespace App\Models\Concerns;

use App\Models\Payment;

trait PresentsInvoiceDetails
{
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

    /**
     * Hitung total bobot kilogram komoditas dari semua pesanan dalam faktur
     */
    public function getTotalNettoWeightAttribute(): float
    {
        return (float) ($this->orders ? $this->orders->sum(function ($order) {
            return $order->orderItems ? $order->orderItems->sum('ordered_qty') : 0;
        }) : 0);
    }

    /**
     * Kategori periode untuk filter tabel (current / last-month)
     */
    public function getPeriodCategoryAttribute(): string
    {
        return $this->created_at?->isCurrentMonth() ? 'current' : 'last-month';
    }

    /**
     * Kata kunci pencarian gabungan no faktur dan no surat jalan
     */
    public function getSearchKeywordsAttribute(): string
    {
        $sjList = $this->orders ? $this->orders->pluck('surat_jalan_number')->filter()->join(' ') : '';

        return trim($this->invoice_number . ' ' . $sjList);
    }
}
