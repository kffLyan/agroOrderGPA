<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sekretaris\TolakPembayaranRequest;
use App\Http\Resources\Sekretaris\AuditPembayaranResource;
use App\Http\Resources\Sekretaris\AntreanPembayaranResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\TataKelolaService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller verifikasi pembayaran dan rekonsiliasi kas untuk Sekretaris
class PembayaranController extends Controller
{
    public function __construct(
        protected TataKelolaService $tataKelolaService
    ) {}

    // Menampilkan konsol verifikasi mutasi pembayaran kas/bank, antrean validasi bukti transfer, dan riwayat audit
    public function index(Request $request): View
    {
        $user = Auth::user();
        $operatorCode = $user ? 'SK-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT) : 'SK-001';

        // Mengambil informasi rekening bank resmi koperasi dari konfigurasi tata kelola keuangan
        $paymentChannels = $this->tataKelolaService->getPaymentChannels();
        $activeBank = collect($paymentChannels)->first(fn($channel) => ($channel['type'] ?? '') === 'BANK' && !empty($channel['is_active']))
            ?? collect($paymentChannels)->first(fn($channel) => ($channel['type'] ?? '') === 'BANK');
        $officialAccount = $activeBank
            ? "{$activeBank['name']} {$activeBank['account_number']} ({$activeBank['account_name']})"
            : 'Rekening Resmi Koperasi GPA';

        // Menghitung metrik total pembayaran yang sudah diajukan bukti bayarnya oleh klien
        $pendingQuery = Payment::whereIn('status', ['MENUNGGU_VERIFIKASI', 'PENDING'])
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('proof_url')->where('proof_url', '!=', '');
                })->orWhereNotNull('paid_at');
            });

        $pendingCount  = (clone $pendingQuery)->count();
        $pendingTotal  = (float) (clone $pendingQuery)->sum('amount');
        $verifiedCount = Payment::whereIn('status', ['LUNAS', 'SUCCESS'])->count();
        $verifiedTotal = (float) Payment::whereIn('status', ['LUNAS', 'SUCCESS'])->sum('amount');

        // Menghitung bukti transfer yang ditolak (hanya transaksi yang memang telah mengunggah bukti bayar, bukan pembatalan PO verifikasi)
        $rejectedCount = Payment::whereIn('status', ['DITOLAK', 'FAILED'])
            ->whereNotNull('proof_url')
            ->where('proof_url', '!=', '')
            ->where(function ($q) {
                $q->whereNull('order_id')
                  ->orWhereDoesntHave('order', fn ($oq) => $oq->where('status', 'BATAL'));
            })
            ->count();

        // Mengambil antrean pembayaran yang telah mengunggah bukti dan menunggu persetujuan
        $pendingPayments = $pendingQuery->with(['order.user', 'invoice.user', 'verifier'])
            ->latest()
            ->get();

        $queueRows = $pendingPayments->map(
            fn ($payment) => (new AntreanPembayaranResource($payment, $officialAccount))->toArray($request)
        )->all();

        // Mengambil log audit riwayat transaksi pembayaran mutakhir untuk transparansi operasional
        // Hanya menampilkan pembayaran yang memiliki bukti transfer sah atau telah disahkan lunas
        $recentPayments = Payment::with(['order.user', 'invoice.user', 'verifier'])
            ->where(function ($q) {
                $q->whereNotNull('proof_url')->where('proof_url', '!=', '')
                  ->orWhereNotNull('paid_at')
                  ->orWhereIn('status', ['LUNAS', 'SUCCESS']);
            })
            ->latest()
            ->take(50)
            ->get();

        $auditRows = $recentPayments->map(
            fn ($payment) => (new AuditPembayaranResource($payment, $operatorCode))->toArray($request)
        )->all();

        return view('sekretaris.pembayaran', [
            'operatorCode'  => $operatorCode,
            'queueRows'     => $queueRows,
            'auditRows'     => $auditRows,
            'pendingCount'  => $pendingCount,
            'pendingTotal'  => $pendingTotal,
            'verifiedCount' => $verifiedCount,
            'verifiedTotal' => $verifiedTotal,
            'rejectedCount' => $rejectedCount,
        ]);
    }

    // Menyetujui keabsahan pembayaran klien, mencocokkan mutasi kas, serta memperbarui status faktur menjadi lunas
    public function approve(Request $request, Payment $payment)
    {
        $verificationNote = $request->input('note', 'Mutasi rekening bank telah diverifikasi cocok dengan rekening koran.');

        $orderService = app(OrderService::class);
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
            'verification_note' => $verificationNote,
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

        $message = "Pembayaran PAY-" . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT) . " berhasil diverifikasi dan disahkan lunas.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // Menolak bukti transfer yang tidak valid atau fiktif dan meminta klien mengunggah bukti pembayaran ulang
    public function reject(TolakPembayaranRequest $request, Payment $payment)
    {
        $rejectionReason = $request->input('rejection_note') ?: $request->input('note', 'Bukti transfer tidak valid atau dana belum masuk rekening koran.');

        $payment->update([
            'status'            => 'DITOLAK',
            'verified_by'       => Auth::id(),
            'verification_note' => $rejectionReason,
        ]);

        $message = "Pembayaran PAY-" . str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT) . " berhasil ditolak. Klien diminta konfirmasi ulang.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
