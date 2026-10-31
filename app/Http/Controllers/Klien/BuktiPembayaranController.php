<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Services\GovernanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Controller Unggah Bukti Pembayaran Klien.
 *
 * Penjelasan UKK:
 * Modul ini menangani konfirmasi transfer pembayaran dari pembeli:
 * 1. Mengunggah foto bukti struk transfer / screenshot QRIS.
 * 2. Proteksi Anti-IDOR: Memastikan tagihan yang dibayar adalah milik klien yang login.
 * 3. Status Terkunci: Jika sudah berstatus PAID (lunas terverifikasi), form otomatis terkunci
 *    sehingga tidak dapat diubah atau diunggah ulang.
 * 4. Integrasi kanal pembayaran resmi (BCA, Mandiri, BRI, QRIS GPA).
 */
class BuktiPembayaranController extends Controller
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

        // 2. Ambil kontrak aktif untuk plafon kredit
        $contract = Contract::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        $creditLimit = $contract ? (float) $contract->credit_limit : 50000000.0;

        $outstandingTotal = (float) Invoice::where('user_id', $user->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('grand_total');

        // 3. Riwayat Pembayaran (History)
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
            $relatedOrder = Order::with('invoice')->where('user_id', $user->id)->find($orderIdParam);
            if ($relatedOrder && $relatedOrder->invoice) {
                $tagihanParam = $relatedOrder->invoice->invoice_number;
            }
        }

        // Jika tagihan berupa nomor order (ORD-...), cari invoice terkait
        if ($tagihanParam && str_starts_with($tagihanParam, 'ORD-')) {
            $matchedOrder = Order::with('invoice')->where('user_id', $user->id)->where('order_number', $tagihanParam)->first();
            if ($matchedOrder && $matchedOrder->invoice) {
                $tagihanParam = $matchedOrder->invoice->invoice_number;
            }
        }

        // 4. Format data invoices untuk UI & Alpine.js
        $invoices = $this->formatInvoices($dbInvoices, $dbPayments);

        // Tentukan invoice yang sedang dipilih (selected)
        $selectedPo = $tagihanParam ?? ($invoices[0]['po'] ?? '');
        $selectedExists = collect($invoices)->contains('po', $selectedPo);
        if (! $selectedExists && ! empty($invoices)) {
            $selectedPo = $invoices[0]['po'];
        }
        $selected = $selectedPo;

        // Cari item invoice yang sedang dipilih untuk mengecek status lunas
        $currentSelectedInvoice = collect($invoices)->firstWhere('po', $selectedPo);
        $isSelectedPaid = $currentSelectedInvoice['is_paid'] ?? false;

        // Deteksi apakah tagihan yang dipilih sudah memiliki bukti pembayaran yang sedang diverifikasi Sekretaris
        $pendingPaymentForSelected = (! $isSelectedPaid)
            ? $dbPayments->first(function ($p) use ($selectedPo) {
                $invNum = $p->invoice?->invoice_number ?: ($p->order?->order_number ?: '');
                return $invNum === $selectedPo 
                    && $p->status === 'MENUNGGU_VERIFIKASI' 
                    && ! empty($p->proof_url);
            })
            : null;

        // Deteksi bukti pembayaran terbaru untuk tagihan yang dipilih
        $latestPaymentForSelected = $dbPayments->first(function ($p) use ($selectedPo) {
            $invNum = $p->invoice?->invoice_number ?: ($p->order?->order_number ?: '');
            return $invNum === $selectedPo;
        });

        // Apakah pembayaran terbaru ditolak dan belum ada pembayaran pengganti yang sedang diverifikasi
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

        // Ambil kanal pembayaran resmi yang telah disetel oleh Direktur
        $governanceService = app(GovernanceService::class);
        $paymentChannels = $governanceService->getActivePaymentChannels();

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

    /**
     * Format koleksi invoice menjadi array view model siap pakai untuk Blade & Alpine.js.
     */
    protected function formatInvoices($dbInvoices, $dbPayments): array
    {
        $invoices = [];
        foreach ($dbInvoices as $inv) {
            $gross = (float) ($inv->subtotal_amount ?: $inv->grand_total);
            $net = (float) $inv->grand_total;
            $creditAdjustment = max(0, $gross - $net);

            $latestPayment = $inv->payments?->sortByDesc('id')->first()
                ?: $dbPayments->first(function ($p) use ($inv) {
                    $invNum = $p->invoice?->invoice_number ?: ($p->order?->order_number ?: '');
                    return $p->invoice_id === $inv->id || $invNum === $inv->invoice_number;
                });

            $isPaid = ($inv->status === 'PAID') || ($latestPayment && in_array($latestPayment->status, ['LUNAS', 'VERIFIED']));
            $isRejected = ($latestPayment && in_array($latestPayment->status, ['DITOLAK', 'REJECTED']));
            $isPending = (($latestPayment && in_array($latestPayment->status, ['MENUNGGU_VERIFIKASI', 'PENDING'])) || $inv->has_pending_proof) && ! $isPaid;

            $verificationStage = $isPaid ? 3 : ($isPending ? 2 : 1);
            $verificationStatus = $isPaid ? 'LUNAS' : ($isPending ? 'MENUNGGU_VERIFIKASI' : ($isRejected ? 'DITOLAK' : 'BELUM_BAYAR'));

            $invoices[] = [
                'po' => $inv->invoice_number,
                'option_label' => sprintf(
                    '%s — Periode %s (Rp %s)',
                    $inv->invoice_number,
                    $inv->period_start ? $inv->period_start->format('d/m/Y') : '-',
                    number_format($net, 0, ',', '.')
                ),
                'status_chip' => match ($inv->status) {
                    'PAID'    => 'Lunas Sah',
                    'OVERDUE' => 'Lewat Jatuh Tempo',
                    'PARTIAL' => 'Terbayar Sebagian',
                    default   => ($inv->has_pending_proof ? 'Menunggu Verifikasi Pembayaran' : 'Menunggu Pembayaran'),
                },
                'due_note'            => $inv->due_date ? 'Jatuh tempo pada: ' . $inv->due_date->translatedFormat('d F Y') : 'Jatuh tempo TOP 30 Hari',
                'gross_label'         => 'Rp ' . number_format($gross, 0, ',', '.'),
                'credit_label'        => 'Penyesuaian Kredit / Retur:',
                'credit_value'        => $creditAdjustment > 0 ? '- Rp ' . number_format($creditAdjustment, 0, ',', '.') : 'Rp 0',
                'credit_value_label'  => $creditAdjustment > 0 ? '- Rp ' . number_format($creditAdjustment, 0, ',', '.') : 'Rp 0',
                'has_credit'          => $creditAdjustment > 0,
                'payable'             => (int) $net,
                'payable_label'       => 'Rp ' . number_format($net, 0, ',', '.'),
                'payable_digits'      => (string) (int) $net,
                'verification_stage'  => $verificationStage,
                'verification_status' => $verificationStatus,
                'is_paid'             => $isPaid,
                'is_pending'          => $isPending,
                'is_rejected'         => $isRejected,
                'status'              => $inv->status,
                'payment_ref'         => $latestPayment ? ($latestPayment->payment_reference ?: 'PAY-' . str_pad((string) $latestPayment->id, 5, '0', STR_PAD_LEFT)) : '-',
                'payment_method'      => $latestPayment?->payment_method ?: 'TRANSFER_BANK',
                'paid_at'             => $latestPayment?->paid_at ? $latestPayment->paid_at->translatedFormat('d F Y H:i') : ($latestPayment?->created_at ? $latestPayment->created_at->translatedFormat('d F Y H:i') : null),
                'verifier_name'       => $latestPayment?->verifier?->name ?: 'Staf Sekretariat Keuangan GPA',
                'verification_note'   => $latestPayment?->verification_note,
                'receipt_number'      => $isPaid ? ('KWT-' . str_pad((string) ($latestPayment?->id ?? $inv->id), 5, '0', STR_PAD_LEFT)) : null,
                'proof_url'           => $latestPayment?->proof_url ? asset('storage/' . $latestPayment->proof_url) : null,
            ];
        }

        return $invoices;
    }

    /**
     * Proses unggah bukti pembayaran oleh Klien.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'invoice_po'     => ['required', 'string'],
            'amount'         => ['required', 'numeric', 'min:1'],
            'sender_bank'    => ['required', 'string', 'max:100'],
            'sender_account' => ['required', 'string', 'max:50'],
            'reference'      => ['nullable', 'string', 'max:100'],
            'transfer_at'    => ['nullable', 'string', 'max:50'],
            'note'           => ['nullable', 'string', 'max:500'],
            'proof_file'     => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
        ]);

        $invoice = Invoice::where('user_id', $user->id)
            ->where(function ($q) use ($validated) {
                $q->where('invoice_number', $validated['invoice_po'])
                  ->orWhere('id', $validated['invoice_po']);
            })
            ->first();

        // Anti-IDOR: Pastikan tagihan yang dibayar adalah milik Klien yang sedang login
        if (!$invoice) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan atau faktur tidak ditemukan pada akun Anda.',
                ], 403);
            }

            return back()->withInput()->withErrors(['invoice_po' => 'Nomor tagihan tidak ditemukan pada akun Anda.']);
        }

        // Cek jika tagihan sudah berstatus PAID (Lunas & Terverifikasi Sah)
        if ($invoice->status === 'PAID') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tagihan ' . $invoice->invoice_number . ' sudah lunas dan terverifikasi sah oleh Sekretariat GPA. Pembayaran tidak dapat diubah atau diunggah ulang.',
                ], 422);
            }

            return redirect()->route('klien.dokumen')->with('info', 'Faktur ' . $invoice->invoice_number . ' sudah lunas dan terverifikasi sah.');
        }

        // Pencegahan dobel unggah bukti transfer yang masih mengantre dalam 60 detik terakhir
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
