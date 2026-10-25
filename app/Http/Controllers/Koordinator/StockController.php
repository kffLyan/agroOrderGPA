<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Koordinator\StoreHarvestBatchRequest;
use App\Http\Resources\Koordinator\StockCommodityResource;
use App\Http\Resources\Koordinator\StockLogResource;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Tampilkan Konsol Manajemen Stok Panen & Alokasi Buffer Stock Gudang.
     */
    public function index(Request $request): View
    {
        $activeStatuses = ['MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'];

        // Eager-load harvest batches dan agregasi order items dalam 1 kueri (Bebas N+1 Query)
        $products = Product::where('is_active', true)
            ->with('harvestBatches')
            ->withSum(['orderItems as reserved_po_qty' => function ($q) use ($activeStatuses) {
                $q->whereHas('order', fn ($o) => $o->whereIn('status', $activeStatuses));
            }], 'ordered_qty')
            ->get();

        $totalBinaan   = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        $totalBuffer   = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $totalStock    = $totalBinaan + $totalBuffer;
        $totalReserved = (float) OrderItem::whereHas('order', fn ($q) => $q->whereIn('status', $activeStatuses))->sum('ordered_qty');
        $freeStock     = max(0, $totalStock - $totalReserved);
        $activePoCount = Order::whereIn('status', $activeStatuses)->count();

        // Transformasi ke API Resource terstruktur
        $commodities = StockCommodityResource::collection($products)->resolve();

        $distinctSuppliers = HarvestBatch::distinct()->pluck('supplier_name')->filter()->values()->all();

        $recentBatches = HarvestBatch::with('product', 'inputter')->latest()->take(10)->get();
        $logItems      = StockLogResource::collection($recentBatches)->resolve();

        $viewName = view()->exists('koordinator.stock') ? 'koordinator.stock' : 'coordinator.stock';

        return view($viewName, [
            'products'          => $products,
            'totalBinaan'       => $totalBinaan,
            'totalBuffer'       => $totalBuffer,
            'totalStock'        => $totalStock,
            'totalReserved'     => $totalReserved,
            'freeStock'         => $freeStock,
            'activePoCount'     => $activePoCount,
            'commodities'       => $commodities,
            'distinctSuppliers' => $distinctSuppliers,
            'logItems'          => $logItems,
        ]);
    }

    /**
     * Simpan data pasokan / batch panen baru ke gudang.
     */
    public function storeBatch(StoreHarvestBatchRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $batch = HarvestBatch::create([
            'product_id'         => $validated['product_id'],
            'source_type'        => $validated['source_type'],
            'supplier_name'      => $validated['supplier_name'],
            'batch_date'         => $validated['batch_date'],
            'initial_quantity'   => $validated['initial_quantity'],
            'available_quantity' => $validated['initial_quantity'],
            'grade'              => $validated['grade'] ?? 'Grade A',
            'qc_notes'           => $validated['qc_notes'] ?? null,
            'inputted_by'        => $request->user()?->id ?? 1,
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Batch panen berhasil dicatat ke gudang.',
                'batch'   => $batch->load('product'),
            ], 201);
        }

        return redirect()->back()->with('success', 'Batch panen ' . number_format($validated['initial_quantity'], 0) . ' KG berhasil dicatat ke sistem.');
    }
}
