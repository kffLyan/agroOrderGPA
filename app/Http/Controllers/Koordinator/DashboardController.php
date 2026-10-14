<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan Konsol Operasional & Kesiapan Panen Koordinator Lapangan.
     */
    public function index(Request $request): View
    {
        // Metrik Dinamis dari Database
        $totalStock = (float) HarvestBatch::sum('available_quantity');
        $binaanStock = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        $bufferStock = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $readyOrdersCount = Order::where('status', 'TERVERIFIKASI')->count();
        $weighedOrdersCount = Order::where('status', 'SIAP_KIRIM')->count();
        $totalOrderQty = (float) OrderItem::whereHas('order', fn($q) => $q->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM']))->sum('ordered_qty');

        // Stok Pasokan Komoditas (Supply Rows)
        $products = Product::where('is_active', true)->with('harvestBatches')->get();
        $supplyRows = $products->map(function ($product) {
            $pOrderQty = (float) OrderItem::where('product_id', $product->id)
                ->whereHas('order', fn($q) => $q->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM']))
                ->sum('ordered_qty');
            $pBinaan = (float) $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
            $pBuffer = (float) $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
            $supplierName = $product->harvestBatches->first()?->supplier_name ?: 'Poktan Binaan Ciwidey';

            return [
                'key' => (string) $product->id,
                'name' => $product->name,
                'batch' => 'LOT-' . strtoupper(substr($product->name, 0, 3)) . '-' . date('md'),
                'grade' => $product->grade ?: 'Grade A',
                'order' => $pOrderQty,
                'supply' => $pBinaan,
                'buffer' => $pBuffer,
                'group' => $supplierName,
                'partner' => 'Mitra Binaan Terverifikasi',
                'zone' => 'Hub Ciwidey',
                'bar' => 5,
                'accent' => false,
            ];
        })->values()->all();

        // Persiapan Pesanan & Packing (Packing Orders)
        $activeOrders = Order::with(['orderItems.product', 'user'])
            ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM'])
            ->latest()
            ->take(10)
            ->get();

        $packingOrders = $activeOrders->map(function ($order, $index) {
            $isWeighed = $order->status === 'SIAP_KIRIM';
            $totalQty = (float) $order->orderItems->sum('ordered_qty');
            $clientName = $order->user?->company_name ?: ($order->user?->name ?? 'Klien B2B');
            $targetDate = $order->target_delivery_date ? $order->target_delivery_date->format('d M') : date('d M');

            return [
                'po' => $order->order_number,
                'client' => $clientName,
                'dispatch' => $targetDate . ' 04:30 WIB',
                'dispatch_tone' => $isWeighed ? 'ink' : 'accent',
                'allocation' => number_format($totalQty, 0, ',', '.') . ' KG',
                'bay' => 'DOCK-BAY-0' . (($index % 4) + 1),
                'status' => $isWeighed ? 'Siap Kirim (Netto Sah)' : 'Sortir & Timbang',
                'percent' => $isWeighed ? 100 : 65,
            ];
        })->values()->all();

        // Gate Intake Logs
        $recentBatches = HarvestBatch::with('product')->latest()->take(10)->get();
        $gateLogs = $recentBatches->map(function ($batch) {
            $gross = (float) $batch->initial_quantity + 20.0;
            $net = (float) $batch->initial_quantity;

            return [
                'ticket' => 'TKT-' . date('Y') . '-' . str_pad($batch->id, 4, '0', STR_PAD_LEFT),
                'time' => $batch->created_at ? $batch->created_at->format('H:i') . ' WIB' : date('H:i') . ' WIB',
                'name' => $batch->supplier_name,
                'commodity' => $batch->product?->name ?? 'Aneka Sayur',
                'gross' => $gross,
                'tare' => 20.0,
                'net' => $net,
                'qc' => 'QC PASS',
                'note' => $batch->source_type === 'BUFFER_LUAR' ? 'Buffer Luar' : 'Panen Binaan',
            ];
        })->values()->all();

        $viewName = view()->exists('koordinator.dashboard') ? 'koordinator.dashboard' : 'coordinator.dashboard';

        return view($viewName, [
            'totalStock'         => $totalStock,
            'binaanStock'        => $binaanStock,
            'bufferStock'        => $bufferStock,
            'readyOrdersCount'   => $readyOrdersCount,
            'weighedOrdersCount' => $weighedOrdersCount,
            'totalOrderQty'      => $totalOrderQty,
            'supplyRows'         => $supplyRows,
            'packingOrders'      => $packingOrders,
            'gateLogs'           => $gateLogs,
        ]);
    }
}
