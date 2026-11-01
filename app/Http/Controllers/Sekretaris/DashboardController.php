<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sekretaris\AntreanDashboardResource;
use App\Http\Resources\Sekretaris\StokDashboardResource;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Controller pemantauan dashboard operasional, antrean verifikasi, dan stok untuk Sekretaris
class DashboardController extends Controller
{
    // Menampilkan konsol operasional sekretaris: antrean verifikasi, konfirmasi pembayaran, dan status stok
    public function index(Request $request): View
    {
        $today = Carbon::today();

        // 1. Mengambil seluruh pesanan aktif beserta relasi user, item produk, pembayaran, dan supir
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

        // 2. Menghitung pembayaran yang sudah mengunggah bukti transfer dan menunggu verifikasi sekretaris
        $pendingPaymentsCount = Payment::whereIn('status', ['MENUNGGU_VERIFIKASI', 'PENDING'])
            ->whereNotNull('proof_url')
            ->where('proof_url', '!=', '')
            ->count();

        $totalTodayCount = $allOrders->where('created_at', '>=', $today)->count();

        // 3. Menyiapkan baris antrean pesanan masuk yang paling mendesak untuk diverifikasi segera
        $ordersToDisplay = $pendingOrders->isNotEmpty() ? $pendingOrders->take(6) : $allOrders->take(4);
        $queueRows = $ordersToDisplay->map(
            fn ($order) => (new AntreanDashboardResource($order))->toArray($request)
        )->all();

        // 4. Memeriksa ketersediaan stok fisik komoditas aktif di gudang untuk mencegah pesanan kosong
        $products = Product::where('is_active', true)
            ->with(['harvestBatches' => fn ($batchQuery) => $batchQuery->where('available_quantity', '>', 0)])
            ->take(4)
            ->get();

        $stockRows = $products->map(
            fn ($product) => (new StokDashboardResource($product))->toArray($request)
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
