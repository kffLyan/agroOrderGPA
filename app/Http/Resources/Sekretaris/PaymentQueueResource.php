<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentQueueResource extends JsonResource
{
    protected string $officialGiroAccount;

    public function __construct($resource, string $officialGiroAccount = 'Rekening Resmi Koperasi GPA')
    {
        parent::__construct($resource);
        $this->officialGiroAccount = $officialGiroAccount;
    }

    /**
     * Transformasi model Payment ke baris antrean verifikasi bukti transfer
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

        $rawProof = $pay->proof_url;
        $fullProofUrl = null;
        if ($rawProof) {
            if (str_starts_with($rawProof, 'http://') || str_starts_with($rawProof, 'https://')) {
                $fullProofUrl = $rawProof;
            } else {
                $fullProofUrl = asset('storage/' . ltrim($rawProof, '/'));
            }
        }
        $cleanFileName = $rawProof ? basename($rawProof) : ('BUKTI_TRANSFER_' . $pay->id . '.JPG');
        $isImage = $rawProof ? (bool) preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $rawProof) : true;

        return [
            'id'              => 'PAY-' . str_pad((string) $pay->id, 4, '0', STR_PAD_LEFT),
            'payment_id'      => $pay->id,
            'invoice'         => $invoiceNum,
            'channel'         => strtolower($pay->payment_method ?: 'bank'),
            'channel_label'   => strtoupper(str_replace('_', ' ', $pay->payment_method ?: 'Transfer Bank')),
            'time'            => $pay->created_at ? $pay->created_at->format('H:i') : date('H:i'),
            'client'          => $clientName,
            'client_short'    => $clientName,
            'amount'          => $amt,
            'amount_label'    => 'Rp ' . number_format($amt, 0, ',', '.'),
            'evidence'        => 'Slip Bukti Transfer',
            'evidence_type'   => 'Foto / Dokumen',
            'evidence_file'   => $cleanFileName,
            'evidence_url'    => $fullProofUrl,
            'proof_url'       => $fullProofUrl,
            'is_image'        => $isImage,
            'evidence_note'   => 'Status Bukti: Terlampir',
            'slip_sender'     => strtoupper($clientName),
            'slip_account'    => 'Rekening Pengirim Klien',
            'slip_nominal'    => 'RP ' . number_format($amt, 0, ',', '.') . ',-',
            'slip_stamp'      => ($pay->created_at ?: now())->format('d/m/Y H:i') . ' WIB',
            'giro_account'    => $this->officialGiroAccount,
            'giro_time'       => ($pay->created_at ?: now())->format('d/m/Y H:i') . ' WIB',
            'giro_time_short' => ($pay->created_at ?: now())->format('d/m H:i') . ' WIB',
            'giro_entry'      => '[CR] Dana Masuk',
            'giro_nominal'    => '+Rp ' . number_format($amt, 0, ',', '.'),
            'match'           => true,
            'due'             => 'Pembayaran Pesanan',
            'note'            => $pay->verification_note ?: 'Bukti transfer siap dicocokkan dengan rekening koran.',
            'status'          => 'inspect',
        ];
    }
}
