<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Http\Resources\Klien\KatalogProdukResource;
use App\Models\Contract;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller katalog komoditas hasil tani untuk Klien
class KatalogController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected StockService $stockService
    ) {}

    // Menampilkan etalase katalog komoditas, resolusi harga kontrak B2B vs pasar, dan batas pagu kredit
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $isB2b = (bool) $user?->is_b2b;

        // Mengambil seluruh produk komoditas panen yang berstatus aktif
        $rawProducts = Product::where('is_active', true)->get();

        // Mengambil kontrak B2B aktif klien untuk penetapan harga terkunci dan komitmen kuota
        $activeContracts = Contract::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->get()
            ->keyBy('product_id');

        // Menghitung ketersediaan stok panen fisik secara massal untuk mencegah query N+1
        $stockMap = $this->stockService->getAvailableStockMap($rawProducts->pluck('id')->all());

        // Hitung kuota bulanan dari kontrak
        $totalCommittedQuota = (float) $activeContracts->sum('committed_volume_per_cycle');

        // Menghitung serapan kuota komitmen pada bulan berjalan dari pesanan yang sedang berjalan
        $startOfMonth = now()->startOfMonth();
        $usedQuota = (float) OrderItem::whereHas('order', function ($orderQuery) use ($user, $startOfMonth) {
            $orderQuery->where('user_id', $user->id)
                ->where('created_at', '>=', $startOfMonth)
                ->whereNotIn('status', ['BATAL', 'DRAFT']);
        })->sum('ordered_qty');

        // Mengkalkulasi batas pagu kredit tempo (TOP) berdasarkan ketentuan kontrak aktif
        $creditLimit = 0.0;
        if ($isB2b) {
            $creditLimit = (float) $activeContracts->sum(function ($contract) {
                return (float) ($contract->fixed_price_per_kg * $contract->committed_volume_per_cycle * 2);
            });
        }

        $unpaidTotal = (float) Order::where('user_id', $user->id)
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->sum('grand_total');

        // Mengambil pesanan aktif terbaru untuk memantau proses logistik pengiriman barang
        $lastActiveOrder = Order::where('user_id', $user->id)
            ->whereIn('status', ['MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->orderBy('id', 'desc')
            ->first();

        // Menghitung persentase pemakaian kuota dan sisa saldo pagu kredit klien
        $quotaPercent = $totalCommittedQuota > 0 ? min(100, ($usedQuota / $totalCommittedQuota) * 100) : 0;
        $creditAvailable = max(0, $creditLimit - $unpaidTotal);
        $creditPercent = $creditLimit > 0 ? ($creditAvailable / $creditLimit) * 100 : 0;
        $quotaBarWidth = max(0, min(100, $quotaPercent)) . '%';

        // Memformat spesifikasi komoditas menggunakan KatalogProdukResource terstandarisasi
        $commodities = $rawProducts->map(
            fn ($product) => (new KatalogProdukResource($product, $activeContracts, $stockMap))->toArray($request)
        )->values()->all();

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
