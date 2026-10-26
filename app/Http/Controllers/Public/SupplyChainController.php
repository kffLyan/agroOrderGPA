<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class SupplyChainController extends Controller
{
    public function __invoke(): View
    {
        $totalStock = (float) HarvestBatch::sum('available_quantity');
        $totalProducts = Product::where('is_active', true)->count();
        $totalBatches = HarvestBatch::count();

        return view('public.supply-chain', compact('totalStock', 'totalProducts', 'totalBatches'));
    }
}

