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

        $creditLimit = $contract ? (float) $contract->credit_limit : 50000000.0;
        $outstandingTotal = (float) Invoice::where('user_id', $user->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('grand_total');

        $invoicesData = [];

        if ($dbInvoices->isNotEmpty()) {
            foreach ($dbInvoices as $inv) {
                $gross = (float) ($inv->subtotal_amount ?: $inv->grand_total);
                $net = (float) $inv->grand_total;
                $creditAdjustment = max(0, $gross - $net);

                $invoicesData[] = [
                    'po' => $inv->invoice_number,
                    'option_label' => sprintf(
                        '%s — Periode %s (Rp %s)',
                        $inv->invoice_number,
                        $inv->period_start ? $inv->period_start->format('d/m/Y') : '-',
                        number_format($net, 0, ',', '.')
                    ),
                    'status_chip' => match ($inv->status) {
                        'PAID' => 'Lunas Sah',
                        'OVERDUE' => 'Lewat Jatuh Tempo',
                        'PARTIAL' => 'Terbayar Sebagian',
                        default => 'Menunggu Pembayaran',
                    },
                    'due_note' => $inv->due_date ? 'Jatuh tempo pada: ' . $inv->due_date->translatedFormat('d F Y') : 'Jatuh tempo TOP 30 Hari',
                    'gross_label' => 'Rp ' . number_format($gross, 0, ',', '.'),
                    'credit_label' => 'Penyesuaian Kredit / Retur:',
                    'credit_value' => $creditAdjustment > 0 ? '- Rp ' . number_format($creditAdjustment, 0, ',', '.') : 'Rp 0',
                    'credit_value_label' => $creditAdjustment > 0 ? '- Rp ' . number_format($creditAdjustment, 0, ',', '.') : 'Rp 0',
                    'has_credit' => $creditAdjustment > 0,
                    'payable' => (int) $net,
                    'payable_label' => 'Rp ' . number_format($net, 0, ',', '.'),
                    'payable_digits' => (string) (int) $net,
                ];
            }
        } else {
            // Data representatif fallback untuk user yang belum memiliki invoice di database
            $invoicesData[] = [
                'po' => 'INV-2026-001',
                'option_label' => 'INV-2026-001 — Periode 01/02 - 15/02/2026 (Rp 18.450.000)',
                'status_chip' => 'Menunggu Pembayaran',
                'due_note' => 'Jatuh tempo: 28 Februari 2026 (TOP 30 Hari)',
                'gross_label' => 'Rp 18.750.000',
                'credit_label' => 'Penyesuaian Retur QC Timbangan Riil:',
                'credit_value' => '- Rp 300.000',
                'credit_value_label' => '- Rp 300.000',
                'has_credit' => true,
                'payable' => 18450000,
                'payable_label' => 'Rp 18.450.000',
                'payable_digits' => '18450000',
            ];
            $invoicesData[] = [
                'po' => 'INV-2026-002',
                'option_label' => 'INV-2026-002 — Periode 16/02 - 28/02/2026 (Rp 12.200.000)',
                'status_chip' => 'Menunggu Pembayaran',
                'due_note' => 'Jatuh tempo: 15 Maret 2026 (TOP 30 Hari)',
                'gross_label' => 'Rp 12.200.000',
                'credit_label' => 'Penyesuaian Retur / Klaim:',
                'credit_value' => 'Rp 0',
                'credit_value_label' => 'Rp 0',
                'has_credit' => false,
                'payable' => 12200000,
                'payable_label' => 'Rp 12.200.000',
                'payable_digits' => '12200000',
            ];
        }

        // Tentukan invoice yang terpilih dari query param
        $selectedPo = $request->query('tagihan', $invoicesData[0]['po'] ?? '');
        $selectedExists = collect($invoicesData)->contains('po', $selectedPo);
        if (! $selectedExists && ! empty($invoicesData)) {
            $selectedPo = $invoicesData[0]['po'];
        }

        // 2. Rekening bank resmi GPA Finance
        $account = [
            'short' => 'BCA',
            'number' => '8210-992-3841',
            'eyebrow' => 'Rekening Penerima Resmi GPA Finance (Virtual & Giro)',
            'account_label' => 'BCA 8210-992-3841',
            'holder' => 'a.n. PT Guntur Pandu Agro (Divisi Keuangan B2B)',
            'action' => 'Salin Rekening',
            'note' => 'Harap sertakan Nomor Faktur di kolom Berita Acara Transfer untuk rekonsiliasi otomatis perbankan dalam hitungan menit.',
        ];

        // 3. Aturan berkas
        $fileRules = [
            'format_label' => 'Format: JPG, PNG, atau PDF (Maks. 5 MB)',
            'mime' => 'image/jpeg,image/png,application/pdf',
            'max_size_mb' => 5,
        ];

        // 4. Default form data
        $form = [
            'sender_bank' => $user->company_name ? $user->company_name . ' - Bank Mandiri/BCA' : 'BCA',
            'sender_account' => '0821-xxxx-xxxx',
            'transfer_at' => Carbon::now()->format('d/m/Y H:i'),
            'reference' => 'TRX-' . strtoupper(Str::random(6)),
            'note' => 'Pelunasan tagihan tempo konsolidasi komoditas agro.',
        ];

        // 5. Compliance
        $compliance = [
            'title' => 'Pernyataan Kepatuhan Keuangan (PRD Rule 11):',
            'body' => 'Saya menyatakan bahwa bukti transfer yang diunggah adalah sah, dana berasal dari rekening koran resmi perusahaan mitra, dan nominal yang dikirimkan sesuai dengan kewajiban tagihan tempo berjalan.',
        ];

        // 6. Header
        $header = [
            'title' => 'Unggah Bukti Pembayaran & Rekonsiliasi Bank',
            'subtitle' => 'Konfirmasi pembayaran transfer bank / giro korporat atas faktur tempo konsolidasi komoditas agro B2B.',
            'source_note' => 'Setiap bukti transfer akan diverifikasi silang dengan mutasi rekening koran BCA/Mandiri GPA oleh Staf Sekretariat.',
            'rule_chip' => 'PRD Rule 11: SLA Verifikasi 1x24 Jam Kerja',
        ];

        // 7. Live Steps
        $steps = [
            [
                'no' => 1,
                'title' => 'Unggah Slip & Validasi Form',
                'tone' => 'done',
                'chip' => 'Selesai',
                'meta' => 'Mitra Klien B2B',
                'body' => 'Pengisian metadata bank pengirim, nominal akurat, dan unggah berkas slip transfer.',
            ],
            [
                'no' => 2,
                'title' => 'Pemeriksaan Mutasi Rekening',
                'tone' => 'active',
                'chip' => 'Dalam Proses',
                'meta' => 'Staf Sekretariat GPA',
                'body' => 'Verifikasi silang nomor referensi transaksi dengan rekening koran giro GPA Finance.',
            ],
            [
                'no' => 3,
                'title' => 'Penerbitan Kuitansi Resmi & Pemulihan Plafon',
                'tone' => 'pending',
                'chip' => 'Tahap Akhir',
                'meta' => 'Sistem Otomatis GPA',
                'body' => 'Status faktur berubah menjadi LUNAS dan limit plafon kredit tempo kembali dipulihkan secara instan.',
            ],
        ];

        // 8. Notice & Hotline
        $notice = [
            'eyebrow' => 'Sistem Rekonsiliasi Otomatis (Host-to-Host)',
            'body' => 'Sistem kami terhubung dengan API perbankan BCA dan Mandiri Corporate. Jika nominal dan nomor referensi klop, verifikasi kuitansi selesai dalam kurun waktu 15 - 30 menit.',
        ];

        $hotline = [
            'title' => 'Kendala Pembayaran / Selisih Transfer?',
            'body' => 'WhatsApp Finance GPA: +62 811-2345-6789 (Senin - Sabtu 08:00 - 17:00 WIB)',
            'action' => 'Hubungi PIC Keuangan',
        ];

        // 9. Plafon Card
        $availableLimit = max(0, $creditLimit - $outstandingTotal);
        $plafon = [
            'title' => 'Kondisi Plafon Kredit Tempo',
            'approved_label' => 'Plafon Disetujui',
            'approved_value' => 'Rp ' . number_format($creditLimit, 0, ',', '.'),
            'outstanding_label' => 'Total Tagihan Berjalan',
            'outstanding_value' => 'Rp ' . number_format($outstandingTotal, 0, ',', '.'),
            'note' => 'Setelah bukti diverifikasi sah oleh Sekretariat, sisa limit kredit Anda akan bertambah sebesar nominal yang dilunasi.',
        ];

        // 10. Riwayat Pembayaran (History)
        $dbPayments = Payment::with(['invoice', 'verifier'])
            ->whereHas('invoice', fn ($q) => $q->where('user_id', $user->id))
            ->orWhereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->latest('paid_at')
            ->take(10)
            ->get();

        $historyColumns = [
            ['label' => 'ID Bayar', 'align' => 'left'],
            ['label' => 'Faktur Terkait', 'align' => 'left'],
            ['label' => 'Tanggal Transfer', 'align' => 'left'],
            ['label' => 'Nominal', 'align' => 'right'],
            ['label' => 'Metode', 'align' => 'left'],
            ['label' => 'Pemeriksa', 'align' => 'left'],
            ['label' => 'Status', 'align' => 'left'],
            ['label' => 'No. Kuitansi', 'align' => 'right'],
        ];

        $history = [];

        if ($dbPayments->isNotEmpty()) {
            foreach ($dbPayments as $pay) {
                $history[] = [
                    'id' => 'PAY-' . str_pad($pay->id, 5, '0', STR_PAD_LEFT),
                    'invoice' => $pay->invoice?->invoice_number ?: ($pay->order?->order_number ?: '-'),
                    'date' => $pay->paid_at ? $pay->paid_at->format('d/m/Y H:i') : $pay->created_at->format('d/m/Y'),
                    'amount_label' => 'Rp ' . number_format((float) $pay->amount, 0, ',', '.'),
                    'method' => $pay->payment_method ?: 'TRANSFER_BANK',
                    'verifier' => $pay->verifier?->name ?: 'Antrean Sistem',
                    'verifier_pending' => ! $pay->verifier_id,
                    'status' => match ($pay->status) {
                        'LUNAS', 'VERIFIED' => 'Terverifikasi Sah',
                        'DITOLAK', 'REJECTED' => 'Ditolak',
                        default => 'Menunggu Verifikasi',
                    },
                    'status_tone' => match ($pay->status) {
                        'LUNAS', 'VERIFIED' => 'success',
                        'DITOLAK', 'REJECTED' => 'danger',
                        default => 'warning',
                    },
                    'receipt' => in_array($pay->status, ['LUNAS', 'VERIFIED']) ? 'KWT-' . str_pad($pay->id, 5, '0', STR_PAD_LEFT) : 'Menunggu Approval',
                    'receipt_pending' => ! in_array($pay->status, ['LUNAS', 'VERIFIED']),
                    'row_tone' => in_array($pay->status, ['LUNAS', 'VERIFIED']) ? 'plain' : 'muted',
                ];
            }
        } else {
            // Data riwayat representatif
            $history = [
                [
                    'id' => 'PAY-00421',
                    'invoice' => 'INV-2026-001',
                    'date' => '05/02/2026 14:15',
                    'amount_label' => 'Rp 18.450.000',
                    'method' => 'BCA Virtual Account',
                    'verifier' => 'Siti Aminah (Sekretaris)',
                    'verifier_pending' => false,
                    'status' => 'Terverifikasi Sah',
                    'status_tone' => 'success',
                    'receipt' => 'KWT-2026-0082',
                    'receipt_pending' => false,
                    'row_tone' => 'plain',
                ],
                [
                    'id' => 'PAY-00388',
                    'invoice' => 'INV-2026-000',
                    'date' => '22/01/2026 09:30',
                    'amount_label' => 'Rp 24.120.000',
                    'method' => 'Mandiri Giro B2B',
                    'verifier' => 'Siti Aminah (Sekretaris)',
                    'verifier_pending' => false,
                    'status' => 'Terverifikasi Sah',
                    'status_tone' => 'success',
                    'receipt' => 'KWT-2026-0061',
                    'receipt_pending' => false,
                    'row_tone' => 'plain',
                ],
            ];
        }

        $exportReport = [
            'label' => 'Unduh Rekap Pembayaran (CSV)',
            'body' => 'Menampilkan riwayat rekonsiliasi pembayaran dan status validasi kuitansi perbankan.',
        ];

        return view('klien.payment-proof', [
            'invoices' => $invoicesData,
            'account' => $account,
            'fileRules' => $fileRules,
            'form' => $form,
            'file' => null,
            'compliance' => $compliance,
            'selected' => $selectedPo,
            'header' => $header,
            'steps' => $steps,
            'notice' => $notice,
            'hotline' => $hotline,
            'plafon' => $plafon,
            'historyColumns' => $historyColumns,
            'history' => $history,
            'exportReport' => $exportReport,
        ]);
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
            $payment = Payment::create([
                'invoice_id' => $invoice?->id,
                'order_id' => $invoice?->orders()->first()?->id,
                'payment_reference' => $validated['reference'] ?: 'PAY-' . strtoupper(Str::random(8)),
                'payment_method' => 'TRANSFER_BANK',
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
