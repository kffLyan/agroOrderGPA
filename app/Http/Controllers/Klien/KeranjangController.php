<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Product;
use App\Services\GovernanceService;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller Keranjang Belanja Komoditas & Draf Pemesanan (PO) Klien.
 *
 * Penjelasan UKK:
 * Halaman ini memfasilitasi klien (Reguler maupun Mitra B2B) dalam memilih
 * komoditas hasil tani, menghitung harga khusus kontrak (jika ada),
 * memeriksa ketersediaan stok panen riil, serta menentukan alamat dan jadwal pengiriman.
 */
class KeranjangController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StockService $stockService
    ) {}

    /**
     * Menampilkan antarmuka keranjang belanja dan penyusunan draf PO mandiri.
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

        // Query kuota stok panen tersedia secara agregat (mencegah query N+1)
        $stockMap = $this->stockService->getAvailableStockMap($products->pluck('id')->all());

        $receivingRoster = [
            [
                'name'  => $user->pic_name ?: $user->name,
                'role'  => 'PIC Penerima Klien',
                'phone' => $user->pic_phone ?: ($user->phone ?: '—'),
            ],
        ];

        // Transformasi View Model untuk kemudahan interaksi frontend
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

        $gov = app(GovernanceService::class);
        $taxSettings = $gov->getTaxSettings();
        $shippingSettings = $gov->getShippingSettings();

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
