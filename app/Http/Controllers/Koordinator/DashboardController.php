<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan Konsol Operasional Pasokan, Persediaan & Intake Koordinator Lapangan.
     */
    public function index(Request $request): View
    {
        // Metrik Pasokan & Persediaan dari Database
        $totalStock = (float) HarvestBatch::sum('available_quantity');
        $binaanStock = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        $bufferStock = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $activeProductsCount = Product::where('is_active', true)->count();
        $totalIntakeBatches = HarvestBatch::count();
        $totalIntakeQty = (float) HarvestBatch::sum('initial_quantity');

        // Antrean Pesanan Masuk Klien untuk acuan kuota panen (Pantau Antrean Pesanan Masuk)
        $incomingOrders = Order::with(['user', 'orderItems.product'])
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM'])
            ->latest()
            ->take(8)
            ->get();

        // Stok Pasokan Komoditas (Supply Rows)
        $supplyRows = Product::where('is_active', true)->with('harvestBatches')->get();

        // Ambil batch panen terkini satu kali untuk gate intake logs
        $gateLogs = HarvestBatch::with('product')->latest()->take(10)->get();

        $viewName = view()->exists('koordinator.dashboard') ? 'koordinator.dashboard' : 'coordinator.dashboard';

        return view($viewName, [
            'totalStock'          => $totalStock,
            'binaanStock'         => $binaanStock,
            'bufferStock'         => $bufferStock,
            'activeProductsCount' => $activeProductsCount,
            'totalIntakeBatches'  => $totalIntakeBatches,
            'totalIntakeQty'      => $totalIntakeQty,
            'readyOrdersCount'    => $totalIntakeBatches,
            'weighedOrdersCount'  => $gateLogs->count(),
            'totalOrderQty'       => $totalStock,
            'supplyRows'          => $supplyRows,
            'gateLogs'            => $gateLogs,
            'incomingOrders'      => $incomingOrders,
        ]);
    }
}
