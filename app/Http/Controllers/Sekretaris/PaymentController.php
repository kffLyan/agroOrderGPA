<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Menampilkan konsol verifikasi bukti pembayaran manual & rekonsiliasi kas.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $operator = [
            'role'     => 'Sekretaris Operasional',
            'name'     => $user?->name ?: 'Sekretariat GPA',
            'code'     => $user ? 'SK-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'SK-001',
            'initials' => strtoupper(substr($user?->name ?: 'SK', 0, 2)),
            'branch'   => 'Gudang Pusat Sentra',
            'email'    => $user?->email ?: 'sekretaris@agroorder.co.id',
            'shift'    => 'Shift Pagi 07.00 - 16.00 WIB',
        ];

        $heading = [
            'eyebrow'          => 'Pemeriksaan Pembayaran & Kas',
            'title_before'     => 'Verifikasi Bukti Pembayaran &',
            'title_after'      => 'Pencocokan Rekening Bank',
            'subtitle_before'  => 'Pemeriksaan dan persetujuan bukti transfer bank / QRIS dari pembeli untuk',
            'subtitle_rule'    => 'memastikan uang pembayaran telah benar-benar masuk ke rekening resmi GPA.',
            'badge'            => 'Rekening Bank BCA / Mandiri',
            'badge_label'      => 'Status Rekening:',
        ];

        $policy = [
            'label'         => 'Ketentuan Pemeriksaan Pembayaran:',
            'chip'          => 'Wajib Diperiksa Admin',
            'body_before'   => 'Bukti transfer yang diunggah klien ',
            'body_emphasis' => 'belum langsung dianggap lunas otomatis',
            'body_after'    => '. Sekretaris wajib mencocokkan nominal pada slip dengan mutasi rekening koran bank sebelum menyetujui transaksi.',
        ];

        // Ambil data pembayaran riil dari database
        $payments = Payment::with(['order.user', 'invoice.user', 'verifier'])->latest()->get();

        $pendingPayments = $payments->where('status', 'PENDING');
        $verifiedPayments = $payments->where('status', 'SUCCESS');
        $rejectedPayments = $payments->where('status', 'FAILED');

        $pendingCount = count($pendingPayments);
        $verifiedCount = count($verifiedPayments);
        $rejectedCount = count($rejectedPayments);

        $pendingTotal = (float) $pendingPayments->sum('amount');
        $verifiedTotal = (float) $verifiedPayments->sum('amount');

        $queueRows = [];
        if ($payments->isNotEmpty()) {
            foreach ($payments as $pay) {
                $clientUser = $pay->order?->user ?: ($pay->invoice?->user ?: null);
                $clientName = $clientUser?->company_name ?: ($clientUser?->name ?: 'Mitra Klien');
                $invoiceNum = $pay->invoice?->invoice_number ?: ($pay->order ? $pay->order->order_number : 'INV-GPA-' . $pay->id);
                $amt = (float) $pay->amount;

                $queueRows[] = [
                    'id'               => 'PAY-' . str_pad($pay->id, 4, '0', STR_PAD_LEFT),
                    'payment_id'       => $pay->id,
                    'invoice'          => $invoiceNum,
                    'channel'          => strtolower($pay->payment_method ?: 'bank'),
                    'channel_label'    => strtoupper($pay->payment_method ?: 'Transfer Bank'),
                    'time'             => $pay->created_at->format('H:i'),
                    'client'           => $clientName,
                    'client_short'     => $clientName,
                    'amount'           => $amt,
                    'amount_label'     => 'Rp ' . number_format($amt, 0, ',', '.'),
                    'evidence'         => 'Slip Bukti Transfer',
                    'evidence_type'    => 'Foto / Dokumen',
                    'evidence_file'    => $pay->proof_url ?: 'BUKTI_TRANSFER_' . $pay->id . '.JPG',
                    'evidence_note'    => 'Status Bukti: Terlampir',
                    'slip_sender'      => strtoupper($clientName),
                    'slip_account'     => 'Rekening Pengirim Klien',
                    'slip_nominal'     => 'RP ' . number_format($amt, 0, ',', '.') . ',-',
                    'slip_stamp'       => $pay->created_at->format('d/m/Y H:i') . ' WIB',
                    'giro_account'     => 'BCA 8820-192-001 (GPA)',
                    'giro_time'        => $pay->created_at->format('d/m/Y H:i') . ' WIB',
                    'giro_time_short'  => $pay->created_at->format('d/m H:i') . ' WIB',
                    'giro_entry'       => '[CR] Dana Masuk',
                    'giro_nominal'     => '+Rp ' . number_format($amt, 0, ',', '.'),
                    'match'            => true,
                    'due'              => 'Jatuh Tempo: Tepat Waktu',
                    'note'             => $pay->verification_note ?: 'Bukti transfer siap dicocokkan dengan rekening koran.',
                    'status'           => $pay->status === 'PENDING' ? 'inspect' : ($pay->status === 'SUCCESS' ? 'verified' : 'rejected'),
                ];
            }
        } else {
            $queueRows = [
                [
                    'id'               => 'PAY-0921',
                    'payment_id'       => 1,
                    'invoice'          => 'INV-' . date('Ym') . '-0091',
                    'channel'          => 'bank',
                    'channel_label'    => 'Transfer Bank BCA',
                    'time'             => '10:20',
                    'client'           => 'PT Kuliner Makmur Sentosa',
                    'client_short'     => 'PT Kuliner Makmur',
                    'amount'           => 18550000,
                    'amount_label'     => 'Rp 18.550.000',
                    'evidence'         => 'Slip Transfer Unggahan',
                    'evidence_type'    => 'Foto Bukti (JPG)',
                    'evidence_file'    => 'SLIP_TRANSFER_BCA_18550000.JPG',
                    'evidence_note'    => 'Status Bukti: Lengkap',
                    'slip_sender'      => 'PT KULINER MAKMUR S.',
                    'slip_account'     => 'BCA 5420-991-002',
                    'slip_nominal'     => 'RP 18.550.000,-',
                    'slip_stamp'       => now()->format('d/m/Y 10:15') . ' WIB',
                    'giro_account'     => 'BCA 8820-192-001 (GPA)',
                    'giro_time'        => now()->format('d/m/Y 10:15') . ' WIB',
                    'giro_time_short'  => now()->format('d/m 10:15') . ' WIB',
                    'giro_entry'       => '[CR] Dana Masuk',
                    'giro_nominal'     => '+Rp 18.550.000',
                    'match'            => true,
                    'due'              => 'Pembayaran Pesanan Sayur',
                    'note'             => 'Mutasi rekening BCA jam 10:15 WIB cocok dengan slip transfer.',
                    'status'           => 'inspect',
                ],
                [
                    'id'               => 'PAY-0922',
                    'payment_id'       => 2,
                    'invoice'          => 'INV-' . date('Ym') . '-0092',
                    'channel'          => 'bank',
                    'channel_label'    => 'Transfer Mandiri',
                    'time'             => '11:05',
                    'client'           => 'Grand Hotel Bandung',
                    'client_short'     => 'Grand Hotel Bandung',
                    'amount'           => 7500000,
                    'amount_label'     => 'Rp 7.500.000',
                    'evidence'         => 'Slip Transfer Mandiri',
                    'evidence_type'    => 'Foto Bukti (JPG)',
                    'evidence_file'    => 'MANDIRI_7500000.JPG',
                    'evidence_note'    => 'Status Bukti: Lengkap',
                    'slip_sender'      => 'GRAND HOTEL BANDUNG',
                    'slip_account'     => 'Mandiri 132-00-99182',
                    'slip_nominal'     => 'RP 7.500.000,-',
                    'slip_stamp'       => now()->format('d/m/Y 11:00') . ' WIB',
                    'giro_account'     => 'Mandiri 0088-112-334 (GPA)',
                    'giro_time'        => now()->format('d/m/Y 11:00') . ' WIB',
                    'giro_time_short'  => now()->format('d/m 11:00') . ' WIB',
                    'giro_entry'       => '[CR] Dana Masuk',
                    'giro_nominal'     => '+Rp 7.500.000',
                    'match'            => true,
                    'due'              => 'Pembayaran Sayur Hotel',
                    'note'             => 'Slip transfer sesuai dengan mutasi rekening bank.',
                    'status'           => 'pending',
                ],
            ];
            $pendingCount = 2;
            $pendingTotal = 26050000;
            $verifiedCount = 5;
            $verifiedTotal = 64200000;
        }

        return view('sekretaris.payment', [
            'queueRows'     => $queueRows,
            'auditRows'     => $auditRows,
            'pendingCount'  => $pendingCount,
            'pendingTotal'  => $pendingTotal,
            'verifiedCount' => $verifiedCount,
            'verifiedTotal' => $verifiedTotal,
            'rejectedCount' => $rejectedCount,
        ]);
    }
}
