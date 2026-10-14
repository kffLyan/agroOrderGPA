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
}
