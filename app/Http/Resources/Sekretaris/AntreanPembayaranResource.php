<?php

namespace App\Http\Resources\Sekretaris;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AntreanPembayaranResource extends JsonResource
{
    protected string $officialGiroAccount;

    // Konstruktor resource antrean pembayaran dengan parameter rekening giro resmi koperasi
    public function __construct($resource, string $officialGiroAccount = 'Rekening Resmi Koperasi GPA')
    {
        parent::__construct($resource);
        $this->officialGiroAccount = $officialGiroAccount;
    }

    // Mengubah model Payment menjadi representasi kartu antrean verifikasi bukti transfer untuk Sekretaris
    public function toArray(Request $request): array
    {
        $payment = $this->resource;
        $clientUser = $payment->order?->user ?: ($payment->invoice?->user ?: null);
        $clientName = $clientUser?->company_name ?: ($clientUser?->name ?: 'Mitra Klien');
        $invoiceNumber = $payment->invoice?->invoice_number ?: ($payment->order ? $payment->order->order_number : 'INV-GPA-' . $payment->id);
        $paymentAmount = (float) $payment->amount;

        // Proses URL berkas bukti transfer pembayaran
        $rawProofUrl = $payment->proof_url;
        $fullProofUrl = null;
        if ($rawProofUrl) {
            if (str_starts_with($rawProofUrl, 'http://') || str_starts_with($rawProofUrl, 'https://')) {
                $fullProofUrl = $rawProofUrl;
            } else {
                $fullProofUrl = asset('storage/' . ltrim($rawProofUrl, '/'));
            }
        }
        $proofFileName = $rawProofUrl ? basename($rawProofUrl) : ('BUKTI_TRANSFER_' . $payment->id . '.JPG');
        $isImage = $rawProofUrl ? (bool) preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $rawProofUrl) : true;

        return [
            'id'              => 'PAY-' . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT),
            'payment_id'      => $payment->id,
            'invoice'         => $invoiceNumber,
            'channel'         => strtolower($payment->payment_method ?: 'bank'),
            'channel_label'   => strtoupper(str_replace('_', ' ', $payment->payment_method ?: 'Transfer Bank')),
            'time'            => $payment->created_at ? $payment->created_at->format('H:i') : date('H:i'),
            'client'          => $clientName,
            'client_short'    => $clientName,
            'amount'          => $paymentAmount,
            'amount_label'    => 'Rp ' . number_format($paymentAmount, 0, ',', '.'),
            'evidence'        => 'Slip Bukti Transfer',
            'evidence_type'   => 'Foto / Dokumen',
            'evidence_file'   => $proofFileName,
            'evidence_url'    => $fullProofUrl,
            'proof_url'       => $fullProofUrl,
            'is_image'        => $isImage,
            'evidence_note'   => 'Status Bukti: Terlampir',
            'slip_sender'     => strtoupper($clientName),
            'slip_account'    => 'Rekening Pengirim Klien',
            'slip_nominal'    => 'RP ' . number_format($paymentAmount, 0, ',', '.') . ',-',
            'slip_stamp'      => ($payment->created_at ?: now())->format('d/m/Y H:i') . ' WIB',
            'giro_account'    => $this->officialGiroAccount,
            'giro_time'       => ($payment->created_at ?: now())->format('d/m/Y H:i') . ' WIB',
            'giro_time_short' => ($payment->created_at ?: now())->format('d/m H:i') . ' WIB',
            'giro_entry'      => '[CR] Dana Masuk',
            'giro_nominal'    => '+Rp ' . number_format($paymentAmount, 0, ',', '.'),
            'match'           => true,
            'due'             => 'Pembayaran Pesanan',
            'note'            => $payment->verification_note ?: 'Bukti transfer siap dicocokkan dengan rekening koran.',
            'status'          => 'inspect',
        ];
    }
}
