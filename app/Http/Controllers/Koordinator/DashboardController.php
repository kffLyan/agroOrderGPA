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
    // Menampilkan konsol operasional pasokan, persediaan komoditas, dan intake timbangan Koordinator Lapangan.
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

        // Ambil batch panen terkini untuk gate intake logs dengan filter opsional
        $gateLogsQuery = HarvestBatch::with('product')->latest();

        if ($request->filled('date_from')) {
            $gateLogsQuery->whereDate('batch_date', '>=', $request->date_from);
        }

        if ($request->filled('commodity')) {
            $commodity = $request->commodity;
            $gateLogsQuery->whereHas('product', function ($q) use ($commodity) {
                $q->where('name', $commodity);
            });
        }

        if ($request->filled('source')) {
            $source = $request->source;
            if ($source === 'binaan') {
                $gateLogsQuery->where('source_type', 'PETANI_BINAAN');
            } elseif ($source === 'buffer') {
                $gateLogsQuery->where('source_type', 'BUFFER_LUAR');
            }
        }

        $gateLogs = $gateLogsQuery->take(10)->get();
        $commodityList = Product::where('is_active', true)->pluck('name')->values()->all();

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
            'commodityList'       => $commodityList,
            'incomingOrders'      => $incomingOrders,
        ]);
    }
}
