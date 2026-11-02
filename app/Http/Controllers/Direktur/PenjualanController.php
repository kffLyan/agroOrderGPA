<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Resources\Direktur\PortofolioPenjualanResource;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller monitoring transaksi penjualan, struktur margin, dan saluran distribusi bagi Direktur Utama
class PenjualanController extends Controller
{
    // Menampilkan dasbor analitik penjualan, pencapaian target MTD/WTD/YTD, dan portofolio komoditas
    public function index(Request $request): View
    {
        $selectedPeriod = $request->query('period', 'MTD');

        // 1. Akumulasi metrik finansial utama (total omzet, tonase penjualan, nilai faktur, dan kas masuk)
        $totalRevenue = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->sum('grand_total');

        $totalTonnage = (float) OrderItem::sum('ordered_qty');
        $totalBilling = (float) Invoice::sum('grand_total');
        $totalPaid = (float) Invoice::where('status', 'PAID')->sum('grand_total');
        $totalOutstanding = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->sum('grand_total');

        $settledOrdersCount = Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $aov = $settledOrdersCount > 0 ? round($totalRevenue / $settledOrdersCount) : 0;

        // 2. Evaluasi portofolio komoditas dan estimasi margin kotor dengan eager loading mencegah query N+1
        $products = Product::with('orderItems')->take(5)->get();
        $preliminaryRows = $products->map(
            fn ($product) => (new PortofolioPenjualanResource($product, 1.0))->toArray($request)
        )->all();

        $revTotal = (float) array_sum(array_column($preliminaryRows, '_rev'));
        $grossTotal = (float) array_sum(array_column($preliminaryRows, '_gross'));
        $volTotal = (float) array_sum(array_column($preliminaryRows, '_vol'));

        $portfolioRows = $products->map(
            fn ($product) => (new PortofolioPenjualanResource($product, $revTotal))->toArray($request)
        )->all();

        $grossMarginPercent = $revTotal > 0 ? round(($grossTotal / $revTotal) * 100, 1) : 0.0;
        $grossProfit = $grossTotal;

        // 3. Klasifikasi performa penjualan menurut horizon waktu (Minggu Berjalan, Bulan Berjalan, dan Tahun Berjalan)
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

        // Format pembulatan nilai mata uang singkat (Ribuan, Jutaan, Miliaran)
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

        // 4. Analisis tren capaian omzet mingguan selama 5 pekan terakhir terhadap titik impas (BEP)
        $trendRows = [];
        for ($weekOffset = -4; $weekOffset <= 0; $weekOffset++) {
            $targetWeekDate = (clone $now)->addWeeks($weekOffset);
            $weekStart = (clone $targetWeekDate)->startOfWeek();
            $weekEnd = (clone $targetWeekDate)->endOfWeek();
            $weekLabel = 'W' . $weekStart->format('W');
            $weeklyRevenue = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('grand_total');

            $status = $weekOffset === 0 ? 'running' : 'closed';
            $trendRows[] = [
                'week'   => $weekLabel,
                'value'  => $weeklyRevenue,
                'label'  => $formatShortMoney($weeklyRevenue, $weekOffset === 0),
                'status' => $status,
            ];
        }

        $bep = 65000000;
        $peak = !empty($trendRows) ? max(array_column($trendRows, 'value')) : 0;
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

        // 5. Pembagian komposisi omzet berdasarkan kanal penjualan (B2B Horeca, Retail Modern, dan WhatsApp)
        $b2bRevenue = (float) Order::whereHas('user', fn ($query) => $query->where('client_type', 'B2B_KONTRAK'))
            ->sum('grand_total');
        $retailRevenue = (float) Order::whereHas('user', fn ($query) => $query->where('client_type', 'REGULER'))
            ->sum('grand_total');
        $manualRevenue = (float) Order::where('order_source', 'MANUAL_SEKRETARIS')
            ->sum('grand_total');

        $totalChannelRevenue = $b2bRevenue + $retailRevenue + $manualRevenue;
        $channelRows = [
            [
                'name'        => 'Kontrak B2B Horeca & Inflight',
                'value'       => $b2bRevenue,
                'dot'         => 'bg-ink-strong',
                'value_label' => 'Rp ' . number_format($b2bRevenue, 0, ',', '.'),
                'share'       => $totalChannelRevenue > 0 ? (int) round(($b2bRevenue / $totalChannelRevenue) * 100) : 0,
                'share_label' => ($totalChannelRevenue > 0 ? (int) round(($b2bRevenue / $totalChannelRevenue) * 100) : 0) . '%',
            ],
            [
                'name'        => 'Retail Modern & Supermarket',
                'value'       => $retailRevenue,
                'dot'         => 'bg-success',
                'value_label' => 'Rp ' . number_format($retailRevenue, 0, ',', '.'),
                'share'       => $totalChannelRevenue > 0 ? (int) round(($retailRevenue / $totalChannelRevenue) * 100) : 0,
                'share_label' => ($totalChannelRevenue > 0 ? (int) round(($retailRevenue / $totalChannelRevenue) * 100) : 0) . '%',
            ],
            [
                'name'        => 'Pesanan Reguler & WhatsApp Sekre',
                'value'       => $manualRevenue,
                'dot'         => 'bg-accent-deep',
                'value_label' => 'Rp ' . number_format($manualRevenue, 0, ',', '.'),
                'share'       => $totalChannelRevenue > 0 ? (int) round(($manualRevenue / $totalChannelRevenue) * 100) : 0,
                'share_label' => ($totalChannelRevenue > 0 ? (int) round(($manualRevenue / $totalChannelRevenue) * 100) : 0) . '%',
            ],
        ];

        // 6. Rekonsiliasi kas berdasarkan metode pembayaran yang digunakan mitra (Transfer, TOP Tempo, QRIS, Kasir)
        $bankTransferRevenue = (float) Payment::where('status', 'VERIFIED')->where('payment_method', 'TRANSFER_BANK')->sum('amount');
        $gatewayRevenue = (float) Payment::where('status', 'VERIFIED')->where('payment_method', 'MIDTRANS')->sum('amount');
        $topReceivableRevenue = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->sum('grand_total');
        $cashRevenue = (float) Payment::where('status', 'VERIFIED')->where('payment_method', 'CASH')->sum('amount');

        $totalSettled = $bankTransferRevenue + $gatewayRevenue + $topReceivableRevenue + $cashRevenue;
        $totalSettledDivisor = $totalSettled > 0 ? $totalSettled : 1;

        $settlementRows = [
            [
                'name'        => 'Transfer Manual Bank',
                'value'       => $bankTransferRevenue,
                'icon'        => 'banknote',
                'note'        => 'Bukti Transfer Rekening Koperasi',
                'note_tone'   => 'success',
                'value_label' => 'Rp ' . number_format($bankTransferRevenue, 0, ',', '.'),
                'share'       => $totalSettled > 0 ? (int) round(($bankTransferRevenue / $totalSettledDivisor) * 100) : 0,
                'share_label' => ($totalSettled > 0 ? (int) round(($bankTransferRevenue / $totalSettledDivisor) * 100) : 0) . '%',
            ],
            [
                'name'        => 'B2B Term of Payment',
                'value'       => $topReceivableRevenue,
                'icon'        => 'building',
                'note'        => 'Faktur Tempo Berjalan',
                'note_tone'   => 'ink',
                'value_label' => 'Rp ' . number_format($topReceivableRevenue, 0, ',', '.'),
                'share'       => $totalSettled > 0 ? (int) round(($topReceivableRevenue / $totalSettledDivisor) * 100) : 0,
                'share_label' => ($totalSettled > 0 ? (int) round(($topReceivableRevenue / $totalSettledDivisor) * 100) : 0) . '%',
            ],
            [
                'name'        => 'Payment Gateway / QRIS',
                'value'       => $gatewayRevenue,
                'icon'        => 'qrcode',
                'note'        => 'Settlement Digital Otomatis',
                'note_tone'   => 'ink',
                'value_label' => 'Rp ' . number_format($gatewayRevenue, 0, ',', '.'),
                'share'       => $totalSettled > 0 ? (int) round(($gatewayRevenue / $totalSettledDivisor) * 100) : 0,
                'share_label' => ($totalSettled > 0 ? (int) round(($gatewayRevenue / $totalSettledDivisor) * 100) : 0) . '%',
            ],
            [
                'name'        => 'Tunai Kasir',
                'value'       => $cashRevenue,
                'icon'        => 'wallet',
                'note'        => 'Setoran Kasir Gudang',
                'note_tone'   => 'ink',
                'value_label' => 'Rp ' . number_format($cashRevenue, 0, ',', '.'),
                'share'       => $totalSettled > 0 ? (int) round(($cashRevenue / $totalSettledDivisor) * 100) : 0,
                'share_label' => ($totalSettled > 0 ? (int) round(($cashRevenue / $totalSettledDivisor) * 100) : 0) . '%',
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

    // Mengunduh rekapitulasi data riwayat penjualan eksekutif dalam format berkas CSV
    public function exportCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rekap_penjualan_gpa_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBFsep=;\r\n");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)'], ';');
            fputcsv($handle, ['REKAPITULASI RESMI PENJUALAN EKSEKUTIF & REALISASI OMZET'], ';');
            fputcsv($handle, ['WAKTU CETAK', date('d/m/Y H:i') . ' WIB'], ';');
            fputcsv($handle, [], ';');
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
            ], ';');

            $orders = Order::with(['user', 'orderItems', 'invoice'])->latest()->get();
            $nomorUrut = 1;
            foreach ($orders as $order) {
                $orderedKg = (float) $order->orderItems->sum('ordered_qty');
                $actualKg = $orderedKg;
                $hasWeighed = true;
                $amount = (float) $order->grand_total;

                $statusLabel = match ($order->status) {
                    'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                    'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
                    'LUNAS'               => 'Lunas',
                    'SIAP_KIRIM'          => 'Siap Kirim',
                    'DALAM_PENGIRIMAN'    => 'Dalam Pengiriman',
                    'SELESAI'             => 'Selesai',
                    'SELESAI_CATATAN'     => 'Selesai (Catatan)',
                    'BATAL'               => 'Dibatalkan',
                    default               => ucwords(strtolower(str_replace('_', ' ', $order->status))),
                };

                $clientTypeLabel = ($order->user?->client_type === 'B2B_KONTRAK') ? 'Mitra Kontrak B2B' : 'Klien Reguler';

                fputcsv($handle, [
                    $nomorUrut++,
                    $order->order_number,
                    $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-',
                    $order->user?->company_name ?: ($order->user?->name ?: 'Klien Umum'),
                    $clientTypeLabel,
                    $order->orderItems->count(),
                    number_format($orderedKg, 2, '.', ''),
                    $hasWeighed ? number_format($actualKg, 2, '.', '') : 'Menunggu Tera',
                    (float) $amount,
                    $statusLabel,
                    $order->invoice ? ($order->invoice->status === 'PAID' ? 'Lunas' : 'Faktur Terbit') : 'Belum Ditagihkan',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
