<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sekretaris\InventoryResource;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Controller Monitoring Stok Gudang & Kesiapan Buffer Panen Sekretaris.
 *
 * Penjelasan UKK:
 * Modul ini menampilkan neraca persediaan sayur di gudang sentral GPA:
 * 1. Total Stok Fisik Panen.
 * 2. Stok Terkunci (Locked Kuota PO).
 * 3. Kuantitas Bebas Siap Jual (Available to Promise / ATP).
 * 4. Stok Cadangan Penyangga (Buffer Stock).
 */
class StokGudangController extends Controller
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

        $resolved = InventoryResource::collection($products)->resolve();

        $totalStock  = collect($resolved)->sum(fn ($r) => $r['_raw']['total_stock'] ?? 0);
        $totalLocked = collect($resolved)->sum(fn ($r) => $r['_raw']['locked'] ?? 0);
        $totalAtp    = collect($resolved)->sum(fn ($r) => $r['_raw']['atp'] ?? 0);
        $totalBuffer = collect($resolved)->sum(fn ($r) => $r['_raw']['buffer'] ?? 0);

        // Bersihkan _raw key agar bersih untuk Alpine.js
        $rows = array_map(function ($row) {
            unset($row['_raw']);
            return $row;
        }, $resolved);

        return view('sekretaris.stok', [
            'rows'        => $rows,
            'totalStock'  => $totalStock,
            'totalLocked' => $totalLocked,
            'totalAtp'    => $totalAtp,
            'totalBuffer' => $totalBuffer,
        ]);
    }
}
