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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

// Controller eksekutif untuk memantau performa bisnis, omzet, piutang, dan SLA koperasi
class DashboardController extends Controller
{
    // Mempersiapkan metrik kinerja utama eksekutif (omzet, tonase panen, AR aging, dan tren mingguan)
    public function index(Request $request): View
    {
        $user = Auth::user();

        // 1. Akumulasi total omzet dari pesanan sah (tidak termasuk pesanan batal atau draf)
        $totalRevenue = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->sum('grand_total');

        $targetRevenue = 600000000;
        $revenuePercent = $targetRevenue > 0 ? round(($totalRevenue / $targetRevenue) * 100, 1) : 0;

        $paidRevenue = (float) Invoice::where('status', 'PAID')->sum('grand_total');
        $paidRatio = $totalRevenue > 0 ? round(($paidRevenue / $totalRevenue) * 100, 1) : 0;

        // 2. Perbandingan volume panen antara pasokan petani binaan vs stok penyangga (buffer luar)
        $totalTonnageKg = (float) OrderItem::whereHas('order', function ($query) {
            $query->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS']);
        })->sum('ordered_qty');

        $binaanKg = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
        $bufferKg = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');
        $totalHarvestKg = $binaanKg + $bufferKg;
        $binaanPercent = $totalHarvestKg > 0 ? round(($binaanKg / $totalHarvestKg) * 100) : 0;
        $bufferPercent = $totalHarvestKg > 0 ? (100 - $binaanPercent) : 0;

        // 3. Rekapitulasi piutang terbuka dan klasifikasi faktur yang mendekati jatuh tempo
        $openInvoices = Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->get();
        $totalReceivables = (float) $openInvoices->sum('grand_total');

        $today = now()->startOfDay();
        $nearDueInvoices = $openInvoices->filter(function ($invoice) use ($today) {
            if (!$invoice->due_date) return false;
            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            $diffDays = (int) round($today->diffInDays($dueDate, false));
            return $diffDays >= 0 && $diffDays <= 7;
        });
        $nearDueCount = $nearDueInvoices->count();
        $nearDueAmount = (float) $nearDueInvoices->sum('grand_total');
        $activeContractsCount = Contract::where('status', 'ACTIVE')->count();

        // Pengelompokan umur piutang (AR Aging) untuk memantau risiko keterlambatan pembayaran
        $bucket0_15 = (float) $openInvoices->filter(function ($invoice) use ($today) {
            if (!$invoice->due_date) return true;
            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            return (int) round($today->diffInDays($dueDate, false)) >= 0;
        })->sum('grand_total');

        $bucket16_30 = (float) $openInvoices->filter(function ($invoice) use ($today) {
            if (!$invoice->due_date) return false;
            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            $diffDays = (int) round($today->diffInDays($dueDate, false));
            return $diffDays >= -15 && $diffDays < 0;
        })->sum('grand_total');

        $bucket31_45 = (float) $openInvoices->filter(function ($invoice) use ($today) {
            if (!$invoice->due_date) return false;
            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            $diffDays = (int) round($today->diffInDays($dueDate, false));
            return $diffDays >= -30 && $diffDays < -15;
        })->sum('grand_total');

        $bucketOver45 = (float) $openInvoices->filter(function ($invoice) use ($today) {
            if (!$invoice->due_date) return false;
            $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
            $diffDays = (int) round($today->diffInDays($dueDate, false));
            return $diffDays < -30;
        })->sum('grand_total');

        $worstOverdueInvoice = Invoice::where('status', 'OVERDUE')
            ->with('user')
            ->orderBy('due_date', 'asc')
            ->first();

        // 4. Tingkat kepatuhan SLA pengiriman (persentase pesanan selesai mulus tanpa retur)
        $totalDelivered = Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $returnCount = Order::where('status', 'SELESAI_CATATAN')->count();
        $slaPercent = $totalDelivered > 0 ? round((($totalDelivered - $returnCount) / $totalDelivered) * 100, 1) : 100.0;

        // 5. Total nilai komitmen finansial dari draf kontrak kemitraan yang mengantre persetujuan
        $pendingContracts = Contract::whereIn('status', ['PENDING', 'PENDING_APPROVAL'])->with(['user', 'product'])->get();
        $pendingCount = $pendingContracts->count();
        $pendingValue = 0;
        foreach ($pendingContracts as $contract) {
            $volume = $contract->committed_volume_per_cycle ?? $contract->volume_kg ?? 0;
            $price = $contract->fixed_price_per_kg ?? $contract->price_per_kg ?? 0;
            $pendingValue += (float) ($volume * $price);
        }

        // 6. Tren mingguan omzet aktual dan realisasi panen selama 5 pekan terakhir
        $currentDate = now();
        $weeklyTrends = [];
        for ($weekOffset = -4; $weekOffset <= 0; $weekOffset++) {
            $targetWeekDate = (clone $currentDate)->addWeeks($weekOffset);
            $weekStart = (clone $targetWeekDate)->startOfWeek();
            $weekEnd = (clone $targetWeekDate)->endOfWeek();
            $weekLabel = 'W' . $weekStart->format('W');
            $weeklyRevenue = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('grand_total');
            $weeklyHarvestKg = (float) HarvestBatch::whereBetween('batch_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->sum('initial_quantity');
            $weeklyHarvestTon = round($weeklyHarvestKg / 1000, 1);
            $weeklyTarget = 120000000;
            $percentReached = $weeklyTarget > 0 ? round(($weeklyRevenue / $weeklyTarget) * 100, 1) : 0;
            $weeklyTrends[] = [
                'week'        => $weekLabel,
                'revenue'     => $weeklyRevenue,
                'target'      => $weeklyTarget,
                'harvest_ton' => $weeklyHarvestTon,
                'pct'         => $percentReached,
                'is_running'  => $weekOffset === 0,
            ];
        }

        // 7. Ambil 5 komoditas unggulan teratas berdasarkan total volume kuantitas terjual
        $topProducts = Product::take(5)->withSum('orderItems', 'ordered_qty')->get();
        $totalKgAll = $totalTonnageKg ?: 1;
        $topProductsData = [];
        $colorPalette = ['bg-ink', 'bg-brand', 'bg-success', 'bg-accent-deep', 'bg-accent-edge'];
        foreach ($topProducts as $index => $product) {
            $volumeKg = (float) ($product->order_items_sum_ordered_qty ?? 0);
            $volumeTon = round($volumeKg / 1000, 2);
            $marketShare = round(($volumeKg / $totalKgAll) * 100);
            $topProductsData[] = [
                'name'    => $product->name,
                'ton'     => $volumeTon,
                'share'   => $marketShare,
                'color'   => $colorPalette[$index % count($colorPalette)],
            ];
        }

        // 8. Distribusi tonase penjualan menurut saluran distribusi (B2B Kontrak, Reguler, dan Manual)
        $horecaKg = (float) OrderItem::whereHas('order.user', fn ($query) => $query->where('client_type', 'B2B_KONTRAK'))->sum('ordered_qty');
        $retailKg = (float) OrderItem::whereHas('order.user', fn ($query) => $query->where('client_type', 'REGULER'))
            ->whereHas('order', fn ($query) => $query->whereNotIn('order_source', ['WHATSAPP_ADMIN', 'MANUAL_SEKRETARIS']))
            ->sum('ordered_qty');
        $manualKg = (float) OrderItem::whereHas('order', fn ($query) => $query->whereIn('order_source', ['WHATSAPP_ADMIN', 'MANUAL_SEKRETARIS']))->sum('ordered_qty');

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
        $totalChannelTons = $channelData['horeca']['ton'] + $channelData['retail']['ton'] + $channelData['manual']['ton'];
        if ($totalChannelTons > 0) {
            $channelData['horeca']['share'] = round(($channelData['horeca']['ton'] / $totalChannelTons) * 100);
            $channelData['retail']['share'] = round(($channelData['retail']['ton'] / $totalChannelTons) * 100);
            $channelData['manual']['share'] = 100 - $channelData['horeca']['share'] - $channelData['retail']['share'];
        }

        $recentOrders = Order::with('user')->latest()->take(20)->get();
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
