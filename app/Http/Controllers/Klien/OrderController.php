<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected StockService $stockService;

    public function __construct(OrderService $orderService, StockService $stockService)
    {
        $this->orderService = $orderService;
        $this->stockService = $stockService;
    }

    /**
     * Menampilkan daftar transaksi dan riwayat pesanan milik Klien
     */
    public function index(Request $request)
    {
        $userId = Auth::guard('web')->id();

        $query = Order::where('user_id', $userId)
            ->with(['orderItems.product', 'driver', 'verifier', 'invoice'])
            ->orderBy('created_at', 'desc');

        $rawOrders = $query->get();

        // Hitung count status tabs
        $counts = [
            'all'        => $rawOrders->count(),
            'verifikasi' => $rawOrders->where('status', 'MENUNGGU_VERIFIKASI')->count(),
            'timbang'    => $rawOrders->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM'])->count(),
            'kirim'      => $rawOrders->where('status', 'DALAM_PENGIRIMAN')->count(),
            'selesai'    => $rawOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count(),
            'batal'      => $rawOrders->where('status', 'BATAL')->count(),
        ];

        $statusTabs = [
            ['label' => 'Semua Pesanan', 'value' => 'all', 'count' => $counts['all']],
            ['label' => 'Menunggu Verifikasi', 'value' => 'verifikasi', 'count' => $counts['verifikasi']],
            ['label' => 'Sortasi & Timbang', 'value' => 'timbang', 'count' => $counts['timbang']],
            ['label' => 'Dalam Armada Chiller', 'value' => 'kirim', 'count' => $counts['kirim']],
            ['label' => 'Selesai & Sah Diterima', 'value' => 'selesai', 'count' => $counts['selesai']],
            ['label' => 'Dibatalkan', 'value' => 'batal', 'count' => $counts['batal']],
        ];

        // Format order list untuk view dan Alpine component
        $orders = $rawOrders->map(function ($order) {
            $totalOrderedQty = (float) $order->orderItems->sum('ordered_qty');
            $totalActualWeight = $order->orderItems->sum(function ($item) {
                return $item->actual_net_weight ? (float) $item->actual_net_weight : null;
            });

            $hasWeighed = $totalActualWeight !== null && $order->orderItems->every(fn($i) => $i->actual_net_weight !== null);
            $deviationKg = $hasWeighed ? round($totalActualWeight - $totalOrderedQty, 2) : 0;
            $deviationPercent = ($hasWeighed && $totalOrderedQty > 0) ? round(abs($deviationKg / $totalOrderedQty) * 100, 2) : 0;

            // Map status ke stage filter
            $stage = match ($order->status) {
                'MENUNGGU_VERIFIKASI'                      => 'verifikasi',
                'TERVERIFIKASI', 'SIAP_KIRIM'              => 'timbang',
                'DALAM_PENGIRIMAN'                         => 'kirim',
                'SELESAI', 'SELESAI_CATATAN'               => 'selesai',
                'BATAL'                                    => 'batal',
                default                                    => 'verifikasi',
            };

            // Map status label & visual tone
            $statusTone = match ($order->status) {
                'MENUNGGU_VERIFIKASI'        => 'warning',
                'TERVERIFIKASI'              => 'accent',
                'SIAP_KIRIM'                 => 'accent',
                'DALAM_PENGIRIMAN'           => 'brand',
                'SELESAI', 'SELESAI_CATATAN' => 'pill',
                'BATAL'                      => 'track',
                default                      => 'track',
            };

            $statusText = match ($order->status) {
                'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                'TERVERIFIKASI'       => 'Diverifikasi Sekretaris',
                'SIAP_KIRIM'          => 'Siap Kirim (Penimbangan Sah)',
                'DALAM_PENGIRIMAN'    => 'Armada Menuju Dock',
                'SELESAI'             => 'Selesai Diterima Dock',
                'SELESAI_CATATAN'     => 'Selesai (Ada Catatan Retur)',
                'BATAL'               => 'Pesanan Dibatalkan',
                default               => $order->status,
            };

            $commoditiesStr = $order->orderItems->map(function ($item) {
                return ($item->product->name ?? 'Komoditas') . ' (' . number_format($item->ordered_qty, 0, ',', '.') . ' kg)';
            })->join(', ');

            return [
                'id'                  => $order->id,
                'po'                  => $order->order_number,
                'date'                => $order->created_at->format('d M Y'),
                'time'                => $order->created_at->format('H:i') . ' WIB',
                'hub'                 => 'lembang',
                'hub_chip'            => 'Sentra Lembang',
                'stage'               => $stage,
                'commodities'         => $commoditiesStr ?: 'Komoditas Pertanian Segar',
                'estimated_weight'    => $totalOrderedQty,
                'estimated_label'     => number_format($totalOrderedQty, 0, ',', '.'),
                'grade_tone'          => 'success',
                'grade_note'          => 'Grade A & Super (Sortasi Bersih)',
                'weight'              => $hasWeighed ? $totalActualWeight : null,
                'weight_label_value'  => $hasWeighed ? number_format($totalActualWeight, 1, ',', '.') : null,
                'weight_label'        => 'KG NETTO',
                'weight_tone'         => $deviationPercent > 1 ? 'warning' : 'default',
                'weight_pending'      => 'Antrean Timbang Digital',
                'weight_chip'         => in_array($order->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI']) ? 'Netto Sah' : null,
                'scale_note'          => $hasWeighed ? 'Sertifikat Timbangan Pasundan' : 'Menunggu sortir packing house',
                'scale_target'        => 'Toleransi Susut < 0.5%',
                'scale_tone'          => 'default',
                'deviation_percent'   => $deviationPercent,
                'deviation_label'     => $hasWeighed ? ($deviationKg >= 0 ? "+{$deviationKg} kg (+{$deviationPercent}%)" : "{$deviationKg} kg (-{$deviationPercent}%)") : '0%',
                'shipment_breakdown'  => $hasWeighed ? [
                    ['label' => 'Pesan:', 'value' => number_format($totalOrderedQty, 0) . ' kg', 'tone' => 'default'],
                    ['label' => 'Kirim:', 'value' => number_format($totalActualWeight, 1) . ' kg', 'tone' => 'default'],
                ] : [],
                'sj'                  => $order->surat_jalan_number,
                'sj_pending'          => 'SJ Belum Terbit',
                'vehicle'             => $order->vehicle_plate_number ?: ($order->driver ? 'Truk Chiller GPA' : null),
                'driver_note'         => $order->driver ? "Supir: {$order->driver->name}" : 'Armada belum ditugaskan',
                'shipment_tone'       => $order->status === 'DALAM_PENGIRIMAN' ? 'success' : 'default',
                'shipment_note'       => $order->status === 'DALAM_PENGIRIMAN' ? 'ETA < 45 Menit Dock' : '',
                'status'              => $statusText,
                'status_tone'         => $statusTone,
                'status_meta'         => 'Order ' . ($order->order_source === 'WEB_PORTAL' ? 'Web Portal' : 'Routine'),
                'status_note'         => $order->status === 'MENUNGGU_VERIFIKASI' ? 'SLA Verifikasi < 2 Jam' : 'Proses Operasional Berjalan',
                'total'               => (float) ($order->grand_total ?: $order->estimated_total),
                'total_note'          => $hasWeighed ? 'Total Final Tagihan Sah' : 'Estimasi Nilai PO',
                'total_chip'          => $order->invoice_id ? 'Faktur Terbit' : 'Belum Ditagihkan',
                'total_chip_tone'     => $order->invoice_id ? 'success' : 'default',
                'payment'             => 'tempo',
                'actions'             => [
                    [
                        'label'   => 'Lacak Detail',
                        'variant' => 'solid',
                        'icon'    => 'package',
                        'url'     => route('klien.orders.show', $order->id),
                    ],
                ],
            ];
        })->values()->all();

        $header = [
            'title'    => 'Daftar Pesanan & Pelacakan Armada',
            'chip'     => 'Real-time Tracking',
            'subtitle' => 'Pantau status pesanan, penimbangan netto fisik gudang, dan pergerakan armada chiller secara transparan.',
            'export'   => 'Cetak Rekap Pesanan (PDF)',
            'create'   => 'Buat Pesanan Baru',
            'timezone' => 'WIB (Waktu Indonesia Barat)',
        ];

        $filters = [
            'range' => [
                'label'   => 'Rentang Waktu',
                'options' => [
                    ['label' => 'Semua Waktu', 'value' => 'all'],
                    ['label' => '30 Hari Terakhir', 'value' => '30d'],
                    ['label' => 'Bulan Berjalan', 'value' => 'mtd'],
                ],
            ],
            'hub' => [
                'label'   => 'Sentra Panen',
                'options' => [
                    ['label' => 'Semua Sentra GPA', 'value' => 'all'],
                    ['label' => 'Sentra Lembang', 'value' => 'lembang'],
                    ['label' => 'Sentra Ciwidey', 'value' => 'ciwidey'],
                ],
            ],
            'payment' => [
                'label'   => 'Metode Bayar',
                'options' => [
                    ['label' => 'Semua Metode', 'value' => 'all'],
                    ['label' => 'Term of Payment (Tempo)', 'value' => 'tempo'],
                    ['label' => 'Transfer Bank BCA/Mandiri', 'value' => 'transfer'],
                    ['label' => 'QRIS Dinamis GPA', 'value' => 'qris'],
                ],
            ],
            'deviation_label' => 'Hanya Deviasi Timbang > 1%',
            'search'          => 'Cari no PO, nama komoditas, supir, plat nomor...',
        ];

        $columns = [
            ['label' => 'No PO / Tanggal', 'align' => 'left'],
            ['label' => 'Komoditas & Estimasi', 'align' => 'left'],
            ['label' => 'Netto Sah & Deviasi', 'align' => 'left'],
            ['label' => 'Surat Jalan & Supir', 'align' => 'left'],
            ['label' => 'Status Pesanan', 'align' => 'left'],
            ['label' => 'Total Nilai (Rp)', 'align' => 'right'],
            ['label' => 'Aksi Operasional', 'align' => 'right'],
        ];

        $pagination = [
            'page'        => 1,
            'per_page'    => max(1, count($orders)),
            'total'       => count($orders),
            'total_pages' => 1,
            'last_page'   => 1,
        ];

        $integrity = [
            'title' => 'Integritas Penimbangan Netto & Standar Cold-Chain GPA',
            'body'  => 'Setiap komoditas dipanen sore hari, disortasi higienis, dan ditimbang secara digital di packing house terkalibrasi sebelum diterbitkan Surat Jalan resmi.',
            'chip'  => 'SOP Terjamin Sah',
        ];

        $totalKgOrder = (float) $rawOrders->sum(fn($o) => $o->orderItems->sum('ordered_qty'));
        $totalKgActual = (float) $rawOrders->sum(fn($o) => $o->orderItems->sum(fn($i) => $i->actual_net_weight ?? 0));

        $metrics = [
            [
                'label' => 'Total Transaksi PO',
                'value' => count($orders),
                'unit'  => 'Pesanan Tercatat',
                'tone'  => 'default',
                'icon'  => 'package',
                'note'  => 'Semua Siklus',
            ],
            [
                'label' => 'Volume Permintaan Dipesan',
                'value' => number_format($totalKgOrder, 0, ',', '.'),
                'unit'  => 'Kilogram PO',
                'tone'  => 'default',
                'icon'  => 'leaf',
                'note'  => 'Estimasi Komitmen',
            ],
            [
                'label' => 'Realisasi Netto Sah Timbang',
                'value' => number_format($totalKgActual, 0, ',', '.'),
                'unit'  => 'Kilogram Sah',
                'tone'  => 'success',
                'icon'  => 'scale',
                'note'  => 'Surat Jalan Tervalidasi',
            ],
            [
                'label' => 'Toleransi Deviasi Timbang',
                'value' => '0.34%',
                'unit'  => 'Batas Aman < 1.0%',
                'tone'  => 'success',
                'icon'  => 'gauge',
                'note'  => 'Standar Mutu Lulus',
            ],
            [
                'label' => 'Kepatuhan SLA Pengiriman',
                'value' => '100%',
                'unit'  => 'On-Time Subuh Dock',
                'tone'  => 'success',
                'icon'  => 'shield',
                'note'  => 'Armada Berpendingin',
            ],
        ];

        return view('klien.orders.index', compact(
            'orders',
            'statusTabs',
            'filters',
            'columns',
            'pagination',
            'header',
            'integrity',
            'metrics'
        ));
    }

    /**
     * Mengalihkan pembuatan pesanan ke halaman keranjang & katalog
     */
    public function create()
    {
        return redirect()->route('klien.cart');
    }

    /**
     * Menyimpan transaksi pesanan baru dalam siklus ACID
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            $user = Auth::guard('web')->user();
            $proofPath = null;

            if ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payments', 'public');
            }

            $order = $this->orderService->createOrder(
                $user,
                $request->validated(),
                $proofPath
            );

            return redirect()->route('klien.orders.show', $order->id)
                ->with('success', "Pesanan {$order->order_number} berhasil diajukan dan sedang masuk antrean verifikasi Sekretaris.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Menampilkan detail dan visual tracking 5 tahap pesanan Klien
     */
    public function show(int $id)
    {
        $userId = Auth::guard('web')->id();

        $order = Order::where('user_id', $userId)
            ->with(['orderItems.product', 'payments', 'verifier', 'driver', 'invoice'])
            ->findOrFail($id);

        // Tahapan pelacakan 5 tahap (Visual Pipeline):
        // 1. Pesanan Diajukan
        // 2. Terverifikasi Sekretaris
        // 3. Ditimbang & Disortasi di Gudang
        // 4. Dalam Pengiriman Armada
        // 5. Selesai Diterima Dock
        $statusOrder = $order->status;

        $stepStates = [
            1 => 'done',
            2 => in_array($statusOrder, ['TERVERIFIKASI', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'MENUNGGU_VERIFIKASI' ? 'active' : 'pending'),
            3 => in_array($statusOrder, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'TERVERIFIKASI' ? 'active' : 'pending'),
            4 => in_array($statusOrder, ['DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'SIAP_KIRIM' ? 'active' : 'pending'),
            5 => in_array($statusOrder, ['SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'DALAM_PENGIRIMAN' ? 'active' : 'pending'),
        ];

        if ($statusOrder === 'BATAL') {
            $stepStates = [1 => 'done', 2 => 'cancelled', 3 => 'pending', 4 => 'pending', 5 => 'pending'];
        }

        $trackingSteps = [
            [
                'step'    => 1,
                'title'   => 'PO Diajukan Mandiri',
                'desc'    => 'Pesanan tercatat pada sistem portal web GPA.',
                'time'    => $order->created_at->format('d M Y, H:i') . ' WIB',
                'state'   => $stepStates[1],
            ],
            [
                'step'    => 2,
                'title'   => 'Verifikasi & Kunci Kuota',
                'desc'    => $order->verifier ? "Diverifikasi oleh Sekretaris: {$order->verifier->name}" : 'Menunggu review dokumen dan stok bebas.',
                'time'    => $order->verifier ? $order->updated_at->format('d M Y, H:i') . ' WIB' : 'SLA < 2 Jam',
                'state'   => $stepStates[2],
            ],
            [
                'step'    => 3,
                'title'   => 'Sortasi & Penimbangan Fisik',
                'desc'    => $order->surat_jalan_number ? "Surat Jalan {$order->surat_jalan_number} diterbitkan dengan bobot sah." : 'Proses penimbangan digital di packing house.',
                'time'    => $order->surat_jalan_number ? 'Netto Sah Tercatat' : 'Estimasi H-1 Sore',
                'state'   => $stepStates[3],
            ],
            [
                'step'    => 4,
                'title'   => 'Distribusi Armada Berpendingin',
                'desc'    => $order->driver ? "Supir: {$order->driver->name} (Plat: {$order->vehicle_plate_number})" : 'Penjadwalan supir & armada chiller.',
                'time'    => $order->departure_time ? $order->departure_time->format('H:i') . ' WIB' : 'Target Subuh 04:30',
                'state'   => $stepStates[4],
            ],
            [
                'step'    => 5,
                'title'   => 'Penerimaan Sah di Dock Klien',
                'desc'    => $order->received_by_name ? "Diterima oleh: {$order->received_by_name}" : 'Serah terima fisik, cek cold-chain & tanda tangan digital.',
                'time'    => $order->arrival_time ? $order->arrival_time->format('d M Y, H:i') . ' WIB' : 'Standby Dock',
                'state'   => $stepStates[5],
            ],
        ];

        return view('klien.orders.show', compact('order', 'trackingSteps'));
    }

    /**
     * Tampilan Siap Cetak Dokumen Rekapitulasi Pesanan Resmi (.PDF / Print View)
     */
    public function printRekap(Request $request)
    {
        $user = Auth::guard('web')->user();
        $statusFilter = $request->query('status', 'all');

        $query = Order::where('user_id', $user->id)
            ->with(['orderItems.product', 'driver', 'verifier', 'invoice'])
            ->orderBy('created_at', 'desc');

        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $stageMap = [
                'verifikasi' => ['MENUNGGU_VERIFIKASI'],
                'timbang'    => ['TERVERIFIKASI', 'SIAP_KIRIM'],
                'kirim'      => ['DALAM_PENGIRIMAN'],
                'selesai'    => ['SELESAI', 'SELESAI_CATATAN'],
                'batal'      => ['BATAL'],
            ];

            if (isset($stageMap[$statusFilter])) {
                $query->whereIn('status', $stageMap[$statusFilter]);
            }
        }

        $orders = $query->get();

        $totalKgOrder = (float) $orders->sum(fn ($o) => $o->orderItems->sum('ordered_qty'));
        $totalKgActual = (float) $orders->sum(fn ($o) => $o->orderItems->sum(fn ($i) => $i->actual_net_weight ?? 0));
        $totalValue = (float) $orders->sum(fn ($o) => (float) ($o->grand_total ?: $o->estimated_total));
        $countOrders = $orders->count();

        $docNumber = 'DOC/GPA-REKAP/' . now()->format('Ym') . '/' . str_pad($user->id, 4, '0', STR_PAD_LEFT);
        $printDate = now()->translatedFormat('d F Y, H:i') . ' WIB';

        return view('klien.orders.print-rekap', compact(
            'user',
            'orders',
            'totalKgOrder',
            'totalKgActual',
            'totalValue',
            'countOrders',
            'docNumber',
            'printDate',
            'statusFilter'
        ));
    }
}
