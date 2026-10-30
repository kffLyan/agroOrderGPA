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
        $isB2b = ($user->client_type === 'B2B_KONTRAK');

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

        // Transformasi View Model (Prinsip MVC: Controller mengolah logika tampilan)
        $commodities = $products->map(function ($product) use ($activeContracts, $stockMap) {
            $hasContract = $activeContracts->has($product->id);
            $contract = $activeContracts->get($product->id);
            $effectivePrice = $hasContract ? (float) $contract->fixed_price_per_kg : (float) $product->base_price;
            $stock = $stockMap[$product->id] ?? 0;
            $moq = (float) ($product->minimum_order ?? 10);

            return [
                'key'        => (string) $product->id,
                'id'         => $product->id,
                'sku'        => $product->sku ?? ('SKU-' . $product->id),
                'name'       => $product->name,
                'grade'      => $product->grade ?: 'Grade A',
                'packaging'  => "Krat Plastik Higienis 10 {$product->unit}",
                'origin'     => 'Lembang',
                'cold_chain' => '2-6°C',
                'price'      => $effectivePrice,
                'stock'      => $stock,
                'moq'        => $moq,
                'step'       => 1,
                'crate_kg'   => 10,
                'pack_label' => 'Krat',
            ];
        })->values()->all();

        $defaultDeliveryDate = now()->addDay()->format('Y-m-d');
        $defaultAddress = $user->address ?: '';

        $docks = [
            [
                'key'      => 'dock-utama',
                'name'     => $user->company_name ?: ($user->name ?: 'Alamat Terdaftar'),
                'address'  => $defaultAddress,
                'chip'     => 'Alamat Utama',
                'access'   => 'Akses Pengiriman Terdaftar',
            ],
        ];

        $cartDefaults = [
            'date'             => $defaultDeliveryDate,
            'window'           => '04:00 - 06:00 WIB (Subuh)',
            'driver_note'      => 'Harap konfirmasi ke PIC dock penerimaan 30 menit sebelum armada tiba.',
            'dock'             => 'dock-utama',
            'docks'            => $docks,
            'default_address'  => $defaultAddress,
            'payment'          => $isB2b ? 'TEMPO_TOP' : 'TRANSFER_BANK',
            'status'           => 'Draf Siap Diajukan',
            'submitted_status' => 'Menunggu Verifikasi Admin',
        ];

        return view('klien.cart', compact(
            'products',
            'activeContracts',
            'stockMap',
            'receivingRoster',
            'user',
            'isB2b',
            'commodities',
            'defaultDeliveryDate',
            'defaultAddress',
            'docks',
            'cartDefaults'
        ));
    }
}
