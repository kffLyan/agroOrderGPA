<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentAuditResource extends JsonResource
{
    protected string $fallbackOperatorCode;

    public function __construct($resource, string $fallbackOperatorCode = 'SK-001')
    {
        parent::__construct($resource);
        $this->fallbackOperatorCode = $fallbackOperatorCode;
    }

    /**
     * Transformasi model Payment ke baris jurnal audit verifikasi
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pay = $this->resource;
        $clientUser = $pay->order?->user ?: ($pay->invoice?->user ?: null);
        $clientName = $clientUser?->company_name ?: ($clientUser?->name ?: 'Mitra Klien');
        $invoiceNum = $pay->invoice?->invoice_number ?: ($pay->order ? $pay->order->order_number : 'INV-GPA-' . $pay->id);
        $amt = (float) $pay->amount;
        $isApproved = in_array($pay->status, ['LUNAS', 'SUCCESS']);
        $isRejected = in_array($pay->status, ['DITOLAK', 'FAILED']);

        return [
            'timestamp'    => ($pay->created_at ?: now())->format('d/m H:i:s'),
            'id'           => 'PAY-' . str_pad((string) $pay->id, 4, '0', STR_PAD_LEFT),
            'invoice'      => $invoiceNum,
            'client'       => $clientName,
            'amount'       => $amt,
            'amount_label' => 'Rp ' . number_format($amt, 0, ',', '.'),
            'amount_class' => $isRejected ? 'text-danger' : 'text-ink',
            'status'       => $isRejected ? 'rejected' : ($isApproved ? 'approved' : 'pending'),
            'status_label' => $isRejected ? 'Ditolak' : ($isApproved ? 'Lunas / Disetujui' : 'Menunggu'),
            'status_class' => $isRejected
                ? 'bg-danger text-white outline outline-1 outline-danger'
                : ($isApproved ? 'bg-accent text-success-deep outline outline-1 outline-accent-deep' : 'bg-warning-soft text-warning-deep outline outline-1 outline-warning'),
            'operator'     => $pay->verifier?->name ? ('OP-' . $pay->verifier->id) : $this->fallbackOperatorCode,
            'note'         => $pay->verification_note ?: ($isApproved ? 'Mutasi bank terverifikasi cocok.' : ($isRejected ? 'Pembayaran ditolak.' : 'Menunggu pencocokan mutasi bank.')),
        ];
    }
}
