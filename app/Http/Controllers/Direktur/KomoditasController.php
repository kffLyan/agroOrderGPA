<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Resources\Direktur\VolumeKomoditasResource;
use App\Models\Contract;
use App\Models\HarvestBatch;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller monitoring volume pasokan komoditas, neraca panen, dan alokasi kontrak B2B
class KomoditasController extends Controller
{
    // Menampilkan neraca pasokan komoditas, stok cadangan buffer, serta proyeksi kebutuhan panen
    public function index(Request $request): View
    {
        // 1. Akumulasi pasokan panen fisik antara mitra petani binaan vs stok penyangga (buffer luar)
        $binaanKg = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
        $bufferKg = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');

        $binaanTon = round($binaanKg / 1000, 2);
        $bufferTon = round($bufferKg / 1000, 2);
        $totalTon  = round($binaanTon + $bufferTon, 2);

        $binaanPercent = $totalTon > 0 ? round(($binaanTon / $totalTon) * 100) : 0;
        $bufferPercent = $totalTon > 0 ? (100 - $binaanPercent) : 0;

        // Hitung total realisasi penjualan riil komoditas pada bulan berjalan
        $realSoldKg = (float) OrderItem::whereHas('order', function ($query) {
            $query->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS']);
        })->sum('ordered_qty');
        $totalSoldTon = round($realSoldKg / 1000, 2);

        // Evaluasi persentase susut sortir atau retur barang terhadap total volume pesanan
        $orderedTotalKg  = (float) OrderItem::sum('ordered_qty');
        $returnedTotalKg = (float) OrderItem::sum('returned_weight');
        $shrinkagePercent = $orderedTotalKg > 0 ? round(($returnedTotalKg / $orderedTotalKg) * 100, 1) : 0.0;

        // Hitung stok cadangan penyangga (safety stock) yang saat ini siap didistribusikan
        $availBufferKg = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $safetyStockBufferTon = round($availBufferKg / 1000, 2);

        // 2. Evaluasi neraca volume untuk lima komoditas utama melalui resource presenter
        $products = Product::with('harvestBatches')->orderBy('id')->take(5)->get();
        $totalHarvestAll = (float) (HarvestBatch::sum('initial_quantity') ?: 1);

        $commodityRows = $products->map(
            fn ($product, $index) => (new VolumeKomoditasResource($product, $index, $totalHarvestAll))->toArray($request)
        )->all();

        // 3. Ambil daftar komitmen kuota dari seluruh kontrak kemitraan B2B yang aktif
        $allocationsList = Contract::with(['user', 'product'])
            ->where('status', 'ACTIVE')
            ->get();

        // 4. Analisis tren perbandingan volume permintaan pesanan vs kapasitas panen selama 8 pekan
        $weeklyRows = [];
        $currentDate = now();
        for ($weekOffset = -4; $weekOffset <= 3; $weekOffset++) {
            $targetDate  = (clone $currentDate)->addWeeks($weekOffset);
            $startOfWeek = (clone $targetDate)->startOfWeek();
            $endOfWeek   = (clone $targetDate)->endOfWeek();

            // Format rentang tanggal mingguan yang ramah dan mudah dipahami
            $startMonth = $startOfWeek->translatedFormat('M');
            $endMonth = $endOfWeek->translatedFormat('M');
            $dateRangeLabel = ($startMonth === $endMonth)
                ? ($startOfWeek->format('d') . '–' . $endOfWeek->translatedFormat('d M'))
                : ($startOfWeek->translatedFormat('d M') . '–' . $endOfWeek->translatedFormat('d M'));

            $demandKg = (float) OrderItem::whereHas('order', function ($query) use ($startOfWeek, $endOfWeek) {
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            })->sum('ordered_qty');

            $harvestKg = (float) HarvestBatch::whereBetween('batch_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])->sum('initial_quantity');
            $availKg   = (float) HarvestBatch::whereBetween('batch_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])->sum('available_quantity');

            $totalTons  = round(($demandKg + $harvestKg) / 1000, 1);
            $demandTon  = round($demandKg / 1000, 1);
            $yieldTon   = round($harvestKg / 1000, 1);
            $reserveTon = round($availKg / 1000, 1);

            $sumTon     = $demandTon + $yieldTon + $reserveTon;
            $demandPct  = $sumTon > 0 ? round(($demandTon / $sumTon) * 100) : 0;
            $yieldPct   = $sumTon > 0 ? round(($yieldTon / $sumTon) * 100) : 0;
            $reservePct = $sumTon > 0 ? (100 - $demandPct - $yieldPct) : 0;

            $status = $weekOffset < 0 ? 'closed' : ($weekOffset === 0 ? 'running' : 'forecast');

            $weeklyRows[] = [
                'week'      => $dateRangeLabel,
                'week_code' => 'W' . $startOfWeek->format('W'),
                'total'     => $totalTons > 0 ? $totalTons : 0.0,
                'demand'    => $demandPct,
                'yield'     => $yieldPct,
                'reserve'   => $reservePct,
                'status'    => $status,
            ];
        }

        return view('direktur.komoditas', [
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

    // Mengunduh rekapitulasi data neraca volume komoditas dalam format berkas CSV
    public function exportCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Volume_Komoditas_' . date('Ymd') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBFsep=;\r\n");

            fputcsv($handle, ['LAPORAN NERACA VOLUME KOMODITAS & SUPPLY CHAIN KOPERASI GPA'], ';');
            fputcsv($handle, ['WAKTU EKSPOR', date('d-m-Y H:i:s') . ' WIB'], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, ['ID', 'Nama Komoditas', 'Kebutuhan B2B (ton)', 'Realisasi Panen (ton)', 'Status Pasokan'], ';');

            $allProducts = Product::orderBy('id')->get();
            foreach ($allProducts as $product) {
                $demandKg  = (float) OrderItem::where('product_id', $product->id)->sum('ordered_qty');
                $harvestKg = (float) HarvestBatch::where('product_id', $product->id)->sum('initial_quantity');
                $demandTon  = round($demandKg / 1000, 2);
                $harvestTon = round($harvestKg / 1000, 2);

                $status = $harvestTon >= $demandTon ? 'Surplus Pasokan' : 'Defisit Pasokan';
                $diff = $demandTon > 0 ? round((($harvestTon - $demandTon) / $demandTon) * 100, 1) : 0;
                $statusLabel = $status . ' (' . ($diff >= 0 ? '+' : '') . $diff . '%)';

                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    number_format($demandTon, 2, '.', ''),
                    number_format($harvestTon, 2, '.', ''),
                    $statusLabel,
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
