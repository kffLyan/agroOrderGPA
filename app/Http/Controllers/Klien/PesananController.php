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
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Manajemen Pesanan (Purchase Order / PO) Klien.
 *
 * Penjelasan UKK:
 * Modul ini menangani seluruh siklus pesanan dari sisi pembeli / klien:
 * 1. Riwayat pesanan dan status siklus 5 tahap live-tracking.
 * 2. Pembuatan pesanan baru (checkout) dengan proteksi idempotency klik ganda.
 * 3. Tampilan detail pesanan beserta bukti serah terima (PoD) foto pengantaran.
 * 4. Cetak rekap pesanan A4 dan ekspor CSV.
 */
class PesananController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StockService $stockService
    ) {}

    /**
     * Menampilkan daftar transaksi dan riwayat pesanan milik Klien.
     */
    public function index(Request $request)
    {
        $userId = Auth::guard('web')->id();

        $rawOrders = Order::where('user_id', $userId)
            ->with(['orderItems.product', 'driver', 'verifier', 'invoice', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 1. Hitung status tabs agregasi
        $counts = [
            'all'        => $rawOrders->count(),
            'verifikasi' => $rawOrders->where('status', 'MENUNGGU_VERIFIKASI')->count(),
            'bayar'      => $rawOrders->whereIn('status', ['MENUNGGU_PEMBAYARAN', 'LUNAS'])->count(),
            'kemas'      => $rawOrders->where('status', 'SIAP_KIRIM')->count(),
            'kirim'      => $rawOrders->where('status', 'DALAM_PENGIRIMAN')->count(),
            'selesai'    => $rawOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count(),
            'batal'      => $rawOrders->where('status', 'BATAL')->count(),
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

        $orders = $rawOrders;

        $pagination = [
            'page'        => 1,
            'per_page'    => max(1, $orders->count()),
            'total'       => $orders->count(),
            'total_pages' => 1,
            'last_page'   => 1,
        ];

        $totalKgOrder = (float) $rawOrders->sum(fn($o) => $o->orderItems ? $o->orderItems->sum('ordered_qty') : 0);
        $totalKgActual = $totalKgOrder;

        return view('klien.pesanan.index', [
            'rawOrders'     => $rawOrders,
            'orders'        => $orders,
            'counts'        => $counts,
            'statusTabs'    => $statusTabs,
            'pagination'    => $pagination,
            'totalKgOrder'  => $totalKgOrder,
            'totalKgActual' => $totalKgActual,
        ]);
    }

    /**
     * Mengalihkan pembuatan pesanan ke halaman keranjang & katalog.
     */
    public function create()
    {
        return redirect()->route('klien.keranjang');
    }

    /**
     * Menyimpan transaksi pesanan baru dari keranjang klien.
     */
    public function store(StoreOrderRequest $request)
    {
        $user = Auth::guard('web')->user();
        $submissionToken = $request->input('submission_token');

        // 1. Cek token pesanan untuk mencegah klik ganda (idempotency)
        if ($submissionToken) {
            $tokenCacheKey = "order_submission_token_{$user->id}_{$submissionToken}";
            if (Cache::has($tokenCacheKey)) {
                $existingOrderId = Cache::get($tokenCacheKey);
                $existingOrder = Order::where('user_id', $user->id)->find($existingOrderId);
                if ($existingOrder) {
                    return redirect()->route('klien.pesanan.show', $existingOrder->id)
                        ->with('info', "Pesanan {$existingOrder->order_number} telah berhasil diajukan sebelumnya.");
                }
            }
        }

        try {
            $validatedData = $request->validated();

            // 2. Deteksi apakah ada pesanan duplikat identik dalam kurun 90 detik terakhir
            $recentOrder = $this->orderService->findRecentDuplicateOrder($user, $validatedData);
            if ($recentOrder) {
                if ($submissionToken) {
                    Cache::put("order_submission_token_{$user->id}_{$submissionToken}", $recentOrder->id, now()->addMinutes(15));
                }

                return redirect()->route('klien.pesanan.show', $recentOrder->id)
                    ->with('info', "Pesanan {$recentOrder->order_number} sudah berhasil diajukan sebelumnya (menghindari duplikasi).");
            }

            // 3. Simpan file foto bukti pembayaran jika ada
            $proofPath = null;
            if ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('payments', 'public');
            }

            // 4. Buat pesanan baru melalui OrderService
            $order = $this->orderService->createOrder(
                $user,
                $validatedData,
                $proofPath
            );

            // 5. Simpan token ke cache agar form tidak bisa dikirim ulang
            if ($submissionToken) {
                Cache::put("order_submission_token_{$user->id}_{$submissionToken}", $order->id, now()->addMinutes(15));
            }

            return redirect()->route('klien.pesanan.show', $order->id)
                ->with('success', "Pesanan {$order->order_number} berhasil diajukan dan sedang masuk antrean verifikasi Sekretaris.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Menampilkan detail dan visual tracking 5 tahap pesanan Klien.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::guard('web')->id()) {
            abort(403, 'Akses pesanan tidak diizinkan.');
        }

        $order->loadMissing(['orderItems.product', 'payments', 'verifier', 'driver', 'invoice']);

        $viewData = $this->prepareOrderDetailViewData($order);

        return view('klien.pesanan.show', array_merge(['order' => $order], $viewData));
    }

    /**
     * Menyiapkan view-model detail pesanan untuk tampilan Blade.
     */
    protected function prepareOrderDetailViewData(Order $order): array
    {
        $hasPendingProof = ($order->status === 'MENUNGGU_PEMBAYARAN' && $order->has_pending_proof);
        $podPhotos = $order->pod_photo_asset_urls;
        $manifestPhoto = $podPhotos[0] ?? null;
        $dockPhoto = $podPhotos[1] ?? ($podPhotos[0] ?? null);
        $hasTwoPhotos = count($podPhotos) >= 2;
        $hasAnyPhoto = !empty($podPhotos);

        $statusStyles = [
            'MENUNGGU_VERIFIKASI' => 'bg-warning-soft text-warning-deep ring-warning/40',
            'MENUNGGU_PEMBAYARAN' => $hasPendingProof ? 'bg-warning-soft text-warning-deep ring-warning/40' : 'bg-brand/10 text-brand ring-brand/30',
            'LUNAS'               => 'bg-accent/40 text-success-deep ring-success/30',
            'SIAP_KIRIM'          => 'bg-accent text-ink ring-accent-edge',
            'DALAM_PENGIRIMAN'    => 'bg-brand text-white ring-accent',
            'SELESAI'             => 'bg-accent text-success-ink ring-accent',
            'SELESAI_CATATAN'     => 'bg-warning-soft text-warning-deep ring-warning',
            'BATAL'               => 'bg-surface-pill text-ink-quiet ring-line-board',
        ];

        $statusLabels = [
            'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi Pesanan',
            'MENUNGGU_PEMBAYARAN' => $hasPendingProof ? 'Menunggu Verifikasi Pembayaran' : 'Menunggu Pembayaran',
            'LUNAS'               => 'Pembayaran Lunas',
            'SIAP_KIRIM'          => 'Surat Jalan Terbit (Siap Kirim)',
            'DALAM_PENGIRIMAN'    => 'Armada Menuju Dock',
            'SELESAI'             => 'Selesai & Diterima',
            'SELESAI_CATATAN'     => 'Selesai (Ada Catatan Retur)',
            'BATAL'               => 'Pesanan Dibatalkan',
        ];

        $statusOrder = $order->status;

        $stepStates = [
            1 => 'done',
            2 => in_array($statusOrder, ['MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'MENUNGGU_VERIFIKASI' ? 'active' : 'pending'),
            3 => in_array($statusOrder, ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'MENUNGGU_PEMBAYARAN' ? 'active' : 'pending'),
            4 => in_array($statusOrder, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? 'done' : ($statusOrder === 'LUNAS' ? 'active' : 'pending'),
            5 => in_array($statusOrder, ['SELESAI', 'SELESAI_CATATAN']) ? 'done' : (in_array($statusOrder, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN']) ? 'active' : 'pending'),
        ];

        if ($statusOrder === 'BATAL') {
            $stepStates = [1 => 'done', 2 => 'cancelled', 3 => 'pending', 4 => 'pending', 5 => 'pending'];
        }

        $rejectedPayment = $order->has_rejected_proof ? $order->rejected_proof_payment : null;

        return compact(
            'hasPendingProof',
            'podPhotos',
            'manifestPhoto',
            'dockPhoto',
            'hasTwoPhotos',
            'hasAnyPhoto',
            'statusStyles',
            'statusLabels',
            'stepStates',
            'rejectedPayment'
        );
    }

    /**
     * Tampilan Siap Cetak Dokumen Rekapitulasi Pesanan Resmi (.PDF / Print View).
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
                'bayar'      => ['MENUNGGU_PEMBAYARAN'],
                'lunas'      => ['LUNAS'],
                'kemas'      => ['SIAP_KIRIM'],
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
        $totalKgActual = $totalKgOrder;
        $totalValue = (float) $orders->sum('grand_total');
        $countOrders = $orders->count();

        $docNumber = 'DOC/GPA-REKAP/' . now()->format('Ym') . '/' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
        $printDate = now()->translatedFormat('d F Y, H:i') . ' WIB';

        return view('prints.client-orders-rekap', compact(
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

    /**
     * Unduh Rekap Transaksi Pemesanan Klien B2B (.CSV / Excel).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = Auth::guard('web')->user();
        $statusFilter = $request->query('status', 'all');

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Rekap_Pesanan_PO_' . ($user->company_name ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $user->company_name) : 'Klien') . '_' . date('Ymd_His') . '.csv"',
        ];

        $query = Order::where('user_id', $user->id)
            ->with(['orderItems.product', 'driver', 'verifier', 'invoice'])
            ->orderBy('created_at', 'desc');

        if ($statusFilter !== 'all' && !empty($statusFilter)) {
            $stageMap = [
                'verifikasi' => ['MENUNGGU_VERIFIKASI'],
                'bayar'      => ['MENUNGGU_PEMBAYARAN'],
                'lunas'      => ['LUNAS'],
                'kemas'      => ['SIAP_KIRIM'],
                'kirim'      => ['DALAM_PENGIRIMAN'],
                'selesai'    => ['SELESAI', 'SELESAI_CATATAN'],
                'batal'      => ['BATAL'],
            ];

            if (isset($stageMap[$statusFilter])) {
                $query->whereIn('status', $stageMap[$statusFilter]);
            }
        }

        $orders = $query->get();

        return response()->stream(function () use ($user, $orders, $statusFilter) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)']);
            fputcsv($handle, ['REKAPITULASI RESMI TRANSAKSI PEMESANAN (PURCHASE ORDER)']);
            fputcsv($handle, ['KLIEN / MITRA B2B', $user->company_name ?: $user->name]);
            fputcsv($handle, ['WAKTU EKSPOR', date('d/m/Y H:i') . ' WIB']);
            fputcsv($handle, ['FILTER STATUS', strtoupper($statusFilter)]);
            fputcsv($handle, []);
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
            ]);

            $no = 1;
            foreach ($orders as $order) {
                $orderedKg = (float) $order->orderItems->sum('ordered_qty');
                $actualKg = $orderedKg;
                $hasWeighed = true;
                $amount = (float) $order->grand_total;

                $commoditiesList = $order->commodities_summary;
                $diffPct = 0;

                fputcsv($handle, [
                    $no++,
                    $order->order_number,
                    $order->date_formatted,
                    $commoditiesList ?: 'Sayuran Segar Pasundan',
                    number_format($orderedKg, 2, '.', ''),
                    $hasWeighed ? number_format($actualKg, 2, '.', '') : 'Belum Timbang',
                    $hasWeighed ? (($diffPct >= 0 ? '+' : '') . $diffPct . '%') : '-',
                    $order->surat_jalan_number ?: '-',
                    $order->vehicle ?: ($order->vehicle_plate_number ?: '-'),
                    $order->status,
                    (float) $amount,
                    $order->invoice ? ($order->invoice->status === 'PAID' ? 'Lunas' : 'Faktur Terbit') : 'Belum Ditagihkan',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
