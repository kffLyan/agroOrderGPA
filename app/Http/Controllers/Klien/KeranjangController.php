<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Http\Resources\Klien\KeranjangKomoditasResource;
use App\Http\Resources\Klien\KeranjangKonfigurasiResource;
use App\Models\Contract;
use App\Models\Order;
use App\Models\Product;
use App\Services\TataKelolaService;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller keranjang belanja dan draf pesanan untuk Klien
class KeranjangController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StockService $stockService,
        protected ?TataKelolaService $tataKelolaService = null
    ) {
        $this->tataKelolaService = $tataKelolaService ?? app(TataKelolaService::class);
    }

    // Menampilkan antarmuka keranjang belanja komoditas sayur, penentuan jadwal kirim, dan kalkulasi PPN/ongkir
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $isB2b = (bool) $user?->is_b2b;

        // Mengambil komoditas produk aktif beserta kontrak B2B yang dimiliki klien
        $products = Product::where('is_active', true)->get();

        $activeContracts = Contract::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->get()
            ->keyBy('product_id');

        // Menghitung ketersediaan kuota stok panen fisik di gudang sentra secara efisien
        $stockMap = $this->stockService->getAvailableStockMap($products->pluck('id')->all());

        $receivingRoster = [
            [
                'name'  => $user->pic_name ?: $user->name,
                'role'  => 'PIC Penerima Klien',
                'phone' => $user->pic_phone ?: ($user->phone ?: '—'),
            ],
        ];

        // 1. Memformat daftar komoditas menggunakan KeranjangKomoditasResource terstandarisasi
        $commodities = $products->map(
            fn ($product) => (new KeranjangKomoditasResource($product, $activeContracts, $stockMap))->toArray($request)
        )->values()->all();

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

        $taxSettings = $this->tataKelolaService->getTaxSettings();
        $shippingSettings = $this->tataKelolaService->getShippingSettings();

        // 2. Evaluasi pagu plafon kredit dan status pembekuan akun klien
        $creditLimit = 0.0;
        if ($isB2b) {
            $creditLimit = (float) $activeContracts->sum(function ($contract) {
                return (float) ($contract->fixed_price_per_kg * $contract->committed_volume_per_cycle * 2);
            });
        }
        $unpaidTotal = (float) Order::where('user_id', $user->id)
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->sum('grand_total');
        $creditAvailable = max(0.0, $creditLimit - $unpaidTotal);
        $isFrozen = $this->tataKelolaService->isClientFrozen((int) $user->id);

        // 3. Merangkum konfigurasi awal keranjang belanja via KeranjangKonfigurasiResource
        $cartDefaults = (new KeranjangKonfigurasiResource($user, [
            'default_delivery_date' => $defaultDeliveryDate,
            'docks'                 => $docks,
            'tax_settings'          => $taxSettings,
            'shipping_settings'     => $shippingSettings,
            'credit_limit'          => $creditLimit,
            'credit_available'      => $creditAvailable,
            'is_frozen'             => $isFrozen,
        ]))->toArray($request);

        return view('klien.keranjang', compact(
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
            'cartDefaults',
            'creditLimit',
            'creditAvailable',
            'isFrozen',
            'taxSettings',
            'shippingSettings'
        ));
    }
}
