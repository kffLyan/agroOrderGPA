<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sekretaris\OperationalReportResource;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Jurnal Operasional & Rekapitulasi Penjualan Sekretaris.
 *
 * Penjelasan UKK:
 * Modul ini menyajikan rekapitulasi harian operasional Koperasi GPA:
 * 1. Perbandingan bobot estimasi PO vs netto hasil timbang tera.
 * 2. Akurasi pengiriman dan deviasi susut sortir.
 * 3. Ekspor jurnal harian ke CSV dan cetak PDF rekapitulasi resmi.
 */
class LaporanController extends Controller
{
    /**
     * Menampilkan konsol rekapitulasi laporan operasional, pengiriman & penjualan.
     */
    public function index(Request $request): View
    {
        $orders = Order::with(['user', 'orderItems.product', 'invoice'])->latest()->get();

        $journalRows = $orders->values()->map(
            fn (Order $ord, int $idx) => (new OperationalReportResource($ord, $idx))->resolve()
        )->all();

        $totalEstimate = (float) array_sum(array_column($journalRows, 'estimate'));
        $totalNet      = (float) array_sum(array_column($journalRows, 'net'));
        $totalValue    = (float) array_sum(array_column($journalRows, 'value'));

        $completedOrdersCount = $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $activeOrdersCount    = $orders->whereNotIn('status', ['BATAL'])->count();
        $deliveryAccuracy     = $activeOrdersCount > 0 ? round(($completedOrdersCount / $activeOrdersCount) * 100, 1) : 100.0;
        $overallDeviation     = $totalEstimate > 0 ? round((($totalNet - $totalEstimate) / $totalEstimate) * 100, 2) : 0;

        return view('sekretaris.laporan', [
            'journalRows'      => $journalRows,
            'totalEstimate'    => $totalEstimate,
            'totalNet'         => $totalNet,
            'totalValue'       => $totalValue,
            'overallDeviation' => $overallDeviation,
            'deliveryAccuracy' => $deliveryAccuracy,
        ]);
    }

    /**
     * Unduh Rekap Laporan Operasional (.CSV).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
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
                $clientName    = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien');
                $address       = $ord->delivery_address ?: 'Gudang Pembeli';
                $firstItem     = $ord->orderItems->first();
                $commodityName = $firstItem?->product?->name ?: 'Sayur Segar';
                $unitPrice     = $firstItem ? (float) $firstItem->unit_price : 0;

                $est     = (float) $ord->orderItems->sum('ordered_qty');
                $net     = $est;
                $val     = (float) ($ord->grand_total ?: ($net * $unitPrice));
                $diffKg  = $net - $est;
                $diffPct = $est > 0 ? round(($diffKg / $est) * 100, 2) : 0;
                $isPaid  = $ord->status === 'SELESAI' || ($ord->invoice && $ord->invoice->status === 'PAID');

                fputcsv($handle, [
                    $no++,
                    $ord->order_number,
                    $ord->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad((string) $ord->id, 4, '0', STR_PAD_LEFT)),
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
     * Tampilan Siap Cetak Dokumen Rekap Resmi Sekretaris (.PDF).
     */
    public function printReport(Request $request): View
    {
        $orders = Order::with(['user', 'orderItems.product', 'invoice'])->latest()->get();

        $journalRows = $orders->values()->map(
            fn (Order $ord, int $idx) => (new OperationalReportResource($ord, $idx))->resolve()
        )->all();

        $totalEstimate    = (float) array_sum(array_column($journalRows, 'estimate'));
        $totalNet         = (float) array_sum(array_column($journalRows, 'net'));
        $totalValue       = (float) array_sum(array_column($journalRows, 'value'));
        $overallDeviation = $totalEstimate > 0 ? round((($totalNet - $totalEstimate) / $totalEstimate) * 100, 2) : 0;
        $operatorName     = Auth::user()?->name ?: 'Sekretaris Operasional';

        return view('prints.secretary-report', [
            'journalRows'      => $journalRows,
            'totalEstimate'    => $totalEstimate,
            'totalNet'         => $totalNet,
            'totalValue'       => $totalValue,
            'overallDeviation' => $overallDeviation,
            'operatorName'     => $operatorName,
        ]);
    }
}
