<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Menampilkan konsol penerbitan faktur tagihan & monitoring tempo pembayaran (TOP).
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
            'title_before'     => 'Manajemen Faktur Tagihan &',
            'title_after'      => 'Pemantauan Tempo Pembayaran',
            'subtitle_before'  => 'Konsolidasi Surat Jalan sayur yang telah selesai dikirim menjadi lembar',
            'subtitle_rule'    => 'faktur penagihan resmi sesuai kesepakatan tempo bayar (14 / 30 / 45 Hari).',
            'export_label'     => 'Unduh Laporan Faktur (CSV)',
            'manual_label'     => '+ Terbitkan Faktur Baru',
        ];

        // Ambil data klien B2B dan pesanan riil
        $clientsQuery = User::where('client_type', 'B2B_KONTRAK')
            ->orWhere('role', 'KLIEN')
            ->with(['orders' => fn($q) => $q->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])->with('orderItems.product')])
            ->get();

        if ($clientsQuery->isEmpty()) {
            $clientsQuery = User::where('role', 'KLIEN')->get();
        }

        $clients = [];
        foreach ($clientsQuery as $c) {
            $docs = [];
            foreach ($c->orders as $ord) {
                $netWeight = (float) $ord->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);
                $val = (float) ($ord->grand_total ?: $ord->estimated_total);
                $commodity = $ord->orderItems->first()?->product?->name ?: 'Sayur Segar Grade A';
                $sjNum = $ord->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad($ord->id, 4, '0', STR_PAD_LEFT));

                $docs[] = [
                    'sj'           => $sjNum,
                    'date'         => ($ord->created_at ?: now())->format('d M Y'),
                    'commodity'    => $commodity,
                    'weight'       => $netWeight,
                    'weight_label' => number_format($netWeight, 0, ',', '.') . ' kg',
                    'pod'          => 'Telah Diterima & Sah',
                    'value'        => $val,
                    'value_label'  => 'Rp ' . number_format($val, 0, ',', '.'),
                ];
            }

            if (empty($docs)) {
                $docs = [
                    [
                        'sj'           => 'SJ-' . date('Ym') . '-0001',
                        'date'         => now()->format('d M Y'),
                        'commodity'    => 'Selada Romaine Grade A',
                        'weight'       => 500,
                        'weight_label' => '500 kg',
                        'pod'          => 'Telah Diterima & Sah',
                        'value'        => 7500000,
                        'value_label'  => 'Rp 7.500.000',
                    ]
                ];
            }

            $clients[] = [
                'id'          => (string) $c->id,
                'name'        => $c->company_name ?: ($c->name ?: 'Mitra Klien'),
                'terms_label' => 'Tempo 30 Hari',
                'terms_days'  => 30,
                'npwp'        => '01.324.981.4-051.000',
                'contract'    => 'KONTRAK-B2B-' . str_pad($c->id, 3, '0', STR_PAD_LEFT),
                'plafon'      => 100000000,
                'used'        => 35000000,
                'issue_date'  => now()->format('Y-m-d'),
                'documents'   => $docs,
            ];
        }

        // Ambil data faktur dari database
        $invoices = Invoice::with(['user', 'orders.orderItems', 'payments'])->latest()->get();

        $receivableTotal = 0;
        $dueSoonTotal = 0;
        $paidTotal = 0;
        $ledgerRows = [];

        if ($invoices->isNotEmpty()) {
            foreach ($invoices as $inv) {
                $amount = (float) $inv->grand_total;
                $isPaid = $inv->status === 'PAID';
                $clientName = $inv->user?->company_name ?: ($inv->user?->name ?: 'Mitra Klien GPA');
                $dueDate = $inv->due_date ?: now()->addDays(30);
                $daysRemaining = now()->diffInDays($dueDate, false);

                if ($isPaid) {
                    $paidTotal += $amount;
                    $statusKey = 'paid';
                    $statusLabel = 'Lunas';
                    $statusClass = 'bg-accent text-success-deep outline outline-1 outline-success-deep';
                    $remainingText = 'Telah Lunas';
                    $remainingClass = 'text-success-deep';
                    $remainingDot = 'bg-success-deep';
                } else {
                    $receivableTotal += $amount;
                    if ($daysRemaining < 0) {
                        $statusKey = 'blocked';
                        $statusLabel = 'Jatuh Tempo';
                        $statusClass = 'bg-danger-soft text-danger outline outline-1 outline-danger/60';
                        $remainingText = 'Lewat ' . abs($daysRemaining) . ' Hari';
                        $remainingClass = 'text-danger';
                        $remainingDot = 'bg-danger';
                    } elseif ($daysRemaining <= 7) {
                        $dueSoonTotal += $amount;
                        $statusKey = 'follow_up';
                        $statusLabel = 'Perlu Dihubungi';
                        $statusClass = 'bg-warning-soft/60 text-warning-deep outline outline-1 outline-warning/50';
                        $remainingText = "Sisa {$daysRemaining} Hari";
                        $remainingClass = 'text-warning-deep';
                        $remainingDot = 'bg-warning';
                    } else {
                        $statusKey = 'lancar';
                        $statusLabel = 'Lancar';
                        $statusClass = 'bg-accent text-success-deep outline outline-1 outline-success-deep';
                        $remainingText = "Sisa {$daysRemaining} Hari";
                        $remainingClass = 'text-success-deep';
                        $remainingDot = 'bg-success-deep';
                    }
                }

                $ledgerRows[] = [
                    'id'             => $inv->invoice_number,
                    'issued'         => $inv->created_at->format('d M Y'),
                    'issued_note'    => '(Faktur)',
                    'client'         => $clientName,
                    'contract'       => 'KONTRAK-' . ($inv->user_id ? str_pad($inv->user_id, 3, '0', STR_PAD_LEFT) : '001'),
                    'contract_note'  => null,
                    'contract_class' => 'text-ink-quiet',
                    'sj_count'       => max(1, $inv->orders->count()),
                    'total'          => $amount,
                    'total_label'    => 'Rp ' . number_format($amount, 0, ',', '.'),
                    'total_class'    => 'text-ink font-bold',
                    'terms_days'     => 30,
                    'terms_label'    => 'Tempo 30',
                    'terms_class'    => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                    'due'            => $dueDate->format('d M Y'),
                    'due_class'      => 'text-ink',
                    'remaining'      => $remainingText,
                    'remaining_class'=> $remainingClass,
                    'remaining_dot'  => $remainingDot,
                    'status_key'     => $statusKey,
                    'status_label'   => $statusLabel,
                    'status_class'   => $statusClass,
                    'row_class'      => '',
                    'highlight'      => '',
                    'paid'           => $isPaid,
                ];
            }
        } else {
            // Default rows jika tabel invoice masih baru
            $ledgerRows = [
                [
                    'id'             => 'INV-' . date('Y-m') . '-0001',
                    'issued'         => now()->format('d M Y'),
                    'issued_note'    => '(Konsolidasi)',
                    'client'         => 'PT Aerofood ACS (Garuda Group)',
                    'contract'       => 'KONTRAK-B2B-001',
                    'contract_note'  => null,
                    'contract_class' => 'text-ink-quiet',
                    'sj_count'       => 3,
                    'total'          => 45200000,
                    'total_label'    => 'Rp 45.200.000',
                    'total_class'    => 'text-ink font-bold',
                    'terms_days'     => 30,
                    'terms_label'    => 'Tempo 30',
                    'terms_class'    => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                    'due'            => now()->addDays(25)->format('d M Y'),
                    'due_class'      => 'text-ink',
                    'remaining'      => 'Sisa 25 Hari',
                    'remaining_class'=> 'text-success-deep font-semibold',
                    'remaining_dot'  => 'bg-success-deep',
                    'status_key'     => 'lancar',
                    'status_label'   => 'Lancar',
                    'status_class'   => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                    'row_class'      => '',
                    'highlight'      => '',
                    'paid'           => false,
                ],
                [
                    'id'             => 'INV-' . date('Y-m') . '-0002',
                    'issued'         => now()->subDays(28)->format('d M Y'),
                    'issued_note'    => null,
                    'client'         => 'Royal Ambarrukmo Hotel',
                    'contract'       => 'KONTRAK-B2B-002',
                    'contract_note'  => null,
                    'contract_class' => 'text-ink-quiet',
                    'sj_count'       => 2,
                    'total'          => 28400000,
                    'total_label'    => 'Rp 28.400.000',
                    'total_class'    => 'text-warning-deep font-bold',
                    'terms_days'     => 30,
                    'terms_label'    => 'Tempo 30',
                    'terms_class'    => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                    'due'            => now()->addDays(2)->format('d M Y'),
                    'due_class'      => 'text-warning-deep',
                    'remaining'      => 'H-2 Jatuh Tempo',
                    'remaining_class'=> 'text-warning-deep font-semibold',
                    'remaining_dot'  => 'bg-warning',
                    'status_key'     => 'follow_up',
                    'status_label'   => 'Perlu Dihubungi',
                    'status_class'   => 'bg-warning-soft/60 text-warning-deep outline outline-1 outline-warning/50',
                    'row_class'      => '',
                    'highlight'      => '',
                    'paid'           => false,
                ],
            ];
            $receivableTotal = 73600000;
            $dueSoonTotal = 28400000;
            $paidTotal = 120500000;
        }

        $metrics = [
            [
                'key'         => 'receivable',
                'label'       => 'TOTAL PIUTANG BERJALAN',
                'value'       => 'Rp ' . number_format($receivableTotal, 0, ',', '.'),
                'icon'        => 'wallet',
                'icon_tile'   => 'bg-surface-track',
                'icon_class'  => 'text-success-deep',
                'value_class' => 'text-ink',
                'foot_label'  => count($ledgerRows) . ' Faktur Aktif',
                'foot_class'  => 'text-ink-body',
                'chip'        => [
                    'label' => 'Termonitor',
                    'class' => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                ],
            ],
            [
                'key'         => 'due_soon',
                'label'       => 'SEGERA JATUH TEMPO (< 7 HARI)',
                'value'       => 'Rp ' . number_format($dueSoonTotal, 0, ',', '.'),
                'icon'        => 'clock',
                'icon_tile'   => 'bg-warning-soft/40',
                'icon_class'  => 'text-warning-deep',
                'value_class' => 'text-warning-deep',
                'foot_label'  => 'Perlu Diingatkan',
                'foot_class'  => 'text-warning-deep',
                'chip'        => [
                    'label' => 'Perhatian',
                    'class' => 'bg-warning-soft/60 text-warning-deep outline outline-1 outline-warning/50',
                ],
            ],
            [
                'key'         => 'overdue',
                'label'       => 'FAKTUR LEWAT TEMPO',
                'value'       => 'Rp 0',
                'icon'        => 'alert-triangle',
                'icon_tile'   => 'bg-danger-soft/50',
                'icon_class'  => 'text-danger',
                'value_class' => 'text-danger',
                'foot_label'  => 'Tidak Ada Keterlambatan',
                'foot_class'  => 'text-success-deep',
                'chip'        => [
                    'label' => 'Aman',
                    'class' => 'bg-accent text-success-ink outline outline-1 outline-success-deep',
                ],
            ],
            [
                'key'         => 'paid',
                'label'       => 'FAKTUR LUNAS BULAN INI',
                'value'       => 'Rp ' . number_format($paidTotal, 0, ',', '.'),
                'icon'        => 'check-circle',
                'icon_tile'   => 'bg-accent',
                'icon_class'  => 'text-success-deep',
                'value_class' => 'text-success-deep',
                'foot_label'  => 'Pembayaran Berhasil Diterima',
                'foot_class'  => 'text-success-deep',
                'chip'        => [
                    'label' => 'Lancar',
                    'class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                ],
            ],
        ];

        $generator = [
            'title'             => 'Penerbitan Faktur Tagihan Surat Jalan',
            'subtitle'          => 'Pilih klien dan gabungkan beberapa surat jalan menjadi 1 faktur tagihan resmi.',
            'badge_label'       => 'STATUS:',
            'badge'             => 'FAKTUR GABUNGAN OTOMATIS',
            'client_label'      => 'Pilih Pembeli / Klien',
            'credit_label'      => 'Batas Plafon Kredit:',
            'credit_sisa_label' => 'SISA PLAFON:',
            'documents_title'   => 'Surat Jalan Siap Ditagihkan',
            'documents_badge'   => 'Dokumen Valid',
            'engine_note'       => 'Tercatat otomatis dari surat jalan yang sudah terbit',
            'columns'           => [
                'sj'        => 'No. Surat Jalan',
                'date'      => 'Tanggal Kirim',
                'commodity' => 'Nama Sayur & Berat Bersih',
                'pod'       => 'Status Penerimaan',
                'value'     => 'Total Tagihan',
            ],
            'summary'           => [
                'documents_label' => 'Total Surat Jalan Dipilih',
                'due_label'       => 'Kesepakatan Tempo Pembayaran',
                'total_label'     => 'Total Nilai Faktur Tagihan',
                'generate_label'  => 'Terbitkan Faktur Tagihan Resmi',
            ],
        ];

        $ledger = [
            'title'          => 'Daftar Faktur Tagihan & Pemantauan Tempo',
            'subtitle'       => 'Pemantauan status pembayaran faktur dan sisa hari sebelum jatuh tempo.',
            'total_invoices' => count($ledgerRows),
            'status_filter'  => 'Semua Status Tagihan',
            'status_filters' => [
                'all'         => 'Semua Status Tagihan',
                'outstanding' => 'Piutang Berjalan',
                'paid'        => 'Sudah Lunas',
            ],
            'columns'        => [
                'invoice'   => 'No. Faktur & Tanggal',
                'client'    => 'Nama Pembeli / Klien',
                'sj'        => 'Surat Jalan Terhubung',
                'total'     => 'Total Tagihan (Rp)',
                'terms'     => 'Tempo Bayar',
                'due'       => 'Jatuh Tempo',
                'remaining' => 'Sisa Hari',
                'status'    => 'Status Tagihan',
                'action'    => 'Aksi',
            ],
            'rows'           => $ledgerRows,
        ];

        return view('sekretaris.invoice', [
            'operator'  => $operator,
            'heading'   => $heading,
            'metrics'   => $metrics,
            'generator' => $generator,
            'clients'   => $clients,
            'ledger'    => $ledger,
        ]);
    }
}
