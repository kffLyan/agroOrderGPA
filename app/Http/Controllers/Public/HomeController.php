<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::where('is_active', true)->with('harvestBatches')->get();
        $contracts = Contract::with(['user', 'product'])->where('status', 'ACTIVE')->get();
        $clients = User::where('role', 'KLIEN')
            ->where('client_type', 'B2B_KONTRAK')
            ->with(['contracts' => function ($query) {
                $query->where('status', 'ACTIVE')->with('product');
            }])
            ->get();

        return view('public.home', compact('products', 'contracts', 'clients'));
    }
}
