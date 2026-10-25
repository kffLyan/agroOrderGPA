<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected OrderService $orderService;
    protected StockService $stockService;

    public function __construct(OrderService $orderService, StockService $stockService)
    {
        $this->orderService = $orderService;
        $this->stockService = $stockService;
    }

    /**
     * Menampilkan antarmuka keranjang belanja dan penyusunan draf PO mandiri
     */
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();

        // Ambil produk aktif beserta ketersediaan stok panen
        $products = Product::where('is_active', true)->get();

        $activeContracts = Contract::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->get()
            ->keyBy('product_id');

        // Query kuota stok panen tersedia dalam 1 query SQL efisien (anti N+1)
        $stockMap = $this->stockService->getAvailableStockMap($products->pluck('id')->all());

        $receivingRoster = [
            [
                'name'  => $user->pic_name ?: $user->name,
                'role'  => 'PIC Penerima Klien',
                'phone' => $user->pic_phone ?: ($user->phone ?: '—'),
            ],
        ];

        return view('klien.cart', compact(
            'products',
            'activeContracts',
            'stockMap',
            'receivingRoster',
            'user'
        ));
    }
}
