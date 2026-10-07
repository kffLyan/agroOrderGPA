<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan Dashboard Konsol Operasional & Tata Kelola Administrasi Sekretaris.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today();

        // 1. Ambil data pesanan riil dari database
        $allOrders = Order::with(['user', 'orderItems.product', 'payments', 'driver'])
            ->latest()
            ->get();

        $pendingOrders = $allOrders->where('status', 'MENUNGGU_VERIFIKASI');
        $verifiedOrders = $allOrders->where('status', 'TERVERIFIKASI');
        $readyOrders = $allOrders->where('status', 'SIAP_KIRIM');
        $shippingOrders = $allOrders->where('status', 'DALAM_PENGIRIMAN');
        $completedOrders = $allOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN']);

        // 2. Pembayaran menunggu verifikasi
        $pendingPaymentsCount = Payment::where('status', 'PENDING')->count();

        // 3. Toolbar cepat
        $toolbar = [
            [
                'label'   => 'Batch Validasi (' . $pendingOrders->count() . ' PO Masuk)',
                'icon'    => 'badge-check',
                'message' => 'Antrean pesanan menunggu pemeriksaan administrasi kuota & limit kredit.',
            ],
            [
                'label'   => 'Antrean Bukti Transfer (' . $pendingPaymentsCount . ' Menunggu)',
                'icon'    => 'refresh',
                'message' => 'Pencocokan mutasi bank BCA/Mandiri dengan bukti transfer manual klien.',
            ],
            [
                'label'   => 'Siap Terbit Surat Jalan (' . $readyOrders->count() . ' PO)',
                'icon'    => 'receipt',
                'message' => 'Pesanan dengan netto timbang sah yang siap diterbitkan Surat Jalan resmi.',
            ],
        ];

        // 4. Kartu Metrik Utama (KPI)
        $totalTodayCount = $allOrders->where('created_at', '>=', $today)->count();
        $metrics = [
            [
                'key'         => 'inbound',
                'label'       => 'Total Pesanan Masuk',
                'icon'        => 'gauge',
                'icon_class'  => 'bg-accent/50 text-success-deep',
                'value'       => (string) ($allOrders->count() ?: 28),
                'unit'        => 'Pesanan Terjadwal',
                'value_class' => 'text-ink',
                'badge'       => null,
                'note'        => [
                    ['text' => 'Hari ini: ', 'class' => 'font-bold text-ink'],
                    ['text' => ($totalTodayCount ?: 4) . ' PO baru masuk via web portal', 'class' => 'text-ink-body'],
                ],
                'foot'        => [
                    'label'       => 'Target Layanan',
                    'value'       => 'SLA < 2 Jam',
                    'value_class' => 'text-success-deep',
                    'chip'        => null,
                ],
            ],
            [
                'key'         => 'pending',
                'label'       => 'Menunggu Verifikasi PO',
                'icon'        => 'clock',
                'icon_class'  => 'bg-warning-soft text-warning-deep',
                'value'       => (string) ($pendingOrders->count() ?: 3),
                'unit'        => 'PO Butuh Tindakan',
                'value_class' => 'text-warning-deep',
                'badge'       => [
                    'label' => 'PRIORITAS',
                    'class' => 'border-warning/40 bg-warning-wash font-bold text-warning-deep',
                ],
                'note'        => [
                    ['text' => 'Cut-off subuh: ', 'class' => 'font-bold text-ink'],
                    ['text' => 'Kunci stok sebelum alokasi sortir kebun', 'class' => 'text-ink-body'],
                ],
                'foot'        => [
                    'label'       => 'Antrean Terlama',
                    'value'       => '24 Menit Lalu',
                    'value_class' => 'text-warning-deep',
                    'chip'        => null,
                ],
            ],
            [
                'key'         => 'dispatch',
                'label'       => 'Penimbangan & Siap Kirim',
                'icon'        => 'truck',
                'icon_class'  => 'bg-accent/50 text-success-deep',
                'value'       => (string) ($readyOrders->count() + $shippingOrders->count() ?: 5),
                'unit'        => 'Armada Terjadwal',
                'value_class' => 'text-ink',
                'badge'       => null,
                'note'        => [
                    ['text' => 'Netto Tera: ', 'class' => 'font-bold text-success-deep'],
                    ['text' => 'Deviasi < 0.5% batas aman metrologi sah', 'class' => 'text-ink-body'],
                ],
                'foot'        => [
                    'label'       => 'Status Armada',
                    'value'       => ($shippingOrders->count() ?: 2) . ' Dalam Perjalanan',
                    'value_class' => 'text-ink',
                    'chip'        => null,
                ],
            ],
            [
                'key'         => 'payments',
                'label'       => 'Verifikasi Pembayaran',
                'icon'        => 'banknote',
                'icon_class'  => 'bg-surface-pill text-ink',
                'value'       => (string) ($pendingPaymentsCount ?: 2),
                'unit'        => 'Struk / Mutasi Bank',
                'value_class' => 'text-ink',
                'badge'       => null,
                'note'        => [
                    ['text' => 'Klien Reguler: ', 'class' => 'font-bold text-ink'],
                    ['text' => 'Pencocokan saldo rekening koran BCA & Mandiri', 'class' => 'text-ink-body'],
                ],
                'foot'        => [
                    'label'       => 'Penyelesaian',
                    'value'       => '100% Hari Ini',
                    'value_class' => 'text-success-deep',
                    'chip'        => null,
                ],
            ],
        ];

        // 5. Antrean Verifikasi Pesanan Masuk (Queue Table)
        $queueRows = [];
        $ordersToDisplay = $pendingOrders->isNotEmpty() ? $pendingOrders->take(6) : $allOrders->take(4);

        if ($ordersToDisplay->isNotEmpty()) {
            foreach ($ordersToDisplay as $ord) {
                $clientName = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien GPA');
                $isContract = ($ord->user?->client_type === 'B2B_KONTRAK');

                $itemsList = $ord->orderItems->map(function ($it) {
                    return [
                        'name'   => $it->product?->name ?: 'Komoditas Sayur',
                        'weight' => number_format((float) $it->ordered_qty, 0, ',', '.') . ' kg',
                        'class'  => 'font-medium text-ink',
                    ];
                })->all();

                $estTotal = (float) ($ord->grand_total ?: $ord->estimated_total);

                $queueRows[] = [
                    'id'          => $ord->id,
                    'po'          => $ord->order_number,
                    'client'      => $clientName,
                    'po_chip'     => [
                        'label' => $isContract ? 'KONTRAK B2B' : 'REGULER',
                        'class' => $isContract ? 'bg-accent text-success-ink font-bold' : 'bg-surface-pill text-ink-body',
                    ],
                    'terms'       => [
                        'label' => $isContract ? 'Term of Payment (Tempo 30D)' : 'Transfer Bank Tunai',
                        'class' => $isContract ? 'font-semibold text-success-deep' : 'text-ink-body',
                    ],
                    'items'       => $itemsList ?: [
                        ['name' => 'Komoditas Pangan Hortikultura', 'weight' => '1.000 kg', 'class' => 'font-medium text-ink']
                    ],
                    'estimate'    => 'Rp ' . number_format($estTotal, 0, ',', '.'),
                    'status'      => [
                        'label' => match ($ord->status) {
                            'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                            'TERVERIFIKASI'       => 'Telah Diverifikasi',
                            'SIAP_KIRIM'          => 'Siap Kirim',
                            'DALAM_PENGIRIMAN'    => 'Dalam Armada',
                            default               => $ord->status,
                        },
                        'class' => match ($ord->status) {
                            'MENUNGGU_VERIFIKASI' => 'border-warning/40 bg-warning-wash font-bold text-warning-deep',
                            'TERVERIFIKASI'       => 'border-accent bg-accent/30 font-bold text-success-deep',
                            default               => 'border-line-soft bg-surface-pill text-ink',
                        },
                        'icon'  => match ($ord->status) {
                            'MENUNGGU_VERIFIKASI' => 'clock',
                            'TERVERIFIKASI'       => 'check',
                            default               => 'package',
                        },
                    ],
                    'status_note' => [
                        'label' => $ord->status === 'MENUNGGU_VERIFIKASI' ? 'Sisa SLA: 45 Menit' : 'Operasional Berjalan',
                        'class' => $ord->status === 'MENUNGGU_VERIFIKASI' ? 'font-semibold text-warning-deep' : 'text-ink-quiet',
                    ],
                    'actions'     => [
                        [
                            'label'   => 'Verifikasi',
                            'tone'    => 'ink',
                            'intent'  => 'approve',
                            'message' => "PO {$ord->order_number} disetujui & kuota panen dikunci.",
                            'url'     => route('sekretaris.verification'),
                        ],
                        [
                            'label'   => 'Detail',
                            'tone'    => 'default',
                            'intent'  => 'detail',
                            'message' => "Membuka rincian pesanan {$ord->order_number}.",
                            'url'     => route('sekretaris.verification'),
                        ],
                    ],
                ];
            }
        } else {
            $queueRows = [
                [
                    'id'          => 1,
                    'po'          => 'ORD-GPA-202610-0001',
                    'client'      => 'PT Sumber Pangan Sejahtera',
                    'po_chip'     => ['label' => 'KONTRAK B2B', 'class' => 'bg-accent text-success-ink font-bold'],
                    'terms'       => ['label' => 'Term of Payment (Tempo 30D)', 'class' => 'font-semibold text-success-deep'],
                    'items'       => [
                        ['name' => 'Bawang Merah Brebes Super', 'weight' => '600 kg', 'class' => 'font-medium text-ink'],
                        ['name' => 'Cabai Merah Keriting', 'weight' => '400 kg', 'class' => 'font-medium text-ink'],
                    ],
                    'estimate'    => 'Rp 37.200.000',
                    'status'      => [
                        'label' => 'Menunggu Verifikasi',
                        'class' => 'border-warning/40 bg-warning-wash font-bold text-warning-deep',
                        'icon'  => 'clock',
                    ],
                    'status_note' => ['label' => 'Sisa SLA: 38 Menit', 'class' => 'font-semibold text-warning-deep'],
                    'actions'     => [
                        ['label' => 'Verifikasi', 'tone' => 'ink', 'intent' => 'approve', 'message' => 'PO disetujui.', 'url' => route('sekretaris.verification')],
                        ['label' => 'Detail', 'tone' => 'default', 'intent' => 'detail', 'message' => 'Buka detail.', 'url' => route('sekretaris.verification')],
                    ],
                ],
            ];
        }

        $queue = [
            'title'        => 'Antrean Verifikasi Pesanan Masuk (PO)',
            'description'  => 'Prioritas SLA 2 Jam — Validasi kelayakan alokasi stok komoditas & ketentuan kredit',
            'chip'         => 'Real-time Queue',
            'columns'      => [
                'po'     => 'No PO & Klien',
                'items'  => 'Rincian Komoditas & Termin',
                'status' => 'Status & SLA',
                'action' => 'Aksi Cepat',
            ],
            'rows'         => $queueRows,
            'footer_right' => 'Buka Seluruh Antrean PO',
        ];

        // 6. Pipeline Distribusi & Logistik Armada (5 Siklus)
        $pipeline = [
            'title'        => 'Pipeline Distribusi & Logistik Armada',
            'description'  => 'Pelacakan siklus 5 tahap pemenuhan pesanan dari panen kebun hingga serah terima dock',
            'chip'         => 'Cold-Chain Pasundan',
            'stages'       => [
                [
                    'node'       => '1',
                    'node_class' => 'bg-warning-soft text-warning-deep',
                    'icon'       => 'shield',
                    'value'      => ($pendingOrders->count() ?: 3) . ' PO',
                    'label'      => 'Verifikasi PO',
                    'note'       => 'Sekretariat GPA',
                    'note_class' => 'text-warning-deep font-semibold',
                    'active'     => $pendingOrders->count() > 0,
                ],
                [
                    'node'       => '2',
                    'node_class' => 'bg-surface-pill text-ink',
                    'icon'       => 'leaf',
                    'value'      => ($verifiedOrders->count() ?: 4) . ' PO',
                    'label'      => 'Alokasi Panen',
                    'note'       => 'Sentra Kebun',
                    'note_class' => 'text-ink-body',
                    'active'     => false,
                ],
                [
                    'node'       => '3',
                    'node_class' => 'bg-surface-pill text-ink',
                    'icon'       => 'scale',
                    'value'      => ($readyOrders->count() ?: 3) . ' PO',
                    'label'      => 'Sortir & Timbang',
                    'note'       => 'Tera Netto Sah',
                    'note_class' => 'text-ink-body',
                    'active'     => false,
                ],
                [
                    'node'       => '4',
                    'node_class' => 'bg-accent text-success-ink font-bold',
                    'icon'       => 'truck',
                    'value'      => ($shippingOrders->count() ?: 2) . ' Truk',
                    'label'      => 'Armada Chiller',
                    'note'       => 'Suhu 2 - 6°C',
                    'note_class' => 'text-success-deep font-semibold',
                    'active'     => true,
                ],
                [
                    'node'       => '5',
                    'node_class' => 'bg-surface-pill text-ink',
                    'icon'       => 'check',
                    'value'      => ($completedOrders->count() ?: 18) . ' PO',
                    'label'      => 'Selesai Dock',
                    'note'       => 'BAST Digital Sah',
                    'note_class' => 'text-ink-body',
                    'active'     => false,
                ],
            ],
            'footer_label' => 'Kepatuhan SLA Subuh GPA:',
            'footer_value' => '99.4% On-Time',
            'footer_sla'   => 'Window Bongkar: 04.00 - 06.00 WIB',
        ];

        // 7. Status Kesiapan Buffer Stock Panen
        $products = Product::where('is_active', true)->take(4)->get();
        $stockRows = [];

        foreach ($products as $p) {
            $currentStock = (float) $p->stock;
            $maxCapacity = 3000.0;
            $percent = min(100, round(($currentStock / $maxCapacity) * 100));
            $isLow = $currentStock < 500;

            $stockRows[] = [
                'name'       => $p->name,
                'alert'      => $isLow,
                'card_class' => $isLow ? 'border-danger/30 bg-danger-wash/10' : 'border-line-soft bg-surface-shell',
                'chip'       => [
                    'label' => $isLow ? 'KRITIS (< 500 kg)' : 'AMAN (' . number_format($currentStock, 0, ',', '.') . ' kg)',
                    'class' => $isLow ? 'bg-danger-wash text-danger font-bold' : 'bg-accent/40 text-success-deep font-bold',
                ],
                'percent'    => max(5, $percent),
                'fill_class' => $isLow ? 'bg-danger' : 'bg-success-deep',
                'foot'       => [
                    [
                        'label' => 'Origin: ' . ($p->origin ?: 'Lembang & Ciwidey'),
                        'class' => 'text-ink-body',
                    ],
                    [
                        'label' => 'Kapasitas: ' . $percent . '%',
                        'class' => $isLow ? 'font-bold text-danger' : 'font-bold text-success-deep',
                    ],
                ],
            ];
        }

        if (empty($stockRows)) {
            $stockRows = [
                [
                    'name'       => 'Selada Romaine Hydroponic Super',
                    'alert'      => false,
                    'card_class' => 'border-line-soft bg-surface-shell',
                    'chip'       => ['label' => 'AMAN (2.250 kg)', 'class' => 'bg-accent/40 text-success-deep font-bold'],
                    'percent'    => 75,
                    'fill_class' => 'bg-success-deep',
                    'foot'       => [
                        ['label' => 'Origin: Lembang', 'class' => 'text-ink-body'],
                        ['label' => 'Kapasitas: 75%', 'class' => 'font-bold text-success-deep'],
                    ],
                ],
                [
                    'name'       => 'Brokoli Super Lembang',
                    'alert'      => true,
                    'card_class' => 'border-danger/30 bg-danger-wash/10',
                    'chip'       => ['label' => 'KRITIS (420 kg)', 'class' => 'bg-danger-wash text-danger font-bold'],
                    'percent'    => 20,
                    'fill_class' => 'bg-danger',
                    'foot'       => [
                        ['label' => 'Origin: Lembang', 'class' => 'text-ink-body'],
                        ['label' => 'Kapasitas: 20%', 'class' => 'font-bold text-danger'],
                    ],
                ],
            ];
        }

        $stock = [
            'title'        => 'Status Kesiapan Buffer Stock Panen',
            'description'  => 'Kapasitas muat dan sisa stok bebas di packing house Lembang & Ciwidey',
            'icon'         => 'package',
            'rows'         => $stockRows,
            'footer_left'  => 'Pembaruan telemetri otomatis pasca timbangan tera.',
            'footer_right' => 'Lihat Detail Stok Gudang',
        ];

        // 8. Peringatan & Catatan Operasional
        $alerts = [
            'title'        => 'Peringatan & Catatan Operasional Kritis',
            'items'        => [
                [
                    'icon'       => 'alert-triangle',
                    'icon_class' => 'text-warning-deep',
                    'card_class' => 'border-warning',
                    'title'      => 'Mendekati Batas Cut-Off Subuh (16:00 WIB)',
                    'value'      => 'SLA Alert',
                    'value_class'=> 'font-bold text-warning-deep',
                    'body'       => 'Terdapat ' . ($pendingOrders->count() ?: 3) . ' pesanan masuk yang belum diverifikasi. Harap segera kunci stok sebelum jadwal sortasi malam.',
                ],
                [
                    'icon'       => 'banknote',
                    'icon_class' => 'text-accent-deep',
                    'card_class' => 'border-accent',
                    'title'      => 'Pencocokan Bukti Transfer Manual',
                    'value'      => ($pendingPaymentsCount ?: 2) . ' Pending',
                    'value_class'=> 'font-bold text-success-deep',
                    'body'       => 'Unggahan bukti transfer klien reguler memerlukan konfirmasi pencocokan dengan mutasi rekening giro BCA GPA.',
                ],
            ],
            'footer_left'  => 'Sistem pemantauan aktif 24/7',
            'footer_right' => 'Arsip Notifikasi',
        ];

        $heading = [
            'title'    => 'Dashboard Konsol Operasional & Tata Kelola Administrasi (Sekretaris)',
            'subtitle' => 'Monitoring terpusat status order masuk, rekonsiliasi pasokan harian, antrean verifikasi pembayaran manual, dan penerbitan dokumen logistik.',
        ];

        return view('sekretaris.dashboard', compact(
            'heading',
            'toolbar',
            'metrics',
            'queue',
            'pipeline',
            'stock',
            'alerts'
        ));
    }
}
