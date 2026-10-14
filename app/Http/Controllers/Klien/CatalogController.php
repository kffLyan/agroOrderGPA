<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{
    protected OrderService $orderService;
    protected StockService $stockService;

    public function __construct(OrderService $orderService, StockService $stockService)
    {
        $this->orderService = $orderService;
        $this->stockService = $stockService;
    }

    // Menampilkan katalog komoditas dengan resolusi harga kontrak B2B vs reguler
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

        $stockMap = [];
        foreach ($rawProducts as $p) {
            $stockMap[$p->id] = $this->stockService->getAvailableStock($p->id);
        }

        // Hitung kuota bulanan dari kontrak
        $totalCommittedQuota = (float) $activeContracts->sum('committed_volume_per_cycle');
        if ($totalCommittedQuota <= 0) {
            $totalCommittedQuota = 5000.0; // Kuota default
        }

        // Hitung serapan kuota bulan berjalan
        $startOfMonth = now()->startOfMonth();
        $usedQuota = (float) Order::where('user_id', $user->id)
            ->where('created_at', '>=', $startOfMonth)
            ->whereNotIn('status', ['BATAL', 'DRAFT'])
            ->with('orderItems')
            ->get()
            ->sum(function ($order) {
                return $order->orderItems->sum('ordered_qty');
            });

        // Plafon kredit tempo
        $creditLimit = $isB2b ? 50000000.0 : 0.0;
        $unpaidTotal = (float) Order::where('user_id', $user->id)
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'TERVERIFIKASI', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->sum('grand_total');

        // Order aktif terakhir untuk pengiriman
        $lastActiveOrder = Order::where('user_id', $user->id)
            ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->orderBy('id', 'desc')
            ->first();

        return view('klien.catalog', compact(
            'rawProducts',
            'activeContracts',
            'stockMap',
            'totalCommittedQuota',
            'usedQuota',
            'creditLimit',
            'unpaidTotal',
            'lastActiveOrder',
            'isB2b'
        ));
    }
}
