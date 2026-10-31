<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller Pusat Dokumen & Faktur Tagihan Konsolidasi Klien.
 *
 * Penjelasan UKK:
 * Modul ini menjadi arsip dokumen penagihan bagi klien B2B, menampilkan
 * faktur gabungan per periode, status jatuh tempo (Term of Payment),
 * rekapitulasi pembayaran lunas, cetak rekap resmi, dan ekspor CSV.
 */
class DokumenController extends Controller
{
    /**
     * Menampilkan pusat dokumen, faktur konsolidasi, dan rekap tagihan tempo.
     */
    public function index(Request $request)
    {
        $userId = Auth::guard('web')->id();
        $user = Auth::guard('web')->user();

        $rawInvoices = Invoice::where('user_id', $userId)
            ->with(['orders.orderItems.product', 'payments'])
            ->orderBy('due_date', 'desc')
            ->get();

        // Hitung total metrik tagihan di controller sesuai arsitektur MVC
        $unpaidAmount = (float) $rawInvoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->sum('grand_total');
        $settledAmount = (float) $rawInvoices->where('status', 'PAID')->sum('grand_total');

        $activeContracts = Contract::where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->get();
        $creditLimit = (float) $activeContracts->sum(function ($c) {
            return (float) (($c->fixed_price_per_kg ?: 0) * ($c->committed_volume_per_cycle ?: 0) * 2);
        });
        if ($creditLimit <= 0) {
            $creditLimit = 150000000.0;
        }

        $creditLimitRemaining = max(0, $creditLimit - $unpaidAmount);

        return view('klien.dokumen', compact(
            'rawInvoices',
            'user',
            'unpaidAmount',
            'settledAmount',
            'creditLimitRemaining'
        ));
    }

    /**
     * Tampilan Siap Cetak Dokumen Rekapitulasi Faktur & Tagihan Resmi (.PDF / Print View).
     */
    public function printRekap(Request $request)
    {
        $userId = Auth::guard('web')->id();
        $user = Auth::guard('web')->user();

        $invoices = Invoice::where('user_id', $userId)
            ->with(['orders.orderItems', 'payments'])
            ->orderBy('due_date', 'desc')
            ->get();

        $totalNetto = (float) $invoices->sum(function ($inv) {
            return $inv->orders->sum(function ($o) {
                return $o->orderItems->sum('ordered_qty');
            });
        });

        $totalGrand = (float) $invoices->sum('grand_total');
        $unpaidTotal = (float) $invoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->sum('grand_total');
        $paidTotal = (float) $invoices->where('status', 'PAID')->sum('grand_total');

        $docNumber = 'DOC/GPA-INV-REKAP/' . now()->format('Ym') . '/' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT);
        $printDate = now()->translatedFormat('d F Y, H:i') . ' WIB';

        return view('prints.client-documents-rekap', compact(
            'user',
            'invoices',
            'totalNetto',
            'totalGrand',
            'unpaidTotal',
            'paidTotal',
            'docNumber',
            'printDate'
        ));
    }

    /**
     * Unduh Rekap Faktur Konsolidasi Klien B2B (.CSV / Excel).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $userId = Auth::guard('web')->id();
        $user = Auth::guard('web')->user();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Rekap_Faktur_GPA_' . ($user->company_name ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $user->company_name) : 'Klien') . '_' . date('Ymd_His') . '.csv"',
        ];

        $invoices = Invoice::where('user_id', $userId)
            ->with(['orders.orderItems', 'payments'])
            ->orderBy('due_date', 'desc')
            ->get();

        return response()->stream(function () use ($user, $invoices) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)']);
            fputcsv($handle, ['REKAPITULASI RESMI FAKTUR KONSOLIDASI & STATUS TAGIHAN TEMPO']);
            fputcsv($handle, ['KLIEN / MITRA B2B', $user->company_name ?: $user->name]);
            fputcsv($handle, ['WAKTU EKSPOR', date('d/m/Y H:i') . ' WIB']);
            fputcsv($handle, []);
            fputcsv($handle, [
                'No',
                'No. Faktur Tagihan',
                'Tanggal Terbit',
                'Periode Konsolidasi',
                'Jumlah SJ',
                'Bobot Netto Tera (Kg)',
                'Total Tagihan (Rp)',
                'Jatuh Tempo (TOP)',
                'Status Pembayaran',
            ]);

            $no = 1;
            foreach ($invoices as $inv) {
                $totalNetto = (float) $inv->orders->sum(function ($o) {
                    return $o->orderItems->sum('ordered_qty');
                });

                $periodLabel = ($inv->period_start ? $inv->period_start->format('d/m/Y') : '-') . ' - ' . ($inv->period_end ? $inv->period_end->format('d/m/Y') : '-');
                $statusLabel = match ($inv->status) {
                    'PAID'    => 'Lunas Terverifikasi',
                    'UNPAID'  => 'Menunggu Pembayaran (Tempo)',
                    'PARTIAL' => 'Dibayar Sebagian',
                    'OVERDUE' => 'Melewati Jatuh Tempo',
                    default   => $inv->status,
                };

                fputcsv($handle, [
                    $no++,
                    $inv->invoice_number,
                    $inv->created_at ? $inv->created_at->format('d/m/Y') : '-',
                    $periodLabel,
                    $inv->orders->count(),
                    number_format($totalNetto, 1, '.', ''),
                    (float) $inv->grand_total,
                    $inv->due_date ? $inv->due_date->format('d/m/Y') : 'Tempo 30 Hari',
                    $statusLabel,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
