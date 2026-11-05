<?php

namespace App\Http\Resources\Klien;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk mengubah model Invoice menjadi representasi visual tagihan pembayaran klien
class BuktiPembayaranInvoiceResource extends JsonResource
{
    protected mixed $dbPayments;

    public function __construct($resource, $dbPayments = null)
    {
        parent::__construct($resource);
        $this->dbPayments = $dbPayments;
    }

    public function toArray(Request $request): array
    {
        $invoice = $this->resource;
        $dbPayments = $this->dbPayments;

        $gross = (float) ($invoice->subtotal_amount ?: $invoice->grand_total);
        $net = (float) $invoice->grand_total;
        $creditAdjustment = max(0, $gross - $net);

        $latestPayment = $invoice->payments?->sortByDesc('id')->first()
            ?: ($dbPayments ? $dbPayments->first(function ($payment) use ($invoice) {
                $invoiceNumber = $payment->invoice?->invoice_number ?: ($payment->order?->order_number ?: '');
                return $payment->invoice_id === $invoice->id || $invoiceNumber === $invoice->invoice_number;
            }) : null);

        $isPaid = ($invoice->status === 'PAID') || ($latestPayment && in_array($latestPayment->status, ['LUNAS', 'VERIFIED']));
        $isRejected = ($latestPayment && in_array($latestPayment->status, ['DITOLAK', 'REJECTED']));
        $isPending = (($latestPayment && in_array($latestPayment->status, ['MENUNGGU_VERIFIKASI', 'PENDING'])) || $invoice->has_pending_proof) && ! $isPaid;

        $verificationStage = $isPaid ? 3 : ($isPending ? 2 : 1);
        $verificationStatus = $isPaid ? 'LUNAS' : ($isPending ? 'MENUNGGU_VERIFIKASI' : ($isRejected ? 'DITOLAK' : 'BELUM_BAYAR'));

        return [
            'po' => $invoice->invoice_number,
            'option_label' => sprintf(
                '%s — Periode %s (Rp %s)',
                $invoice->invoice_number,
                $invoice->period_start ? $invoice->period_start->format('d/m/Y') : '-',
                number_format($net, 0, ',', '.')
            ),
            'status_chip' => match ($invoice->status) {
                'PAID'    => 'Lunas Sah',
                'OVERDUE' => 'Lewat Jatuh Tempo',
                'PARTIAL' => 'Terbayar Sebagian',
                default   => ($invoice->has_pending_proof ? 'Menunggu Verifikasi Pembayaran' : 'Menunggu Pembayaran'),
            },
            'due_note'            => $invoice->due_date ? 'Jatuh tempo pada: ' . $invoice->due_date->translatedFormat('d F Y') : 'Jatuh tempo TOP 30 Hari',
            'gross_label'         => 'Rp ' . number_format($gross, 0, ',', '.'),
            'credit_label'        => 'Penyesuaian Kredit / Retur:',
            'credit_value'        => $creditAdjustment > 0 ? '- Rp ' . number_format($creditAdjustment, 0, ',', '.') : 'Rp 0',
            'credit_value_label'  => $creditAdjustment > 0 ? '- Rp ' . number_format($creditAdjustment, 0, ',', '.') : 'Rp 0',
            'has_credit'          => $creditAdjustment > 0,
            'payable'             => (int) $net,
            'payable_label'       => 'Rp ' . number_format($net, 0, ',', '.'),
            'payable_digits'      => (string) (int) $net,
            'verification_stage'  => $verificationStage,
            'verification_status' => $verificationStatus,
            'is_paid'             => $isPaid,
            'is_pending'          => $isPending,
            'is_rejected'         => $isRejected,
            'status'              => $invoice->status,
            'payment_id'          => $latestPayment?->id,
            'payment_code'        => $latestPayment?->code,
            'payment_ref'         => $latestPayment ? ($latestPayment->payment_reference ?: 'PAY-' . str_pad((string) $latestPayment->id, 5, '0', STR_PAD_LEFT)) : '-',
            'payment_method'      => $latestPayment ? ($latestPayment->method_label ?? str_replace('_', ' ', $latestPayment->payment_method)) : 'Transfer Bank',
            'payment_method_label'=> $latestPayment ? ($latestPayment->method_label ?? str_replace('_', ' ', $latestPayment->payment_method)) : 'Transfer Bank',
            'paid_at'             => $latestPayment?->paid_at ? $latestPayment->paid_at->translatedFormat('d F Y H:i') : ($latestPayment?->created_at ? $latestPayment->created_at->translatedFormat('d F Y H:i') : null),
            'verifier_name'       => $latestPayment?->verifier?->name ?: 'Staf Sekretariat Keuangan GPA',
            'verification_note'   => $latestPayment?->verification_note,
            'receipt_number'      => $isPaid ? ('KWT-' . str_pad((string) ($latestPayment?->id ?? $invoice->id), 5, '0', STR_PAD_LEFT)) : null,
            'proof_url'           => $latestPayment?->proof_url ? asset('storage/' . $latestPayment->proof_url) : null,
        ];
    }
}

