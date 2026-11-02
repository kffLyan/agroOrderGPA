<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller pusat dokumen dan faktur tagihan untuk Klien
class DokumenController extends Controller
{
    // Menampilkan pusat berkas faktur tagihan konsolidasi, status tempo pembayaran, dan sisa pagu kredit
    public function index(Request $request)
    {
        $userId = Auth::guard('web')->id();
        $user = Auth::guard('web')->user();

        $rawInvoices = Invoice::where('user_id', $userId)
            ->with(['orders.orderItems.product', 'payments'])
            ->orderBy('due_date', 'desc')
            ->get();

        // Menghitung akumulasi nominal tagihan belum bayar dan tagihan yang telah disahkan lunas
        $unpaidAmount = (float) $rawInvoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->sum('grand_total');
        $settledAmount = (float) $rawInvoices->where('status', 'PAID')->sum('grand_total');

        $activeContracts = Contract::where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->get();
        $creditLimit = (float) $activeContracts->sum(function ($contract) {
            return (float) (($contract->fixed_price_per_kg ?: 0) * ($contract->committed_volume_per_cycle ?: 0) * 2);
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

    // Menyiapkan lembar cetak dokumen rekapitulasi faktur dan status piutang resmi untuk pembukuan klien
    public function printRekap(Request $request)
    {
        $userId = Auth::guard('web')->id();
        $user = Auth::guard('web')->user();

        $invoices = Invoice::where('user_id', $userId)
            ->with(['orders.orderItems', 'payments'])
            ->orderBy('due_date', 'desc')
            ->get();

        $totalNetto = (float) $invoices->sum(function ($invoice) {
            return $invoice->orders->sum(function ($order) {
                return $order->orderItems->sum('ordered_qty');
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

    // Mengekspor daftar faktur tagihan konsolidasi ke berkas spreadsheet CSV untuk kebutuhan rekonsiliasi keuangan
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
            fputs($handle, "\xEF\xBB\xBFsep=;\r\n");

            fputcsv($handle, ['KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)'], ';');
            fputcsv($handle, ['REKAPITULASI RESMI FAKTUR KONSOLIDASI & STATUS TAGIHAN TEMPO'], ';');
            fputcsv($handle, ['KLIEN / MITRA B2B', $user->company_name ?: $user->name], ';');
            fputcsv($handle, ['WAKTU EKSPOR', date('d/m/Y H:i') . ' WIB'], ';');
            fputcsv($handle, [], ';');
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
            ], ';');

            $rowNumber = 1;
            foreach ($invoices as $invoice) {
                $totalNetto = (float) $invoice->orders->sum(function ($order) {
                    return $order->orderItems->sum('ordered_qty');
                });

                $periodLabel = ($invoice->period_start ? $invoice->period_start->format('d/m/Y') : '-') . ' - ' . ($invoice->period_end ? $invoice->period_end->format('d/m/Y') : '-');
                $statusLabel = match ($invoice->status) {
                    'PAID'    => 'Lunas Terverifikasi',
                    'UNPAID'  => 'Menunggu Pembayaran (Tempo)',
                    'PARTIAL' => 'Dibayar Sebagian',
                    'OVERDUE' => 'Melewati Jatuh Tempo',
                    default   => ucwords(strtolower(str_replace('_', ' ', $invoice->status))),
                };

                fputcsv($handle, [
                    $rowNumber++,
                    $invoice->invoice_number,
                    $invoice->created_at ? $invoice->created_at->format('d/m/Y') : '-',
                    $periodLabel,
                    $invoice->orders->count(),
                    number_format($totalNetto, 1, '.', ''),
                    (float) $invoice->grand_total,
                    $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'Tempo 30 Hari',
                    $statusLabel,
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
