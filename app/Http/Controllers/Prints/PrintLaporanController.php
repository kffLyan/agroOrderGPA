<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Services\LaporanPrintService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller pusat penyedia data & ekspor untuk tampilan cetak laporan resmi eksekutif (prints.report & prints.report-excel)
class PrintLaporanController extends Controller
{
    // Menyiapkan data dan merender tampilan dokumen cetak laporan resmi eksekutif standar kertas A4 (prints/report.blade.php)
    public function __invoke(Request $request, LaporanPrintService $service): View
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $clientId  = $request->query('client_id') ? (int) $request->query('client_id') : null;
        $period    = $request->query('period');

        $data = $service->buildPrintData(
            $startDate,
            $endDate,
            $clientId,
            $period
        );

        return view('prints.report', $data);
    }

    // Menyiapkan data dan mengekspor buku besar transaksi konsolidasi ke format Excel (.xls SpreadsheetML) atau CSV
    public function export(Request $request, LaporanPrintService $printService): Response|StreamedResponse
    {
        $period    = $request->query('period');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');
        $clientId  = $request->query('client_id') ? (int) $request->query('client_id') : null;
        $format    = strtolower($request->query('format', 'csv'));

        $now = now();
        $start = null;
        $end = null;
        $labelPeriode = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $labelPeriode = 'Periode: ' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
        } elseif ($period) {
            switch ($period) {
                case 'hari_ini':
                    $start = (clone $now)->startOfDay();
                    $end = (clone $now)->endOfDay();
                    $labelPeriode = 'Hari Ini (' . $now->format('d/m/Y') . ')';
                    break;
                case 'minggu_ini':
                    $start = (clone $now)->startOfWeek();
                    $end = (clone $now)->endOfWeek();
                    $labelPeriode = 'Minggu Ini';
                    break;
                case 'tahun_ini':
                    $start = (clone $now)->startOfYear();
                    $end = (clone $now)->endOfYear();
                    $labelPeriode = 'Tahun Ini (' . $now->format('Y') . ')';
                    break;
                case 'semua':
                    $start = null;
                    $end = null;
                    $labelPeriode = 'Semua Riwayat Transaksi (Konsolidasi Buku Besar)';
                    break;
                case 'bulan_ini':
                default:
                    $start = (clone $now)->startOfMonth();
                    $end = (clone $now)->endOfMonth();
                    $labelPeriode = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
                    break;
            }
        }

        // 1. Dapatkan data analitik transaksi (omzet, tonase, komoditas, pesanan)
        $dataCetak = $printService->buildPrintData($startDate, $endDate, $clientId, $period);
        $dataCetak['periodName'] = $labelPeriode;

        // 2. Susun data buku besar piutang klien secara akurat (memperhitungkan invoice & pembayaran kasir/QRIS langsung)
        $daftarKlien = User::where('role', 'KLIEN')->with(['orders.orderItems', 'invoices', 'contracts'])->get();
        if ($clientId) {
            $daftarKlien = $daftarKlien->where('id', $clientId);
        }

        $dataKlien = [];
        foreach ($daftarKlien as $client) {
            $clientOrders = $client->orders->whereNotIn('status', ['BATAL']);
            if ($start && $end) {
                $clientOrders = $clientOrders->whereBetween('created_at', [$start, $end]);
            }
            $poCount = $clientOrders->count();
            $volumeKg = (float) $clientOrders->sum(fn($pesanan) => $pesanan->orderItems->sum('ordered_qty'));
            $gross = (float) $clientOrders->sum('grand_total');

            $invQuery = $client->invoices();
            if ($start && $end) {
                $invQuery->whereBetween('created_at', [$start, $end]);
            }
            $settledInv = (float) (clone $invQuery)->where('status', 'PAID')->sum('grand_total');
            $settledDirectPayments = (float) Payment::whereIn('order_id', $clientOrders->pluck('id'))
                ->whereNull('invoice_id')
                ->where('status', 'LUNAS')
                ->sum('amount');
            $settled = $settledInv + $settledDirectPayments;
            $outstanding = max(0, $gross - $settled);

            $isB2b = ($client->client_type === 'B2B_KONTRAK');
            $contract = $client->contracts->first();
            $contractNo = $contract ? $contract->contract_number : ('REG-' . str_pad((string) $client->id, 3, '0', STR_PAD_LEFT));
            $termDays = $contract ? (int) $contract->top_days : ($isB2b ? 30 : 0);
            $termLabel = $termDays > 0 ? ('TOP ' . $termDays . ' Hari') : 'Tunai / QRIS Instan';
            $statusLabel = $outstanding <= 0 ? 'Lunas Sah' : 'Belum Lunas';

            $dataKlien[] = [
                'name'         => $client->company_name ?: $client->name,
                'category'     => $isB2b ? 'Mitra B2B Kontrak' : 'Pelanggan Reguler',
                'code'         => $contractNo,
                'po_count'     => $poCount,
                'volume_kg'    => $volumeKg,
                'gross'        => $gross,
                'settled'      => $settled,
                'receivable'   => $outstanding,
                'term_label'   => $termLabel,
                'status_label' => $statusLabel,
            ];
        }

        // A. JIKA PENGGUNA MEMILIH FORMAT EXCEL SPREADSHEET (Default & Rekomendasi Utama)
        // Dirender langsung melalui Blade template resmi: resources/views/prints/report-excel.blade.php
        if ($format === 'excel' || $format === 'xls') {
            $namaDirektur = Auth::user()?->name ?: 'Ahmad Sanusi, S.P.';
            $payload = array_merge($dataCetak, [
                'dataKlien'    => $dataKlien,
                'namaDirektur' => $namaDirektur,
            ]);

            $xmlContent = view('prints.report-excel', $payload)->render();
            $namaFile = 'Laporan_Eksekutif_Penjualan_GPA_' . date('Ymd_His') . '.xls';

            return response($xmlContent, 200, [
                'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $namaFile . '"',
                'Cache-Control'       => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
            ]);
        }

        // B. JIKA PENGGUNA MEMILIH FORMAT DATA MENTAH (.CSV)
        $headerBerkas = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Laporan_Penjualan_GPA_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($dataCetak, $dataKlien, $labelPeriode) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBFsep=;\r\n");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)'], ';');
            fputcsv($handle, ['REKAPITULASI RESMI PENJUALAN EKSEKUTIF & JURNAL TRANSAKSI KONSOLIDASI'], ';');
            fputcsv($handle, ['CAKUPAN PERIODE', $labelPeriode], ';');
            fputcsv($handle, ['STATUS AUDIT', 'DISAHKAN // SISTEM RESMI GPA'], ';');
            fputcsv($handle, ['TANGGAL CETAK', date('d/m/Y H:i') . ' WIB'], ';');
            fputcsv($handle, [], ';');

            // Tabel 1: Rincian Jurnal Transaksi Pemesanan
            fputcsv($handle, ['=== 1. RINCIAN LENGKAP BUKU JURNAL TRANSAKSI PEMESANAN ==='], ';');
            fputcsv($handle, [
                'No',
                'No. Pesanan (PO)',
                'Tanggal',
                'Entitas Pembeli',
                'Kategori Klien',
                'Rincian Komoditas & Netto',
                'Metode Bayar',
                'Status Transaksi',
                'Total Nilai Transaksi (Rp)',
            ], ';');

            $idx = 1;
            foreach ($dataCetak['orders'] as $ord) {
                $itemStrings = [];
                foreach ($ord->orderItems as $item) {
                    $itemStrings[] = ($item->product?->name ?? 'Sayuran') . ' (' . number_format($item->ordered_qty, 1) . ' kg)';
                }

                fputcsv($handle, [
                    $idx++,
                    $ord->order_number,
                    $ord->created_at->format('d/m/Y'),
                    $ord->user?->company_name ?: ($ord->user?->name ?: 'Klien'),
                    $ord->client_type_label ?? ($ord->user?->is_b2b ? 'Mitra B2B Kontrak' : 'Pelanggan Reguler'),
                    implode(', ', $itemStrings),
                    $ord->payment_method_label ?? ($ord->payment?->method_label ?? str_replace('_', ' ', $ord->payment_method ?? 'Kredit Tempo TOP')),
                    $ord->status_label ?? str_replace('_', ' ', $ord->status),
                    number_format((float) $ord->grand_total, 0, ',', '.'),
                ], ';');
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['=== 2. BUKU BESAR REKAPITULASI PELANGGAN & STATUS PIUTANG ==='], ';');
            fputcsv($handle, [
                'No',
                'Entitas Mitra / Pelanggan',
                'Kategori',
                'No. Kontrak / Ref ID',
                'Frekuensi PO',
                'Volume Tera (Kg)',
                'Tagihan Bruto (Rp)',
                'Kas Masuk / Settled (Rp)',
                'Sisa Piutang (Rp)',
                'Ketentuan Tenor',
                'Status Pelunasan',
            ], ';');

            $cIdx = 1;
            foreach ($dataKlien as $c) {
                fputcsv($handle, [
                    $cIdx++,
                    $c['name'],
                    $c['category'],
                    $c['code'],
                    $c['po_count'],
                    number_format($c['volume_kg'], 2, '.', ''),
                    number_format($c['gross'], 0, ',', '.'),
                    number_format($c['settled'], 0, ',', '.'),
                    number_format($c['receivable'], 0, ',', '.'),
                    $c['term_label'],
                    $c['status_label'],
                ], ';');
            }

            fclose($handle);
        }, 200, $headerBerkas);
    }
}
