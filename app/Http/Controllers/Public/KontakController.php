<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class KontakController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::where('is_active', true)->get();
        return view('public.kontak', compact('products'));
    }
}

