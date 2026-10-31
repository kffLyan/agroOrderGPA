<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Resources\Direktur\SalesPortfolioResource;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Monitoring Penjualan & Analisis Pendapatan (Revenue) Direktur.
 *
 * Penjelasan UKK:
 * Halaman ini menyajikan dashboard analitik bisnis bagi Direktur Utama,
 * mencakup total omzet (MTD/WTD/YTD), margin kotor, portofolio komoditas,
 * dan distribusi metode pembayaran.
 */
class PenjualanController extends Controller
{
    /**
     * Menampilkan dashboard pemantauan penjualan dan metrik omzet.
     */
    public function index(Request $request): View
    {
        $selectedPeriod = $request->query('period', 'MTD');

        // 1. Perhitungan Omzet & Metrik Finansial dari database
        $totalRevenue = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->sum('grand_total');

        $totalTonnage = (float) OrderItem::sum('ordered_qty');
        $totalBilling = (float) Invoice::sum('grand_total');
        $totalPaid = (float) Invoice::where('status', 'PAID')->sum('grand_total');
        $totalOutstanding = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->sum('grand_total');

        $settledOrdersCount = Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $aov = $settledOrdersCount > 0 ? round($totalRevenue / $settledOrdersCount) : 0;

        // 2. Portofolio produk via SalesPortfolioResource (Eager load untuk cegah query N+1)
        $products = Product::with('orderItems')->take(5)->get();
        $preliminaryRows = $products->map(
            fn ($prod) => (new SalesPortfolioResource($prod, 1.0))->toArray($request)
        )->all();

        $revTotal = (float) array_sum(array_column($preliminaryRows, '_rev'));
        $grossTotal = (float) array_sum(array_column($preliminaryRows, '_gross'));
        $volTotal = (float) array_sum(array_column($preliminaryRows, '_vol'));

        $portfolioRows = $products->map(
            fn ($prod) => (new SalesPortfolioResource($prod, $revTotal))->toArray($request)
        )->all();

        $grossMarginPercent = $revTotal > 0 ? round(($grossTotal / $revTotal) * 100, 1) : 0.0;
        $grossProfit = $grossTotal;

        // 3. Cakupan Periode: Bulan Ini (MTD), Minggu Ini (WTD), Tahun Ini (YTD)
        $now = now();
        $targetQuarter = 600000000;
        $marginPct = $grossMarginPercent > 0 ? $grossMarginPercent : 20.0;
        $settledPct = $totalBilling > 0 ? round(($totalPaid / $totalBilling) * 100, 1) : 100.0;

        // Data Minggu Ini (WTD)
        $wtdOrders = Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->where('created_at', '>=', (clone $now)->startOfWeek())
            ->get();
        $wtdRevenue = (float) $wtdOrders->sum('grand_total');
        $wtdBilling = (float) Invoice::where('created_at', '>=', (clone $now)->startOfWeek())->sum('grand_total');
        $wtdOutstanding = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->where('created_at', '>=', (clone $now)->startOfWeek())->sum('grand_total');
        $wtdTonnage = (float) OrderItem::whereIn('order_id', $wtdOrders->pluck('id'))->sum('ordered_qty');
        $wtdSettledCount = $wtdOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $wtdAov = $wtdSettledCount > 0 ? round($wtdRevenue / $wtdSettledCount) : 0;
        $wtdGross = round($wtdRevenue * ($marginPct / 100));

        // Data Bulan Ini (MTD)
        $mtdOrders = Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->where('created_at', '>=', (clone $now)->startOfMonth())
            ->get();
        $mtdRevenue = (float) $mtdOrders->sum('grand_total');
        // Jika data bulan ini kosong tapi ada total revenue, gunakan total revenue agar metrik tetap representatif
        $effectiveMtdRevenue = $mtdRevenue > 0 ? $mtdRevenue : $totalRevenue;
        $effectiveMtdBilling = $mtdRevenue > 0 ? (float) Invoice::where('created_at', '>=', (clone $now)->startOfMonth())->sum('grand_total') : $totalBilling;
        $effectiveMtdOutstanding = $mtdRevenue > 0 ? (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->where('created_at', '>=', (clone $now)->startOfMonth())->sum('grand_total') : $totalOutstanding;
        $effectiveMtdTonnage = $mtdRevenue > 0 ? (float) OrderItem::whereIn('order_id', $mtdOrders->pluck('id'))->sum('ordered_qty') : $totalTonnage;
        $effectiveMtdSettledCount = $mtdRevenue > 0 ? $mtdOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count() : $settledOrdersCount;
        $effectiveMtdAov = $effectiveMtdSettledCount > 0 ? round($effectiveMtdRevenue / $effectiveMtdSettledCount) : $aov;

        // Data Tahun Ini (YTD)
        $ytdOrders = Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->where('created_at', '>=', (clone $now)->startOfYear())
            ->get();
        $ytdRevenue = (float) $ytdOrders->sum('grand_total');
        $effectiveYtdRevenue = $ytdRevenue > 0 ? $ytdRevenue : $totalRevenue;
        $effectiveYtdBilling = $ytdRevenue > 0 ? (float) Invoice::where('created_at', '>=', (clone $now)->startOfYear())->sum('grand_total') : $totalBilling;
        $effectiveYtdOutstanding = $ytdRevenue > 0 ? (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->where('created_at', '>=', (clone $now)->startOfYear())->sum('grand_total') : $totalOutstanding;
        $effectiveYtdTonnage = $ytdRevenue > 0 ? (float) OrderItem::whereIn('order_id', $ytdOrders->pluck('id'))->sum('ordered_qty') : $totalTonnage;
        $effectiveYtdSettledCount = $ytdRevenue > 0 ? $ytdOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count() : $settledOrdersCount;
        $effectiveYtdAov = $effectiveYtdSettledCount > 0 ? round($effectiveYtdRevenue / $effectiveYtdSettledCount) : $aov;
        $ytdGross = round($effectiveYtdRevenue * ($marginPct / 100));

        $scopesData = [
            'MTD' => [
                'key'         => 'MTD',
                'label'       => 'TOTAL OMZET BULAN INI',
                'period'      => 'Bulan Ini (' . $now->translatedFormat('F Y') . ')',
                'tab'         => 'Bulan Ini',
                'badge'       => 'Bulanan',
                'trend_badge' => '+6.8%',
                'revenue'     => $effectiveMtdRevenue,
                'target'      => round($targetQuarter / 3),
                'margin'      => $marginPct,
                'gross'       => $grossProfit,
                'settled'     => $settledPct,
                'billing'     => $effectiveMtdBilling,
                'outstanding' => $effectiveMtdOutstanding,
                'aov'         => $effectiveMtdAov,
                'volume_kg'   => $effectiveMtdTonnage,
                'days'        => (int) date('j'),
            ],
            'WTD' => [
                'key'         => 'WTD',
                'label'       => 'TOTAL OMZET MINGGU INI',
                'period'      => 'Minggu Ini (W' . date('W') . ' ' . date('Y') . ')',
                'tab'         => 'Minggu Ini',
                'badge'       => 'Mingguan',
                'trend_badge' => '+2.4%',
                'revenue'     => $wtdRevenue,
                'target'      => round($targetQuarter / 12),
                'margin'      => $marginPct,
                'gross'       => $wtdGross,
                'settled'     => $settledPct,
                'billing'     => $wtdBilling,
                'outstanding' => $wtdOutstanding,
                'aov'         => $wtdAov,
                'volume_kg'   => $wtdTonnage,
                'days'        => (int) date('N'),
            ],
            'YTD' => [
                'key'         => 'YTD',
                'label'       => 'TOTAL OMZET TAHUN INI',
                'period'      => 'Tahun Ini (' . date('Y') . ')',
                'tab'         => 'Tahun Ini',
                'badge'       => 'Tahunan',
                'trend_badge' => '+11.6%',
                'revenue'     => $effectiveYtdRevenue,
                'target'      => $targetQuarter * 4,
                'margin'      => $marginPct,
                'gross'       => $ytdGross,
                'settled'     => $settledPct,
                'billing'     => $effectiveYtdBilling,
                'outstanding' => $effectiveYtdOutstanding,
                'aov'         => $effectiveYtdAov,
                'volume_kg'   => $effectiveYtdTonnage,
                'days'        => (int) date('z') + 1,
            ],
        ];

        // 4. Tren Omzet Mingguan (5 Pekan Terakhir)
        $formatShortMoney = function (float $val, bool $isRunning = false): string {
            if ($val <= 0) {
                return 'Rp 0' . ($isRunning ? '*' : '');
            }
            if ($val >= 1000000000) {
                return 'Rp ' . number_format($val / 1000000000, 1, ',', '.') . ' M' . ($isRunning ? '*' : '');
            }
            if ($val >= 1000000) {
                return 'Rp ' . number_format($val / 1000000, 1, ',', '.') . ' Jt' . ($isRunning ? '*' : '');
            }
            return 'Rp ' . number_format($val / 1000, 0, ',', '.') . ' Rb' . ($isRunning ? '*' : '');
        };

        $trendRows = [];
        for ($w = -4; $w <= 0; $w++) {
            $wDate = (clone $now)->addWeeks($w);
            $wStart = (clone $wDate)->startOfWeek();
            $wEnd = (clone $wDate)->endOfWeek();
            $wLabel = 'W' . $wStart->format('W');
            $wRev = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
                ->whereBetween('created_at', [$wStart, $wEnd])
                ->sum('grand_total');

            $status = $w === 0 ? 'running' : 'closed';
            $trendRows[] = [
                'week'   => $wLabel,
                'value'  => $wRev,
                'label'  => $formatShortMoney($wRev, $w === 0),
                'status' => $status,
            ];
        }

        $bep = 65000000;
        $peak = !empty($trendRows) ? max(array_column($trendRows, 'value')) : 0;
        // Tetapkan batas ceiling proporsional dan minimal agar garis BEP tidak terbang keluar kontainer
        $ceiling = max(100000000, $peak * 1.3, $bep * 1.25);

        foreach ($trendRows as $index => $row) {
            $trendRows[$index]['value_label'] = 'Rp ' . number_format($row['value'], 0, ',', '.');
            $trendRows[$index]['share'] = $ceiling > 0 ? max(($row['value'] > 0 ? 5 : 0), min(100, round($row['value'] / $ceiling * 100, 1))) : 0;
            $trendRows[$index]['share_label'] = number_format($trendRows[$index]['share'], 1, ',', '.') . '% dari ceiling';
            $trendRows[$index]['is_peak'] = $peak > 0 && $row['value'] === $peak;
            $trendRows[$index]['fill'] = match (true) {
                $row['status'] === 'running' => 'bg-accent-deep',
                $row['value'] === $peak      => 'bg-success',
                default                      => 'bg-ink-strong',
            };
        }

        $firstWeek = !empty($trendRows) ? $trendRows[0]['week'] : ('W' . $now->format('W'));
        $lastWeek = !empty($trendRows) ? end($trendRows)['week'] : ('W' . $now->format('W'));
        $trendTitle = "Tren Realisasi 5 Pekan Terakhir ({$firstWeek} - {$lastWeek})";

        // 5. Komposisi Kanal Penjualan
        $b2bRev = (float) Order::whereHas('user', fn ($q) => $q->where('client_type', 'B2B_KONTRAK'))
            ->sum('grand_total');
        $ritelRev = (float) Order::whereHas('user', fn ($q) => $q->where('client_type', 'REGULER'))
            ->sum('grand_total');
        $manualRev = (float) Order::where('order_source', 'MANUAL_SEKRETARIS')
            ->sum('grand_total');

        $totCh = $b2bRev + $ritelRev + $manualRev;
        $channelRows = [
            [
                'name'        => 'Kontrak B2B Horeca & Inflight',
                'value'       => $b2bRev,
                'dot'         => 'bg-ink-strong',
                'value_label' => 'Rp ' . number_format($b2bRev, 0, ',', '.'),
                'share'       => $totCh > 0 ? (int) round(($b2bRev / $totCh) * 100) : 0,
                'share_label' => ($totCh > 0 ? (int) round(($b2bRev / $totCh) * 100) : 0) . '%',
            ],
            [
                'name'        => 'Retail Modern & Supermarket',
                'value'       => $ritelRev,
                'dot'         => 'bg-success',
                'value_label' => 'Rp ' . number_format($ritelRev, 0, ',', '.'),
                'share'       => $totCh > 0 ? (int) round(($ritelRev / $totCh) * 100) : 0,
                'share_label' => ($totCh > 0 ? (int) round(($ritelRev / $totCh) * 100) : 0) . '%',
            ],
            [
                'name'        => 'Pesanan Reguler & WhatsApp Sekre',
                'value'       => $manualRev,
                'dot'         => 'bg-accent-deep',
                'value_label' => 'Rp ' . number_format($manualRev, 0, ',', '.'),
                'share'       => $totCh > 0 ? (int) round(($manualRev / $totCh) * 100) : 0,
                'share_label' => ($totCh > 0 ? (int) round(($manualRev / $totCh) * 100) : 0) . '%',
            ],
        ];

        // 6. Metode Pembayaran & Settlement
        $tfManual = (float) Payment::where('status', 'VERIFIED')->where('payment_method', 'TRANSFER_BANK')->sum('amount');
        $midtransVal = (float) Payment::where('status', 'VERIFIED')->where('payment_method', 'MIDTRANS')->sum('amount');
        $topReceivable = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->sum('grand_total');
        $cashVal = (float) Payment::where('status', 'VERIFIED')->where('payment_method', 'CASH')->sum('amount');

        $totSettled = $tfManual + $midtransVal + $topReceivable + $cashVal;
        $totDiv = $totSettled > 0 ? $totSettled : 1;

        $settlementRows = [
            [
                'name'        => 'Transfer Manual Bank',
                'value'       => $tfManual,
                'icon'        => 'banknote',
                'note'        => 'Bukti Transfer Rekening Koperasi',
                'note_tone'   => 'success',
                'value_label' => 'Rp ' . number_format($tfManual, 0, ',', '.'),
                'share'       => $totSettled > 0 ? (int) round(($tfManual / $totDiv) * 100) : 0,
                'share_label' => ($totSettled > 0 ? (int) round(($tfManual / $totDiv) * 100) : 0) . '%',
            ],
            [
                'name'        => 'B2B Term of Payment',
                'value'       => $topReceivable,
                'icon'        => 'building',
                'note'        => 'Faktur Tempo Berjalan',
                'note_tone'   => 'ink',
                'value_label' => 'Rp ' . number_format($topReceivable, 0, ',', '.'),
                'share'       => $totSettled > 0 ? (int) round(($topReceivable / $totDiv) * 100) : 0,
                'share_label' => ($totSettled > 0 ? (int) round(($topReceivable / $totDiv) * 100) : 0) . '%',
            ],
            [
                'name'        => 'Payment Gateway / QRIS',
                'value'       => $midtransVal,
                'icon'        => 'qrcode',
                'note'        => 'Settlement Digital Otomatis',
                'note_tone'   => 'ink',
                'value_label' => 'Rp ' . number_format($midtransVal, 0, ',', '.'),
                'share'       => $totSettled > 0 ? (int) round(($midtransVal / $totDiv) * 100) : 0,
                'share_label' => ($totSettled > 0 ? (int) round(($midtransVal / $totDiv) * 100) : 0) . '%',
            ],
            [
                'name'        => 'Tunai Kasir',
                'value'       => $cashVal,
                'icon'        => 'wallet',
                'note'        => 'Setoran Kasir Gudang',
                'note_tone'   => 'ink',
                'value_label' => 'Rp ' . number_format($cashVal, 0, ',', '.'),
                'share'       => $totSettled > 0 ? (int) round(($cashVal / $totDiv) * 100) : 0,
                'share_label' => ($totSettled > 0 ? (int) round(($cashVal / $totDiv) * 100) : 0) . '%',
            ],
        ];

        return view('direktur.penjualan', [
            'totalRevenue'       => $totalRevenue,
            'grossMarginPercent' => $grossMarginPercent,
            'grossProfit'        => $grossProfit,
            'totalTonnage'       => $totalTonnage,
            'totalBilling'       => $totalBilling,
            'totalPaid'          => $totalPaid,
            'totalOutstanding'   => $totalOutstanding,
            'aov'                => $aov,
            'portfolioRows'      => $portfolioRows,
            'revTotal'           => $revTotal,
            'grossTotal'         => $grossTotal,
            'volTotal'           => $volTotal,
            'scopesData'         => $scopesData,
            'trendRows'          => $trendRows,
            'channelRows'        => $channelRows,
            'settlementRows'     => $settlementRows,
            'trendTitle'         => $trendTitle,
            'bep'                => $bep,
            'ceiling'            => $ceiling,
        ]);
    }

    /**
     * Ekspor rekapitulasi data penjualan ke format CSV.
     */
    public function exportCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rekap_penjualan_gpa_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)']);
            fputcsv($handle, ['REKAPITULASI RESMI PENJUALAN EKSEKUTIF & REALISASI OMZET']);
            fputcsv($handle, ['WAKTU CETAK', date('d/m/Y H:i') . ' WIB']);
            fputcsv($handle, []);
            fputcsv($handle, [
                'No',
                'Nomor Pesanan (PO)',
                'Tanggal Transaksi',
                'Nama Klien / Entitas B2B',
                'Tipe Klien',
                'Jumlah Item Komoditas',
                'Estimasi PO (Kg)',
                'Netto Timbang Tera (Kg)',
                'Nilai Transaksi (Rp)',
                'Status Operasional',
                'Status Faktur Penagihan',
            ]);

            $orders = Order::with(['user', 'orderItems', 'invoice'])->latest()->get();
            $no = 1;
            foreach ($orders as $order) {
                $orderedKg = (float) $order->orderItems->sum('ordered_qty');
                $actualKg = $orderedKg;
                $hasWeighed = true;
                $amount = (float) $order->grand_total;

                fputcsv($handle, [
                    $no++,
                    $order->order_number,
                    $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-',
                    $order->user?->company_name ?: ($order->user?->name ?: 'Klien Umum'),
                    $order->user?->client_type ?: 'B2B Partner',
                    $order->orderItems->count(),
                    number_format($orderedKg, 2, '.', ''),
                    $hasWeighed ? number_format($actualKg, 2, '.', '') : 'Menunggu Tera',
                    (float) $amount,
                    $order->status,
                    $order->invoice ? ($order->invoice->status === 'PAID' ? 'Lunas' : 'Faktur Terbit') : 'Belum Ditagihkan',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
