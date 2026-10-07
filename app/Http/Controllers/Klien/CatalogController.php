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

        // Ambil semua produk aktif
        $rawProducts = Product::where('is_active', true)->get();

        // Kontrak aktif user
        $activeContracts = Contract::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->get()
            ->keyBy('product_id');

        $commodities = $rawProducts->map(function ($product) use ($user, $activeContracts) {
            $availableStock = $this->stockService->getAvailableStock($product->id);
            $hasContract = $activeContracts->has($product->id);
            $contract = $activeContracts->get($product->id);
            $effectivePrice = $hasContract ? (float) $contract->fixed_price_per_kg : (float) $product->base_price;
            $moq = (float) ($product->minimum_order ?? 10);

            return [
                'key'                => (string) $product->id,
                'id'                 => $product->id,
                'sku'                => $product->sku ?? ('SKU-' . $product->id),
                'code'               => $product->sku ?? ('SKU-' . $product->id),
                'name'               => $product->name,
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

        // Data kuota dan kredit
        $isB2b = $user->client_type === 'B2B_KONTRAK';
        $activeContractFirst = $activeContracts->first();

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

        $quotaPercent = $totalCommittedQuota > 0 ? min(100, ($usedQuota / $totalCommittedQuota) * 100) : 0;

        // Plafon kredit tempo
        $creditLimit = $isB2b ? 50000000.0 : 0.0;
        $unpaidTotal = (float) Order::where('user_id', $user->id)
            ->whereIn('status', ['MENUNGGU_VERIFIKASI', 'TERVERIFIKASI', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->sum('grand_total');

        $creditAvailable = max(0, $creditLimit - $unpaidTotal);
        $creditPercent = $creditLimit > 0 ? ($creditAvailable / $creditLimit) * 100 : 0;

        // Order aktif terakhir untuk pengiriman
        $lastActiveOrder = Order::where('user_id', $user->id)
            ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->orderBy('id', 'desc')
            ->first();

        $contractData = [
            'number'  => $activeContractFirst ? $activeContractFirst->contract_number : ($isB2b ? 'CTR-GPA-B2B' : 'REGULER-SPOT'),
            'binding' => $isB2b ? 'Kontrak B2B Mengikat' : 'Pembelian Reguler Harian',
            'hub'     => 'Ciwidey & Lembang Agro-Hub',
        ];

        $quota = [
            'label'    => 'Serapan Kuota Kontrak Bulanan',
            'used'     => $usedQuota,
            'total'    => $totalCommittedQuota,
            'percent'  => $quotaPercent,
            'footnote' => 'Reset otomatis setiap tanggal 1 kalender operasional.',
        ];

        $credit = [
            'label'     => 'Plafon Kredit & Termin Tempo',
            'limit'     => $creditLimit,
            'available' => $creditAvailable,
            'percent'   => $creditPercent,
            'status'    => $creditAvailable > 0 ? 'Lancar / Kuota Tersedia' : 'Plafon Penuh / Perlu Pembayaran',
            'chip'      => $isB2b ? 'TOP 30 Hari' : 'H+0 Cash/Transfer',
        ];

        $dispatch = [
            'label'       => 'Pengiriman Terdekat',
            'po'          => $lastActiveOrder ? $lastActiveOrder->order_number : 'Belum Ada',
            'weight'      => $lastActiveOrder ? number_format($lastActiveOrder->orderItems()->sum('ordered_qty'), 0, ',', '.') . ' kg' : '0 kg',
            'destination' => $user->address ?: 'Gudang Utama Klien',
            'eta'         => $lastActiveOrder ? ($lastActiveOrder->target_delivery_date ? $lastActiveOrder->target_delivery_date->format('d M Y') . ' 05:30 WIB' : 'Besok 05:30 WIB') : 'Standby',
            'temperature' => '4°C Stabil',
        ];

        $rulebook = [
            'label' => 'SOP Pemesanan & Alokasi Petani',
            'rules' => [
                ['code' => 'Rule 01', 'text' => 'Order cut-off pukul 16:00 WIB untuk pengiriman H+1 subuh.'],
                ['code' => 'Rule 02', 'text' => 'Harga kontrak terkunci mengikat tanpa fluktuasi harian.'],
                ['code' => 'Rule 03', 'text' => 'Penimbangan neto sah dilakukan di packing house dengan bukti timbang digital.'],
            ],
            'footer' => 'Ketentuan mengacu pada Perjanjian Kerjasama Pasokan Komoditas GPA.',
        ];

        $categories = [
            ['label' => 'Semua', 'value' => 'all', 'count' => count($commodities)],
            ['label' => 'Sayuran Segar', 'value' => 'sayur-daun', 'count' => count($commodities)],
        ];

        $origins = [
            ['value' => 'lembang', 'label' => 'Sentra Panen Lembang (Ketinggian 1.200 mdpl)'],
            ['value' => 'ciwidey', 'label' => 'Sentra Panen Ciwidey (Ketinggian 1.400 mdpl)'],
            ['value' => 'pangalengan', 'label' => 'Sentra Pangalengan (Buffer Stock Gudang)']
        ];

        $sortOptions = [
            ['value' => 'name-asc', 'label' => 'Nama Komoditas (A - Z)'],
            ['value' => 'price-asc', 'label' => 'Harga Termurah'],
            ['value' => 'price-desc', 'label' => 'Harga Tertinggi'],
            ['value' => 'stock-desc', 'label' => 'Stok Tersedia Terbanyak'],
        ];

        $adhoc = [
            'chip'       => 'Fasilitas Mitra B2B',
            'title'      => 'Kebutuhan Pasokan Ad-Hoc & Skala Tonase Khusus',
            'body'       => 'Membutuhkan penambahan alokasi komoditas di luar batas kuota kontrak reguler? Hubungi Account Executive GPA untuk pembukaan buffer panen darurat 24 jam.',
            'cta'        => 'Ajukan Pasokan Khusus',
            'highlights' => [
                'Prioritas alokasi sortasi Grade Super dari green house binaan',
                'Pengiriman armada chiller berpendingin khusus langsung ke sentra produksi Anda',
            ],
        ];

        return view('klien.catalog', compact(
            'commodities',
            'contractData',
            'quota',
            'credit',
            'dispatch',
            'rulebook',
            'categories',
            'origins',
            'sortOptions',
            'adhoc'
        ))->with('contract', $contractData);
    }
}
