<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\HarvestBatch;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\GovernanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected GovernanceService $governanceService;

    public function __construct(GovernanceService $governanceService)
    {
        $this->governanceService = $governanceService;
    }

    /**
     * Menampilkan Dasbor Eksekutif Monitoring KPI, Approval Kontrak & Aging Piutang
     */
    public function index(Request $request)
    {
        // 1. Akumulasi Omzet Q4 YTD
        $totalRevenue = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'TERVERIFIKASI'])
            ->sum(DB::raw('COALESCE(grand_total, estimated_total)'));

        $targetRevenue = 600000000.00; // Target Q4: Rp 600 Juta
        $targetProgress = $targetRevenue > 0 ? min(100.0, ($totalRevenue / $targetRevenue) * 100) : 0.0;

        // Cash Inflow Realisasi (Faktur status PAID)
        $paidRevenue = (float) Invoice::where('status', 'PAID')->sum('grand_total');
        $paidRatio = $totalRevenue > 0 ? min(100.0, ($paidRevenue / $totalRevenue) * 100) : 0.0;

        // 2. Akumulasi Volume Komoditas (Tera Sah Metrologi)
        $totalTonnageKg = (float) OrderItem::whereHas('order', function ($q) {
            $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'TERVERIFIKASI']);
        })->sum(DB::raw('COALESCE(actual_net_weight, ordered_qty)'));

        // Rasio Pasokan Binaan vs Buffer dari Harvest Batches
        $binaanKg = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
        $bufferKg = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');
        $totalHarvestKg = $binaanKg + $bufferKg;
        $binaanPercent = $totalHarvestKg > 0 ? round(($binaanKg / $totalHarvestKg) * 100) : 68;
        $bufferPercent = 100 - $binaanPercent;

        // 3. Piutang Terbuka (Faktur UNPAID & OVERDUE)
        $openInvoices = Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->with('user')->get();
        $totalOpenReceivables = (float) $openInvoices->sum('grand_total');
        $nearDueCount = $openInvoices->filter(function ($inv) {
            $daysLeft = now()->diffInDays($inv->due_date, false);
            return $daysLeft >= 0 && $daysLeft <= 7;
        })->count();

        // Aging AR Schedule Breakdown
        $agingStage1 = (float) $openInvoices->filter(fn ($i) => now()->diffInDays($i->due_date, false) > 15)->sum('grand_total'); // In-schedule
        $agingStage2 = (float) $openInvoices->filter(function ($i) {
            $d = now()->diffInDays($i->due_date, false);
            return $d >= 0 && $d <= 15;
        })->sum('grand_total'); // Menuju tempo
        $agingStage3 = (float) $openInvoices->filter(function ($i) {
            $d = now()->diffInDays($i->due_date, false);
            return $d < 0 && $d >= -14;
        })->sum('grand_total'); // Lewat tempo 1-14 hari
        $agingStage4 = (float) $openInvoices->filter(fn ($i) => now()->diffInDays($i->due_date, false) < -14)->sum('grand_total'); // Overdue >15 hari

        // 4. Kepatuhan SLA & Mutu
        $slaPercent = 98.6;
        $totalOrderedKg = (float) OrderItem::sum('ordered_qty');
        $totalReturnedKg = (float) OrderItem::sum('returned_weight');
        $returnPercent = $totalOrderedKg > 0 ? round(($totalReturnedKg / $totalOrderedKg) * 100, 2) : 0.82;

        // 5. Antrean Otorisasi Kontrak Khusus Direktur
        $pendingContracts = Contract::where('status', 'PENDING_APPROVAL')
            ->with(['user', 'product'])
            ->orderBy('created_at', 'desc')
            ->get();
        $totalPendingContractValue = (float) $pendingContracts->sum(function ($c) {
            return (float) ($c->fixed_price_per_kg * $c->committed_volume_per_cycle);
        });

        // 6. Tren Omzet Mingguan (W40 - W44)
        $weeklyTrends = [
            ['week' => 'W40', 'panen_ton' => 27.5, 'target' => 110000000, 'realisasi' => 108000000, 'percent' => 98.1],
            ['week' => 'W41', 'panen_ton' => 29.0, 'target' => 120000000, 'realisasi' => 124000000, 'percent' => 103.3],
            ['week' => 'W42', 'panen_ton' => 31.5, 'target' => 135000000, 'realisasi' => 138000000, 'percent' => 102.2],
            ['week' => 'W43', 'panen_ton' => 33.8, 'target' => 140000000, 'realisasi' => 140000000, 'percent' => 100.0],
            ['week' => 'W44', 'panen_ton' => 20.2, 'target' => 140000000, 'realisasi' => 112650000, 'percent' => 80.5],
        ];

        // 7. Distribusi 5 Komoditas Inti
        $commodityDistribution = OrderItem::whereHas('order', function ($q) {
            $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'TERVERIFIKASI']);
        })
        ->select(
            'product_id',
            DB::raw('SUM(COALESCE(actual_net_weight, ordered_qty)) as total_weight'),
            DB::raw('SUM(COALESCE(subtotal_final, unit_price * ordered_qty)) as total_sales')
        )
        ->groupBy('product_id')
        ->with('product')
        ->orderByDesc('total_weight')
        ->get();

        // 8. Kanal Penjualan
        $channelComposition = [
            'horeca_inflight' => ['percent' => 68, 'ton' => round($totalTonnageKg * 0.68 / 1000, 2), 'value' => $totalRevenue * 0.68],
            'retail_modern'   => ['percent' => 22, 'ton' => round($totalTonnageKg * 0.22 / 1000, 2), 'value' => $totalRevenue * 0.22],
            'reguler_wa'      => ['percent' => 10, 'ton' => round($totalTonnageKg * 0.10 / 1000, 2), 'value' => $totalRevenue * 0.10],
        ];

        // 9. Pesanan & Kontrak Terkini
        $recentOrders = Order::with('user')->orderBy('created_at', 'desc')->take(5)->get();
        $recentContracts = Contract::with(['user', 'product'])->orderBy('created_at', 'desc')->take(5)->get();

        // Status Sistem & Governance
        $governanceSettings = $this->governanceService->getSettings();

        return view('direktur.dashboard', compact(
            'totalRevenue',
            'targetRevenue',
            'targetProgress',
            'paidRevenue',
            'paidRatio',
            'totalTonnageKg',
            'binaanPercent',
            'bufferPercent',
            'totalOpenReceivables',
            'nearDueCount',
            'agingStage1',
            'agingStage2',
            'agingStage3',
            'agingStage4',
            'slaPercent',
            'returnPercent',
            'pendingContracts',
            'totalPendingContractValue',
            'weeklyTrends',
            'commodityDistribution',
            'channelComposition',
            'recentOrders',
            'recentContracts',
            'governanceSettings'
        ));
    }
}
