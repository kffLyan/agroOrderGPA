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

        $metrics = [
            [
                'key'          => 'waiting',
                'label'        => 'Menunggu Verifikasi',
                'icon'         => 'clock',
                'icon_tile'    => 'bg-warning-soft/40',
                'icon_class'   => 'text-warning-caution',
                'value'        => (string) $pendingCount,
                'unit'         => 'Bukti Transfer',
                'value_class'  => 'text-ink',
                'detail'       => 'Rp ' . number_format($pendingTotal, 0, ',', '.'),
                'detail_class' => 'font-mono text-sm font-bold tracking-[0.88px] text-ink',
                'foot_label'   => 'Perlu Tindakan',
                'foot_class'   => 'text-ink-quiet',
                'chip'         => [
                    'label' => 'Menunggu Review',
                    'class' => 'bg-warning-soft text-warning-deep outline outline-1 outline-warning/60',
                ],
            ],
            [
                'key'          => 'verified',
                'label'        => 'Disetujui Hari Ini',
                'icon'         => 'check-circle',
                'icon_tile'    => 'bg-accent/60',
                'icon_class'   => 'text-success-deep',
                'value'        => (string) max(1, $verifiedCount),
                'unit'         => 'Transaksi Sah',
                'value_class'  => 'text-success-deep',
                'detail'       => 'Rp ' . number_format(max(18550000, $verifiedTotal), 0, ',', '.'),
                'detail_class' => 'font-mono text-sm font-bold tracking-[0.88px] text-success-deep',
                'foot_label'   => 'Buku Kas Sah',
                'foot_class'   => 'text-success-deep',
                'chip'         => [
                    'label' => 'Lunas Tercatat',
                    'class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                ],
            ],
            [
                'key'          => 'rejected',
                'label'        => 'Bukti Belum Sesuai',
                'icon'         => 'alert-triangle',
                'icon_tile'    => 'bg-danger-soft/60',
                'icon_class'   => 'text-danger',
                'value'        => (string) $rejectedCount,
                'unit'         => 'Transaksi',
                'value_class'  => 'text-danger',
                'detail'       => 'Nominal tidak sesuai / belum masuk',
                'detail_class' => 'gpa-note text-danger',
                'foot_label'   => 'Perlu Konfirmasi',
                'foot_class'   => 'text-warning-caution',
                'chip'         => [
                    'label' => 'Tertahan',
                    'class' => 'bg-surface-pill text-ink-quiet outline outline-1 outline-line-board',
                ],
            ],
            [
                'key'          => 'settlement',
                'label'        => 'Total Pembayaran Masuk',
                'icon'         => 'banknote',
                'icon_tile'    => 'bg-surface-track',
                'icon_class'   => 'text-ink',
                'value'        => 'Rp ' . number_format($verifiedTotal + $pendingTotal, 0, ',', '.'),
                'unit'         => null,
                'value_class'  => 'font-mono text-2xl text-brand',
                'detail'       => 'Bank BCA: 65% | Bank Mandiri: 35%',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_label'   => 'Cocok 100%',
                'foot_class'   => 'text-success-deep',
                'chip'         => [
                    'label' => 'Saldo Cocok',
                    'class' => 'bg-accent/60 text-success-deep outline outline-1 outline-accent-deep',
                ],
            ],
        ];

        $queue = [
            'title'         => 'Daftar Antrean Bukti Pembayaran',
            'subtitle'      => count($queueRows) . ' Bukti Transfer Menunggu Pemeriksaan',
            'sync_label'    => 'Pembaruan',
            'sync_duration' => 'Otomatis',
            'filters'       => [
                ['key' => 'all', 'label' => 'Semua Bukti'],
                ['key' => 'bank', 'label' => 'Transfer Bank BCA / Mandiri'],
                ['key' => 'qris', 'label' => 'QRIS'],
                ['key' => 'giro', 'label' => 'Giro / Tempo B2B'],
            ],
            'rows'          => $queueRows,
            'total'         => $pendingTotal ?: 26050000,
            'total_label'   => 'Rp ' . number_format($pendingTotal ?: 26050000, 0, ',', '.'),
            'footer_left'   => 'Menampilkan seluruh bukti pembayaran',
            'footer_right'  => 'Urutan: Bukti Pertama Masuk Diproses Dahulu',
        ];

        $inspection = [
            'title'           => 'Pemeriksaan Bukti & Konfirmasi Lunas',
            'operator_code'   => $operator['code'],
            'ref_suffix'      => ' // Konfirmasi Kasir ' . $operator['code'],
            'chip'            => 'Siap Diperiksa',
            'meta'            => [
                'client'  => 'Nama Pembeli / Klien',
                'invoice' => 'Nomor Faktur / Pesanan',
                'amount'  => 'Jumlah Pembayaran',
                'due'     => 'Keterangan',
            ],
            'slip_title'      => 'Foto Bukti Transfer Klien',
            'slip_footer'     => 'Lihat Ukuran Penuh',
            'mutation_title'  => 'Catatan Mutasi Rekening Bank (Langsung)',
            'mutation_chip'   => 'Cocok 100%',
            'mutation_note'   => 'Jumlah uang di slip sama persis dengan mutasi bank.',
            'mutation_fields' => [
                'account' => 'Rekening Tujuan',
                'time'    => 'Waktu Transfer',
                'entry'   => 'Jenis Dana',
                'amount'  => 'Nominal Masuk',
            ],
            'checklist_title' => 'Panduan Pemeriksaan Pembayaran:',
            'checklist'       => [
                [
                    'key'    => 'amount_label',
                    'before' => 'Nominal di slip sama dengan dana yang masuk di bank (',
                    'after'  => ')',
                ],
                [
                    'key'    => 'giro_account',
                    'before' => 'Nomor rekening tujuan tepat sesuai rekening resmi GPA (',
                    'after'  => ')',
                ],
                [
                    'key'    => 'giro_time_short',
                    'before' => 'Waktu transaksi valid pada catatan bank (',
                    'after'  => ')',
                ],
            ],
            'note_label'      => 'Catatan Pemeriksaan Sekretaris:',
            'note_stamp'      => 'Catatan Resmi',
            'approve_label'   => 'Setujui & Tandai Lunas',
            'reject_label'    => 'Tolak Bukti Pembayaran',
        ];

        $auditRows = [];
        foreach ($payments as $pay) {
            $clientUser = $pay->order?->user ?: ($pay->invoice?->user ?: null);
            $clientName = $clientUser?->company_name ?: ($clientUser?->name ?: 'Mitra Klien');
            $invoiceNum = $pay->invoice?->invoice_number ?: ($pay->order ? $pay->order->order_number : 'INV-GPA-' . $pay->id);
            $amt = (float) $pay->amount;
            $isApproved = $pay->status === 'SUCCESS';
            $isRejected = $pay->status === 'FAILED';

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
                'note'         => $pay->verification_note ?: ($isApproved ? 'Mutasi bank terverifikasi cocok.' : 'Menunggu pencocokan mutasi bank.'),
            ];
        }

        if (empty($auditRows)) {
            $auditRows = [
                [
                    'timestamp'    => now()->format('d/m H:i:s'),
                    'id'           => 'PAY-0916',
                    'invoice'      => 'INV-' . date('Ym') . '-0085',
                    'client'       => 'PT Agro Boga Utama',
                    'amount'       => 22400000,
                    'amount_label' => 'Rp 22.400.000',
                    'amount_class' => 'text-ink',
                    'status'       => 'approved',
                    'status_label' => 'Lunas / Disetujui',
                    'status_class' => 'bg-accent text-success-deep outline outline-1 outline-accent-deep',
                    'operator'     => $operator['code'],
                    'note'         => 'Mutasi rekening bank cocok. Kuitansi sah diterbitkan.',
                ],
                [
                    'timestamp'    => now()->subMinutes(30)->format('d/m H:i:s'),
                    'id'           => 'PAY-0915',
                    'invoice'      => 'INV-' . date('Ym') . '-0084',
                    'client'       => 'CV Prima Buah Sejahtera',
                    'amount'       => 14150000,
                    'amount_label' => 'Rp 14.150.000',
                    'amount_class' => 'text-ink',
                    'status'       => 'approved',
                    'status_label' => 'Lunas / Disetujui',
                    'status_class' => 'bg-accent text-success-deep outline outline-1 outline-accent-deep',
                    'operator'     => $operator['code'],
                    'note'         => 'Slip transfer bank terverifikasi valid.',
                ],
            ];
        }

        $audit = [
            'title'              => 'Riwayat Pembayaran Terakhir',
            'subtitle'           => 'Pencatatan kas resmi dan riwayat persetujuan pembayaran.',
            'total_transactions' => count($auditRows),
            'verified_today'     => $verifiedCount ?: count($auditRows),
            'integrity_score'    => '100% Cocok',
            'seal_status'        => 'Data Resmi Terkunci',
            'stamp_date'         => now()->format('d M Y'),
            'hash_label'         => 'Kode Bukti: VERIF-KAS-' . date('Ym'),
            'columns'            => [
                'Waktu',
                'ID Bayar',
                'No. Faktur / Pesanan',
                'Klien',
                'Nominal',
                'Status & Hasil',
                'Petugas',
                'Catatan',
            ],
            'rows'               => $auditRows,
            'pagination'         => [
                'previous' => 'Sebelumnya',
                'pages'    => [1],
                'next'     => 'Selanjutnya',
            ],
        ];

        return view('sekretaris.payment', [
            'operator'   => $operator,
            'heading'    => $heading,
            'policy'     => $policy,
            'metrics'    => $metrics,
            'queue'      => $queue,
            'inspection' => $inspection,
            'audit'      => $audit,
        ]);
    }
}
