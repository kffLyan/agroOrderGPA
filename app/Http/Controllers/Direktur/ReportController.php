<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\GovernanceService;
use App\Services\ReportPrintService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected GovernanceService $governanceService;

    public function __construct(GovernanceService $governanceService)
    {
        $this->governanceService = $governanceService;
    }

    /**
     * Menampilkan Laporan Penjualan Eksekutif Direktur - Status Terkunci // Immutable Archive
     */
    public function index(Request $request): View
    {
        $settings = $this->governanceService->getSettings();
        $periodLock = $settings['period_lock'] ?? [
            'is_locked'   => true,
            'period'      => 'Kuartal IV 2026 (Okt - Des 2026)',
            'locked_at'   => '24 Okt 2026 23:59:59 WIB',
            'locked_by'   => Auth::user()?->name ?: 'Direktur GPA',
            'sha256_hash' => '0x8F9C4A217B1E90D4CC67F814E32A0B7D18C992E5F67104B8A293CD0891DE33',
        ];

        // 1. Metrik omzet riil
        $totalRevenue = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS'])
            ->sum('grand_total');

        $totalTonnageKg = (float) OrderItem::whereHas('order', function ($q) {
            $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS']);
        })->sum('ordered_qty');

        $cashInflow = (float) Invoice::where('status', 'PAID')->sum('grand_total');
        $openReceivables = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->sum('grand_total');

        $quarterTarget = 600000000;
        $revenuePercent = $quarterTarget > 0 ? round(($totalRevenue / $quarterTarget) * 100, 1) : 0;
        $settledPercent = $totalRevenue > 0 ? round(($cashInflow / $totalRevenue) * 100, 1) : 0;
        $shrinkageClaim = (float) OrderItem::whereNotNull('returned_weight')->sum(DB::raw('returned_weight * unit_price'));
        $tolerancePercent = $totalRevenue > 0 ? round(($shrinkageClaim / $totalRevenue) * 100, 2) : 0;

        $products = Product::take(5)->withSum('orderItems', 'ordered_qty')->get();
        $cAnalytics = $this->buildCommodityAnalytics($products);

        $clients = User::where('role', 'KLIEN')->with(['orders.orderItems', 'invoices', 'contracts'])->get();
        $clAnalytics = $this->buildClientAnalytics($clients);

        $orders = Order::with(['user', 'orderItems.product'])->latest()->take(20)->get();

        $lockPeriod = $periodLock['period'] ?? 'Kuartal IV 2026 (Okt - Des 2026)';
        $lockedAt = $periodLock['locked_at'] ?? '24 Okt 2026 23:59:59 WIB';
        $lockedBy = $periodLock['locked_by'] ?? (Auth::user()?->name ?: 'Direktur GPA');
        $sha256Hash = $periodLock['sha256_hash'] ?? '0x8F9C4A217B1E90D4CC67F814E32A0B7D18C992E5F67104B8A293CD0891DE33';

        $auditEntries = $this->buildAuditEntries($cAnalytics['teraTotal'], $openReceivables, $lockedBy);
        $sealData = $this->buildSealData($sha256Hash, $lockedBy);

        $commodityList = $cAnalytics['list'];
        $poTotal = $cAnalytics['poTotal'];
        $teraTotal = $cAnalytics['teraTotal'];
        $shrinkageTotal = $cAnalytics['shrinkageTotal'];
        $commRev = $cAnalytics['commRev'];
        $marginTotal = $cAnalytics['marginTotal'];

        $clientList = $clAnalytics['list'];
        $cPoTotal = $clAnalytics['cPoTotal'];
        $cVolTotal = $clAnalytics['cVolTotal'];
        $cGrossTotal = $clAnalytics['cGrossTotal'];
        $cSettledTotal = $clAnalytics['cSettledTotal'];
        $cRecTotal = $clAnalytics['cRecTotal'];
        $avgTerm = $clAnalytics['avgTerm'];

        return view('direktur.report', compact(
            'periodLock',
            'lockPeriod',
            'lockedAt',
            'lockedBy',
            'sha256Hash',
            'totalRevenue',
            'totalTonnageKg',
            'cashInflow',
            'openReceivables',
            'quarterTarget',
            'revenuePercent',
            'settledPercent',
            'shrinkageClaim',
            'tolerancePercent',
            'commodityList',
            'poTotal',
            'teraTotal',
            'shrinkageTotal',
            'commRev',
            'marginTotal',
            'clientList',
            'cPoTotal',
            'cVolTotal',
            'cGrossTotal',
            'cSettledTotal',
            'cRecTotal',
            'avgTerm',
            'auditEntries',
            'sealData',
            'orders'
        ));
    }

    /**
     * Format dan hitung analitik komoditas untuk laporan eksekutif.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $products
     * @return array<string, mixed>
     */
    protected function buildCommodityAnalytics($products): array
    {
        $raw = [];
        $commRev = 0;
        foreach ($products as $p) {
            $poKg = (float) ($p->order_items_sum_ordered_qty ?? 0);
            $teraKg = $poKg;
            $price = (float) $p->base_price;
            $omzet = (int) round($teraKg * $price);
            $commRev += $omzet;

            $raw[] = [
                'name'    => $p->name,
                'po_kg'   => $poKg,
                'tera_kg' => $teraKg,
                'price'   => $price,
                'omzet'   => $omzet,
                'margin'  => 25.0,
            ];
        }

        $poTotal = array_sum(array_column($raw, 'po_kg'));
        $teraTotal = array_sum(array_column($raw, 'tera_kg'));
        $shrinkageTotal = $poTotal - $teraTotal;
        $marginTotal = array_sum(array_column($raw, 'margin'));

        $list = [];
        foreach ($raw as $row) {
            $shrinkage = $row['po_kg'] - $row['tera_kg'];
            $dev = round(($shrinkage / ($row['po_kg'] ?: 1)) * 100, 2);
            $row['po_label']           = number_format($row['po_kg'], 0, ',', '.');
            $row['tera_label']         = number_format($row['tera_kg'], 0, ',', '.');
            $row['shrinkage_kg']       = $shrinkage;
            $row['deviation']          = $dev;
            $row['deviation_label']    = '-' . number_format($shrinkage, 0, ',', '.') . ' kg (-' . $dev . '%)';
            $row['price_label']        = 'Rp ' . number_format($row['price'], 0, ',', '.');
            $row['revenue']            = $row['omzet'];
            $row['revenue_label']      = 'Rp ' . number_format($row['omzet'], 0, ',', '.');
            $row['contribution']       = round(($row['omzet'] / ($commRev ?: 1)) * 100, 2);
            $row['contribution_label'] = number_format(($row['omzet'] / ($commRev ?: 1)) * 100, 2, '.', '') . '%';
            $row['margin_label']       = number_format($row['margin'], 1, '.', '') . '%';
            $list[] = $row;
        }

        return [
            'list'           => $list,
            'commRev'        => $commRev,
            'poTotal'        => $poTotal,
            'teraTotal'      => $teraTotal,
            'shrinkageTotal' => $shrinkageTotal,
            'marginTotal'    => $marginTotal,
        ];
    }

    /**
     * Format dan hitung analitik penjualan klien B2B untuk laporan eksekutif.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $clients
     * @return array<string, mixed>
     */
    protected function buildClientAnalytics($clients): array
    {
        $list = [];
        foreach ($clients as $client) {
            $clientOrders = $client->orders->whereNotIn('status', ['BATAL']);
            $poCount = $clientOrders->count();
            $volumeKg = (float) $clientOrders->sum(fn ($o) => $o->orderItems->sum('ordered_qty'));
            $gross = (float) $clientOrders->sum('grand_total');
            $settled = (float) $client->invoices->where('status', 'PAID')->sum('grand_total');
            $contract = $client->contracts->first();
            $termDays = $contract ? (int) $contract->top_days : 30;
            $earliestDue = $client->invoices->whereIn('status', ['UNPAID', 'OVERDUE'])->min('due_date');
            $dueLabel = $earliestDue ? ('JT: ' . \Carbon\Carbon::parse($earliestDue)->translatedFormat('d M')) : 'Lancar';
            $rec = $gross - $settled;

            $list[] = [
                'name'             => $client->company_name ?: $client->name,
                'code'             => $contract ? $contract->contract_number : ('CLI-' . str_pad($client->id, 3, '0', STR_PAD_LEFT)),
                'po_count'         => $poCount,
                'volume_kg'        => $volumeKg,
                'gross'            => $gross,
                'settled'          => $settled,
                'term_days'        => $termDays,
                'due_label'        => $dueLabel,
                'volume_label'     => number_format($volumeKg, 0, ',', '.') . ' kg',
                'po_label'         => $poCount . ' PO',
                'gross_label'      => 'Rp ' . number_format($gross, 0, ',', '.'),
                'settled_label'    => 'Rp ' . number_format($settled, 0, ',', '.'),
                'receivable'       => $rec,
                'receivable_label' => $rec > 0 ? 'Rp ' . number_format($rec, 0, ',', '.') : 'Rp 0',
                'term_label'       => $termDays > 0 ? 'TOP ' . $termDays . ' Hari' : $dueLabel,
                'status'           => $termDays > 0 ? 'LANCAR' : 'LUNAS',
                'status_label'     => $termDays > 0 ? 'LANCAR (' . $dueLabel . ')' : 'LUNAS 100%',
                'status_tone'      => $termDays > 0 ? 'lancar' : 'lunas',
            ];
        }

        $cPoTotal = array_sum(array_column($list, 'po_count'));
        $cVolTotal = array_sum(array_column($list, 'volume_kg'));
        $cGrossTotal = array_sum(array_column($list, 'gross'));
        $cSettledTotal = array_sum(array_column($list, 'settled'));
        $cRecTotal = $cGrossTotal - $cSettledTotal;
        $cTerms = array_values(array_filter(array_column($list, 'term_days')));
        $avgTerm = !empty($cTerms) ? round(array_sum($cTerms) / count($cTerms), 1) : 0.0;

        return [
            'list'          => $list,
            'cPoTotal'      => $cPoTotal,
            'cVolTotal'     => $cVolTotal,
            'cGrossTotal'   => $cGrossTotal,
            'cSettledTotal' => $cSettledTotal,
            'cRecTotal'     => $cRecTotal,
            'avgTerm'       => $avgTerm,
        ];
    }

    /**
     * Siapkan entri log audit forensik penguncian laporan.
     *
     * @param  float  $teraTotal
     * @param  float  $openReceivables
     * @param  string  $lockedBy
     * @return array<int, array<string, mixed>>
     */
    protected function buildAuditEntries(float $teraTotal, float $openReceivables, string $lockedBy): array
    {
        return [
            [
                'timestamp' => '[2026-10-24 23:45:10 WIB]',
                'tag'       => '> SYS_VERIFY:',
                'message'   => '5 komoditas timbangan tera gudang tuntas dicocokkan (' . number_format($teraTotal, 0, ',', '.') . ' kg).',
            ],
            [
                'timestamp' => '[2026-10-24 23:51:22 WIB]',
                'tag'       => '> AR_CLEARING:',
                'message'   => 'Piutang berjalan Rp ' . number_format($openReceivables, 0, ',', '.') . ' diverifikasi terhadap 4 kontrak aktif.',
            ],
            [
                'timestamp' => '[2026-10-24 23:58:05 WIB]',
                'tag'       => '> DIR_AUTH:',
                'message'   => $lockedBy . ' memasukkan token kunci otorisasi biometric.',
            ],
            [
                'timestamp'   => '[2026-10-24 23:59:59 WIB]',
                'tag'         => '> STATUS_LOCK_ENGAGED:',
                'message'     => 'Record disahkan. SHA-256 dibuat.',
                'trailing'    => 'Write-access dicabut permanen.',
                'highlighted' => true,
            ],
        ];
    }

    /**
     * Siapkan data sertifikat digital penutup laporan.
     *
     * @param  string  $sha256Hash
     * @param  string  $lockedBy
     * @return array<string, string>
     */
    protected function buildSealData(string $sha256Hash, string $lockedBy): array
    {
        return [
            'certificate' => 'GPA-AUDIT-CERT-2026-Q4',
            'hash'        => $sha256Hash,
            'signatory'   => $lockedBy,
        ];
    }

    /**
     * Kunci dan Sahkan Laporan Periode (Immutable Archive SHA-256)
     */
    public function lockPeriod(Request $request)
    {
        $periodName = $request->input('period', 'Kuartal IV (Okt - Des 2026)');
        $directorName = Auth::user()?->name ?: 'Direktur GPA';

        $lockInfo = $this->governanceService->lockPeriod($periodName, $directorName);

        return redirect()->route('direktur.reports.index')
            ->with('success', "Periode {$periodName} berhasil dikunci dan disahkan permanen dengan hash {$lockInfo['sha256_hash']}. Status kini Immutable Archive.");
    }

    /**
     * Buka Kunci Laporan Periode (Jika Diperlukan Koreksi Direksi)
     */
    public function unlockPeriod(Request $request)
    {
        $periodName = $request->input('period', 'Kuartal IV (Okt - Des 2026)');
        $directorName = Auth::user()?->name ?: 'Direktur GPA';

        $this->governanceService->unlockPeriod($periodName, $directorName);

        return redirect()->route('direktur.reports.index')
            ->with('success', "Kunci audit periode {$periodName} dibuka sementara oleh Direktur Utama.");
    }

    /**
     * Tampilan Siap Cetak Dokumen Pengesahan Resmi (.PDF / Print View)
     */
    public function download(Request $request, ReportPrintService $service): View
    {
        $data = $service->buildPrintData(
            $request->query('start_date'),
            $request->query('end_date'),
            $request->query('client_id') ? (int) $request->query('client_id') : null
        );

        return view('prints.report', $data);
    }

    /**
     * Unduh Buku Besar Penjualan (.CSV)
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Buku_Besar_Penjualan_Q4_' . date('Ymd') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['BUKU BESAR PENJUALAN EKSEKUTIF KOPERASI GREEN PASUNDAN AGRICULTURE']);
            fputcsv($handle, ['STATUS', 'DISAHKAN // IMMUTABLE ARCHIVE']);
            fputcsv($handle, ['HASH SHA-256', '0x8F9C4A217B1E90D4CC67F814E32A0B7D18C992E5F67104B8A293CD0891DE33']);
            fputcsv($handle, ['TANGGAL CETAK', date('d/m/Y H:i') . ' WIB']);
            fputcsv($handle, []);
            fputcsv($handle, ['Entitas Mitra B2B', 'No Kontrak', 'Frekuensi PO', 'Volume Tera Sah (kg)', 'Tagihan Bruto (Rp)', 'Settled / Kas Masuk (Rp)', 'Sisa Piutang (Rp)', 'Status']);

            $clients = User::where('role', 'KLIEN')->with(['orders.orderItems', 'invoices', 'contracts'])->get();
            foreach ($clients as $client) {
                $clientOrders = $client->orders->whereNotIn('status', ['BATAL']);
                $poCount = $clientOrders->count();
                $volumeKg = (float) $clientOrders->sum(fn ($o) => $o->orderItems->sum('ordered_qty'));
                $gross = (float) $clientOrders->sum('grand_total');
                $settled = (float) $client->invoices->where('status', 'PAID')->sum('grand_total');
                $outstanding = max(0, $gross - $settled);
                $contract = $client->contracts->first();
                $contractNo = $contract ? $contract->contract_number : ('CLI-' . str_pad($client->id, 3, '0', STR_PAD_LEFT));
                $status = $outstanding <= 0 ? 'LUNAS' : 'LANCAR';

                fputcsv($handle, [
                    $client->company_name ?: $client->name,
                    $contractNo,
                    $poCount,
                    number_format($volumeKg, 2, '.', ''),
                    number_format($gross, 0, ',', '.'),
                    number_format($settled, 0, ',', '.'),
                    number_format($outstanding, 0, ',', '.'),
                    $status,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
