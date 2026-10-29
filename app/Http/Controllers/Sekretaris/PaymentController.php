<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Services\GovernanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        protected GovernanceService $governanceService
    ) {}

    /**
     * Menampilkan konsol verifikasi bukti pembayaran manual & rekonsiliasi kas.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $operatorCode = $user ? 'SK-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT) : 'SK-001';

        // Ambil rekening giro resmi aktif dari pengaturan tata kelola
        $paymentChannels = $this->governanceService->getPaymentChannels();
        $activeBank = collect($paymentChannels)->first(fn($c) => ($c['type'] ?? '') === 'BANK' && !empty($c['is_active']))
            ?? collect($paymentChannels)->first(fn($c) => ($c['type'] ?? '') === 'BANK');
        $officialGiroAccount = $activeBank
            ? "{$activeBank['name']} {$activeBank['account_number']} ({$activeBank['account_name']})"
            : 'Rekening Resmi Koperasi GPA';

        // 1. Hitung metrik ringkasan langsung dari basis data (anti in-memory filtering)
        $pendingQuery = Payment::whereIn('status', ['MENUNGGU_VERIFIKASI', 'PENDING'])
            ->whereNotNull('proof_url')
            ->where('proof_url', '!=', '');

        $pendingCount  = (clone $pendingQuery)->count();
        $pendingTotal  = (float) (clone $pendingQuery)->sum('amount');
        $verifiedCount = Payment::whereIn('status', ['LUNAS', 'SUCCESS'])->count();
        $verifiedTotal = (float) Payment::whereIn('status', ['LUNAS', 'SUCCESS'])->sum('amount');
        $rejectedCount = Payment::whereIn('status', ['DITOLAK', 'FAILED'])->count();

        // 2. Antrean pembayaran yang menunggu verifikasi bukti transfer
        $pendingPayments = $pendingQuery->with(['order.user', 'invoice.user', 'verifier'])
            ->latest()
            ->get();

        $queueRows = $pendingPayments->map(
            fn ($pay) => (new \App\Http\Resources\Sekretaris\PaymentQueueResource($pay, $officialGiroAccount))->toArray($request)
        )->all();

        // 3. Jurnal riwayat audit pembayaran mutakhir
        $recentPayments = Payment::with(['order.user', 'invoice.user', 'verifier'])
            ->latest()
            ->take(50)
            ->get();

        $auditRows = $recentPayments->map(
            fn ($pay) => (new \App\Http\Resources\Sekretaris\PaymentAuditResource($pay, $operatorCode))->toArray($request)
        )->all();

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
    public function approve(Request $request, Payment $payment)
    {
        $note = $request->input('note', 'Mutasi rekening bank telah diverifikasi cocok dengan rekening koran.');

        $orderService = app(\App\Services\OrderService::class);
        $order = $payment->order;

        // Jika pesanan masih berstatus MENUNGGU_VERIFIKASI, alokasikan stok FIFO & terbitkan invoice terlebih dahulu
        if ($order && $order->status === 'MENUNGGU_VERIFIKASI') {
            try {
                $order = $orderService->approveOrder($order->id, Auth::id() ?: 1);
            } catch (\Exception $e) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Gagal menyetujui pesanan: ' . $e->getMessage()], 422);
                }
                return back()->withErrors(['error' => 'Gagal mengalokasikan stok pesanan: ' . $e->getMessage()]);
            }
        }

        if (!$payment->invoice_id && $order?->invoice_id) {
            $payment->invoice_id = $order->invoice_id;
        }

        $payment->update([
            'status'            => 'LUNAS',
            'verified_by'       => Auth::id(),
            'paid_at'           => now(),
            'verification_note' => $note,
            'invoice_id'        => $payment->invoice_id,
        ]);

        if ($payment->invoice_id && $payment->invoice) {
            $payment->invoice->update(['status' => 'PAID']);
            // Perbarui pesanan yang terhubung dengan invoice menjadi LUNAS
            Order::where('invoice_id', $payment->invoice_id)
                ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN'])
                ->update(['status' => 'LUNAS']);
        }

        if ($order && in_array($order->status, ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN'])) {
            $order->update([
                'status'      => 'LUNAS',
                'verified_by' => Auth::id(),
            ]);
        }

        $msg = "Pembayaran PAY-" . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT) . " berhasil diverifikasi dan disahkan lunas.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Menolak bukti pembayaran yang tidak sesuai
     */
    public function reject(\App\Http\Requests\Sekretaris\RejectPaymentRequest $request, Payment $payment)
    {
        $note = $request->input('rejection_note') ?: $request->input('note', 'Bukti transfer tidak valid atau dana belum masuk rekening koran.');

        $payment->update([
            'status'            => 'DITOLAK',
            'verified_by'       => Auth::id(),
            'verification_note' => $note,
        ]);

        $msg = "Pembayaran PAY-" . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT) . " berhasil ditolak. Klien diminta konfirmasi ulang.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }
}
