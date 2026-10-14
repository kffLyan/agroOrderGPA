<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StockController extends Controller
{
    /**
     * Tampilkan Konsol Manajemen Stok Panen & Alokasi Buffer Stock Gudang.
     */
    public function index(Request $request): View
    {
        $products = Product::where('is_active', true)->with('harvestBatches')->get();
        $totalBinaan = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        $totalBuffer = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $totalStock = $totalBinaan + $totalBuffer;
        $totalReserved = (float) OrderItem::whereHas('order', fn($q) => $q->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM']))->sum('ordered_qty');
        $freeStock = max(0, $totalStock - $totalReserved);
        $activePoCount = Order::whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM'])->count();

        $commodities = $products->map(function ($product) {
            $slug = $product->slug ?: Str::slug($product->name);
            $lowerName = strtolower($product->name);

            $category = 'daun';
            if (str_contains($lowerName, 'tomat') || str_contains($lowerName, 'stroberi') || str_contains($lowerName, 'cabai')) {
                $category = 'buah';
            } elseif (str_contains($lowerName, 'brokoli') || str_contains($lowerName, 'kol') || str_contains($lowerName, 'kubis')) {
                $category = 'cruciferae';
            }

            $binaan = (float) $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
            $buffer = (float) $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
            $total = $binaan + $buffer;

            $po = (float) OrderItem::where('product_id', $product->id)
                ->whereHas('order', fn($q) => $q->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM']))
                ->sum('ordered_qty');

            $rtp = $total - $po;
            $utilization = $total > 0 ? round(($po / $total) * 100, 1) : 0;
            $bufferNeed = $rtp < 0 ? abs($rtp) : 0;
            $isDeficit = $rtp < 0;

            $supplierBinaan = $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->first()?->supplier_name ?: 'Poktan Binaan';
            $supplierBuffer = $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->first()?->supplier_name ?: 'Mitra Buffer';

            return [
                'key' => $slug,
                'name' => $product->name,
                'grade' => strtoupper($product->grade ?: 'GRADE A'),
                'grade_tone' => 'accent',
                'code' => 'CMD-' . strtoupper(substr(str_replace('-', '', $slug), 0, 3)) . '-' . str_pad($product->id, 3, '0', STR_PAD_LEFT),
                'hub' => 'SENTRA CIWIDEY & LEMBANG',
                'icon' => str_contains($lowerName, 'selada') ? 'leaf' : 'package',
                'icon_tone' => $isDeficit ? 'danger' : 'success',
                'total' => $total,
                'rtp' => $rtp,
                'rtp_tone' => $isDeficit ? 'danger' : 'success',
                'rtp_note' => $isDeficit ? '(DEFISIT)' : ($rtp < 100 && $total > 0 ? '(KETAT)' : null),
                'category' => $category,
                'tiles' => [
                    ['label' => 'PASOKAN BINAAN INTI', 'value' => $binaan, 'unit' => 'KG', 'note' => $supplierBinaan, 'tone' => 'ink', 'note_tone' => 'success'],
                    ['label' => 'BUFFER MITRA SIAGA', 'value' => $buffer, 'unit' => 'KG', 'note' => $supplierBuffer, 'tone' => 'ink', 'note_tone' => 'muted'],
                    ['label' => 'ALOKASI TERIKAT PO', 'value' => $po, 'unit' => 'KG', 'note' => 'PO Aktif Terverifikasi', 'tone' => 'danger', 'note_tone' => 'ink'],
                    ['label' => 'KONDISI FISIK / MUTU', 'value' => 'SUHU 4°C | COLD CHAIN', 'unit' => null, 'note' => 'Toleransi Susut: 1.0%', 'tone' => 'success', 'note_tone' => 'ink'],
                ],
                'utilization' => min(100, $utilization),
                'utilization_note' => $utilization . '% Terjadwal',
                'utilization_status' => $isDeficit ? 'Perlu Buffer +' . number_format($bufferNeed, 0) . 'kg' : ($utilization > 80 ? 'Ketat' : 'Aman'),
                'utilization_status_tone' => $isDeficit ? 'warning' : 'ink',
                'utilization_tone' => $isDeficit ? 'brand' : 'success',
                'buffer_need' => $bufferNeed,
                'injection' => $isDeficit,
            ];
        })->values()->all();

        $distinctSuppliers = HarvestBatch::distinct()->pluck('supplier_name')->filter()->values()->all();
        if (empty($distinctSuppliers)) {
            $distinctSuppliers = ['Pak Endang (Gapoktan Pacet Makmur)', 'Bu Yanti (Gapoktan Barokah)', 'Mitra Tani Sejahtera'];
        }

        $recentBatches = HarvestBatch::with('product', 'inputter')->latest()->take(10)->get();
        $logItems = $recentBatches->map(function ($batch) {
            $isBuffer = $batch->source_type === 'BUFFER_LUAR';
            $productName = $batch->product?->name ?? 'Komoditas Sayur';
            $batchDate = $batch->batch_date ? $batch->batch_date->format('Ymd') : now()->format('Ymd');

            return [
                'lot' => 'LOT-' . strtoupper(substr(str_replace(' ', '', $productName), 0, 3)) . '-' . $batchDate . '-' . str_pad($batch->id, 2, '0', STR_PAD_LEFT),
                'time' => $batch->created_at ? $batch->created_at->format('H:i:s') . ' WIB' : now()->format('H:i:s') . ' WIB',
                'entry' => '+' . number_format($batch->initial_quantity, 0, ',', '.') . ' KG ' . $productName . ' (' . ($isBuffer ? 'Buffer Mitra' : 'Binaan') . ' - ' . $batch->supplier_name . ')',
                'note' => 'QC Pass: Mutu Terverifikasi',
                'audit' => 'AUDIT: SEC-OP-' . str_pad($batch->inputted_by, 3, '0', STR_PAD_LEFT),
                'tone' => $isBuffer ? 'brand' : 'success',
            ];
        })->all();

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
