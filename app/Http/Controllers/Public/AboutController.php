<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::where('is_active', true)->get();
        $totalProducts = $products->count();
        $totalCapacity = (float) HarvestBatch::sum('initial_quantity');

        return view('public.about', compact('products', 'totalProducts', 'totalCapacity'));
    }
}
