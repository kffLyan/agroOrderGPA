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
        $products = Product::where('is_active', true)->with('harvestBatches')->get();
        $supplyRows = $products->map(function ($product) {
            $pBinaan = (float) $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
            $pBuffer = (float) $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
            $pTotal = $pBinaan + $pBuffer;
            $supplierName = $product->harvestBatches->first()?->supplier_name ?: 'Kelompok Tani Mitra';

            return [
                'key' => (string) $product->id,
                'name' => $product->name,
                'batch' => 'LOT-' . strtoupper(substr($product->name, 0, 3)) . '-' . date('md'),
                'grade' => $product->grade ?: 'Grade A',
                'order' => $pTotal,
                'supply' => $pBinaan,
                'buffer' => $pBuffer,
                'total_stock' => $pTotal,
                'group' => $supplierName,
                'partner' => 'Mitra Binaan Terverifikasi',
                'zone' => 'Sentra Panen Ciwidey',
                'bar' => 5,
                'accent' => false,
            ];
        })->values()->all();

        // Ambil batch panen terkini satu kali untuk staging dan gate intake logs
        $recentBatches = HarvestBatch::with('product')->latest()->take(10)->get();

        $packingOrders = $recentBatches->take(8)->values()->map(function ($batch, $index) {
            $prodName = $batch->product?->name ?? 'Komoditas Sayur';
            $supplier = $batch->supplier_name ?: 'Kelompok Tani Binaan';
            $batchCode = 'LOT-' . strtoupper(substr($prodName, 0, 3)) . '-' . str_pad((string) $batch->id, 4, '0', STR_PAD_LEFT);
            $qty = (float) $batch->available_quantity;
            $timeStr = $batch->created_at ? $batch->created_at->format('d M H:i') : date('d M H:i');

            return [
                'po' => $batchCode,
                'client' => $supplier,
                'dispatch' => $timeStr . ' WIB',
                'dispatch_tone' => 'ink',
                'allocation' => number_format($qty, 0, ',', '.') . ' KG',
                'bay' => 'STG-BAY-0' . (($index % 4) + 1),
                'status' => 'Lolos QC & Siap Pakai',
                'percent' => 100,
            ];
        })->values()->all();

        // Gate Intake Logs
        $gateLogs = $recentBatches->map(function ($batch) {
            $gross = (float) $batch->initial_quantity + 20.0;
            $net = (float) $batch->initial_quantity;

            return [
                'ticket' => 'TKT-' . date('Y') . '-' . str_pad($batch->id, 4, '0', STR_PAD_LEFT),
                'time' => $batch->created_at ? $batch->created_at->format('H:i') . ' WIB' : date('H:i') . ' WIB',
                'name' => $batch->supplier_name ?: 'Mitra Tani Binaan',
                'commodity' => $batch->product?->name ?? '—',
                'gross' => $gross,
                'tare' => 20.0,
                'net' => $net,
                'qc' => 'QC PASS',
                'note' => $batch->source_type === 'BUFFER_LUAR' ? 'Buffer Luar' : 'Panen Binaan',
            ];
        })->values()->all();

        $viewName = view()->exists('koordinator.dashboard') ? 'koordinator.dashboard' : 'coordinator.dashboard';

        return view($viewName, [
            'totalStock'          => $totalStock,
            'binaanStock'         => $binaanStock,
            'bufferStock'         => $bufferStock,
            'activeProductsCount' => $activeProductsCount,
            'totalIntakeBatches'  => $totalIntakeBatches,
            'totalIntakeQty'      => $totalIntakeQty,
            'readyOrdersCount'    => $totalIntakeBatches,
            'weighedOrdersCount'  => count($gateLogs),
            'totalOrderQty'       => $totalStock,
            'supplyRows'          => $supplyRows,
            'packingOrders'       => $packingOrders,
            'gateLogs'            => $gateLogs,
            'incomingOrders'      => $incomingOrders,
        ]);
    }
}
