<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Menampilkan konsol rekapitulasi laporan operasional, pengiriman & penjualan.
     */
    public function index(Request $request): View
    {
        // Ambil data pesanan dari database
        $orders = Order::with(['user', 'orderItems.product', 'invoice'])->latest()->get();

        $journalRows = [];
        $totalEstimate = 0;
        $totalNet = 0;
        $totalValue = 0;

        $completedOrdersCount = $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $activeOrdersCount = $orders->whereNotIn('status', ['BATAL'])->count();
        $deliveryAccuracy = $activeOrdersCount > 0 ? round(($completedOrdersCount / $activeOrdersCount) * 100, 1) : 100.0;

        foreach ($orders->values() as $idx => $ord) {
            $clientName = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien');
            $address = $ord->delivery_address ?: 'Gudang Pembeli';
            $firstItem = $ord->orderItems->first();
            $commodityName = $firstItem?->product?->name ?: 'Sayur Segar';
            $unitPrice = $firstItem ? (float) $firstItem->unit_price : 0;

            $est = (float) $ord->orderItems->sum('ordered_qty');
            $net = $est;

            $val = (float) ($ord->grand_total ?: ($net * $unitPrice));
            $diffKg = $net - $est;
            $diffPct = $est > 0 ? round(($diffKg / $est) * 100, 2) : 0;

            $totalEstimate += $est;
            $totalNet += $net;
            $totalValue += $val;

            $isPaid = $ord->status === 'SELESAI' || ($ord->invoice && $ord->invoice->status === 'PAID');

            $journalRows[] = [
                'no'              => str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                'invoice'         => $ord->order_number,
                'sj'              => $ord->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad($ord->id, 4, '0', STR_PAD_LEFT)),
                'client'          => $clientName,
                'location'        => $address,
                'commodity'       => $commodityName,
                'estimate'        => $est,
                'estimate_label'  => number_format($est, 0, ',', '.') . ' kg',
                'net'             => $net,
                'net_label'       => number_format($net, 1, ',', '.') . ' kg',
                'unit_price'      => $unitPrice,
                'value'           => $val,
                'value_label'     => 'Rp ' . number_format($val, 0, ',', '.'),
                'deviation_value' => $diffPct,
                'deviation_label' => ($diffPct >= 0 ? '+' : '') . number_format($diffPct, 2, '.', '') . '%',
                'deviation_class' => $diffPct < -1.0 ? 'text-warning-caution' : 'text-success-deep',
                'blocked'         => false,
                'status'          => $isPaid ? 'lunas' : 'top30',
                'status_label'    => $isPaid ? 'Lunas' : 'Tempo 30',
                'status_class'    => $isPaid ? 'bg-accent text-success-deep outline outline-1 outline-success-deep' : 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                'audit_files'     => 3,
            ];
        }

        $overallDeviation = $totalEstimate > 0 ? round((($totalNet - $totalEstimate) / $totalEstimate) * 100, 2) : 0;

        return view('sekretaris.report', [
            'journalRows'      => $journalRows,
            'totalEstimate'    => $totalEstimate,
            'totalNet'         => $totalNet,
            'totalValue'       => $totalValue,
            'overallDeviation' => $overallDeviation,
            'deliveryAccuracy' => $deliveryAccuracy,
        ]);
    }

    /**
     * Unduh Rekap Laporan Operasional (.CSV)
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Rekap_Laporan_Operasional_GPA_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)']);
            fputcsv($handle, ['JURNAL REKAPITULASI OPERASIONAL, PENIMBANGAN & PENJUALAN']);
            fputcsv($handle, ['WAKTU CETAK', date('d/m/Y H:i') . ' WIB']);
            fputcsv($handle, []);
            fputcsv($handle, [
                'No',
                'No. PO / Invoice',
                'Surat Jalan',
                'Entitas Klien / Pembeli',
                'Lokasi / Alamat Kirim',
                'Komoditas Sayur',
                'Estimasi PO (Kg)',
                'Netto Tera (Kg)',
                'Deviasi Bobot (Kg)',
                'Deviasi Bobot (%)',
                'Harga Satuan (Rp)',
                'Total Nilai (Rp)',
                'Status Pembayaran',
            ]);

            $orders = Order::with(['user', 'orderItems.product', 'invoice'])->latest()->get();
            $no = 1;
            foreach ($orders as $ord) {
                $clientName = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien');
                $address = $ord->delivery_address ?: 'Gudang Pembeli';
                $firstItem = $ord->orderItems->first();
                $commodityName = $firstItem?->product?->name ?: 'Sayur Segar';
                $unitPrice = $firstItem ? (float) $firstItem->unit_price : 0;

                $est = (float) $ord->orderItems->sum('ordered_qty');
                $net = $est;
                $val = (float) ($ord->grand_total ?: ($net * $unitPrice));
                $diffKg = $net - $est;
                $diffPct = $est > 0 ? round(($diffKg / $est) * 100, 2) : 0;
                $isPaid = $ord->status === 'SELESAI' || ($ord->invoice && $ord->invoice->status === 'PAID');

                fputcsv($handle, [
                    $no++,
                    $ord->order_number,
                    $ord->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad($ord->id, 4, '0', STR_PAD_LEFT)),
                    $clientName,
                    $address,
                    $commodityName,
                    $est,
                    $net,
                    $diffKg,
                    ($diffPct >= 0 ? '+' : '') . $diffPct . '%',
                    $unitPrice,
                    $val,
                    $isPaid ? 'Lunas' : 'Tempo 30',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Tampilan Siap Cetak Dokumen Rekap Resmi Sekretaris (.PDF)
     */
    public function printReport(Request $request): View
    {
        $orders = Order::with(['user', 'orderItems.product', 'invoice'])->latest()->get();

        $journalRows = [];
        $totalEstimate = 0;
        $totalNet = 0;
        $totalValue = 0;

        foreach ($orders->values() as $idx => $ord) {
            $clientName = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien');
            $firstItem = $ord->orderItems->first();
            $commodityName = $firstItem?->product?->name ?: 'Sayur Segar';
            $unitPrice = $firstItem ? (float) $firstItem->unit_price : 0;

            $est = (float) $ord->orderItems->sum('ordered_qty');
            $net = $est;
            $val = (float) ($ord->grand_total ?: ($net * $unitPrice));
            $diffKg = $net - $est;
            $diffPct = $est > 0 ? round(($diffKg / $est) * 100, 2) : 0;

            $totalEstimate += $est;
            $totalNet += $net;
            $totalValue += $val;

            $isPaid = $ord->status === 'SELESAI' || ($ord->invoice && $ord->invoice->status === 'PAID');

            $journalRows[] = [
                'no'              => str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                'invoice'         => $ord->order_number,
                'sj'              => $ord->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad($ord->id, 4, '0', STR_PAD_LEFT)),
                'client'          => $clientName,
                'commodity'       => $commodityName,
                'estimate'        => $est,
                'net'             => $net,
                'value'           => $val,
                'deviation_label' => ($diffPct >= 0 ? '+' : '') . number_format($diffPct, 2, '.', '') . '%',
                'status_label'    => $isPaid ? 'Lunas' : 'Tempo 30',
            ];
        }

        $overallDeviation = $totalEstimate > 0 ? round((($totalNet - $totalEstimate) / $totalEstimate) * 100, 2) : 0;
        $operatorName = Auth::user()?->name ?: 'Sekretaris Operasional';

        return view('sekretaris.reports.print', [
            'journalRows'      => $journalRows,
            'totalEstimate'    => $totalEstimate,
            'totalNet'         => $totalNet,
            'totalValue'       => $totalValue,
            'overallDeviation' => $overallDeviation,
            'operatorName'     => $operatorName,
        ]);
    }
}
