<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller Katalog Komoditas Hasil Tani untuk Klien.
 *
 * Penjelasan UKK:
 * Modul ini menampilkan etalase komoditas agrikultur segar, kuota panen yang tersedia,
 * status kontrak harga khusus B2B, batas kuota bulanan, dan plafon kredit Term of Payment (TOP).
 */
class KatalogController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StockService $stockService
    ) {}

    /**
     * Menampilkan katalog komoditas dengan resolusi harga kontrak B2B vs harga reguler.
     */
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $isB2b = ($user->client_type === 'B2B_KONTRAK');

        // Ambil semua produk aktif
        $rawProducts = Product::where('is_active', true)->get();

        // Kontrak aktif user
        $activeContracts = Contract::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->get()
            ->keyBy('product_id');

        // Query kuota stok panen tersedia dalam 1 query SQL efisien (anti N+1)
        $stockMap = $this->stockService->getAvailableStockMap($rawProducts->pluck('id')->all());

        // Hitung kuota bulanan dari kontrak
        $totalCommittedQuota = (float) $activeContracts->sum('committed_volume_per_cycle');

        // Hitung serapan kuota bulan berjalan langsung melalui agregasi database
        $startOfMonth = now()->startOfMonth();
        $usedQuota = (float) OrderItem::whereHas('order', function ($q) use ($user, $startOfMonth) {
            $q->where('user_id', $user->id)
                ->where('created_at', '>=', $startOfMonth)
                ->whereNotIn('status', ['BATAL', 'DRAFT']);
        })->sum('ordered_qty');

        // Plafon kredit tempo
        $creditLimit = 0.0;
        if ($isB2b) {
            $creditLimit = (float) $activeContracts->sum(function ($c) {
                return (float) ($c->fixed_price_per_kg * $c->committed_volume_per_cycle * 2);
            });
        }
        $unpaidTotal = (float) Order::where('user_id', $user->id)
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->sum('grand_total');

        // Order aktif terakhir untuk pengiriman
        $lastActiveOrder = Order::where('user_id', $user->id)
            ->whereIn('status', ['MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->orderBy('id', 'desc')
            ->first();

        // 1. Kalkulasi metrik plafon & kuota
        $quotaPercent = $totalCommittedQuota > 0 ? min(100, ($usedQuota / $totalCommittedQuota) * 100) : 0;
        $creditAvailable = max(0, $creditLimit - $unpaidTotal);
        $creditPercent = $creditLimit > 0 ? ($creditAvailable / $creditLimit) * 100 : 0;
        $quotaBarWidth = max(0, min(100, $quotaPercent)) . '%';

        // 2. Transformasi ke View Model komoditas
        $commodities = $rawProducts->map(function ($product) use ($activeContracts, $stockMap) {
            $availableStock = $stockMap[$product->id] ?? 0;
            $hasContract = $activeContracts->has($product->id);
            $contractItem = $activeContracts->get($product->id);
            $effectivePrice = $hasContract ? (float) $contractItem->fixed_price_per_kg : (float) $product->base_price;
            $moq = (float) ($product->minimum_order ?? 10);

            return [
                'key'                => (string) $product->id,
                'id'                 => $product->id,
                'sku'                => $product->sku ?? ('SKU-' . $product->id),
                'code'               => $product->sku ?? ('SKU-' . $product->id),
                'name'               => $product->name,
                'image_path'         => $product->image_path,
                'image_url'          => asset($product->image_path),
                'subtitle'           => $product->description ?: "Komoditas hortikultura unggulan Pasundan Grade {$product->grade}",
                'category'           => 'sayur-daun',
                'category_label'     => 'Komoditas Pertanian GPA',
                'grade'              => $product->grade ?: 'Grade A',
                'packaging'          => "Krat Plastik Higienis 10 {$product->unit}",
                'price'              => $effectivePrice,
                'base_price'         => (float) $product->base_price,
                'has_contract'       => $hasContract,
                'price_note'         => $hasContract ? 'Harga Kontrak Terkunci' : 'Harga Pasar Reguler',
                'price_note_tone'    => $hasContract ? 'success' : 'default',
                'stock'              => $availableStock,
                'stock_label'        => 'Stok Bebas:',
                'availability'       => $availableStock > 0 ? 'Stok Tersedia' : 'Habis Terpesan',
                'availability_tone'  => $availableStock > 0 ? 'safe' : 'caution',
                'moq'                => $moq,
                'step'               => 1,
                'crate_kg'           => 10,
                'pack_label'         => 'Keranjang',
                'cold_chain'         => '2-6°C Pre-Cooled',
                'critical'           => $availableStock < 50 && $availableStock > 0,
                'origin'             => 'Lembang',
                'specs'              => [
                    [
                        'label' => 'Satuan',
                        'value' => strtoupper($product->unit ?? 'KG'),
                        'tone'  => 'default',
                    ],
                    [
                        'label' => 'Grade Mutu',
                        'value' => $product->grade ?: 'Grade A',
                        'tone'  => 'success',
                    ],
                    [
                        'label' => 'Stok Riil Panen',
                        'value' => number_format($availableStock, 0, ',', '.') . ' ' . $product->unit,
                        'tone'  => $availableStock > 0 ? 'success' : 'caution',
                    ],
                    [
                        'label' => 'Min Order (MOQ)',
                        'value' => number_format($moq, 0, ',', '.') . ' ' . $product->unit,
                        'tone'  => 'default',
                    ],
                ],
            ];
        })->values()->all();

        return view('klien.katalog', compact(
            'rawProducts',
            'activeContracts',
            'stockMap',
            'totalCommittedQuota',
            'usedQuota',
            'creditLimit',
            'unpaidTotal',
            'lastActiveOrder',
            'isB2b',
            'quotaPercent',
            'creditAvailable',
            'creditPercent',
            'quotaBarWidth',
            'commodities'
        ));
    }
}
