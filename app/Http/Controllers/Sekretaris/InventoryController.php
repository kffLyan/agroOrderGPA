<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Menampilkan konsol pemantauan stok gudang & ketersediaan sayur (ATP).
     */
    public function index(Request $request): View
    {
        // Ambil data produk riil
        $products = Product::with(['harvestBatches', 'orderItems.order'])
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            $products = Product::with(['harvestBatches', 'orderItems.order'])->get();
        }

        $resolved = \App\Http\Resources\Sekretaris\InventoryResource::collection($products)->resolve();

        $totalStock = collect($resolved)->sum(fn ($r) => $r['_raw']['total_stock'] ?? 0);
        $totalLocked = collect($resolved)->sum(fn ($r) => $r['_raw']['locked'] ?? 0);
        $totalAtp = collect($resolved)->sum(fn ($r) => $r['_raw']['atp'] ?? 0);
        $totalBuffer = collect($resolved)->sum(fn ($r) => $r['_raw']['buffer'] ?? 0);

        // Bersihkan _raw key agar bersih untuk Alpine.js
        $rows = array_map(function ($row) {
            unset($row['_raw']);
            return $row;
        }, $resolved);

        return view('sekretaris.inventory', [
            'rows'        => $rows,
            'totalStock'  => $totalStock,
            'totalLocked' => $totalLocked,
            'totalAtp'    => $totalAtp,
            'totalBuffer' => $totalBuffer,
        ]);
    }
}
