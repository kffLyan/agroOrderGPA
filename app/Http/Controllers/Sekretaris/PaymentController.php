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

        $pendingPayments = $payments->filter(fn ($p) => in_array($p->status, ['MENUNGGU_VERIFIKASI', 'PENDING']));
        $verifiedPayments = $payments->filter(fn ($p) => in_array($p->status, ['LUNAS', 'SUCCESS']));
        $rejectedPayments = $payments->filter(fn ($p) => in_array($p->status, ['DITOLAK', 'FAILED']));

        $pendingCount = $pendingPayments->count();
        $verifiedCount = $verifiedPayments->count();
        $rejectedCount = $rejectedPayments->count();

        $pendingTotal = (float) $pendingPayments->sum('amount');
        $verifiedTotal = (float) $verifiedPayments->sum('amount');

        $queueRows = [];
        foreach ($pendingPayments as $pay) {
            $clientUser = $pay->order?->user ?: ($pay->invoice?->user ?: null);
            $clientName = $clientUser?->company_name ?: ($clientUser?->name ?: 'Mitra Klien');
            $invoiceNum = $pay->invoice?->invoice_number ?: ($pay->order ? $pay->order->order_number : 'INV-GPA-' . $pay->id);
            $amt = (float) $pay->amount;

            $queueRows[] = [
                'id'               => 'PAY-' . str_pad($pay->id, 4, '0', STR_PAD_LEFT),
                'payment_id'       => $pay->id,
                'invoice'          => $invoiceNum,
                'channel'          => strtolower($pay->payment_method ?: 'bank'),
                'channel_label'    => strtoupper(str_replace('_', ' ', $pay->payment_method ?: 'Transfer Bank')),
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
                'due'              => 'Pembayaran Pesanan',
                'note'             => $pay->verification_note ?: 'Bukti transfer siap dicocokkan dengan rekening koran.',
                'status'           => 'inspect',
            ];
        }

        $auditRows = [];
        foreach ($payments as $pay) {
            $clientUser = $pay->order?->user ?: ($pay->invoice?->user ?: null);
            $clientName = $clientUser?->company_name ?: ($clientUser?->name ?: 'Mitra Klien');
            $invoiceNum = $pay->invoice?->invoice_number ?: ($pay->order ? $pay->order->order_number : 'INV-GPA-' . $pay->id);
            $amt = (float) $pay->amount;
            $isApproved = in_array($pay->status, ['LUNAS', 'SUCCESS']);
            $isRejected = in_array($pay->status, ['DITOLAK', 'FAILED']);

            $auditRows[] = [
                'timestamp'    => $pay->created_at->format('d/m H:i:s'),
                'id'           => 'PAY-' . str_pad($pay->id, 4, '0', STR_PAD_LEFT),
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
                'operator'     => $pay->verifier?->name ? ('OP-' . $pay->verifier->id) : $operator['code'],
                'note'         => $pay->verification_note ?: ($isApproved ? 'Mutasi bank terverifikasi cocok.' : ($isRejected ? 'Pembayaran ditolak.' : 'Menunggu pencocokan mutasi bank.')),
            ];
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
