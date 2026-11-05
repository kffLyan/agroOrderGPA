<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Http\Requests\Klien\SimpanBuktiPembayaranRequest;
use App\Http\Resources\Klien\BuktiPembayaranInvoiceResource;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Services\TataKelolaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

// Controller unggah bukti transfer pembayaran untuk Klien
class BuktiPembayaranController extends Controller
{
    // Menampilkan halaman konfirmasi pembayaran, pilihan invoice terbuka, dan riwayat verifikasi bukti transfer
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Mengambil seluruh faktur tagihan milik klien yang masih aktif atau memiliki tunggakan
        $dbInvoices = Invoice::with(['orders', 'payments'])
            ->where('user_id', $user->id)
            ->latest('due_date')
            ->get();

        // Mengambil data kontrak aktif untuk mengecek sisa pagu kredit yang tersedia
        $contract = Contract::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        $creditLimit = $contract ? (float) $contract->credit_limit : 50000000.0;

        $outstandingTotal = (float) Invoice::where('user_id', $user->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('grand_total');

        // Mengambil daftar riwayat pembayaran terdahulu untuk kebutuhan audit dan bukti serah
        $dbPayments = Payment::with(['invoice', 'order', 'verifier'])
            ->where(function ($query) use ($user) {
                $query->whereHas('invoice', fn ($subQuery) => $subQuery->where('user_id', $user->id))
                    ->orWhereHas('order', fn ($subQuery) => $subQuery->where('user_id', $user->id));
            })
            ->latest('id')
            ->take(15)
            ->get();

        $tagihanParam = $request->query('tagihan');
        $orderIdParam = $request->query('order_id');

        // Memeriksa parameter rujukan faktur atau nomor pesanan yang dipilih pengguna
        if ($orderIdParam) {
            $relatedOrder = Order::with('invoice')->where('user_id', $user->id)->find($orderIdParam);
            if ($relatedOrder && $relatedOrder->invoice) {
                $tagihanParam = $relatedOrder->invoice->invoice_number;
            }
        }

        if ($tagihanParam && str_starts_with($tagihanParam, 'ORD-')) {
            $matchedOrder = Order::with('invoice')->where('user_id', $user->id)->where('order_number', $tagihanParam)->first();
            if ($matchedOrder && $matchedOrder->invoice) {
                $tagihanParam = $matchedOrder->invoice->invoice_number;
            }
        }

        // Memformat data faktur menggunakan BuktiPembayaranInvoiceResource
        $invoices = $dbInvoices->map(
            fn ($invoice) => (new BuktiPembayaranInvoiceResource($invoice, $dbPayments))->toArray($request)
        )->all();

        $selectedPo = $tagihanParam ?? ($invoices[0]['po'] ?? '');
        $selectedExists = collect($invoices)->contains('po', $selectedPo);
        if (! $selectedExists && ! empty($invoices)) {
            $selectedPo = $invoices[0]['po'];
        }
        $selected = $selectedPo;

        $currentSelectedInvoice = collect($invoices)->firstWhere('po', $selectedPo);
        $isSelectedPaid = $currentSelectedInvoice['is_paid'] ?? false;

        // Memeriksa apakah faktur yang dipilih sedang dalam proses peninjauan bukti transfer oleh sekretaris
        $pendingPaymentForSelected = (! $isSelectedPaid)
            ? $dbPayments->first(function ($payment) use ($selectedPo) {
                $invoiceNumber = $payment->invoice?->invoice_number ?: ($payment->order?->order_number ?: '');
                return $invoiceNumber === $selectedPo 
                    && $payment->status === 'MENUNGGU_VERIFIKASI' 
                    && ! empty($payment->proof_url);
            })
            : null;    

        // Mengidentifikasi catatan pembayaran terakhir untuk mendeteksi penolakan atau status transaksi
        $latestPaymentForSelected = $dbPayments->first(function ($payment) use ($selectedPo) {
            $invoiceNumber = $payment->invoice?->invoice_number ?: ($payment->order?->order_number ?: '');
            return $invoiceNumber === $selectedPo;
        });

        $rejectedPaymentForSelected = (! $isSelectedPaid && $latestPaymentForSelected && in_array($latestPaymentForSelected->status, ['DITOLAK', 'REJECTED']))
            ? $latestPaymentForSelected
            : null;

        $form = [
            'sender_bank'    => '',
            'sender_account' => $user?->phone ?: '',
            'transfer_at'    => Carbon::now()->format('d/m/Y H:i'),
            'reference'      => '',
            'note'           => '',
        ];

        $history = $dbPayments;

        $tataKelolaService = app(TataKelolaService::class);
        $paymentChannels = $tataKelolaService->getActivePaymentChannels();

        return view('klien.bukti-pembayaran', compact(
            'invoices',
            'selected',
            'selectedPo',
            'contract',
            'creditLimit',
            'outstandingTotal',
            'pendingPaymentForSelected',
            'rejectedPaymentForSelected',
            'form',
            'history',
            'user',
            'tagihanParam',
            'paymentChannels'
        ));
    }

    // Memvalidasi dan menyimpan unggahan berkas bukti transfer pembayaran serta mencatatnya ke antrean verifikasi
    public function store(SimpanBuktiPembayaranRequest $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validated();

        $invoice = Invoice::where('user_id', $user->id)
            ->where(function ($query) use ($validated) {
                $query->where('invoice_number', $validated['invoice_po'])
                      ->orWhere('id', $validated['invoice_po']);
            })
            ->first();

        // Memvalidasi kepemilikan tagihan (Anti-IDOR) agar klien hanya dapat membayar fakturnya sendiri
        if (!$invoice) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan atau faktur tidak ditemukan pada akun Anda.',
                ], 403);
            }

            return back()->withInput()->withErrors(['invoice_po' => 'Nomor tagihan tidak ditemukan pada akun Anda.']);
        }

        // Memastikan faktur yang sudah berstatus lunas tidak dapat dibayar atau diunggah ulang
        if ($invoice->status === 'PAID') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan ' . $invoice->invoice_number . ' sudah lunas dan terverifikasi sah oleh Sekretariat GPA. Pembayaran tidak dapat diubah atau diunggah ulang.',
                ], 422);
            }

            return redirect()->route('klien.dokumen')->with('info', 'Faktur ' . $invoice->invoice_number . ' sudah lunas dan terverifikasi sah.');
        }

        // Mencegah pengiriman ganda (double submission) berkas bukti pembayaran dalam rentang 60 detik
        $existingPendingPayment = Payment::where('invoice_id', $invoice->id)
            ->where('status', 'MENUNGGU_VERIFIKASI')
            ->where('created_at', '>=', now()->subSeconds(60))
            ->first();

        if ($existingPendingPayment) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'Bukti pembayaran Anda sudah diterima dan sedang dalam antrean verifikasi.',
                    'payment_id' => $existingPendingPayment->id,
                ]);
            }

            return redirect()->route('klien.dokumen')->with('info', 'Bukti pembayaran faktur ' . $validated['invoice_po'] . ' sudah diajukan sebelumnya dan sedang dalam antrean verifikasi.');
        }

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('payments', 'public');
        }

        $paidAt = Carbon::now();
        if (! empty($validated['transfer_at'] ?? null)) {
            try {
                $paidAt = Carbon::createFromFormat('d/m/Y H:i', $validated['transfer_at']);
            } catch (\Exception $exception) {
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
                'invoice_id'        => $invoice?->id,
                'order_id'          => $invoice?->orders()->first()?->id,
                'payment_reference' => (!empty($validated['reference'] ?? null)) ? $validated['reference'] : ('PAY-' . strtoupper(Str::random(8))),
                'payment_method'    => $paymentMethod,
                'amount'            => $validated['amount'],
                'proof_url'         => $proofPath,
                'status'            => 'MENUNGGU_VERIFIKASI',
                'paid_at'           => $paidAt,
                'verification_note' => (!empty($validated['note'] ?? null)) ? $validated['note'] : ('Unggahan bukti bayar oleh ' . $user->name),
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'Bukti pembayaran berhasil diunggah dan sedang dalam antrean verifikasi Sekretariat GPA.',
                    'payment_id' => $payment->id,
                ]);
            }

            return redirect()->route('klien.dokumen')->with('success', 'Bukti pembayaran faktur ' . $validated['invoice_po'] . ' berhasil diunggah. Menunggu konfirmasi verifikasi Staf Sekretariat.');
        } catch (\Exception $exception) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses bukti pembayaran: ' . $exception->getMessage(),
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memproses bukti pembayaran: ' . $exception->getMessage());
        }
    }
}
