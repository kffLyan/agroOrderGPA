<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentProofController extends Controller
{
    /**
     * Tampilkan formulir unggah bukti transfer dan pelacakan verifikasi pembayaran.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        // 1. Ambil semua invoice milik user yang belum lunas atau baru diterbitkan
        $dbInvoices = Invoice::with(['orders', 'payments'])
            ->where('user_id', $user->id)
            ->latest('due_date')
            ->get();

        // Ambil kontrak aktif untuk plafon kredit
        $contract = Contract::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        $outstandingTotal = (float) Invoice::where('user_id', $user->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('grand_total');

        // Riwayat Pembayaran (History)
        $dbPayments = Payment::with(['invoice', 'order', 'verifier'])
            ->where(function ($query) use ($user) {
                $query->whereHas('invoice', fn ($q) => $q->where('user_id', $user->id))
                    ->orWhereHas('order', fn ($q) => $q->where('user_id', $user->id));
            })
            ->latest('id')
            ->take(15)
            ->get();

        $tagihanParam = $request->query('tagihan');
        $orderIdParam = $request->query('order_id');

        // Jika tagihan kosong atau diberikan order_id, cari invoice terkait pesanan tersebut
        if ($orderIdParam) {
            $relatedOrder = \App\Models\Order::with('invoice')->where('user_id', $user->id)->find($orderIdParam);
            if ($relatedOrder && $relatedOrder->invoice) {
                $tagihanParam = $relatedOrder->invoice->invoice_number;
            }
        }

        // Ambil kanal pembayaran resmi yang telah disetel oleh Direktur
        $governanceService = app(\App\Services\GovernanceService::class);
        $paymentChannels = $governanceService->getActivePaymentChannels();

        return view('klien.payment-proof', compact(
            'dbInvoices',
            'contract',
            'outstandingTotal',
            'dbPayments',
            'user',
            'tagihanParam',
            'paymentChannels'
        ));
    }

    /**
     * Proses unggah bukti pembayaran oleh Klien.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'invoice_po' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'sender_bank' => ['required', 'string', 'max:100'],
            'sender_account' => ['required', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'transfer_at' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:500'],
            'proof_file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
        ]);

        $invoice = Invoice::where('user_id', $user->id)
            ->where('invoice_number', $validated['invoice_po'])
            ->first();

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('payments', 'public');
        }

        $paidAt = Carbon::now();
        if (! empty($validated['transfer_at'])) {
            try {
                $paidAt = Carbon::createFromFormat('d/m/Y H:i', $validated['transfer_at']);
            } catch (\Exception $e) {
                $paidAt = Carbon::now();
            }
        }

        DB::beginTransaction();
        try {
            $paymentMethod = $request->input('payment_method');
            if (empty($paymentMethod)) {
                $paymentMethod = str_contains(strtoupper($validated['sender_bank']), 'QRIS') ? 'QRIS' : 'TRANSFER_BANK';
            }

            $payment = Payment::create([
                'invoice_id' => $invoice?->id,
                'order_id' => $invoice?->orders()->first()?->id,
                'payment_reference' => $validated['reference'] ?: 'PAY-' . strtoupper(Str::random(8)),
                'payment_method' => $paymentMethod,
                'amount' => $validated['amount'],
                'proof_url' => $proofPath,
                'status' => 'MENUNGGU_VERIFIKASI',
                'paid_at' => $paidAt,
                'verification_note' => $validated['note'] ?: ('Unggahan bukti bayar oleh ' . $user->name),
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pembayaran berhasil diunggah dan sedang dalam antrean verifikasi Sekretariat GPA.',
                    'payment_id' => $payment->id,
                ]);
            }

            return redirect()->route('klien.documents')->with('success', 'Bukti pembayaran faktur ' . $validated['invoice_po'] . ' berhasil diunggah. Menunggu konfirmasi verifikasi Staf Sekretariat.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses bukti pembayaran: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memproses bukti pembayaran: ' . $e->getMessage());
        }
    }
}
