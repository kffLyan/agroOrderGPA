<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    /**
     * Menampilkan konsol penerbitan faktur tagihan & monitoring tempo pembayaran (TOP).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $operator = [
            'role'     => 'Sekretaris Operasional',
            'name'     => $user?->name ?: 'Sekretariat GPA',
            'code'     => $user ? 'SK-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'SK-001',
            'initials' => strtoupper(substr($user?->name ?: 'SK', 0, 2)),
            'branch'   => 'Gudang Pusat Sentra',
            'email'    => $user?->email ?: 'sekretaris@agroorder.co.id',
            'shift'    => 'Shift Pagi 07.00 - 16.00 WIB',
        ];

        $heading = [
            'title_before'     => 'Manajemen Faktur Tagihan &',
            'title_after'      => 'Pemantauan Tempo Pembayaran',
            'subtitle_before'  => 'Konsolidasi Surat Jalan sayur yang telah selesai dikirim menjadi lembar',
            'subtitle_rule'    => 'faktur penagihan resmi sesuai kesepakatan tempo bayar (14 / 30 / 45 Hari).',
            'export_label'     => 'Unduh Laporan Faktur (CSV)',
            'manual_label'     => '+ Terbitkan Faktur Baru',
        ];

        // Ambil data klien B2B dan pesanan riil
        $clientsQuery = User::where('client_type', 'B2B_KONTRAK')
            ->orWhere('role', 'KLIEN')
            ->with(['orders' => fn($q) => $q->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])->with('orderItems.product')])
            ->get();

        if ($clientsQuery->isEmpty()) {
            $clientsQuery = User::where('role', 'KLIEN')->get();
        }

        $clients = [];
        foreach ($clientsQuery as $c) {
            $docs = [];
            foreach ($c->orders as $ord) {
                $netWeight = (float) $ord->orderItems->sum(fn($it) => $it->ordered_qty);
                $val = (float) $ord->grand_total;
                $commodity = $ord->orderItems->first()?->product?->name ?: 'Sayur Segar Grade A';
                $sjNum = $ord->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad($ord->id, 4, '0', STR_PAD_LEFT));

                $docs[] = [
                    'sj'           => $sjNum,
                    'date'         => ($ord->created_at ?: now())->format('d M Y'),
                    'commodity'    => $commodity,
                    'weight'       => $netWeight,
                    'weight_label' => number_format($netWeight, 0, ',', '.') . ' kg',
                    'pod'          => 'Telah Diterima & Sah',
                    'value'        => $val,
                    'value_label'  => 'Rp ' . number_format($val, 0, ',', '.'),
                ];
            }

            $contractObj = method_exists($c, 'contracts') ? $c->contracts()->where('status', 'ACTIVE')->first() : null;
            $topDays = $contractObj?->top_days ?: 30;
            $plafon = $contractObj ? (float) ($contractObj->fixed_price_per_kg * $contractObj->committed_volume_per_cycle * 2) : 0;
            $usedPlafon = (float) $c->orders()->whereIn('status', ['MENUNGGU_VERIFIKASI', 'MENUNGGU_PEMBAYARAN', 'LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN'])->sum('grand_total');

            $clients[] = [
                'id'          => (string) $c->id,
                'name'        => $c->company_name ?: ($c->name ?: 'Mitra Klien'),
                'terms_label' => 'Tempo ' . $topDays . ' Hari',
                'terms_days'  => $topDays,
                'npwp'        => '—',
                'contract'    => $contractObj?->contract_number ?: ('KONTRAK-' . str_pad($c->id, 3, '0', STR_PAD_LEFT)),
                'plafon'      => $plafon,
                'used'        => $usedPlafon,
                'issue_date'  => now()->format('Y-m-d'),
                'documents'   => $docs,
            ];
        }

        // Ambil data faktur dari database
        $invoices = Invoice::with(['user', 'orders.orderItems', 'payments'])->latest()->get();

        $receivableTotal = 0;
        $dueSoonTotal = 0;
        $paidTotal = 0;
        $ledgerRows = [];

        if ($invoices->isNotEmpty()) {
            foreach ($invoices as $inv) {
                $amount = (float) $inv->grand_total;
                $isPaid = $inv->status === 'PAID';
                $clientName = $inv->user?->company_name ?: ($inv->user?->name ?: 'Mitra Klien GPA');
                $dueDate = $inv->due_date ?: now()->addDays(30);
                $daysRemaining = now()->diffInDays($dueDate, false);

                if ($isPaid) {
                    $paidTotal += $amount;
                    $statusKey = 'paid';
                    $statusLabel = 'Lunas';
                    $statusClass = 'bg-accent text-success-deep outline outline-1 outline-success-deep';
                    $remainingText = 'Telah Lunas';
                    $remainingClass = 'text-success-deep';
                    $remainingDot = 'bg-success-deep';
                } else {
                    $receivableTotal += $amount;
                    if ($daysRemaining < 0) {
                        $statusKey = 'blocked';
                        $statusLabel = 'Jatuh Tempo';
                        $statusClass = 'bg-danger-soft text-danger outline outline-1 outline-danger/60';
                        $remainingText = 'Lewat ' . abs($daysRemaining) . ' Hari';
                        $remainingClass = 'text-danger';
                        $remainingDot = 'bg-danger';
                    } elseif ($daysRemaining <= 7) {
                        $dueSoonTotal += $amount;
                        $statusKey = 'follow_up';
                        $statusLabel = 'Perlu Dihubungi';
                        $statusClass = 'bg-warning-soft/60 text-warning-deep outline outline-1 outline-warning/50';
                        $remainingText = "Sisa {$daysRemaining} Hari";
                        $remainingClass = 'text-warning-deep';
                        $remainingDot = 'bg-warning';
                    } else {
                        $statusKey = 'lancar';
                        $statusLabel = 'Lancar';
                        $statusClass = 'bg-accent text-success-deep outline outline-1 outline-success-deep';
                        $remainingText = "Sisa {$daysRemaining} Hari";
                        $remainingClass = 'text-success-deep';
                        $remainingDot = 'bg-success-deep';
                    }
                }

                $ledgerRows[] = [
                    'id'             => $inv->invoice_number,
                    'issued'         => $inv->created_at->format('d M Y'),
                    'issued_note'    => '(Faktur)',
                    'client'         => $clientName,
                    'contract'       => 'KONTRAK-' . ($inv->user_id ? str_pad($inv->user_id, 3, '0', STR_PAD_LEFT) : '001'),
                    'contract_note'  => null,
                    'contract_class' => 'text-ink-quiet',
                    'sj_count'       => max(1, $inv->orders->count()),
                    'total'          => $amount,
                    'total_label'    => 'Rp ' . number_format($amount, 0, ',', '.'),
                    'total_class'    => 'text-ink font-bold',
                    'terms_days'     => 30,
                    'terms_label'    => 'Tempo 30',
                    'terms_class'    => 'bg-surface-pill text-ink-body outline outline-1 outline-line-board/70',
                    'due'            => $dueDate->format('d M Y'),
                    'due_class'      => 'text-ink',
                    'remaining'      => $remainingText,
                    'remaining_class'=> $remainingClass,
                    'remaining_dot'  => $remainingDot,
                    'status_key'     => $statusKey,
                    'status_label'   => $statusLabel,
                    'status_class'   => $statusClass,
                    'row_class'      => '',
                    'highlight'      => '',
                    'paid'           => $isPaid,
                ];
            }
        }

        return view('sekretaris.invoice', [
            'clients'         => $clients,
            'ledgerRows'      => $ledgerRows,
            'receivableTotal' => $receivableTotal,
            'dueSoonTotal'    => $dueSoonTotal,
            'paidTotal'       => $paidTotal,
        ]);
    }

    /**
     * Unduh Laporan Faktur Tagihan & Piutang B2B (.CSV)
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
                $contractNo = 'KONTRAK-' . ($inv->user_id ? str_pad($inv->user_id, 3, '0', STR_PAD_LEFT) : '001');
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
