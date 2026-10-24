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

        // Hanya tampilkan pembayaran yang berstatus menunggu DAN telah mengunggah bukti pembayaran
        $pendingPayments = $payments->filter(fn ($p) => in_array($p->status, ['MENUNGGU_VERIFIKASI', 'PENDING']) && !empty($p->proof_url));
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
                'evidence_file'    => $cleanFileName,
                'evidence_url'     => $fullProofUrl,
                'proof_url'        => $fullProofUrl,
                'is_image'         => $isImage,
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

    /**
     * Menyetujui dan memverifikasi bukti pembayaran kas/bank
     */
    public function approve(Request $request, int $id)
    {
        $payment = Payment::findOrFail($id);
        $note = $request->input('note', 'Mutasi rekening bank telah diverifikasi cocok dengan rekening koran.');

        $payment->update([
            'status'            => 'LUNAS',
            'verified_by'       => Auth::id(),
            'paid_at'           => now(),
            'verification_note' => $note,
        ]);

        if ($payment->invoice_id && $payment->invoice) {
            $payment->invoice->update(['status' => 'PAID']);
            // Perbarui pesanan yang terhubung dengan invoice menjadi LUNAS
            Order::where('invoice_id', $payment->invoice_id)
                ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN'])
                ->update(['status' => 'LUNAS']);
        }

        if ($payment->order_id && $payment->order && in_array($payment->order->status, ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN'])) {
            $payment->order->update([
                'status'      => 'LUNAS',
                'verified_by' => Auth::id(),
            ]);
        }

        $msg = "Pembayaran PAY-" . str_pad($payment->id, 4, '0', STR_PAD_LEFT) . " berhasil diverifikasi dan disahkan lunas.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Menolak bukti pembayaran yang tidak sesuai
     */
    public function reject(Request $request, int $id)
    {
        $payment = Payment::findOrFail($id);
        $note = $request->input('note', 'Bukti transfer tidak valid atau dana belum masuk rekening koran.');

        $payment->update([
            'status'            => 'DITOLAK',
            'verified_by'       => Auth::id(),
            'verification_note' => $note,
        ]);

        $msg = "Pembayaran PAY-" . str_pad($payment->id, 4, '0', STR_PAD_LEFT) . " berhasil ditolak. Klien diminta konfirmasi ulang.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }
}
