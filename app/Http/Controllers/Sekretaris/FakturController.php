<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sekretaris\InvoiceClientResource;
use App\Http\Resources\Sekretaris\InvoiceLedgerResource;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Penerbitan Faktur Penagihan & Monitoring Tempo (TOP) Sekretaris.
 *
 * Penjelasan UKK:
 * Modul ini menangani administrasi penagihan B2B Koperasi GPA:
 * 1. Menampilkan daftar faktur konsolidasi dan status pelunasan.
 * 2. Monitoring batas waktu jatuh tempo (Aging Receivable / TOP).
 * 3. Ekspor buku besar faktur penagihan ke CSV.
 */
class FakturController extends Controller
{
    /**
     * Menampilkan konsol penerbitan faktur tagihan & monitoring tempo pembayaran (TOP).
     */
    public function index(Request $request): View
    {
        // Ambil data klien B2B dan pesanan riil
        $clientsQuery = User::where('client_type', 'B2B_KONTRAK')
            ->orWhere('role', 'KLIEN')
            ->with(['orders' => fn($q) => $q->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])->with('orderItems.product')])
            ->get();

        if ($clientsQuery->isEmpty()) {
            $clientsQuery = User::where('role', 'KLIEN')->get();
        }

        $clients = InvoiceClientResource::collection($clientsQuery)->resolve();

        // Ambil data faktur dari database
        $invoices = Invoice::with(['user', 'orders.orderItems', 'payments'])->latest()->get();

        $receivableTotal = (float) $invoices->where('status', '!=', 'PAID')->sum('grand_total');
        $paidTotal       = (float) $invoices->where('status', 'PAID')->sum('grand_total');

        $dueSoonTotal = (float) $invoices->filter(function ($inv) {
            if ($inv->status === 'PAID') {
                return false;
            }
            $dueDate = $inv->due_date ?: now()->addDays(30);
            $days = now()->diffInDays($dueDate, false);
            return $days >= 0 && $days <= 7;
        })->sum('grand_total');

        $ledgerRows = InvoiceLedgerResource::collection($invoices)->resolve();

        return view('sekretaris.faktur', [
            'clients'         => $clients,
            'ledgerRows'      => $ledgerRows,
            'receivableTotal' => $receivableTotal,
            'dueSoonTotal'    => $dueSoonTotal,
            'paidTotal'       => $paidTotal,
        ]);
    }

    /**
     * Unduh Laporan Faktur Tagihan & Piutang B2B (.CSV).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Laporan_Faktur_Tagihan_GPA_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)']);
            fputcsv($handle, ['BUKU BESAR FAKTUR PENAGIHAN & MONITORING TEMPO PEMBAYARAN (TOP)']);
            fputcsv($handle, ['WAKTU CETAK', date('d/m/Y H:i') . ' WIB']);
            fputcsv($handle, []);
            fputcsv($handle, [
                'No. Faktur',
                'Tanggal Terbit',
                'Entitas Klien / Mitra B2B',
                'Nomor Kontrak',
                'Jumlah SJ Terlampir',
                'Total Tagihan (Rp)',
                'Skema Tempo',
                'Jatuh Tempo',
                'Status Tagihan',
            ]);

            $invoices = Invoice::with(['user', 'orders'])->latest()->get();
            foreach ($invoices as $inv) {
                $clientName = $inv->user?->company_name ?: ($inv->user?->name ?: 'Mitra Klien');
                $contractNo = 'KONTRAK-' . ($inv->user_id ? str_pad((string) $inv->user_id, 3, '0', STR_PAD_LEFT) : '001');
                $dueDate = $inv->due_date ?: now()->addDays(30);

                fputcsv($handle, [
                    $inv->invoice_number,
                    $inv->created_at ? $inv->created_at->format('d/m/Y') : '-',
                    $clientName,
                    $contractNo,
                    max(1, $inv->orders->count()),
                    (float) $inv->grand_total,
                    'Tempo 30 Hari',
                    $dueDate->format('d/m/Y'),
                    $inv->status === 'PAID' ? 'Lunas' : ($inv->status === 'OVERDUE' ? 'Jatuh Tempo' : 'Belum Lunas'),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
