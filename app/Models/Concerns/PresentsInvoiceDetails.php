<?php

namespace App\Models\Concerns;

use App\Models\Payment;

trait PresentsInvoiceDetails
{
    // Memeriksa apakah faktur memiliki bukti pembayaran yang sedang menunggu verifikasi sekretaris.
    public function getHasPendingProofAttribute(): bool
    {
        return $this->payments
            ->where('status', 'MENUNGGU_VERIFIKASI')
            ->filter(fn ($payment) => !empty($payment->proof_url))
            ->isNotEmpty();
    }

    // Memeriksa apakah bukti transfer faktur terakhir berstatus ditolak oleh pihak keuangan.
    public function getHasRejectedProofAttribute(): bool
    {
        $latestPayment = $this->payments->sortByDesc('id')->first();

        return $latestPayment && in_array($latestPayment->status, ['DITOLAK', 'REJECTED']);
    }

    // Mengambil entitas rekaman pembayaran terakhir yang ditolak beserta catatan perbaikannya.
    public function getRejectedProofPaymentAttribute(): ?Payment
    {
        $latestPayment = $this->payments->sortByDesc('id')->first();

        return ($latestPayment && in_array($latestPayment->status, ['DITOLAK', 'REJECTED']))
            ? $latestPayment
            : null;
    }

    // Memformat total nominal penagihan faktur ke format mata uang resmi Rupiah.
    public function getFormattedGrandTotalAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->grand_total ?? 0), 0, ',', '.');
    }

    // Memformat subtotal komoditas faktur sebelum penyesuaian ke format mata uang Rupiah.
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->subtotal_amount ?? 0), 0, ',', '.');
    }

    // Menghitung akumulasi tonase bobot bersih komoditas dari seluruh pesanan dalam faktur.
    public function getTotalNettoWeightAttribute(): float
    {
        return (float) ($this->orders ? $this->orders->sum(function ($order) {
            return $order->orderItems ? $order->orderItems->sum('ordered_qty') : 0;
        }) : 0);
    }

    // Mengklasifikasikan kategori periode penagihan faktur untuk filter pembukuan.
    public function getPeriodCategoryAttribute(): string
    {
        return $this->created_at?->isCurrentMonth() ? 'current' : 'last-month';
    }

    // Menggabungkan nomor faktur dan nomor surat jalan sebagai token indeks pencarian cepat.
    public function getSearchKeywordsAttribute(): string
    {
        $suratJalanList = $this->orders ? $this->orders->pluck('surat_jalan_number')->filter()->join(' ') : '';

        return trim($this->invoice_number . ' ' . $suratJalanList);
    }
}
