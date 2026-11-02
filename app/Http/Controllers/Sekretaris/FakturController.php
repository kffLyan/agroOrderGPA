<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sekretaris\FakturKlienResource;
use App\Http\Resources\Sekretaris\BukuBesarFakturResource;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller penerbitan faktur penagihan dan monitoring tempo untuk Sekretaris
class FakturController extends Controller
{
    // Menampilkan buku besar faktur penagihan klien B2B, status pelunasan, dan kalkulasi tempo TOP
    public function index(Request $request): View
    {
        // Mengambil data klien B2B beserta pesanan sah yang telah siap kirim, dalam perjalanan, atau selesai
        $clientsQuery = User::where('client_type', 'B2B_KONTRAK')
            ->orWhere('role', 'KLIEN')
            ->with(['orders' => fn($orderQuery) => $orderQuery->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])->with('orderItems.product')])
            ->get();

        if ($clientsQuery->isEmpty()) {
            $clientsQuery = User::where('role', 'KLIEN')->get();
        }

        $clients = FakturKlienResource::collection($clientsQuery)->resolve();

        // Mengambil seluruh data faktur yang tercatat beserta rincian user dan status pelunasannya
        $invoices = Invoice::with(['user', 'orders.orderItems', 'payments'])->latest()->get();

        $receivableTotal = (float) $invoices->where('status', '!=', 'PAID')->sum('grand_total');
        $paidTotal       = (float) $invoices->where('status', 'PAID')->sum('grand_total');

        $today = now()->startOfDay();
        $dueSoonTotal = (float) $invoices->filter(function ($invoice) use ($today) {
            if ($invoice->status === 'PAID') {
                return false;
            }
            $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->startOfDay() : $today->copy()->addDays(30);
            $diffDays = (int) round($today->diffInDays($dueDate, false));
            return $diffDays >= 0 && $diffDays <= 7;
        })->sum('grand_total');

        $ledgerRows = BukuBesarFakturResource::collection($invoices)->resolve();

        return view('sekretaris.faktur', [
            'clients'         => $clients,
            'ledgerRows'      => $ledgerRows,
            'receivableTotal' => $receivableTotal,
            'dueSoonTotal'    => $dueSoonTotal,
            'paidTotal'       => $paidTotal,
        ]);
    }

    // Mengekspor rekapitulasi buku besar faktur penagihan dan piutang B2B ke format CSV untuk arsip akuntansi
    public function exportCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Laporan_Faktur_Tagihan_GPA_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBFsep=;\r\n");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)'], ';');
            fputcsv($handle, ['BUKU BESAR FAKTUR PENAGIHAN & MONITORING TEMPO PEMBAYARAN (TOP)'], ';');
            fputcsv($handle, ['WAKTU CETAK', date('d/m/Y H:i') . ' WIB'], ';');
            fputcsv($handle, [], ';');
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
            ], ';');

            $invoices = Invoice::with(['user', 'orders'])->latest()->get();
            foreach ($invoices as $invoice) {
                $clientName = $invoice->user?->company_name ?: ($invoice->user?->name ?: 'Mitra Klien');
                $contractNo = 'KONTRAK-' . ($invoice->user_id ? str_pad((string) $invoice->user_id, 3, '0', STR_PAD_LEFT) : '001');
                $dueDate = $invoice->due_date ?: now()->addDays(30);

                fputcsv($handle, [
                    $invoice->invoice_number,
                    $invoice->created_at ? $invoice->created_at->format('d/m/Y') : '-',
                    $clientName,
                    $contractNo,
                    max(1, $invoice->orders->count()),
                    (float) $invoice->grand_total,
                    'Tempo 30 Hari',
                    $dueDate->format('d/m/Y'),
                    $invoice->status === 'PAID' ? 'Lunas' : ($invoice->status === 'OVERDUE' ? 'Jatuh Tempo' : 'Belum Lunas'),
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
