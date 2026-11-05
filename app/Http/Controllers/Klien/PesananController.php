<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimpanPesananRequest;
use App\Http\Resources\Klien\DetailPesananResource;
use App\Models\Order;
use App\Models\Product;
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
            'all'        => $allOrders->count(),
            'verifikasi' => $allOrders->where('status', 'MENUNGGU_VERIFIKASI')->count(),
            'bayar'      => $allOrders->whereIn('status', ['MENUNGGU_PEMBAYARAN', 'LUNAS'])->count(),
            'kemas'      => $allOrders->where('status', 'SIAP_KIRIM')->count(),
            'kirim'      => $allOrders->where('status', 'DALAM_PENGIRIMAN')->count(),
            'selesai'    => $allOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count(),
            'batal'      => $allOrders->where('status', 'BATAL')->count(),
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

        $orders = $allOrders;

        $pagination = [
            'page'        => 1,
            'per_page'    => max(1, $orders->count()),
            'total'       => $orders->count(),
            'total_pages' => 1,
            'last_page'   => 1,
        ];

        // Menghitung akumulasi tonase komoditas yang dipesan klien dalam kilogram
        $totalKgOrder  = (float) $allOrders->sum(fn($order) => $order->orderItems ? $order->orderItems->sum('ordered_qty') : 0);
        $totalKgActual = $totalKgOrder;

        return view('klien.pesanan.index', [
            'rawOrders'     => $allOrders,
            'orders'        => $orders,
            'counts'        => $counts,
            'statusTabs'    => $statusTabs,
            'pagination'    => $pagination,
            'totalKgOrder'  => $totalKgOrder,
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
        $user            = Auth::guard('web')->user();
        $submissionToken = $request->input('submission_token');

        // Memeriksa token idempotensi untuk mencegah transaksi ganda akibat klik tombol berulang kali
        if ($submissionToken) {
            $tokenCacheKey = "order_submission_token_{$user->id}_{$submissionToken}";
            if (Cache::has($tokenCacheKey)) {
                $existingOrderId = Cache::get($tokenCacheKey);
                $existingOrder   = Order::where('user_id', $user->id)->find($existingOrderId);
                if ($existingOrder) {
                    return redirect()->route('klien.pesanan.show', $existingOrder->id)
                        ->with('info', "Pesanan {$existingOrder->order_number} telah berhasil diajukan sebelumnya.");
                }
            }
        }

        try {
            $validatedData = $request->validated();

            // Mendeteksi pengajuan pesanan identik dalam 90 detik terakhir guna mencegah pesanan duplikat
            $recentOrder = $this->orderService->findRecentDuplicateOrder($user, $validatedData);
            if ($recentOrder) {
                if ($submissionToken) {
                    Cache::put("order_submission_token_{$user->id}_{$submissionToken}", $recentOrder->id, now()->addMinutes(15));
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
            if ($submissionToken) {
                Cache::put("order_submission_token_{$user->id}_{$submissionToken}", $order->id, now()->addMinutes(15));
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

        $totalKgOrder = (float) $orders->sum(fn ($order) => $order->orderItems->sum('ordered_qty'));
        $totalKgActual = $totalKgOrder;
        $totalValue = (float) $orders->sum('grand_total');
        $countOrders = $orders->count();

        $docNumber = 'DOC/GPA-REKAP/' . now()->format('Ym') . '/' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT);
        $printDate = now()->translatedFormat('d F Y, H:i') . ' WIB';
        $koordinatorName = User::where('role', 'KOORDINATOR')->first()?->name ?? 'Koordinator Lapangan GPA';
        $sekretarisName  = User::where('role', 'SEKRETARIS')->first()?->name ?? 'Sekretaris GPA';

        return view('prints.client-orders-rekap', compact(
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
            fputs($handle, "\xEF\xBB\xBFsep=;\r\n");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)'], ';');
            fputcsv($handle, ['REKAPITULASI RESMI TRANSAKSI PEMESANAN (PURCHASE ORDER)'], ';');
            fputcsv($handle, ['KLIEN / MITRA B2B', $user->company_name ?: $user->name], ';');
            fputcsv($handle, ['WAKTU EKSPOR', date('d/m/Y H:i') . ' WIB'], ';');
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
                    'LUNAS'               => 'Lunas',
                    'SIAP_KIRIM'          => 'Siap Kirim',
                    'DALAM_PENGIRIMAN'    => 'Dalam Pengiriman',
                    'SELESAI'             => 'Selesai',
                    'SELESAI_CATATAN'     => 'Selesai (Catatan)',
                    'BATAL'               => 'Dibatalkan',
                    default               => ucwords(strtolower(str_replace('_', ' ', $order->status))),
                };

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
                    $statusLabel,
                    (float) $amount,
                    $order->invoice ? ($order->invoice->status === 'PAID' ? 'Lunas' : 'Faktur Terbit') : 'Belum Ditagihkan',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
