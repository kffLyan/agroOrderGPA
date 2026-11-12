<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sekretaris\InventarisResource;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

// Controller monitoring stok gudang dan buffer panen untuk Sekretaris
class StokGudangController extends Controller
{
    // Menampilkan konsol pemantauan persediaan stok fisik komoditas, alokasi terkunci, dan kuota bebas jual (ATP)
    public function index(Request $request): View
    {
        // Mengambil seluruh produk aktif beserta batch panen fisik dan relasi pesanan pengalokasi
        $products = Product::with(['harvestBatches', 'orderItems.order'])
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            $products = Product::with(['harvestBatches', 'orderItems.order'])->get();
        }

        $resolved = InventarisResource::collection($products)->resolve();

        $totalStock  = collect($resolved)->sum(fn ($item) => $item['_raw']['total_stock'] ?? 0);
        $totalLocked = collect($resolved)->sum(fn ($item) => $item['_raw']['locked'] ?? 0);
        $totalAtp    = collect($resolved)->sum(fn ($item) => $item['_raw']['atp'] ?? 0);
        $totalBuffer = collect($resolved)->sum(fn ($item) => $item['_raw']['buffer'] ?? 0);

        // Menghapus atribut internal _raw agar data bersih saat dikirim ke frontend Alpine.js
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
