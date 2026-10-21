<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommodityVolumeController extends Controller
{
    /**
     * Menampilkan Monitoring Volume Komoditas, Stok Panen & Alokasi Pasokan
     */
    public function index(Request $request): View
    {
        // 1. Perhitungan data stok & panen riil
        $binaanKg = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
        $bufferKg = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');
        $totalHarvestKg = $binaanKg + $bufferKg;

        $binaanTon = round($binaanKg / 1000, 2);
        $bufferTon = round($bufferKg / 1000, 2);
        $totalTon = round($binaanTon + $bufferTon, 2);

        $binaanPercent = $totalTon > 0 ? round(($binaanTon / $totalTon) * 100) : 0;
        $bufferPercent = $totalTon > 0 ? (100 - $binaanPercent) : 0;

        // Total Penjualan Riil (MTD)
        $realSoldKg = (float) OrderItem::whereHas('order', function ($q) {
            $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'TERVERIFIKASI']);
        })->sum(DB::raw('COALESCE(actual_net_weight, ordered_qty)'));

        $totalSoldTon = round($realSoldKg / 1000, 2);

        // Susut Gudang & Sortir
        $orderedTotalKg = (float) OrderItem::sum('ordered_qty');
        $returnedTotalKg = (float) OrderItem::sum('returned_weight');
        $shrinkagePercent = $orderedTotalKg > 0 ? round(($returnedTotalKg / $orderedTotalKg) * 100, 1) : 0.0;

        // Safety stock buffer siap pakai
        $availBufferKg = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $safetyStockBufferTon = round($availBufferKg / 1000, 2);

        // 2. Neraca Volume Lima Komoditas Inti
        $products = Product::orderBy('id')->get();
        $totalHarvestAll = HarvestBatch::sum('initial_quantity') ?: 1;

        $commodityRows = [];
        foreach ($products->take(5) as $idx => $p) {
            $prodBinaan = (float) HarvestBatch::where('product_id', $p->id)->where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
            $prodBuffer = (float) HarvestBatch::where('product_id', $p->id)->where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');
            $prodTotal = $prodBinaan + $prodBuffer;
            $prodAvail = (float) HarvestBatch::where('product_id', $p->id)->sum('available_quantity');

            $farmerTon = round($prodBinaan / 1000, 1);
            $bufferTon = round($prodBuffer / 1000, 1);
            $valueTon = round($prodTotal / 1000, 1);
            $headroomTon = round($prodAvail / 1000, 1);

            $sharePercent = $totalHarvestAll > 1 ? round(($prodTotal / $totalHarvestAll) * 100) : 0;
            $status = $prodAvail > 500 ? 'PASOKAN STABIL' : ($prodAvail > 0 ? 'KUOTA TERBATAS' : 'STOK HABIS');
            $statusTone = $prodAvail > 500 ? 'success' : ($prodAvail > 0 ? 'caution' : 'danger');

            $commodityRows[] = [
                'code'          => 'KMD-0' . ($idx + 1),
                'name'          => $p->name,
                'grade'         => $p->category ?: 'Grade A Standard',
                'share'         => $sharePercent,
                'value'         => $valueTon,
                'farmer'        => $farmerTon,
                'buffer'        => $bufferTon,
                'headroom'      => $headroomTon,
                'status'        => $status,
                'status_tone'   => $statusTone,
                'headroom_tone' => $prodAvail > 100 ? 'shell' : 'danger',
                'value_label'   => number_format($valueTon, 1, '.', ''),
                'farmer_label'  => number_format($farmerTon, 1, '.', '') . ' T',
                'buffer_label'  => number_format($bufferTon, 1, '.', '') . ' T',
                'headroom_label'=> number_format($headroomTon, 1, '.', '') . ' T',
                'share_label'   => $sharePercent . '% VOL',
                'share_tone'    => $statusTone === 'caution' ? 'caution' : 'success',
            ];
        }

        // 3. Matriks Alokasi Kontrak B2B Aktif
        $activeContracts = Contract::with(['user', 'product'])
            ->where('status', 'ACTIVE')
            ->get();

        $allocationsList = [];
        foreach ($activeContracts as $contract) {
            $volKg = (float) $contract->committed_volume_per_cycle;
            $volTon = round($volKg / 1000, 2);
            $allocationsList[] = [
                'name'       => $contract->user?->company_name ?: ($contract->user?->name ?: 'Mitra B2B'),
                'sentra'     => $contract->product ? strtoupper($contract->product->name) : 'SENTRA PRODUKSI',
                'commitment' => $volTon > 0 ? $volTon : round($volKg, 2),
                'priority'   => 'KONTRAK AKTIF',
            ];
        }

        // 4. Tren Mingguan Volume vs Kapasitas Panen (8 Pekan)
        $weeklyRows = [];
        $now = now();
        for ($i = -4; $i <= 3; $i++) {
            $targetDate = (clone $now)->addWeeks($i);
            $startOfWeek = (clone $targetDate)->startOfWeek();
            $endOfWeek = (clone $targetDate)->endOfWeek();
            $wNum = 'W' . $startOfWeek->format('W');

            $demandKg = (float) OrderItem::whereHas('order', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            })->sum('ordered_qty');

            $harvestKg = (float) HarvestBatch::whereBetween('batch_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])->sum('initial_quantity');
            $availKg = (float) HarvestBatch::whereBetween('batch_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])->sum('available_quantity');

            $totalTons = round(($demandKg + $harvestKg) / 1000, 1);
            $demandTon = round($demandKg / 1000, 1);
            $yieldTon = round($harvestKg / 1000, 1);
            $reserveTon = round($availKg / 1000, 1);

            $sumTon = $demandTon + $yieldTon + $reserveTon;
            $demandPct = $sumTon > 0 ? round(($demandTon / $sumTon) * 100) : 0;
            $yieldPct = $sumTon > 0 ? round(($yieldTon / $sumTon) * 100) : 0;
            $reservePct = $sumTon > 0 ? (100 - $demandPct - $yieldPct) : 0;

            $status = $i < 0 ? 'closed' : ($i === 0 ? 'running' : 'forecast');

            $weeklyRows[] = [
                'week'    => $wNum,
                'total'   => $totalTons > 0 ? $totalTons : 0.0,
                'demand'  => $demandPct,
                'yield'   => $yieldPct,
                'reserve' => $reservePct,
                'status'  => $status,
            ];
        }

        return view('direktur.volume', [
            'totalSoldTon'         => $totalSoldTon,
            'binaanTon'            => $binaanTon,
            'bufferTon'            => $bufferTon,
            'totalTon'             => $totalTon,
            'binaanPercent'        => $binaanPercent,
            'bufferPercent'        => $bufferPercent,
            'shrinkagePercent'     => $shrinkagePercent,
            'safetyStockBufferTon' => $safetyStockBufferTon,
            'commodityRows'        => $commodityRows,
            'weeklyRows'           => $weeklyRows,
            'allocationsList'      => $allocationsList,
        ]);
    }

    /**
     * Unduh Laporan Volume Komoditas (.CSV)
     */
    public function exportCsv(Request $request)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Volume_Komoditas_' . date('Ymd') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['LAPORAN NERACA VOLUME KOMODITAS & SUPPLY CHAIN KOPERASI GPA']);
            fputcsv($handle, ['WAKTU EKSPOR', date('d-m-Y H:i:s') . ' WIB']);
            fputcsv($handle, []);
            fputcsv($handle, ['ID', 'Nama Komoditas', 'Kebutuhan B2B (ton)', 'Realisasi Panen (ton)', 'Status Pasokan']);

            $allProducts = Product::orderBy('id')->get();
            foreach ($allProducts as $p) {
                $demandKg = (float) OrderItem::where('product_id', $p->id)->sum('ordered_qty');
                $harvestKg = (float) HarvestBatch::where('product_id', $p->id)->sum('initial_quantity');
                $demandTon = round($demandKg / 1000, 2);
                $harvestTon = round($harvestKg / 1000, 2);

                $status = $harvestTon >= $demandTon ? 'SURPLUS' : 'DEFISIT';
                $diff = $demandTon > 0 ? round((($harvestTon - $demandTon) / $demandTon) * 100, 1) : 0;
                $statusLabel = $status . ' (' . ($diff >= 0 ? '+' : '') . $diff . '%)';

                fputcsv($handle, [
                    $p->id,
                    $p->name,
                    number_format($demandTon, 2, '.', ''),
                    number_format($harvestTon, 2, '.', ''),
                    $statusLabel,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
