<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimpanPesananRequest;
use App\Http\Resources\Klien\DetailPesananResource;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller manajemen pesanan (PO) untuk Klien
class PesananController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StockService $stockService
    ) {}

    // Menampilkan daftar pesanan riil klien, ringkasan kuota per status siklus, dan total tonase belanja
    public function index(Request $request)
    {
        $userId = Auth::guard('web')->id();

        $allOrders = Order::where('user_id', $userId)
            ->with(['orderItems.product', 'driver', 'verifier', 'invoice', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Mengelompokkan total kuantitas pesanan berdasarkan status operasional (verifikasi, bayar, kirim, selesai)
        $counts = [
            'all' => $allOrders->count(),
            'verifikasi' => $allOrders->where('status', 'MENUNGGU_VERIFIKASI')->count(),
            'bayar' => $allOrders->whereIn('status', ['MENUNGGU_PEMBAYARAN', 'LUNAS'])->count(),
            'kemas' => $allOrders->where('status', 'SIAP_KIRIM')->count(),
            'kirim' => $allOrders->where('status', 'DALAM_PENGIRIMAN')->count(),
            'selesai' => $allOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count(),
            'batal' => $allOrders->where('status', 'BATAL')->count(),
        ];

        $statusTabs = [
            ['label' => 'Semua Pesanan', 'value' => 'all', 'count' => $counts['all']],
            ['label' => 'Menunggu Verifikasi', 'value' => 'verifikasi', 'count' => $counts['verifikasi']],
            ['label' => 'Faktur & Pembayaran', 'value' => 'bayar', 'count' => $counts['bayar']],
            ['label' => 'Siap Kirim', 'value' => 'kemas', 'count' => $counts['kemas']],
            ['label' => 'Dalam Armada Chiller', 'value' => 'kirim', 'count' => $counts['kirim']],
            ['label' => 'Selesai & Sah Diterima', 'value' => 'selesai', 'count' => $counts['selesai']],
            ['label' => 'Dibatalkan', 'value' => 'batal', 'count' => $counts['batal']],
        ];

        $rawOrders = $allOrders ?? ($orders ?? collect());

        $orders = $rawOrders->map(function ($order) {
            $totalOrderedQty = (float) ($order->orderItems ? $order->orderItems->sum('ordered_qty') : 0);
            $totalActualWeight = $totalOrderedQty;
            $hasWeighed = true;
            $deviationKg = 0;
            $deviationPercent = 0;

            $hasPendingProof = ($order->status === 'MENUNGGU_PEMBAYARAN' && $order->has_pending_proof);
            $hasRejectedProof = ($order->status === 'MENUNGGU_PEMBAYARAN' && $order->has_rejected_proof);

            $stage = match ($order->status) {
                'MENUNGGU_VERIFIKASI' => 'verifikasi',
                'MENUNGGU_PEMBAYARAN', 'LUNAS' => 'bayar',
                'SIAP_KIRIM' => 'kemas',
                'DALAM_PENGIRIMAN' => 'kirim',
                'SELESAI', 'SELESAI_CATATAN' => 'selesai',
                'BATAL' => 'batal',
                default => 'verifikasi',
            };

            $statusTone = match ($order->status) {
                'MENUNGGU_VERIFIKASI' => 'warning',
                'MENUNGGU_PEMBAYARAN' => $hasRejectedProof ? 'danger' : ($hasPendingProof ? 'warning' : 'brand'),
                'LUNAS' => 'accent',
                'SIAP_KIRIM' => 'accent',
                'DALAM_PENGIRIMAN' => 'brand',
                'SELESAI', 'SELESAI_CATATAN' => 'pill',
                'BATAL' => 'track',
                default => 'track',
            };

            $statusText = match ($order->status) {
                'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                'MENUNGGU_PEMBAYARAN' => $hasRejectedProof ? 'Bukti Bayar Ditolak' : ($hasPendingProof ? 'Menunggu Verifikasi Pembayaran' : 'Menunggu Pembayaran'),
                'LUNAS' => 'Lunas Terverifikasi',
                'SIAP_KIRIM' => 'Surat Jalan Terbit',
                'DALAM_PENGIRIMAN' => 'Armada Menuju Dock',
                'SELESAI' => 'Selesai Diterima Dock',
                'SELESAI_CATATAN' => 'Selesai (Ada Catatan Retur)',
                'BATAL' => 'Pesanan Dibatalkan',
                default => $order->status,
            };

            $commoditiesStr = $order->orderItems ? $order->orderItems->map(function ($item) {
                return ($item->product->name ?? 'Komoditas').' ('.number_format($item->ordered_qty, 0, ',', '.').' kg)';
            })->join(', ') : '';

            $latestPayment = $order->payments->sortByDesc('id')->first();
            $rawPaymentMethod = strtoupper((string) ($latestPayment?->payment_method ?? ($order->payment_method ?? 'TEMPO_TOP')));
            $resolvedPaymentKey = match (true) {
                str_contains($rawPaymentMethod, 'QRIS') => 'qris',
                str_contains($rawPaymentMethod, 'TRANSFER') => 'transfer',
                str_contains($rawPaymentMethod, 'CASH') => 'cash',
                default => 'tempo',
            };

            return [
                'id' => $order->id,
                'po' => $order->order_number,
                'date' => $order->created_at ? $order->created_at->format('d M Y') : '-',
                'time' => $order->created_at ? $order->created_at->format('H:i').' WIB' : '-',
                'hub' => 'lembang',
                'hub_chip' => 'Sentra Lembang',
                'stage' => $stage,
                'commodities' => $commoditiesStr ?: 'Komoditas Pertanian Segar',
                'estimated_weight' => $totalOrderedQty,
                'estimated_label' => number_format($totalOrderedQty, 0, ',', '.'),
                'grade_tone' => 'success',
                'grade_note' => 'Grade A & Super (Sortasi Bersih)',
                'weight' => $hasWeighed ? $totalActualWeight : null,
                'weight_label_value' => $hasWeighed ? number_format($totalActualWeight, 1, ',', '.') : null,
                'weight_label' => 'KG NETTO',
                'weight_tone' => $deviationPercent > 1 ? 'warning' : 'default',
                'weight_pending' => 'Menunggu Verifikasi & Alokasi',
                'weight_chip' => in_array($order->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI']) ? 'Netto Sah' : null,
                'scale_note' => $hasWeighed ? 'Netto Sah Surat Jalan' : 'Menunggu verifikasi kuota',
                'scale_target' => 'Toleransi Susut < 0.5%',
                'scale_tone' => 'default',
                'deviation_percent' => $deviationPercent,
                'deviation_label' => $hasWeighed ? ($deviationKg >= 0 ? "+{$deviationKg} kg (+{$deviationPercent}%)" : "{$deviationKg} kg (-{$deviationPercent}%)") : '0%',
                'shipment_breakdown' => $hasWeighed ? [
                    ['label' => 'Pesan:', 'value' => number_format($totalOrderedQty, 0).' kg', 'tone' => 'default'],
                    ['label' => 'Kirim:', 'value' => number_format($totalActualWeight, 1).' kg', 'tone' => 'default'],
                ] : [],
                'sj' => $order->surat_jalan_number,
                'sj_pending' => 'SJ Belum Terbit',
                'vehicle' => $order->vehicle_plate_number ?: ($order->driver ? 'Truk Chiller GPA' : null),
                'driver_note' => $order->driver ? ("Supir: {$order->driver->name}".($order->vehicle_plate_number ? " • {$order->vehicle_plate_number}" : '')) : 'Armada belum ditugaskan',
                'shipment_tone' => $order->status === 'DALAM_PENGIRIMAN' ? 'success' : 'default',
                'shipment_note' => $order->status === 'DALAM_PENGIRIMAN' ? 'ETA < 45 Menit Dock' : '',
                'status' => $statusText,
                'status_tone' => $statusTone,
                'status_meta' => 'Order '.($order->order_source === 'WEB_PORTAL' ? 'Web Portal' : 'Routine'),
                'status_note' => $order->status === 'BATAL'
                    ? ('Alasan: '.($order->rejection_reason ?: 'Ditolak Verifikasi Sekretariat'))
                    : ($hasRejectedProof
                        ? ('Ditolak: '.($order->rejected_proof_payment?->verification_note ?: 'Bukti transfer tidak valid/ditolak. Silakan unggah ulang.'))
                        : ($hasPendingProof
                            ? 'Bukti bayar terkirim ke Sekretaris • Menunggu verifikasi'
                            : ($order->status === 'MENUNGGU_VERIFIKASI'
                                ? 'SLA Verifikasi < 2 Jam'
                                : ($order->status === 'MENUNGGU_PEMBAYARAN'
                                    ? 'Menunggu unggah bukti transfer'
                                    : 'Proses Operasional Berjalan')))),
                'total' => (float) $order->grand_total,
                'total_note' => 'Total Nilai Tagihan',
                'total_chip' => $order->invoice_id ? 'Faktur Terbit' : 'Belum Ditagihkan',
                'total_chip_tone' => $order->invoice_id ? 'success' : 'default',
                'payment' => $resolvedPaymentKey,
            ];
        })->values()->all();

        $pagination = [
            'page' => 1,
            'per_page' => 10,
            'total' => count($orders),
            'total_pages' => max(1, (int) ceil(count($orders) / 10)),
            'last_page' => max(1, (int) ceil(count($orders) / 10)),
        ];

        $totalKgOrder = (float) $rawOrders->sum(fn ($o) => $o->orderItems ? $o->orderItems->sum('ordered_qty') : 0);
        $totalKgActual = $totalKgOrder;

        // Menghitung akumulasi tonase komoditas yang dipesan klien dalam kilogram
        $totalKgOrder = (float) $allOrders->sum(fn ($order) => $order->orderItems ? $order->orderItems->sum('ordered_qty') : 0);
        $totalKgActual = $totalKgOrder;

        return view('klien.pesanan.index', [
            'rawOrders' => $allOrders,
            'orders' => $orders,
            'counts' => $counts,
            'statusTabs' => $statusTabs,
            'pagination' => $pagination,
            'totalKgOrder' => $totalKgOrder,
            'totalKgActual' => $totalKgActual,
        ]);
    }

    // Mengalihkan pembuatan pesanan mandiri ke antarmuka katalog dan keranjang belanja
    public function create()
    {
        return redirect()->route('klien.keranjang');
    }

    // Memproses pengajuan pesanan baru, memvalidasi idempotensi duplikasi, dan mengarahkan ke antrean verifikasi
    public function store(SimpanPesananRequest $request)
    {
        $user = Auth::guard('web')->user();
        $submissionToken = $request->input('submission_token');
        $tokenCacheKey = $submissionToken ? "order_submission_token_{$user->id}_{$submissionToken}" : null;

        // Memeriksa token idempotensi untuk mencegah transaksi ganda akibat klik tombol berulang kali
        if ($tokenCacheKey && Cache::has($tokenCacheKey)) {
            $existingOrderId = Cache::get($tokenCacheKey);
            $existingOrder = Order::where('user_id', $user->id)->find($existingOrderId);
            if ($existingOrder) {
                return redirect()->route('klien.pesanan.show', $existingOrder->id)
                    ->with('info', "Pesanan {$existingOrder->order_number} telah berhasil diajukan sebelumnya.");
            }
        }

        try {
            $validatedData = $request->validated();

            // Mendeteksi pengajuan pesanan identik dalam 90 detik terakhir guna mencegah pesanan duplikat
            $recentOrder = $this->orderService->findRecentDuplicateOrder($user, $validatedData);
            if ($recentOrder) {
                if ($tokenCacheKey) {
                    Cache::put($tokenCacheKey, $recentOrder->id, now()->addMinutes(15));
                }

                return redirect()->route('klien.pesanan.show', $recentOrder->id)
                    ->with('info', "Pesanan {$recentOrder->order_number} sudah berhasil diajukan sebelumnya (menghindari duplikasi).");
            }

            // Mengunggah berkas foto bukti transfer pembayaran jika dilampirkan langsung oleh klien
            $proofPath = null;
            if ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payments', 'public');
            }

            // Memproses pembuatan pesanan dan pencadangan stok awal melalui OrderService
            $order = $this->orderService->createOrder(
                $user,
                $validatedData,
                $proofPath
            );

            // Menyimpan token ke cache selama 15 menit agar formulir tidak dapat dikirim berulang
            if ($tokenCacheKey) {
                Cache::put($tokenCacheKey, $order->id, now()->addMinutes(15));
            }

            return redirect()->route('klien.pesanan.show', $order->id)
                ->with('success', "Pesanan {$order->order_number} berhasil diajukan dan sedang masuk antrean verifikasi Sekretaris.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Menampilkan visual tracking status 5 tahap pengiriman, rincian item produk, dan foto serah terima PoD
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::guard('web')->id()) {
            abort(403, 'Akses pesanan tidak diizinkan.');
        }

        $order->loadMissing(['orderItems.product', 'payments', 'verifier', 'driver', 'invoice']);

        $viewData = (new DetailPesananResource($order))->toArray(request());

        return view('klien.pesanan.show', array_merge(['order' => $order], $viewData));
    }

    // Menyiapkan lembar cetak ringkasan transaksi pesanan resmi untuk dokumentasi dan arsip klien
    public function printRekap(Request $request)
    {
        $user = Auth::guard('web')->user();
        $statusFilter = $request->query('status', 'all');

        $orders = $this->buildFilteredOrdersQuery($user, $statusFilter)->get();

        $totalKgOrder = (float) $orders->sum(fn ($order) => $order->orderItems->sum('ordered_qty'));
        $totalKgActual = $totalKgOrder;
        $totalValue = (float) $orders->sum('grand_total');
        $countOrders = $orders->count();

        $docNumber = 'DOC/GPA-REKAP/'.now()->format('Ym').'/'.str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
        $printDate = now()->translatedFormat('d F Y, H:i').' WIB';
        $koordinatorName = User::where('role', 'KOORDINATOR')->first()?->name ?? 'Koordinator Lapangan GPA';
        $sekretarisName = User::where('role', 'SEKRETARIS')->first()?->name ?? 'Sekretaris GPA';

        return view('prints.klien-orders-rekap', compact(
            'user',
            'orders',
            'totalKgOrder',
            'totalKgActual',
            'totalValue',
            'countOrders',
            'docNumber',
            'printDate',
            'statusFilter',
            'koordinatorName',
            'sekretarisName'
        ));
    }

    // Mengekspor daftar transaksi pemesanan klien ke format CSV untuk pencatatan dan rekonsiliasi internal
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = Auth::guard('web')->user();
        $statusFilter = $request->query('status', 'all');
        $filename = 'Rekap_Pesanan_PO_'.($user->company_name ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $user->company_name) : 'Klien').'_'.date('Ymd_His').'.csv';

        $orders = $this->buildFilteredOrdersQuery($user, $statusFilter)->get();

        return $this->streamCsv($filename, function ($handle) use ($user, $orders, $statusFilter) {
            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)'], ';');
            fputcsv($handle, ['REKAPITULASI RESMI TRANSAKSI PEMESANAN (PURCHASE ORDER)'], ';');
            fputcsv($handle, ['KLIEN / MITRA B2B', $user->company_name ?: $user->name], ';');
            fputcsv($handle, ['WAKTU EKSPOR', date('d/m/Y H:i').' WIB'], ';');
            fputcsv($handle, ['FILTER STATUS', ucwords(strtolower(str_replace('_', ' ', $statusFilter)))], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, [
                'No',
                'No. Pesanan (PO)',
                'Tanggal Pesan',
                'Rincian Komoditas',
                'Estimasi Qty (Kg)',
                'Netto Timbang (Kg)',
                'Deviasi Bobot (%)',
                'No. Surat Jalan',
                'Armada & Supir',
                'Status Siklus',
                'Total Nilai (Rp)',
                'Status Faktur',
            ], ';');

            $no = 1;
            foreach ($orders as $order) {
                $orderedKg = (float) $order->orderItems->sum('ordered_qty');
                $actualKg = $orderedKg;
                $hasWeighed = true;
                $amount = (float) $order->grand_total;

                $commoditiesList = $order->commodities_summary;
                $diffPct = 0;

                $statusLabel = match ($order->status) {
                    'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                    'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
                    'LUNAS' => 'Lunas',
                    'SIAP_KIRIM' => 'Siap Kirim',
                    'DALAM_PENGIRIMAN' => 'Dalam Pengiriman',
                    'SELESAI' => 'Selesai',
                    'SELESAI_CATATAN' => 'Selesai (Catatan)',
                    'BATAL' => 'Dibatalkan',
                    default => ucwords(strtolower(str_replace('_', ' ', $order->status))),
                };

                fputcsv($handle, [
                    $no++,
                    $order->order_number,
                    $order->date_formatted,
                    $commoditiesList ?: 'Sayuran Segar Pasundan',
                    number_format($orderedKg, 2, '.', ''),
                    $hasWeighed ? number_format($actualKg, 2, '.', '') : 'Belum Timbang',
                    $hasWeighed ? (($diffPct >= 0 ? '+' : '').$diffPct.'%') : '-',
                    $order->surat_jalan_number ?: '-',
                    $order->vehicle ?: ($order->vehicle_plate_number ?: '-'),
                    $statusLabel,
                    (float) $amount,
                    $order->invoice ? ($order->invoice->status === 'PAID' ? 'Lunas' : 'Faktur Terbit') : 'Belum Ditagihkan',
                ], ';');
            }
        });
    }


    // Membangun query pesanan klien dengan eager loading dan penyaringan status siklus (DRY Helper).
    // Filter status pesanan
    protected function buildFilteredOrdersQuery(User $user, ?string $statusFilter = 'all')
    {
        $query = Order::where('user_id', $user->id)
            ->with(['orderItems.product', 'driver', 'verifier', 'invoice'])
            ->orderBy('created_at', 'desc');

        if ($statusFilter !== 'all' && ! empty($statusFilter)) {
            $stageMap = [
                'verifikasi' => ['MENUNGGU_VERIFIKASI'],
                'bayar' => ['MENUNGGU_PEMBAYARAN'],
                'lunas' => ['LUNAS'],
                'kemas' => ['SIAP_KIRIM'],
                'kirim' => ['DALAM_PENGIRIMAN'],
                'selesai' => ['SELESAI', 'SELESAI_CATATAN'],
                'batal' => ['BATAL'],
            ];

            if (isset($stageMap[$statusFilter])) {
                $query->whereIn('status', $stageMap[$statusFilter]);
            }
        }

        return $query;
    }
}
