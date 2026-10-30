<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\HarvestBatch;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan Dasbor Eksekutif Monitoring Bisnis & Otorisasi Direktur
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // 1. Akumulasi Omzet dari pesanan sah
        $totalRevenue = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->sum('grand_total');

        $targetRevenue = 600000000;
        $revenuePercent = $targetRevenue > 0 ? round(($totalRevenue / $targetRevenue) * 100, 1) : 0;

        $paidRevenue = (float) Invoice::where('status', 'PAID')->sum('grand_total');
        $paidRatio = $totalRevenue > 0 ? round(($paidRevenue / $totalRevenue) * 100, 1) : 0;

        // 2. Volume Komoditas (Tera Sah Metrologi)
        $totalTonnageKg = (float) OrderItem::whereHas('order', function ($q) {
            $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS']);
        })->sum('ordered_qty');

        $binaanKg = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
        $bufferKg = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');
        $totalHarvestKg = $binaanKg + $bufferKg;
        $binaanPercent = $totalHarvestKg > 0 ? round(($binaanKg / $totalHarvestKg) * 100) : 0;
        $bufferPercent = $totalHarvestKg > 0 ? (100 - $binaanPercent) : 0;

        // 3. Piutang Terbuka (Faktur UNPAID & OVERDUE)
        $openInvoices = Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->get();
        $totalReceivables = (float) $openInvoices->sum('grand_total');

        $nearDueInvoices = $openInvoices->filter(function ($inv) {
            if (!$inv->due_date) return false;
            $diff = now()->diffInDays($inv->due_date, false);
            return $diff >= 0 && $diff <= 7;
        });
        $nearDueCount = $nearDueInvoices->count();
        $nearDueAmount = (float) $nearDueInvoices->sum('grand_total');
        $activeContractsCount = Contract::where('status', 'ACTIVE')->count();

        // AR Aging Brackets
        $bucket0_15 = (float) $openInvoices->filter(fn ($i) => !$i->due_date || now()->diffInDays($i->due_date, false) >= 0)->sum('grand_total');
        $bucket16_30 = (float) $openInvoices->filter(function ($i) {
            if (!$i->due_date) return false;
            $d = now()->diffInDays($i->due_date, false);
            return $d >= -15 && $d < 0;
        })->sum('grand_total');
        $bucket31_45 = (float) $openInvoices->filter(function ($i) {
            if (!$i->due_date) return false;
            $d = now()->diffInDays($i->due_date, false);
            return $d >= -30 && $d < -15;
        })->sum('grand_total');
        $bucketOver45 = (float) $openInvoices->filter(function ($i) {
            if (!$i->due_date) return false;
            $d = now()->diffInDays($i->due_date, false);
            return $d < -30;
        })->sum('grand_total');

        $worstOverdueInvoice = Invoice::where('status', 'OVERDUE')
            ->with('user')
            ->orderBy('due_date', 'asc')
            ->first();

        // 4. SLA Pengiriman
        $totalDelivered = Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $returnCount = Order::where('status', 'SELESAI_CATATAN')->count();
        $slaPercent = $totalDelivered > 0 ? round((($totalDelivered - $returnCount) / $totalDelivered) * 100, 1) : 100.0;

        // 5. Otorisasi Kontrak Tier-1
        $pendingContracts = Contract::whereIn('status', ['PENDING', 'PENDING_APPROVAL'])->with(['user', 'product'])->get();
        $pendingCount = $pendingContracts->count();
        $pendingValue = 0;
        foreach ($pendingContracts as $c) {
            $pendingValue += (float) (($c->committed_volume_per_cycle ?? $c->volume_kg ?? 0) * ($c->fixed_price_per_kg ?? $c->price_per_kg ?? 0));
        }

        // 6. Tren Mingguan Realistis
        $now = now();
        $weeklyTrends = [];
        for ($w = -4; $w <= 0; $w++) {
            $wDate = (clone $now)->addWeeks($w);
            $wStart = (clone $wDate)->startOfWeek();
            $wEnd = (clone $wDate)->endOfWeek();
            $wLabel = 'W' . $wStart->format('W');
            $wRev = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
                ->whereBetween('created_at', [$wStart, $wEnd])
                ->sum('grand_total');
            $wHarvestKg = (float) HarvestBatch::whereBetween('batch_date', [$wStart->toDateString(), $wEnd->toDateString()])->sum('initial_quantity');
            $wHarvestTon = round($wHarvestKg / 1000, 1);
            $targetW = 120000000;
            $pct = $targetW > 0 ? round(($wRev / $targetW) * 100, 1) : 0;
            $weeklyTrends[] = [
                'week'        => $wLabel,
                'revenue'     => $wRev,
                'target'      => $targetW,
                'harvest_ton' => $wHarvestTon,
                'pct'         => $pct,
                'is_running'  => $w === 0,
            ];
        }

        // 7. 5 Komoditas Teratas
        $topProducts = Product::take(5)->withSum('orderItems', 'ordered_qty')->get();
        $totalKgAll = $totalTonnageKg ?: 1;
        $topProductsData = [];
        $palette = ['bg-ink', 'bg-brand', 'bg-success', 'bg-accent-deep', 'bg-accent-edge'];
        foreach ($topProducts as $idx => $p) {
            $volKg = (float) ($p->order_items_sum_ordered_qty ?? 0);
            $volTon = round($volKg / 1000, 2);
            $share = round(($volKg / $totalKgAll) * 100);
            $topProductsData[] = [
                'name'    => $p->name,
                'ton'     => $volTon,
                'share'   => $share,
                'color'   => $palette[$idx % count($palette)],
            ];
        }

        // 8. Saluran Penjualan
        $horecaKg = (float) OrderItem::whereHas('order.user', fn ($q) => $q->where('client_type', 'B2B_KONTRAK'))->sum('ordered_qty');
        $retailKg = (float) OrderItem::whereHas('order.user', fn ($q) => $q->where('client_type', 'REGULER'))->sum('ordered_qty');
        $manualKg = (float) OrderItem::whereHas('order', fn ($q) => $q->where('order_source', 'MANUAL_SEKRETARIS'))->sum('ordered_qty');

        $channelData = [
            'horeca' => [
                'share' => 0,
                'ton'   => round($horecaKg / 1000, 2),
            ],
            'retail' => [
                'share' => 0,
                'ton'   => round($retailKg / 1000, 2),
            ],
            'manual' => [
                'share' => 0,
                'ton'   => round($manualKg / 1000, 2),
            ],
        ];
        $totChanTon = $channelData['horeca']['ton'] + $channelData['retail']['ton'] + $channelData['manual']['ton'];
        if ($totChanTon > 0) {
            $channelData['horeca']['share'] = round(($channelData['horeca']['ton'] / $totChanTon) * 100);
            $channelData['retail']['share'] = round(($channelData['retail']['ton'] / $totChanTon) * 100);
            $channelData['manual']['share'] = 100 - $channelData['horeca']['share'] - $channelData['retail']['share'];
        }

        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $recentContracts = Contract::with(['user', 'product'])->latest()->take(5)->get();

        return view('direktur.dashboard', [
            'totalRevenue'         => $totalRevenue,
            'targetRevenue'        => $targetRevenue,
            'revenuePercent'       => $revenuePercent,
            'paidRevenue'          => $paidRevenue,
            'paidRatio'            => $paidRatio,
            'totalTonnageKg'       => $totalTonnageKg,
            'binaanPercent'        => $binaanPercent,
            'bufferPercent'        => $bufferPercent,
            'totalReceivables'     => $totalReceivables,
            'nearDueCount'         => $nearDueCount,
            'nearDueAmount'        => $nearDueAmount,
            'activeContractsCount' => $activeContractsCount,
            'bucket0_15'           => $bucket0_15,
            'bucket16_30'          => $bucket16_30,
            'bucket31_45'          => $bucket31_45,
            'bucketOver45'         => $bucketOver45,
            'worstOverdueInvoice'  => $worstOverdueInvoice,
            'slaPercent'           => $slaPercent,
            'returnCount'          => $returnCount,
            'pendingCount'         => $pendingCount,
            'pendingValue'         => $pendingValue,
            'weeklyTrends'         => $weeklyTrends,
            'topProductsData'      => $topProductsData,
            'channelData'          => $channelData,
            'recentOrders'         => $recentOrders,
            'recentContracts'      => $recentContracts,
        ]);
    }
}
