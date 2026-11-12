<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Koordinator\SimpanBatchPanenRequest;
use App\Http\Resources\Koordinator\StokKomoditasResource;
use App\Http\Resources\Koordinator\LogStokResource;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Controller pemantauan neraca persediaan gudang dan pencatatan intake panen oleh Koordinator Lapangan
class StokController extends Controller
{
    // Menampilkan konsol pemantauan stok fisik, alokasi pesanan (ATP), dan log pasokan panen terbaru
    public function index(Request $request): View
    {
        // Status pesanan yang kuotanya masih mengantre verifikasi dan mencadangkan alokasi stok gudang
        $pendingStatuses = ['MENUNGGU_VERIFIKASI'];

        // Mengambil produk aktif beserta relasi batch panen dan kalkulasi total reservasi pesanan klien
        $products = Product::where('is_active', true)
            ->with('harvestBatches')
            ->withSum(['orderItems as reserved_po_qty' => function ($query) use ($pendingStatuses) {
                $query->whereHas('order', function ($orderQuery) use ($pendingStatuses) {
                    $orderQuery->whereIn('status', $pendingStatuses);
                });
            }], 'ordered_qty')
            ->get();

        $totalBinaan   = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        $totalBuffer   = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $totalStock    = $totalBinaan + $totalBuffer;
        $totalReserved = (float) OrderItem::whereHas('order', function ($query) use ($pendingStatuses) {
            $query->whereIn('status', $pendingStatuses);
        })->sum('ordered_qty');
        $freeStock     = max(0, $totalStock - $totalReserved);
        $activePoCount = Order::whereIn('status', $pendingStatuses)->count();

        // Memformat data komoditas menjadi struktur data standar untuk tampilan tabel persediaan
        $commodities = StokKomoditasResource::collection($products)->resolve();

        $distinctSuppliers = HarvestBatch::distinct()->pluck('supplier_name')->filter()->values()->all();

        $recentBatches = HarvestBatch::with('product', 'inputter')->latest()->take(10)->get();
        $logItems      = LogStokResource::collection($recentBatches)->resolve();

        return view('koordinator.stok', [
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

    // Mencatat batch pasokan hasil panen baru dari petani binaan maupun buffer luar ke sistem gudang
    public function storeBatch(SimpanBatchPanenRequest $request): JsonResponse|RedirectResponse
    {
        $validatedData = $request->validated();

        $batch = HarvestBatch::create([
            'product_id'         => $validatedData['product_id'],
            'source_type'        => $validatedData['source_type'],
            'supplier_name'      => $validatedData['supplier_name'],
            'batch_date'         => $validatedData['batch_date'],
            'initial_quantity'   => $validatedData['initial_quantity'],
            'available_quantity' => $validatedData['initial_quantity'],
            'grade'              => $validatedData['grade'] ?? 'Grade A',
            'qc_notes'           => $validatedData['qc_notes'] ?? null,
            'inputted_by'        => $request->user()?->id ?? 1,
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Batch panen berhasil dicatat ke gudang.',
                'batch'   => $batch->load('product'),
            ], 201);
        }

        return redirect()->back()->with('success', 'Batch panen ' . number_format($validatedData['initial_quantity'], 0) . ' KG berhasil dicatat ke sistem.');
    }
}
