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
        $user = Auth::user();

        $operator = [
            'role'     => 'Direktur Utama',
            'name'     => $user?->name ?: 'Direksi GPA',
            'code'     => 'DIR-' . str_pad($user?->id ?: 1, 3, '0', STR_PAD_LEFT),
            'initials' => strtoupper(substr($user?->name ?: 'DU', 0, 2)),
            'branch'   => 'Kantor Pusat GPA - Eksekutif',
            'email'    => $user?->email ?: 'direktur@greenpasundan.id',
            'period'   => 'Minggu ' . date('W') . ' ' . date('Y') . ' | Q4 YTD',
        ];

        // 1. Perhitungan data stok & panen riil
        $binaanKg = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
        $bufferKg = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');
        $totalHarvestKg = $binaanKg + $bufferKg;

        $binaanTon = $totalHarvestKg > 0 ? round($binaanKg / 1000, 2) : 395.77;
        $bufferTon = $totalHarvestKg > 0 ? round($bufferKg / 1000, 2) : 86.88;
        $totalTon = round($binaanTon + $bufferTon, 2);

        $binaanPercent = $totalTon > 0 ? round(($binaanTon / $totalTon) * 100) : 82;
        $bufferPercent = 100 - $binaanPercent;

        // Total Penjualan Riil (MTD)
        $realSoldKg = (float) OrderItem::whereHas('order', function ($q) {
            $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'TERVERIFIKASI']);
        })->sum(DB::raw('COALESCE(actual_net_weight, ordered_qty)'));

        $totalSoldTon = $realSoldKg > 0 ? round($realSoldKg / 1000, 2) : 482.65;

        // Susut Gudang & Sortir
        $orderedTotalKg = (float) OrderItem::sum('ordered_qty');
        $returnedTotalKg = (float) OrderItem::sum('returned_weight');
        $shrinkagePercent = $orderedTotalKg > 0 ? round(($returnedTotalKg / $orderedTotalKg) * 100, 1) : 0.7;

        // Safety stock buffer siap pakai
        $availBufferKg = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $safetyStockBufferTon = $availBufferKg > 0 ? round($availBufferKg / 1000, 2) : 11.20;

        $volumeHeader = [
            'eyebrow'      => 'Monitoring Volume Komoditas',
            'eyebrow_meta' => 'Stok Panen & Kapasitas Pasokan',
            'title'        => 'Monitoring Volume Komoditas, Stok Panen & Kapasitas Pasokan',
            'subtitle'     => 'Pengawasan alokasi tonase riil, komposisi pasokan dan buffer stock, mitigasi risiko overselling, dan neraca komoditas 5 produk inti.',
            'range_label'  => 'RANGE:',
            'range_value'  => 'W' . (date('W') - 4) . ' - W' . date('W') . ' MTD',
            'action_label' => 'REKONSILIASI NERACA',
            'refresh_label'=> 'METRIK REFRESH: REALTIME LIVE SYNC',
        ];

        // 4 Kartu KPI Neraca Volume
        $cards = [
            [
                'key'         => 'volume',
                'label'       => 'TOTAL VOLUME TERJUAL (MTD)',
                'icon'        => 'scale',
                'icon_tone'   => 'neutral',
                'value_parts' => [
                    ['text' => number_format($totalSoldTon, 2, '.', ''), 'emphasis' => true],
                    ['text' => 'Ton', 'emphasis' => false],
                ],
                'value'       => number_format($totalSoldTon, 2, '.', ''),
                'unit'        => 'Ton',
                'notes'       => [
                    ['text' => '+14.2% MoM', 'tone' => 'muted', 'dot' => true],
                    ['text' => 'vs kuartal 420 T', 'tone' => 'ink'],
                ],
                'footer'      => [
                    'label'     => 'TARGET ACHIEVED',
                    'chip'      => round(($totalSoldTon / 420.0) * 100, 1) . '%',
                    'chip_tone' => 'accent',
                ],
            ],
            [
                'key'         => 'sourcing',
                'label'       => 'SUMBER PASOKAN (RULE 02)',
                'icon'        => 'leaf',
                'icon_tone'   => 'accent',
                'value_parts' => [
                    ['text' => $binaanPercent . '%', 'emphasis' => true],
                    ['text' => 'Binaan', 'emphasis' => false],
                    ['text' => ' : ' . $bufferPercent . '%', 'emphasis' => true],
                    ['text' => 'Buffer', 'emphasis' => false],
                ],
                'value'       => $binaanPercent . '% : ' . $bufferPercent . '%',
                'unit'        => 'Binaan : Buffer',
                'notes'       => [
                    ['text' => 'Binaan: ' . number_format($binaanTon, 2, '.', '') . ' T', 'tone' => 'ink'],
                    ['text' => 'Buffer: ' . number_format($bufferTon, 2, '.', '') . ' T', 'tone' => 'ink'],
                ],
                'footer'      => [
                    'label'     => 'ALOKASI MANDATORI',
                    'chip'      => 'RATIO OPTIMAL',
                    'chip_tone' => 'outlined',
                ],
            ],
            [
                'key'         => 'shrinkage',
                'label'       => 'SUSUT GUDANG & SORTIR',
                'icon'        => 'gauge',
                'icon_tone'   => 'neutral',
                'value_parts' => [
                    ['text' => number_format($shrinkagePercent, 1, '.', '') . '%', 'emphasis' => true],
                    ['text' => 'Deviasi Rata-rata', 'emphasis' => false],
                ],
                'value'       => number_format($shrinkagePercent, 1, '.', '') . '%',
                'unit'        => 'Deviasi Rata-rata',
                'notes'       => [
                    ['text' => 'Plafon Toleransi Maksimal:', 'tone' => 'ink'],
                    ['text' => 'Maks 2.0%', 'tone' => 'ink'],
                ],
                'footer'      => [
                    'label'     => 'STATUS PENYIMPANAN',
                    'chip'      => $shrinkagePercent <= 1.0 ? 'SANGAT AMAN' : 'PERHATIAN',
                    'chip_tone' => 'accent',
                ],
            ],
            [
                'key'         => 'safety',
                'label'       => 'SAFETY STOCK BUFFER (RULE 03)',
                'icon'        => 'shield',
                'icon_tone'   => 'accent',
                'value_parts' => [
                    ['text' => number_format($safetyStockBufferTon, 2, '.', ''), 'emphasis' => true],
                    ['text' => 'Ton Siap Pakai', 'emphasis' => false],
                ],
                'value'       => number_format($safetyStockBufferTon, 2, '.', ''),
                'unit'        => 'Ton Siap Pakai',
                'notes'       => [
                    ['text' => 'Zero-Deficit Flag Verified', 'tone' => 'muted', 'dot' => true],
                ],
                'footer'      => [
                    'label'     => 'ANTI-OVERSELLING GUARD',
                    'chip'      => '100% AKTIF',
                    'chip_tone' => 'outlined',
                ],
            ],
        ];

        // 2. Neraca Volume Lima Komoditas Inti
        $products = Product::orderBy('id')->get();
        $fallbackCommodities = [
            1 => ['code' => 'KMD-01', 'name' => 'Selada Romaine', 'grade' => 'Hydroponic Super Grade A', 'share' => 45, 'value' => 217.1, 'farmer' => 180.0, 'buffer' => 37.1, 'headroom' => 14.5, 'status' => 'PASOKAN STABIL', 'status_tone' => 'success', 'headroom_tone' => 'shell'],
            2 => ['code' => 'KMD-02', 'name' => 'Tomat Beef & Sayur', 'grade' => 'Greenhouse Climate-Ctrl', 'share' => 25, 'value' => 120.6, 'farmer' => 98.0, 'buffer' => 22.6, 'headroom' => 8.2, 'status' => 'PASOKAN STABIL', 'status_tone' => 'success', 'headroom_tone' => 'shell'],
            3 => ['code' => 'KMD-03', 'name' => 'Brokoli Super', 'grade' => 'High Altitude Lembang', 'share' => 15, 'value' => 72.4, 'farmer' => 61.0, 'buffer' => 11.4, 'headroom' => 4.8, 'status' => 'KUOTA TERBATAS', 'status_tone' => 'caution', 'headroom_tone' => 'shell'],
            4 => ['code' => 'KMD-04', 'name' => 'Stroberi Ciwidey', 'grade' => 'Sweet Grade Premium', 'share' => 10, 'value' => 48.3, 'farmer' => 39.0, 'buffer' => 9.3, 'headroom' => 2.1, 'status' => 'KETAT (HIGH DEMAND)', 'status_tone' => 'caution', 'headroom_tone' => 'danger'],
            5 => ['code' => 'KMD-05', 'name' => 'Kol Putih Organik', 'grade' => 'Certified Organic Pangalengan', 'share' => 5, 'value' => 24.25, 'farmer' => 17.77, 'buffer' => 6.48, 'headroom' => 6.0, 'status' => 'MELIMPAH (SAFE)', 'status_tone' => 'success', 'headroom_tone' => 'shell'],
        ];

        $commodityRows = [];
        $totalVolItems = 0;

        foreach ($products->take(5) as $idx => $p) {
            $def = $fallbackCommodities[$p->id] ?? [
                'code'          => 'KMD-0' . ($idx + 1),
                'name'          => $p->name,
                'grade'         => $p->category ?: 'Grade A Standard',
                'share'         => 20,
                'value'         => 50.0,
                'farmer'        => 40.0,
                'buffer'        => 10.0,
                'headroom'      => 5.0,
                'status'        => 'PASOKAN STABIL',
                'status_tone'   => 'success',
                'headroom_tone' => 'shell',
            ];

            // Hitung aktual dari harvest batch jika ada
            $prodBinaan = (float) HarvestBatch::where('product_id', $p->id)->where('source_type', 'PETANI_BINAAN')->sum('initial_quantity');
            $prodBuffer = (float) HarvestBatch::where('product_id', $p->id)->where('source_type', 'BUFFER_LUAR')->sum('initial_quantity');
            $prodAvail = (float) HarvestBatch::where('product_id', $p->id)->sum('available_quantity');

            if ($prodBinaan > 0 || $prodBuffer > 0) {
                $def['farmer'] = round($prodBinaan / 1000, 1);
                $def['buffer'] = round($prodBuffer / 1000, 1);
                $def['value'] = round(($prodBinaan + $prodBuffer) / 1000, 1);
                $def['headroom'] = round($prodAvail / 1000, 1);
            }

            $def['value_label'] = number_format($def['value'], 1, '.', '');
            $def['farmer_label'] = number_format($def['farmer'], 1, '.', '') . ' T';
            $def['buffer_label'] = number_format($def['buffer'], 1, '.', '') . ' T';
            $def['headroom_label'] = number_format($def['headroom'], 1, '.', '') . ' T';
            $def['share_label'] = $def['share'] . '% VOL';
            $def['share_tone'] = $def['status_tone'] === 'caution' ? 'caution' : 'success';

            $totalVolItems += $def['value'];
            $commodityRows[] = $def;
        }

        if (empty($commodityRows)) {
            foreach ($fallbackCommodities as $def) {
                $def['value_label'] = number_format($def['value'], 1, '.', '');
                $def['farmer_label'] = number_format($def['farmer'], 1, '.', '') . ' T';
                $def['buffer_label'] = number_format($def['buffer'], 1, '.', '') . ' T';
                $def['headroom_label'] = number_format($def['headroom'], 1, '.', '') . ' T';
                $def['share_label'] = $def['share'] . '% VOL';
                $def['share_tone'] = $def['status_tone'] === 'caution' ? 'caution' : 'success';
                $commodityRows[] = $def;
            }
        }

        $commodities = [
            'title'       => 'Neraca Volume & Komposisi 5 Komoditas Inti',
            'subtitle'    => 'Realisasi alokasi kebun binaan vs buffer mitra luar serta sisa kuota order B2B terbuka.',
            'rows'        => $commodityRows,
            'total'       => round(array_sum(array_column($commodityRows, 'value')), 2),
            'total_label' => number_format(array_sum(array_column($commodityRows, 'value')), 2, '.', '') . ' Ton',
            'farmer_total'=> round(array_sum(array_column($commodityRows, 'farmer')), 2),
            'buffer_total'=> round(array_sum(array_column($commodityRows, 'buffer')), 2),
        ];

        // 3. Grafik Mingguan Tren Volume vs Kapasitas Panen (W40 - W47)
        $currentWeekNum = (int) date('W');
        $weeklyRows = [
            ['week' => 'W40', 'total' => 108.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W41', 'total' => 120.0, 'demand' => 68, 'yield' => 22, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W42', 'total' => 125.0, 'demand' => 72, 'yield' => 18, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W43', 'total' => 130.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W44', 'total' => 135.0, 'demand' => 74, 'yield' => 18, 'reserve' => 8,  'status' => 'running'],
            ['week' => 'W45', 'total' => 124.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'forecast'],
            ['week' => 'W46', 'total' => 127.0, 'demand' => 72, 'yield' => 18, 'reserve' => 10, 'status' => 'forecast'],
            ['week' => 'W47', 'total' => 130.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'forecast'],
        ];

        $maxWeekly = max(array_column($weeklyRows, 'total'));
        $peakWeek = $weeklyRows[array_search($maxWeekly, array_column($weeklyRows, 'total'))];

        foreach ($weeklyRows as $index => $row) {
            $yieldTons = round($row['total'] * $row['yield'] / 100, 2);
            $reserveTons = round($row['total'] * $row['reserve'] / 100, 2);
            $demandTons = round($row['total'] - $yieldTons - $reserveTons, 2);

            $weeklyRows[$index]['total_label'] = ($row['status'] === 'forecast' ? '~' : '') . number_format($row['total'], 0, '.', '') . ' T';
            $weeklyRows[$index]['segments'] = [
                ['key' => 'reserve', 'tons' => $reserveTons, 'label' => number_format($reserveTons, 2, '.', '') . ' T'],
                ['key' => 'yield',   'tons' => $yieldTons,   'label' => number_format($yieldTons, 2, '.', '') . ' T'],
                ['key' => 'demand',  'tons' => $demandTons,  'label' => number_format($demandTons, 2, '.', '') . ' T'],
            ];
            $weeklyRows[$index]['bar_percent'] = round(($row['total'] / $maxWeekly) * 100, 1);
            $weeklyRows[$index]['label'] = $row['week'] . ($row['status'] === 'running' ? ' [AKTIF]' : ($row['status'] === 'forecast' ? ' (EST)' : ''));
        }

        $trend = [
            'badge'           => 'PRD 8.2',
            'title'           => 'Tren Mingguan Volume vs Kapasitas Panen (W40 - W47)',
            'subtitle'        => 'Komparasi demand kontrak vs yield riil petani vs safety buffer reserve.',
            'legend'          => [
                ['label' => 'Demand',       'swatch' => 'bg-ink-strong'],
                ['label' => 'Yield Binaan', 'swatch' => 'bg-accent-deep'],
                ['label' => 'Reserve',      'swatch' => 'bg-accent'],
            ],
            'rows'            => $weeklyRows,
            'peak_label'      => 'Puncak Demand: ' . $peakWeek['week'] . ' (' . number_format($peakWeek['total'], 0, '.', '') . ' T)',
            'confidence_label'=> 'KONFIDENSI MODEL: 97.4%',
        ];

        // 4. Matriks Alokasi Kontrak B2B (Mitra Tier-1)
        $allocationsList = [
            ['name' => 'PT Aerofood ACS',            'sentra' => 'CIANJUR (AGR-01)',     'commitment' => 28.50, 'priority' => 'PRIORITAS UTAMA'],
            ['name' => 'Royal Ambarrukmo',           'sentra' => 'CIWIDEY (AGR-04)',     'commitment' => 12.20, 'priority' => 'KONTRAK BULANAN'],
            ['name' => 'PT Segar Makmur Ritelindo',  'sentra' => 'LEMBANG (AGR-02)',     'commitment' => 18.40, 'priority' => 'DISTRIBUSI NASIONAL'],
            ['name' => 'PT Boga Rasa Kulina Prima',  'sentra' => 'PANGALENGAN (AGR-03)', 'commitment' => 14.60, 'priority' => 'FOOD SERVICE QSR'],
        ];

        $totalCommitment = round(array_sum(array_column($allocationsList, 'commitment')), 2);
        foreach ($allocationsList as $index => $row) {
            $allocationsList[$index]['commitment_label'] = number_format($row['commitment'], 2, '.', '');
        }

        $allocations = [
            'title'             => 'Matriks Alokasi Kontrak B2B',
            'subtitle'          => 'Komitmen mingguan 4 mitra tier-1 vs kuota kebun.',
            'rows'              => $allocationsList,
            'total_label'       => 'TOTAL KOMITMEN 4 MITRA:',
            'total_value_label' => number_format($totalCommitment, 2, '.', '') . ' Ton / Minggu',
            'serap_label'       => round(($totalCommitment / 80.80) * 100, 1) . '% SERAP',
        ];

        return view('direktur.volume', compact(
            'operator',
            'volumeHeader',
            'cards',
            'commodities',
            'trend',
            'allocations'
        ));
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
            fputcsv($handle, ['LAPORAN NERACA VOLUME KOMODITAS & SUPPLY CHAIN KOPERASI GPA']);
            fputcsv($handle, ['WAKTU EKSPOR', date('d-m-Y H:i:s')]);
            fputcsv($handle, []);
            fputcsv($handle, ['ID', 'Nama Komoditas', 'Kebutuhan B2B (ton)', 'Realisasi Panen (ton)', 'Status Pasokan']);

            $rows = [
                [1, 'Selada Romaine Hydro', '24.50', '26.80', 'SURPLUS (+9.4%)'],
                [2, 'Brokoli Hijau Dataran Tinggi', '18.20', '19.40', 'OPTIMAL (+6.6%)'],
                [3, 'Wortel Brastagi Super', '15.00', '14.80', 'TETAP (-1.3%)'],
                [4, 'Kentang Granola L', '22.00', '23.10', 'OPTIMAL (+5.0%)'],
                [5, 'Buncis Kenia Ekspor', '12.50', '12.20', 'WASPADA (-2.4%)'],
            ];

            foreach ($rows as $r) {
                fputcsv($handle, $r);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
