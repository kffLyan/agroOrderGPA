<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    /**
     * Menampilkan pusat dokumen, faktur konsolidasi, dan rekap tagihan tempo
     */
    public function index(Request $request)
    {
        $userId = Auth::guard('web')->id();
        $user = Auth::guard('web')->user();

        $rawInvoices = Invoice::where('user_id', $userId)
            ->with(['orders.orderItems', 'payments'])
            ->orderBy('due_date', 'desc')
            ->get();

        // Hitung metrik keuangan
        $unpaidAmount = (float) $rawInvoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->sum('grand_total');
        $settledAmount = (float) $rawInvoices->where('status', 'PAID')->sum('grand_total');

        $metrics = [
            [
                'label'      => 'Total Tagihan Tempo Aktif',
                'title'      => 'Outstanding Payable',
                'value'      => 'Rp ' . number_format($unpaidAmount, 0, ',', '.'),
                'tone'       => 'warning',
                'icon'       => 'clock',
                'foot_left'  => 'Status',
                'foot_right' => $unpaidAmount > 0 ? 'Menunggu Pelunasan' : 'Nihil / Lancar',
            ],
            [
                'label'      => 'Total Tagihan Lunas',
                'title'      => 'Settled Invoices',
                'value'      => 'Rp ' . number_format($settledAmount, 0, ',', '.'),
                'tone'       => 'accent',
                'icon'       => 'check-circle',
                'foot_left'  => 'Verifikasi',
                'foot_right' => '100% Tervalidasi',
            ],
            [
                'label'      => 'Sisa Plafon Kredit B2B',
                'title'      => 'Credit Vault',
                'value'      => 'Rp ' . number_format(max(0, 50000000 - $unpaidAmount), 0, ',', '.'),
                'tone'       => 'accent-soft',
                'icon'       => 'wallet',
                'foot_left'  => 'Fasilitas',
                'foot_right' => $user->client_type === 'B2B_KONTRAK' ? 'TOP 30 Hari' : 'Reguler',
            ],
            [
                'label'      => 'Deviasi & Rekonsiliasi Timbang',
                'title'      => 'Netto Sah vs PO',
                'value'      => '0.34%',
                'tone'       => 'neutral',
                'icon'       => 'scale',
                'foot_left'  => 'Toleransi Aman',
                'foot_right' => '< 1.0% Sesuai SOP',
            ],
        ];

        $header = [
            'title'    => 'Pusat Dokumen & Faktur Konsolidasi',
            'chip'     => 'Faktur Resmi & Berita Acara',
            'subtitle' => 'Arsip digital dokumen penagihan bulanan, faktur pajak, dan surat jalan penimbangan sah PT Green Pasundan Agriculture.',
            'actions'  => [
                ['label' => 'Rekap Tagihan (CSV)', 'variant' => 'outline', 'icon' => 'download'],
                ['label' => 'Cetak Laporan', 'variant' => 'solid', 'icon' => 'printer'],
            ],
        ];

        $proofMenu = [
            [
                'label'   => 'Unggah Bukti Bayar Tagihan',
                'note'    => 'Lampirkan bukti transfer bank',
                'icon'    => 'upload',
                'variant' => 'primary',
                'href'    => route('klien.payment-proof'),
            ],
            [
                'label'   => 'Salin Rekening BCA GPA',
                'note'    => 'BCA: 840-552-1920 (GPA Rekening Utama)',
                'icon'    => 'copy',
                'variant' => 'default',
                'href'    => null,
            ],
        ];

        $tabs = [
            ['label' => 'Semua Tagihan & Faktur', 'value' => 'all', 'icon' => 'file-text', 'badge' => ''],
            ['label' => 'Menunggu Pembayaran', 'value' => 'unpaid', 'icon' => 'clock', 'badge' => 'Tempo'],
            ['label' => 'Lunas Terverifikasi', 'value' => 'settled', 'icon' => 'check-circle', 'badge' => ''],
            ['label' => 'Dokumen Pajak & BA', 'value' => 'tax', 'icon' => 'shield', 'badge' => ''],
        ];

        $periods = [
            ['value' => 'all', 'label' => 'Semua Periode Transaksi'],
            ['value' => 'current', 'label' => 'Bulan Berjalan (' . now()->translatedFormat('F Y') . ')'],
            ['value' => 'last-month', 'label' => 'Bulan Sebelumnya'],
        ];

        $columns = [
            ['key' => 'no', 'label' => 'No', 'align' => 'left'],
            ['key' => 'invoice', 'label' => 'No. Faktur / Tanggal', 'align' => 'left'],
            ['key' => 'period', 'label' => 'Periode & Dokumen', 'align' => 'left'],
            ['key' => 'netto', 'label' => 'Bobot Netto', 'align' => 'right'],
            ['key' => 'total', 'label' => 'Total Nilai (Rp)', 'align' => 'right'],
            ['key' => 'due', 'label' => 'Jatuh Tempo', 'align' => 'left'],
            ['key' => 'status', 'label' => 'Status Pembayaran', 'align' => 'left'],
            ['key' => 'actions', 'label' => 'Aksi', 'align' => 'center'],
        ];

        $invoices = $rawInvoices->map(function ($inv, $idx) {
            $totalNetto = (float) $inv->orders->sum(function ($o) {
                return $o->orderItems->sum(fn($i) => $i->actual_net_weight ?? $i->ordered_qty);
            });

            $statusText = match ($inv->status) {
                'UNPAID'   => 'Menunggu Pembayaran',
                'PARTIAL'  => 'Dibayar Sebagian',
                'PAID'     => 'Lunas Terverifikasi',
                'OVERDUE'  => 'Melewati Jatuh Tempo',
                default    => $inv->status,
            };

            $statusTone = match ($inv->status) {
                'UNPAID'   => 'warning',
                'PARTIAL'  => 'warning',
                'PAID'     => 'accent',
                'OVERDUE'  => 'warning',
                default    => 'muted',
            };

            $isPaid = $inv->status === 'PAID';

            // Baris rincian delivery / SJ di dalam faktur
            $attachments = $inv->orders->map(function ($ord, $oIdx) {
                $itemNames = $ord->orderItems->map(fn($i) => $i->product->name ?? 'Komoditas')->join(', ');
                $kg = (float) $ord->orderItems->sum(fn($i) => $i->actual_net_weight ?? $i->ordered_qty);

                return [
                    'no'           => str_pad($oIdx + 1, 2, '0', STR_PAD_LEFT),
                    'sj'           => $ord->surat_jalan_number ?: ('SJ-' . $ord->id),
                    'date'         => $ord->target_delivery_date ? $ord->target_delivery_date->format('d/m/Y') : $ord->created_at->format('d/m/Y'),
                    'hub'          => 'Sentra Lembang',
                    'commodity'    => $itemNames ?: 'Sayuran Segar Pasundan',
                    'weight_label' => number_format($kg, 1, ',', '.') . ' kg',
                    'amount_label' => 'Rp ' . number_format($ord->grand_total ?: $ord->estimated_total, 0, ',', '.'),
                    'pod_chip'     => $ord->pod_photo_url ? 'POD Valid' : 'BA Sah',
                    'ttd'          => $ord->received_by_name ?: 'Staff Receiving',
                ];
            })->all();

            return [
                'no'                => str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                'po'                => $inv->invoice_number,
                'printed'           => $inv->created_at->format('d M Y'),
                'period_label'      => ($inv->period_start ? $inv->period_start->format('d M') : '-') . ' - ' . ($inv->period_end ? $inv->period_end->format('d M Y') : '-'),
                'sj_chip'           => $inv->orders->count() . ' Surat Jalan',
                'sj_chip_muted'     => false,
                'netto_value'       => number_format($totalNetto, 1, ',', '.'),
                'netto_unit'        => 'kg',
                'netto_weight'      => 600,
                'total_label'       => 'Rp ' . number_format($inv->grand_total, 0, ',', '.'),
                'total_weight'      => 700,
                'due'               => $inv->due_date ? $inv->due_date->format('d M Y') : '-',
                'due_muted'         => $isPaid,
                'due_struck'        => $isPaid,
                'due_note'          => $isPaid ? 'Sudah Lunas' : ($inv->due_date && $inv->due_date->isPast() ? 'Overdue' : 'Tempo 30 Hari'),
                'due_note_tone'     => $isPaid ? 'success' : 'default',
                'status'            => $statusText,
                'status_tone'       => $statusTone,
                'status_kind'       => $isPaid ? 'check' : 'dot',
                'status_raw'        => $inv->status,
                'row_tone'          => $isPaid ? 'plain' : 'active',
                'tempo_days'        => 30,
                'sj_count'          => $inv->orders->count(),
                'netto_net_label'   => number_format($totalNetto, 1, ',', '.') . ' kg Netto Sah',
                'subtotal_label'    => 'Rp ' . number_format($inv->subtotal_amount ?: $inv->grand_total, 0, ',', '.'),
                'ppn_label'         => 'PPN DTP Hasil Pertanian (0%)',
                'ppn_value'         => 'Bebas PPN',
                'credit_label'      => 'Penyesuaian Mutu / Retur',
                'credit_value'      => '- Rp 0',
                'grand_total_label' => 'Rp ' . number_format($inv->grand_total, 0, ',', '.'),
                'attachments'       => $attachments,
                'actions'           => [
                    [
                        'shape'   => 'label',
                        'label'   => $isPaid ? 'Lihat Bukti' : 'Bayar Sekarang',
                        'variant' => 'solid',
                        'icon'    => 'upload',
                        'href'    => route('klien.payment-proof', ['tagihan' => $inv->invoice_number]),
                    ],
                ],
            ];
        })->values()->all();

        $archiveCounts = [
            'all'     => count($invoices),
            'unpaid'  => $rawInvoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->count(),
            'settled' => $rawInvoices->where('status', 'PAID')->count(),
            'tax'     => count($invoices),
        ];

        $pagination = [
            'page'        => 1,
            'per_page'    => max(1, count($invoices)),
            'total'       => count($invoices),
            'total_pages' => 1,
            'last_page'   => 1,
        ];

        $detail = [
            'title' => 'Lampiran Rekap Surat Jalan & Berita Acara Fisik',
            'badge' => 'Faktur Resmi GPA',
            'strip' => 'Terintegrasi Netto Timbangan Digital',
        ];

        $bank = [
            'label'   => 'Rekening Penerima Resmi',
            'bank'    => 'Bank Central Asia (BCA)',
            'account' => '840-552-1920',
            'holder'  => 'PT Green Pasundan Agriculture',
            'action'  => 'Unggah Bukti Transfer',
        ];

        $assurance = [
            'title' => 'Jaminan Hukum & Rekonsiliasi Faktur Sah GPA',
            'body'  => 'Faktur konsolidasi tempo ini dijamin oleh Berita Acara timbang sah digital dan faktur pajak elektronik resmi.',
            'chips' => ['Hubungi Finance GPA', 'Konfirmasi Pelunasan'],
        ];

        return view('klien.documents', compact(
            'invoices',
            'tabs',
            'archiveCounts',
            'periods',
            'pagination',
            'header',
            'proofMenu',
            'metrics',
            'columns',
            'detail',
            'bank',
            'assurance'
        ));
    }

    /**
     * Tampilan Siap Cetak Dokumen Rekapitulasi Faktur & Tagihan Resmi (.PDF / Print View)
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
                return $o->orderItems->sum(fn($i) => $i->actual_net_weight ?? $i->ordered_qty);
            });
        });

        $totalGrand = (float) $invoices->sum('grand_total');
        $unpaidTotal = (float) $invoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->sum('grand_total');
        $paidTotal = (float) $invoices->where('status', 'PAID')->sum('grand_total');

        $docNumber = 'DOC/GPA-INV-REKAP/' . now()->format('Ym') . '/' . str_pad($userId, 4, '0', STR_PAD_LEFT);
        $printDate = now()->translatedFormat('d F Y, H:i') . ' WIB';

        return view('klien.documents.print-rekap', compact(
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
}
