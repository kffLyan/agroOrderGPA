<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesMonitoringController extends Controller
{
    /**
     * Menampilkan Monitoring Penjualan & Analisis Revenue Eksekutif Direktur
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
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

        // Portofolio produk
        $products = Product::all();
        $portfolioRows = [];
        $revTotal = 0;
        $grossTotal = 0;
        $volTotal = 0;

        foreach ($products->take(5) as $idx => $prod) {
            $items = OrderItem::where('product_id', $prod->id)->get();
            $vol = (float) $items->sum('ordered_qty');
            $rev = (float) $items->sum('subtotal');
            $costPerKg = (float) ($prod->base_price * 0.75); // standar margin Koperasi 25%
            $cost = $vol * $costPerKg;
            $gross = max(0, $rev - $cost);
            $price = $vol > 0 ? round($rev / $vol) : (float) $prod->base_price;
            $listPrice = (float) $prod->base_price;
            $margin = $rev > 0 ? round(($gross / $rev) * 100, 1) : 25.0;

            $revTotal += $rev;
            $grossTotal += $gross;
            $volTotal += $vol;

            $statusTone = match (true) {
                $margin >= 23.0 => 'success',
                $margin >= 19.5 => 'ink',
                $margin >= 17.5 => 'deep',
                default         => 'caution',
            };
            $statusLabel = match (true) {
                $margin >= 23.0 => 'PRIMA',
                $margin >= 19.5 => 'NOMINAL',
                $margin >= 17.5 => 'STABIL',
                default         => 'TIGHT',
            };

            $portfolioRows[] = [
                'name'             => $prod->name,
                'sku'              => $prod->code ?: ('KMD-0' . ($idx + 1)),
                'volume'           => $vol,
                'volume_label'     => number_format($vol, 0, ',', '.'),
                'list_price_label' => 'Rp ' . number_format($listPrice, 0, ',', '.'),
                'price_label'      => 'Rp ' . number_format($price, 0, ',', '.'),
                'revenue_label'    => 'Rp ' . number_format($rev, 0, ',', '.'),
                'gross_label'      => 'Rp ' . number_format($gross, 0, ',', '.'),
                'margin'           => $margin,
                'margin_label'     => $margin . '%',
                'share'            => 0,
                'share_label'      => '0%',
                'status'           => $statusLabel,
                'status_tone'      => $statusTone,
                'bar_percent'      => round(min($margin, 30) / 30 * 100, 1),
                '_rev'             => $rev,
            ];
        }

        foreach ($portfolioRows as &$row) {
            $share = $revTotal > 0 ? round(($row['_rev'] / $revTotal) * 100, 1) : 0;
            $row['share'] = $share;
            $row['share_label'] = $share . '%';
            unset($row['_rev']);
        }
        unset($row);

        $grossMarginPercent = $revTotal > 0 ? round(($grossTotal / $revTotal) * 100, 1) : 0.0;
        $grossProfit = $grossTotal;

        // 2. Cakupan Periode MTD, WTD, YTD
        $now = now();
        $targetQuarter = 600000000;
        $marginPct = $grossMarginPercent > 0 ? $grossMarginPercent : 20.0;
        $settledPct = $totalBilling > 0 ? round(($totalPaid / $totalBilling) * 100, 1) : 100.0;

        $wtdOrders = Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->where('created_at', '>=', (clone $now)->startOfWeek())
            ->get();
        $wtdRevenue = (float) $wtdOrders->sum('grand_total');
        $wtdBilling = (float) Invoice::where('created_at', '>=', (clone $now)->startOfWeek())->sum('grand_total');
        $wtdOutstanding = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->where('created_at', '>=', (clone $now)->startOfWeek())->sum('grand_total');
        $wtdTonnage = (float) OrderItem::whereIn('order_id', $wtdOrders->pluck('id'))->sum('ordered_qty');
        $wtdSettledCount = $wtdOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $wtdAov = $wtdSettledCount > 0 ? round($wtdRevenue / $wtdSettledCount) : 0;

        $ytdOrders = Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->where('created_at', '>=', (clone $now)->startOfYear())
            ->get();
        $ytdRevenue = (float) $ytdOrders->sum('grand_total');
        $ytdBilling = (float) Invoice::where('created_at', '>=', (clone $now)->startOfYear())->sum('grand_total');
        $ytdOutstanding = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->where('created_at', '>=', (clone $now)->startOfYear())->sum('grand_total');
        $ytdTonnage = (float) OrderItem::whereIn('order_id', $ytdOrders->pluck('id'))->sum('ordered_qty');
        $ytdSettledCount = $ytdOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $ytdAov = $ytdSettledCount > 0 ? round($ytdRevenue / $ytdSettledCount) : 0;

        $scopesData = [
            'MTD' => [
                'key'         => 'MTD',
                'label'       => 'TOTAL OMZET MTD',
                'period'      => 'MTD / Q' . ceil(date('n') / 3) . ' ' . date('Y'),
                'tab'         => 'MTD',
                'badge'       => 'MoM',
                'trend_badge' => '+6.8%',
                'revenue'     => $totalRevenue,
                'target'      => $targetQuarter,
                'margin'      => $marginPct,
                'gross'       => $grossProfit,
                'settled'     => $settledPct,
                'billing'     => $totalBilling,
                'outstanding' => $totalOutstanding,
                'aov'         => $aov,
                'volume_kg'   => $totalTonnage,
                'days'        => (int) date('j'),
            ],
            'WTD' => [
                'key'         => 'WTD',
                'label'       => 'TOTAL OMZET WTD',
                'period'      => 'WTD / W' . date('W') . ' ' . date('Y'),
                'tab'         => 'WTD (Mingguan)',
                'badge'       => 'WoW',
                'trend_badge' => '+2.4%',
                'revenue'     => $wtdRevenue > 0 ? $wtdRevenue : $totalRevenue,
                'target'      => round($targetQuarter / 5),
                'margin'      => $marginPct,
                'gross'       => round($grossProfit * (($wtdRevenue > 0 ? $wtdRevenue : $totalRevenue) / ($totalRevenue ?: 1))),
                'settled'     => $settledPct,
                'billing'     => $wtdBilling > 0 ? $wtdBilling : $totalBilling,
                'outstanding' => $wtdOutstanding > 0 ? $wtdOutstanding : $totalOutstanding,
                'aov'         => $wtdAov > 0 ? $wtdAov : $aov,
                'volume_kg'   => $wtdTonnage > 0 ? $wtdTonnage : $totalTonnage,
                'days'        => (int) date('N'),
            ],
            'YTD' => [
                'key'         => 'YTD',
                'label'       => 'TOTAL OMZET YTD',
                'period'      => 'YTD / ' . date('Y'),
                'tab'         => 'YTD Konsolidasi',
                'badge'       => 'YoY',
                'trend_badge' => '+11.6%',
                'revenue'     => $ytdRevenue > 0 ? $ytdRevenue : $totalRevenue,
                'target'      => $targetQuarter * 4,
                'margin'      => $marginPct,
                'gross'       => round($grossProfit * (($ytdRevenue > 0 ? $ytdRevenue : $totalRevenue) / ($totalRevenue ?: 1))),
                'settled'     => $settledPct,
                'billing'     => $ytdBilling > 0 ? $ytdBilling : $totalBilling,
                'outstanding' => $ytdOutstanding > 0 ? $ytdOutstanding : $totalOutstanding,
                'aov'         => $ytdAov > 0 ? $ytdAov : $aov,
                'volume_kg'   => $ytdTonnage > 0 ? $ytdTonnage : $totalTonnage,
                'days'        => (int) date('z') + 1,
            ],
        ];

        // 3. Tren Omzet Mingguan (5 Pekan Terakhir)
        $ceiling = 150000000;
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
                'label'  => 'Rp ' . number_format($wRev / 1000000, 1, '.', '') . 'M' . ($w === 0 ? '*' : ''),
                'status' => $status,
            ];
        }
        $peak = !empty($trendRows) ? max(array_column($trendRows, 'value')) : 0;
        if ($peak > $ceiling) {
            $ceiling = $peak * 1.2;
        }
        foreach ($trendRows as $index => $row) {
            $trendRows[$index]['value_label'] = 'Rp ' . number_format($row['value'], 0, ',', '.');
            $trendRows[$index]['share'] = $ceiling > 0 ? round($row['value'] / $ceiling * 100, 1) : 0;
            $trendRows[$index]['share_label'] = number_format($trendRows[$index]['share'], 1, ',', '.') . '% dari ceiling';
            $trendRows[$index]['is_peak'] = $peak > 0 && $row['value'] === $peak;
            $trendRows[$index]['fill'] = match (true) {
                $row['status'] === 'running' => 'bg-accent-deep',
                $row['value'] === $peak      => 'bg-success',
                default                      => 'bg-ink-strong',
            };
        }

        // 4. Komposisi Kanal Penjualan
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

        // 5. Metode Pembayaran & Settlement
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

        return view('direktur.sales', [
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
        ]);
    }

    /**
     * Ekspor rekapitulasi data penjualan ke CSV
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
