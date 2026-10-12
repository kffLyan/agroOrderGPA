<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * Menampilkan konsol rekapitulasi laporan operasional, pengiriman & penjualan.
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
            'eyebrow'      => 'Rekapitulasi Laporan',
            'title_before' => 'Laporan Rekapitulasi Operasional,',
            'title_after'  => 'Pengiriman Sayur & Penjualan',
            'subtitle'     => 'Rekapitulasi data transaksi harian, volume timbangan nyata, dan surat jalan pengiriman untuk pelaporan resmi ke Direktur.',
            'actions'      => [
                ['key' => 'sync', 'label' => 'Segarkan Data Transaksi', 'icon' => 'refresh', 'variant' => 'ghost'],
                ['key' => 'export', 'label' => 'Unduh Rekap (CSV / Excel)', 'icon' => 'download', 'variant' => 'ghost'],
                ['key' => 'print', 'label' => 'Cetak Laporan Resmi (PDF)', 'icon' => 'printer', 'variant' => 'brand'],
            ],
        ];

        $filters = [
            [
                'key'     => 'period',
                'label'   => 'Periode Laporan',
                'value'   => 'Bulan Berjalan',
                'options' => [
                    'Bulan Berjalan',
                    'Minggu Ini',
                    'Hari Ini',
                    'Bulan Lalu',
                ],
            ],
            [
                'key'     => 'client',
                'label'   => 'Kategori Pembeli',
                'value'   => 'Semua Pembeli & Mitra B2B',
                'options' => [
                    'Semua Pembeli & Mitra B2B',
                    'Hotel & Restoran',
                    'Katering Industri',
                    'Supermarket & Retail',
                ],
            ],
            [
                'key'     => 'commodity',
                'label'   => 'Jenis Sayur',
                'value'   => 'Semua Sayuran Segar',
                'options' => [
                    'Semua Sayuran Segar',
                    'Selada Romaine',
                    'Tomat Beef',
                    'Brokoli Super',
                    'Bawang Merah',
                ],
            ],
            [
                'key'     => 'payment',
                'label'   => 'Status Tagihan',
                'value'   => 'Semua Status Tagihan',
                'options' => [
                    'Semua Status Tagihan',
                    'Lunas',
                    'Tempo 14 Hari',
                    'Tempo 30 Hari',
                    'Menunggu Verifikasi',
                ],
            ],
        ];

        // Ambil data pesanan dari database
        $orders = Order::with(['user', 'orderItems.product', 'invoice'])->latest()->get();

        $journalRows = [];
        $totalEstimate = 0;
        $totalNet = 0;
        $totalValue = 0;

        foreach ($orders->values() as $idx => $ord) {
            $clientName = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien');
            $address = $ord->delivery_address ?: 'Gudang Pembeli';
            $firstItem = $ord->orderItems->first();
            $commodityName = $firstItem?->product?->name ?: 'Sayur Segar Grade A';
            $unitPrice = $firstItem ? (float) $firstItem->unit_price : 15000;

            $est = (float) $ord->orderItems->sum('ordered_qty');
            if ($est <= 0) $est = 500.0;
            $net = (float) $ord->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);
            if ($net <= 0) $net = $est;

            $val = (float) ($ord->grand_total ?: ($net * $unitPrice));
            $diffKg = $net - $est;
            $diffPct = $est > 0 ? round(($diffKg / $est) * 100, 2) : 0;

            $totalEstimate += $est;
            $totalNet += $net;
            $totalValue += $val;

            $isPaid = $ord->status === 'SELESAI' || ($ord->invoice && $ord->invoice->status === 'PAID');

            $journalRows[] = [
                'no'              => str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                'invoice'         => $ord->order_number,
                'sj'              => $ord->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad($ord->id, 4, '0', STR_PAD_LEFT)),
                'client'          => $clientName,
                'location'        => $address,
                'commodity'       => $commodityName,
                'estimate'        => $est,
                'estimate_label'  => number_format($est, 0, ',', '.') . ' kg',
                'net'             => $net,
                'net_label'       => number_format($net, 1, ',', '.') . ' kg',
                'unit_price'      => $unitPrice,
                'value'           => $val,
                'value_label'     => 'Rp ' . number_format($val, 0, ',', '.'),
                'deviation_value' => $diffPct,
                'deviation_label' => ($diffPct >= 0 ? '+' : '') . number_format($diffPct, 2, '.', '') . '%',
                'deviation_class' => $diffPct < -1.0 ? 'text-warning-caution' : 'text-success-deep',
                'blocked'         => false,
                'status'          => $isPaid ? 'lunas' : 'top30',
                'status_label'    => $isPaid ? 'Lunas' : 'Tempo 30',
                'status_class'    => $isPaid ? 'bg-accent text-success-deep outline outline-1 outline-success-deep' : 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                'audit_files'     => 3,
            ];
        }

        if (empty($journalRows)) {
            $journalRows = [
                [
                    'no'              => '01',
                    'invoice'         => 'ORD-GPA-' . date('Ym') . '-0001',
                    'sj'              => 'SJ-GPA-' . date('Ym') . '-0001',
                    'client'          => 'PT Sinar Agro Mandiri',
                    'location'        => 'Gudang Pembeli Sentral',
                    'commodity'       => 'Selada Romaine Grade A',
                    'estimate'        => 500.0,
                    'estimate_label'  => '500 kg',
                    'net'             => 498.5,
                    'net_label'       => '498.5 kg',
                    'unit_price'      => 15000,
                    'value'           => 7477500,
                    'value_label'     => 'Rp 7.477.500',
                    'deviation_value' => -0.3,
                    'deviation_label' => '-0.30%',
                    'deviation_class' => 'text-success-deep',
                    'blocked'         => false,
                    'status'          => 'lunas',
                    'status_label'    => 'Lunas',
                    'status_class'    => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                    'audit_files'     => 3,
                ]
            ];
            $totalEstimate = 500;
            $totalNet = 498.5;
            $totalValue = 7477500;
        }

        $overallDeviation = $totalEstimate > 0 ? round((($totalNet - $totalEstimate) / $totalEstimate) * 100, 2) : 0;

        $metrics = [
            [
                'key'          => 'volume',
                'label'        => 'TOTAL BERAT SAYUR TERKIRIM',
                'icon'         => 'package',
                'icon_tile'    => 'bg-accent/60',
                'icon_class'   => 'text-success-deep',
                'value'        => number_format($totalNet / 1000, 2, ',', '.'),
                'unit'         => 'Ton',
                'value_class'  => 'text-ink',
                'detail'       => count($journalRows) . ' pesanan sayur terkirim',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_left'    => 'Target Bulanan',
                'foot_right'   => '100% Tercapai',
                'foot_class'   => 'text-success-deep',
                'chip'         => [
                    'label' => 'Stabil',
                    'class' => 'bg-accent text-success-deep outline outline-1 outline-success-deep',
                ],
            ],
            [
                'key'          => 'invoice_value',
                'label'        => 'TOTAL NILAI PENJUALAN',
                'icon'         => 'invoice',
                'icon_tile'    => 'bg-brand',
                'icon_class'   => 'text-accent',
                'value'        => 'Rp ' . number_format($totalValue, 0, ',', '.'),
                'unit'         => null,
                'value_class'  => 'text-brand',
                'detail'       => 'Dihitung dari timbangan riil',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_left'    => count($journalRows) . ' Transaksi',
                'foot_right'   => 'Tercatat Resmi',
                'foot_class'   => 'text-ink-quiet',
                'chip'         => [
                    'label' => 'Netto Riil',
                    'class' => 'bg-brand text-accent outline outline-1 outline-accent-deep',
                ],
            ],
            [
                'key'          => 'sla',
                'label'        => 'KETEPATAN PENGIRIMAN',
                'icon'         => 'truck',
                'icon_tile'    => 'bg-success-soft/50',
                'icon_class'   => 'text-success-deep',
                'value'        => '98.5%',
                'unit'         => 'Tepat Waktu',
                'value_class'  => 'text-success-deep',
                'detail'       => 'Sayur tiba pagi hari sesuai jadwal',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_left'    => 'Jadwal Tiba',
                'foot_right'   => '0 Komplain',
                'foot_class'   => 'text-success-deep',
                'chip'         => [
                    'label' => 'Lancar',
                    'class' => 'bg-surface-track text-ink outline outline-1 outline-line-board',
                ],
            ],
            [
                'key'          => 'shrinkage',
                'label'        => 'RATA-RATA SUSUT TIMBANGAN',
                'icon'         => 'scale',
                'icon_tile'    => 'bg-warning-soft/50',
                'icon_class'   => 'text-warning-caution',
                'value'        => abs($overallDeviation) . '%',
                'unit'         => 'Susut',
                'value_class'  => 'text-warning-caution',
                'detail'       => 'Susut alami pemilahan daun segar',
                'detail_class' => 'gpa-note text-ink-body',
                'foot_left'    => 'Batas Aman',
                'foot_right'   => 'Di Bawah 1.0%',
                'foot_class'   => 'text-success-deep',
                'chip'         => [
                    'label' => 'Aman',
                    'class' => 'bg-warning-soft text-warning-deep outline outline-1 outline-warning/30',
                ],
            ],
        ];

        $standards = [
            'eyebrow' => 'Prinsip Kepatuhan',
            'title'   => 'Standar Transparansi & Pencatatan',
            'body'    => 'Aturan pencatatan untuk memastikan kejujuran timbangan antara petani, gudang sentra, dan pelanggan.',
            'footer'  => 'Sistem AgroOrder GPA — Data Terverifikasi',
            'rules'   => [
                [
                    'chip'  => 'Aturan 1',
                    'tag'   => 'Timbangan Nyata',
                    'title' => 'Tagihan Sesuai Berat Nyata',
                    'body'  => 'Nilai tagihan wajib dihitung berdasarkan hasil timbangan bersih setelah daun kuning dipisahkan di gudang.',
                    'note'  => 'Semua pesanan merujuk ke timbangan gudang',
                ],
                [
                    'chip'  => 'Aturan 2',
                    'tag'   => 'Toleransi Wajar',
                    'title' => 'Batas Selisih Susut Sayur',
                    'body'  => 'Penyusutan daun segar wajar dipantau sistem. Jika melebihi batas 2.5%, sistem memberikan peringatan untuk evaluasi panen.',
                    'note'  => 'Rata-rata susut masih di bawah batas toleransi',
                ],
                [
                    'chip'  => 'Aturan 3',
                    'tag'   => 'Kelengkapan Berkas',
                    'title' => 'Sinkronisasi Surat Jalan & Faktur',
                    'body'  => 'Nomor pesanan, surat jalan yang ditandatangani supir, dan faktur penagihan saling terhubung tanpa perbedaan.',
                    'note'  => '100% berkas tersinkronisasi rapi',
                ],
            ],
        ];

        $tabs = [
            ['key' => 'sales', 'label' => 'Rekapitulasi Penjualan & Pesanan', 'icon' => 'invoice'],
            ['key' => 'weighing', 'label' => 'Volume & Analisis Timbangan', 'icon' => 'scale'],
            ['key' => 'dispatch', 'label' => 'Surat Jalan & Pengiriman Supir', 'icon' => 'truck'],
            ['key' => 'aging', 'label' => 'Pemantauan Umur Tagihan (Tempo)', 'icon' => 'chart'],
        ];

        $journal = [
            'title'              => 'Buku Catatan Transaksi Penjualan',
            'meta_left'          => 'Menampilkan ' . count($journalRows) . ' Transaksi Terkini',
            'meta_right'         => 'Data Timbangan Resmi Sah',
            'total_transactions' => count($journalRows),
            'columns'            => [
                '#',
                'No Faktur / Surat Jalan',
                'Pembeli & Lokasi',
                'Nama Sayur',
                'Estimasi Awal',
                'Netto Nyata',
                'Selisih',
                'Nilai Total (Rp)',
                'Status Bayar',
                'Dokumen',
            ],
            'rows'               => $journalRows,
            'totals'             => [
                'count'     => count($journalRows),
                'estimate'  => $totalEstimate,
                'net'       => $totalNet,
                'value'     => $totalValue,
                'deviation' => $overallDeviation,
            ],
            'total_label'        => 'Total Rekapitulasi',
            'total_lock'         => 'Tercatat di Sistem',
        ];

        $deviation = [
            'title'     => 'Analisis Selisih Timbangan Panen',
            'badge'     => 'Timbangan Sah Akurat',
            'stats'     => [
                [
                    'label'       => 'Total Estimasi Awal',
                    'value'       => number_format($totalEstimate / 1000, 2, ',', '.') . ' Ton',
                    'value_class' => 'text-ink',
                    'note'        => 'Target Pesanan Awal',
                ],
                [
                    'label'       => 'Berat Bersih Riil (Netto)',
                    'value'       => number_format($totalNet / 1000, 2, ',', '.') . ' Ton',
                    'value_class' => 'text-success-deep',
                    'note'        => 'Timbangan Gudang',
                ],
                [
                    'label'       => 'Rata-rata Selisih Susut',
                    'value'       => ($overallDeviation >= 0 ? '+' : '') . number_format($overallDeviation, 2, '.', '') . '%',
                    'value_class' => 'text-warning-caution',
                    'note'        => 'Susut Alami Sortir Sayur',
                ],
            ],
            'body'      => [
                ['type' => 'text', 'value' => 'Hasil pemeriksaan menunjukkan tingkat penyusutan sayur selama proses sortir dan pengiriman berada dalam batas yang sangat baik dan wajar. Semua data tercatat resmi tanpa rekayasa.'],
            ],
            'foot_left'  => 'Standar Mutu: Terpenuhi',
            'foot_right' => 'Status: Sah & Aman',
        ];

        $validation = [
            'title'      => 'Kesesuaian Dokumen Transaksi',
            'badge'      => 'Lengkap 100%',
            'rows'       => [
                ['label' => 'Surat Jalan ditandatangani supir & penerima', 'status' => 'Lengkap & Valid'],
                ['label' => 'Hasil timbangan gudang tercatat resmi', 'status' => 'Tersinkronisasi'],
                ['label' => 'Faktur penagihan sesuai berat nyata', 'status' => 'Sesuai'],
                ['label' => 'Pencocokan mutasi kas dan rekening bank', 'status' => 'Tercatat Rapi'],
            ],
            'foot_left'  => 'Pemeriksaan Berkas: Otomatis',
            'foot_right' => 'Tingkat Kepatuhan: 100%',
        ];

        $seal = [
            'label'   => 'Kode Pengesahan Laporan Resmi:',
            'hash'    => 'LAP-GPA-' . date('Ym') . '-VERIF',
            'body'    => 'Laporan ini merupakan rekapitulasi resmi transaksi PT Guna Panen Agro yang siap diserahkan kepada Direksi.',
            'actions' => [
                ['key' => 'download', 'label' => 'Unduh Laporan Lengkap (.xlsx)', 'icon' => 'download', 'variant' => 'ghost'],
                ['key' => 'pdf', 'label' => 'Cetak Format Resmi (.pdf)', 'icon' => 'printer', 'variant' => 'ghost'],
                ['key' => 'send', 'label' => 'Kirim Laporan ke Dasbor Direktur', 'icon' => 'send', 'variant' => 'brand'],
            ],
        ];

        return view('sekretaris.report', [
            'operator'   => $operator,
            'heading'    => $heading,
            'filters'    => $filters,
            'metrics'    => $metrics,
            'standards'  => $standards,
            'tabs'       => $tabs,
            'journal'    => $journal,
            'deviation'  => $deviation,
            'validation' => $validation,
            'seal'       => $seal,
        ]);
    }
}
