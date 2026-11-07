<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
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
        protected StockService $stockService
    ) {}

    // Menampilkan antarmuka keranjang belanja komoditas sayur, penentuan jadwal kirim, dan kalkulasi PPN/ongkir
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $isB2b = ($user->client_type === 'B2B_KONTRAK');

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

        // Memformat daftar komoditas beserta harga kontrak efektif untuk interaktivitas keranjang belanja
        $commodities = $products->map(function ($product) use ($activeContracts, $stockMap) {
            $hasContract = $activeContracts->has($product->id);
            $contract = $activeContracts->get($product->id);
            $effectivePrice = $hasContract ? (float) $contract->fixed_price_per_kg : (float) $product->base_price;
            $stock = $stockMap[$product->id] ?? 0;
            $minimumOrder = (float) ($product->minimum_order ?? 10);

            return [
                'key'        => (string) $product->id,
                'id'         => $product->id,
                'sku'        => $product->sku ?? ('GPA-' . str_pad((string) $product->id, 3, '0', STR_PAD_LEFT)),
                'name'       => $product->name,
                'grade'      => $product->grade ?: 'Grade A',
                'packaging'  => "Krat Plastik Higienis 10 {$product->unit}",
                'origin'     => 'Lembang',
                'cold_chain' => '2-6°C',
                'price'      => $effectivePrice,
                'stock'      => $stock,
                'moq'        => $minimumOrder,
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

        $tataKelolaService = app(TataKelolaService::class);
        $taxSettings = $tataKelolaService->getTaxSettings();
        $shippingSettings = $tataKelolaService->getShippingSettings();

        $cartDefaults = [
            'date'              => $defaultDeliveryDate,
            'window'            => '04:00 - 06:00 WIB (Subuh)',
            'driver_note'       => 'Harap konfirmasi ke PIC dock penerimaan 30 menit sebelum armada tiba.',
            'dock'              => 'dock-utama',
            'docks'             => $docks,
            'default_address'   => $defaultAddress,
            'payment'           => $isB2b ? 'TEMPO_TOP' : 'TRANSFER_BANK',
            'status'            => 'Draf Siap Diajukan',
            'submitted_status'  => 'Menunggu Verifikasi Sekretaris',
            'tax_settings'      => $taxSettings,
            'shipping_settings' => $shippingSettings,
        ];

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
            'taxSettings',
            'shippingSettings'
        ));
    }
}
