<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sekretaris\DashboardQueueResource;
use App\Http\Resources\Sekretaris\DashboardStockResource;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
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

        // 1. Ambil data pesanan riil dari database dengan relasi lengkap
        $allOrders = Order::with(['user', 'orderItems.product', 'payments', 'driver'])
            ->latest()
            ->get();

        $pendingOrders        = $allOrders->where('status', 'MENUNGGU_VERIFIKASI');
        $waitingPaymentOrders = $allOrders->where('status', 'MENUNGGU_PEMBAYARAN');
        $paidOrders           = $allOrders->where('status', 'LUNAS');
        $verifiedOrders       = $allOrders->whereIn('status', ['MENUNGGU_PEMBAYARAN', 'LUNAS']);
        $readyOrders          = $allOrders->where('status', 'SIAP_KIRIM');
        $shippingOrders       = $allOrders->where('status', 'DALAM_PENGIRIMAN');
        $completedOrders      = $allOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN']);

        // 2. Pembayaran menunggu verifikasi (hanya yang sudah upload bukti)
        $pendingPaymentsCount = Payment::whereIn('status', ['MENUNGGU_VERIFIKASI', 'PENDING'])
            ->whereNotNull('proof_url')
            ->where('proof_url', '!=', '')
            ->count();

        $totalTodayCount = $allOrders->where('created_at', '>=', $today)->count();

        // 3. Antrean Verifikasi Pesanan Masuk (Queue Table) via DashboardQueueResource
        $ordersToDisplay = $pendingOrders->isNotEmpty() ? $pendingOrders->take(6) : $allOrders->take(4);
        $queueRows = $ordersToDisplay->map(
            fn ($ord) => (new DashboardQueueResource($ord))->toArray($request)
        )->all();

        // 4. Status Kesiapan Buffer Stock Panen via DashboardStockResource (Eager load cegah N+1)
        $products = Product::where('is_active', true)
            ->with(['harvestBatches' => fn ($q) => $q->where('available_quantity', '>', 0)])
            ->take(4)
            ->get();

        $stockRows = $products->map(
            fn ($p) => (new DashboardStockResource($p))->toArray($request)
        )->all();

        return view('sekretaris.dashboard', [
            'queueRows'                 => $queueRows,
            'stockRows'                 => $stockRows,
            'allOrdersCount'            => $allOrders->count(),
            'pendingOrdersCount'        => $pendingOrders->count(),
            'waitingPaymentOrdersCount' => $waitingPaymentOrders->count(),
            'paidOrdersCount'           => $paidOrders->count(),
            'verifiedOrdersCount'       => $verifiedOrders->count(),
            'readyOrdersCount'          => $readyOrders->count(),
            'shippingOrdersCount'       => $shippingOrders->count(),
            'completedOrdersCount'      => $completedOrders->count(),
            'totalTodayCount'           => $totalTodayCount,
            'pendingPaymentsCount'      => $pendingPaymentsCount,
        ]);
    }
}
