<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditPembayaranResource extends JsonResource
{
    protected string $fallbackOperatorCode;

    // Konstruktor resource audit pembayaran dengan parameter kode operator default
    public function __construct($resource, string $fallbackOperatorCode = 'SK-001')
    {
        parent::__construct($resource);
        $this->fallbackOperatorCode = $fallbackOperatorCode;
    }

    // Mengubah model Payment menjadi baris riwayat jurnal audit verifikasi pembayaran untuk Sekretaris
    public function toArray(Request $request): array
    {
        $payment = $this->resource;
        $clientUser = $payment->order?->user ?: ($payment->invoice?->user ?: null);
        $clientName = $clientUser?->company_name ?: ($clientUser?->name ?: 'Mitra Klien');
        $invoiceNumber = $payment->invoice?->invoice_number ?: ($payment->order ? $payment->order->order_number : 'INV-GPA-' . $payment->id);
        $paymentAmount = (float) $payment->amount;
        $isApproved = in_array($payment->status, ['LUNAS', 'SUCCESS']);
        $isRejected = in_array($payment->status, ['DITOLAK', 'FAILED']);

        return [
            'timestamp'    => ($payment->created_at ?: now())->format('d/m H:i:s'),
            'id'           => 'PAY-' . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT),
            'invoice'      => $invoiceNumber,
            'client'       => $clientName,
            'amount'       => $paymentAmount,
            'amount_label' => 'Rp ' . number_format($paymentAmount, 0, ',', '.'),
            'amount_class' => $isRejected ? 'text-danger' : 'text-ink',
            'status'       => $isRejected ? 'rejected' : ($isApproved ? 'approved' : 'pending'),
            'status_label' => $isRejected ? 'Ditolak' : ($isApproved ? 'Lunas / Disetujui' : 'Menunggu'),
            'status_class' => $isRejected
                ? 'bg-danger text-white outline outline-1 outline-danger'
                : ($isApproved ? 'bg-accent text-success-deep outline outline-1 outline-accent-deep' : 'bg-warning-soft text-warning-deep outline outline-1 outline-warning'),
            'operator'     => $payment->verifier?->name ? ('OP-' . $payment->verifier->id) : $this->fallbackOperatorCode,
            'note'         => $payment->verification_note ?: ($isApproved ? 'Mutasi bank terverifikasi cocok.' : ($isRejected ? 'Pembayaran ditolak.' : 'Menunggu pencocokan mutasi bank.')),
        ];
    }
}
