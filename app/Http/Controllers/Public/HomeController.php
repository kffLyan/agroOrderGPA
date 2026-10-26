<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::where('is_active', true)->with('harvestBatches')->get();
        $contracts = Contract::with(['user', 'product'])->where('status', 'ACTIVE')->get();

        return view('public.home', compact('products', 'contracts'));
    }
}
