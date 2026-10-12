<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Product;
use App\Support\CoordinatorStockData;
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
        $stock = CoordinatorStockData::for($request->user());

        // Gabungkan log riil dari tabel harvest_batches jika ada
        $recentBatches = HarvestBatch::with('product', 'inputter')->latest()->take(10)->get();
        if ($recentBatches->isNotEmpty()) {
            $dbLogs = $recentBatches->map(function ($batch) {
                $isBuffer = $batch->source_type === 'BUFFER_LUAR';
                $productName = $batch->product?->name ?? 'Komoditas';

                return [
                    'lot' => 'LOT-' . strtoupper(substr($productName, 0, 3)) . '-' . $batch->batch_date->format('Ymd') . '-' . str_pad($batch->id, 2, '0', STR_PAD_LEFT),
                    'time' => $batch->created_at->format('H:i:s') . ' WIB',
                    'entry' => '+' . number_format($batch->initial_quantity, 0, ',', '.') . ' KG ' . $productName . ' (' . ($isBuffer ? 'Buffer Mitra' : 'Binaan') . ' - ' . $batch->supplier_name . ')',
                    'note' => 'QC Pass: Mutu Terverifikasi',
                    'audit' => 'AUDIT: SEC-OP-' . str_pad($batch->inputted_by, 3, '0', STR_PAD_LEFT),
                    'tone' => $isBuffer ? 'brand' : 'success',
                ];
            })->all();

            $stock['logs']['items'] = array_merge($dbLogs, $stock['logs']['items']);
            $stock['logs']['total'] = count($stock['logs']['items']);
        }

        $viewName = view()->exists('koordinator.stock') ? 'koordinator.stock' : 'coordinator.stock';

        return view($viewName, [
            'operator' => $stock['operator'],
            'stockData' => $stock,
        ]);
    }

    /**
     * Simpan data pasokan / batch panen baru ke gudang.
     */
    public function storeBatch(Request $request): JsonResponse|RedirectResponse
    {
        // Temukan product_id berdasarkan input langsung atau slug/nama komoditas
        $productId = $request->input('product_id');
        if (!$productId && $request->filled('commodity')) {
            $commodityKey = $request->input('commodity');
            $product = Product::where('slug', $commodityKey)
                ->orWhere('name', 'like', '%' . str_replace('-', ' ', $commodityKey) . '%')
                ->first();
            $productId = $product?->id;
        }

        if (!$productId) {
            $productId = Product::first()?->id;
        }

        // Normalisasi source_type
        $sourceType = $request->input('source_type');
        if (!in_array($sourceType, ['PETANI_BINAAN', 'BUFFER_LUAR'])) {
            $category = (string) $request->input('category', '');
            $sourceType = str_contains(strtoupper($category), 'BUFFER') ? 'BUFFER_LUAR' : 'PETANI_BINAAN';
        }

        $supplierName = $request->input('supplier_name', $request->input('vendor', 'Petani Binaan'));
        $batchDate = $request->input('batch_date', $request->input('date', now()->toDateString()));
        $initialQuantity = (float) $request->input('initial_quantity', $request->input('quantity', 0));

        // Validasi
        $validated = validator([
            'product_id' => $productId,
            'source_type' => $sourceType,
            'supplier_name' => $supplierName,
            'batch_date' => $batchDate,
            'initial_quantity' => $initialQuantity,
        ], [
            'product_id' => 'required|exists:products,id',
            'source_type' => 'required|in:PETANI_BINAAN,BUFFER_LUAR',
            'supplier_name' => 'required|string|max:100',
            'batch_date' => 'required|date',
            'initial_quantity' => 'required|numeric|min:0.01',
        ])->validate();

        $batch = HarvestBatch::create([
            'product_id' => $validated['product_id'],
            'source_type' => $validated['source_type'],
            'supplier_name' => $validated['supplier_name'],
            'batch_date' => $validated['batch_date'],
            'initial_quantity' => $validated['initial_quantity'],
            'available_quantity' => $validated['initial_quantity'],
            'inputted_by' => $request->user()?->id ?? 1,
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Batch panen berhasil dicatat ke gudang.',
                'batch' => $batch->load('product'),
            ], 201);
        }

        return redirect()->back()->with('success', 'Batch panen ' . number_format($initialQuantity, 0) . ' KG berhasil dicatat ke sistem.');
    }
}
