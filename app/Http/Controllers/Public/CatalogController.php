<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    public function __invoke(): View
    {
        $commodities = Product::where('is_active', true)->with('harvestBatches')->get();

        return view('public.catalog', compact('commodities'));
    }
}
