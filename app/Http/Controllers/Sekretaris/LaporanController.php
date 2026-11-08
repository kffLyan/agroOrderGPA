<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sekretaris\LaporanOperasionalResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller jurnal operasional dan rekapitulasi penjualan untuk Sekretaris
class LaporanController extends Controller
{
    // Menampilkan rekapitulasi jurnal operasional, akurasi penimbangan, deviasi tonase, dan tingkat penyelesaian pengiriman
    public function index(Request $request): View
    {
        $orders = Order::with(['user', 'orderItems.product', 'invoice'])->latest()->get();

        $journalRows = $orders->values()->map(
            fn (Order $order, int $index) => (new LaporanOperasionalResource($order, $index))->resolve()
        )->all();

        $totalEstimate = (float) array_sum(array_column($journalRows, 'estimate'));
        $totalNet      = (float) array_sum(array_column($journalRows, 'net'));
        $totalValue    = (float) array_sum(array_column($journalRows, 'value'));

        $completedOrdersCount = $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $activeOrdersCount    = $orders->whereNotIn('status', ['BATAL'])->count();
        $deliveryAccuracy     = $activeOrdersCount > 0 ? round(($completedOrdersCount / $activeOrdersCount) * 100, 1) : 100.0;
        $overallDeviation     = $totalEstimate > 0 ? round((($totalNet - $totalEstimate) / $totalEstimate) * 100, 2) : 0;

        $commodityOptions = $orders->pluck('orderItems.*.product.name')->flatten()->filter()->unique()->values()->all();
        $clientOptions    = $orders->map(fn ($o) => $o->user?->company_name ?: $o->user?->name)->filter()->unique()->values()->all();

        return view('sekretaris.laporan', [
            'journalRows'      => $journalRows,
            'totalEstimate'    => $totalEstimate,
            'totalNet'         => $totalNet,
            'totalValue'       => $totalValue,
            'overallDeviation' => $overallDeviation,
            'deliveryAccuracy' => $deliveryAccuracy,
            'commodityOptions' => $commodityOptions,
            'clientOptions'    => $clientOptions,
        ]);
    }

    // Mengekspor rekapitulasi data penimbangan dan status pelunasan transaksi ke berkas CSV
    public function exportCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Rekap_Laporan_Operasional_GPA_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBFsep=;\r\n");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)'], ';');
            fputcsv($handle, ['JURNAL REKAPITULASI OPERASIONAL, PENIMBANGAN & PENJUALAN'], ';');
            fputcsv($handle, ['WAKTU CETAK', date('d/m/Y H:i') . ' WIB'], ';');
            fputcsv($handle, [], ';');
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
            ], ';');

            $orders = Order::with(['user', 'orderItems.product', 'invoice'])->latest()->get();
            $rowNumber = 1;
            foreach ($orders as $order) {
                $clientName    = $order->user?->company_name ?: ($order->user?->name ?: 'Mitra Klien');
                $address       = $order->delivery_address ?: 'Gudang Pembeli';
                $firstItem     = $order->orderItems->first();
                $commodityName = $firstItem?->product?->name ?: 'Sayur Segar';
                $unitPrice     = $firstItem ? (float) $firstItem->unit_price : 0;

                $estimatedQty = (float) $order->orderItems->sum('ordered_qty');
                $netQty       = $estimatedQty;
                $orderTotal   = (float) ($order->grand_total ?: ($netQty * $unitPrice));
                $diffKg       = $netQty - $estimatedQty;
                $diffPct      = $estimatedQty > 0 ? round(($diffKg / $estimatedQty) * 100, 2) : 0;
                $isPaid       = $order->status === 'SELESAI' || ($order->invoice && $order->invoice->status === 'PAID');

                fputcsv($handle, [
                    $rowNumber++,
                    $order->order_number,
                    $order->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)),
                    $clientName,
                    $address,
                    $commodityName,
                    $estimatedQty,
                    $netQty,
                    $diffKg,
                    ($diffPct >= 0 ? '+' : '') . $diffPct . '%',
                    $unitPrice,
                    $orderTotal,
                    $isPaid ? 'Lunas' : 'Tempo 30 Hari',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    // Menyiapkan lembar cetak dokumen rekapitulasi operasional resmi untuk kebutuhan arsip sekretaris
    public function printReport(Request $request): View
    {
        $orders = Order::with(['user', 'orderItems.product', 'invoice'])->latest()->get();

        $journalRows = $orders->values()->map(
            fn (Order $order, int $index) => (new LaporanOperasionalResource($order, $index))->resolve()
        )->all();

        $totalEstimate    = (float) array_sum(array_column($journalRows, 'estimate'));
        $totalNet         = (float) array_sum(array_column($journalRows, 'net'));
        $totalValue       = (float) array_sum(array_column($journalRows, 'value'));
        $overallDeviation = $totalEstimate > 0 ? round((($totalNet - $totalEstimate) / $totalEstimate) * 100, 2) : 0;
        $operatorName     = Auth::user()?->name ?: 'Sekretaris GPA';
        $direkturName     = User::where('role', 'DIREKTUR')->first()?->name ?? 'Direktur GPA';

        return view('prints.sekretaris-laporan', [
            'journalRows'      => $journalRows,
            'totalEstimate'    => $totalEstimate,
            'totalNet'         => $totalNet,
            'totalValue'       => $totalValue,
            'overallDeviation' => $overallDeviation,
            'operatorName'     => $operatorName,
            'direkturName'     => $direkturName,
        ]);
    }
}
