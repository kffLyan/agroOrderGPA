<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class GalleryController extends Controller
{
    // Menampilkan galeri visual aktivitas budidaya tani Ciwidey, fasilitas sortir, dan armada distribusi.
    public function __invoke(): View
    {
        $totalProducts = Product::where('is_active', true)->count();
        $totalBatches = HarvestBatch::count();
        $totalVolume = (float) HarvestBatch::sum('available_quantity');

        return view('public.gallery', compact('totalProducts', 'totalBatches', 'totalVolume'));
    }
}

