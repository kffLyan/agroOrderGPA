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
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $rawOrders = Order::where('user_id', $userId)
            ->with(['orderItems.product', 'driver', 'verifier', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('klien.orders.index', compact('rawOrders'));
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

        return view('klien.orders.show', compact('order'));
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

    /**
     * Unduh Rekap Transaksi Pemesanan Klien B2B (.CSV / Excel)
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = Auth::guard('web')->user();
        $statusFilter = $request->query('status', 'all');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Rekap_Pesanan_PO_' . ($user->company_name ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $user->company_name) : 'Klien') . '_' . date('Ymd_His') . '.csv"',
        ];

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
                $actualKg = $order->orderItems->sum(fn ($i) => $i->actual_net_weight ? (float) $i->actual_net_weight : null);
                $hasWeighed = $actualKg !== null && $order->orderItems->every(fn($i) => $i->actual_net_weight !== null);
                $amount = (float) ($order->grand_total ?: $order->estimated_total);

                $commoditiesList = $order->orderItems->map(function ($item) {
                    return ($item->product->name ?? 'Komoditas') . ' (' . number_format($item->ordered_qty, 0, ',', '.') . ' kg)';
                })->join(', ');

                $diffPct = ($hasWeighed && $orderedKg > 0) ? round((($actualKg - $orderedKg) / $orderedKg) * 100, 2) : 0;

                fputcsv($handle, [
                    $no++,
                    $order->order_number,
                    $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-',
                    $commoditiesList ?: 'Sayuran Segar Pasundan',
                    number_format($orderedKg, 2, '.', ''),
                    $hasWeighed ? number_format($actualKg, 2, '.', '') : 'Belum Timbang',
                    $hasWeighed ? (($diffPct >= 0 ? '+' : '') . $diffPct . '%') : '-',
                    $order->surat_jalan_number ?: '-',
                    $order->vehicle_plate_number ? ($order->vehicle_plate_number . ' / ' . ($order->driver ? $order->driver->name : 'Supir GPA')) : '-',
                    $order->status,
                    (float) $amount,
                    $order->invoice ? ($order->invoice->status === 'PAID' ? 'Lunas' : 'Faktur Terbit') : 'Belum Ditagihkan',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
