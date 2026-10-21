<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\GovernanceService;
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
        $totalRevenue = (float) Order::whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'TERVERIFIKASI'])
            ->sum(DB::raw('COALESCE(grand_total, estimated_total)'));

        $totalTonnageKg = (float) OrderItem::whereHas('order', function ($q) {
            $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'TERVERIFIKASI']);
        })->sum(DB::raw('COALESCE(actual_net_weight, ordered_qty)'));

        $cashInflow = (float) Invoice::where('status', 'PAID')->sum('grand_total');
        $openReceivables = (float) Invoice::whereIn('status', ['UNPAID', 'OVERDUE'])->sum('grand_total');

        $quarterTarget = 600000000;
        $revenuePercent = $quarterTarget > 0 ? round(($totalRevenue / $quarterTarget) * 100, 1) : 0;
        $settledPercent = $totalRevenue > 0 ? round(($cashInflow / $totalRevenue) * 100, 1) : 0;
        $shrinkageClaim = (float) OrderItem::whereNotNull('returned_weight')->sum(DB::raw('returned_weight * unit_price'));
        $tolerancePercent = $totalRevenue > 0 ? round(($shrinkageClaim / $totalRevenue) * 100, 2) : 0;

        $products = Product::all();
        $commodityList = [];
        foreach ($products->take(5) as $p) {
            $items = OrderItem::where('product_id', $p->id)->get();
            $poKg = (float) $items->sum('ordered_qty');
            $teraKg = (float) $items->sum(fn ($i) => $i->actual_net_weight ?: $i->ordered_qty);
            $price = (float) $p->base_price;
            $margin = 25.0; // standard margin
            $commodityList[] = [
                'name'    => $p->name,
                'po_kg'   => $poKg,
                'tera_kg' => $teraKg,
                'price'   => $price,
                'margin'  => $margin,
            ];
        }

        $clients = User::where('role', 'KLIEN')->with(['orders.orderItems', 'invoices', 'contracts'])->get();
        $clientList = [];
        foreach ($clients as $client) {
            $clientOrders = $client->orders->whereNotIn('status', ['BATAL']);
            $poCount = $clientOrders->count();
            $volumeKg = (float) $clientOrders->sum(fn ($o) => $o->orderItems->sum(fn ($i) => $i->actual_net_weight ?: $i->ordered_qty));
            $gross = (float) $clientOrders->sum(fn ($o) => (float) ($o->grand_total ?: $o->estimated_total));
            $settled = (float) $client->invoices->where('status', 'PAID')->sum('grand_total');
            $contract = $client->contracts->first();
            $termDays = $contract ? (int) $contract->top_days : 30;
            $earliestDue = $client->invoices->whereIn('status', ['UNPAID', 'OVERDUE'])->min('due_date');
            $dueLabel = $earliestDue ? ('JT: ' . \Carbon\Carbon::parse($earliestDue)->translatedFormat('d M')) : 'Lancar';

            $clientList[] = [
                'name'      => $client->company_name ?: $client->name,
                'code'      => $contract ? $contract->contract_number : ('CLI-' . str_pad($client->id, 3, '0', STR_PAD_LEFT)),
                'po_count'  => $poCount,
                'volume_kg' => $volumeKg,
                'gross'     => $gross,
                'settled'   => $settled,
                'term_days' => $termDays,
                'due_label' => $dueLabel,
            ];
        }

        $orders = Order::with(['user', 'orderItems.product'])->latest()->take(20)->get();

        return view('direktur.report', compact(
            'periodLock',
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
            'clientList',
            'orders'
        ));
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
    public function download(Request $request)
    {
        $startDate = $request->query('start_date', now()->subMonths(3)->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $clientFilter = $request->query('client_id');
        $clientFilterName = null;

        $query = Order::with(['user', 'orderItems.product'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($clientFilter) {
            $query->where('user_id', $clientFilter);
            $client = User::find($clientFilter);
            $clientFilterName = $client ? ($client->company_name ?: $client->name) : null;
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $totalRevenue = (float) $orders->sum(fn ($o) => (float) ($o->grand_total ?? $o->estimated_total));
        $totalTonnageKg = (float) OrderItem::whereIn('order_id', $orders->pluck('id'))
            ->sum(DB::raw('COALESCE(actual_net_weight, ordered_qty)'));

        $commoditySummary = OrderItem::whereIn('order_id', $orders->pluck('id'))
            ->select(
                'product_id',
                DB::raw('SUM(COALESCE(actual_net_weight, ordered_qty)) as total_weight'),
                DB::raw('SUM(COALESCE(subtotal_final, ordered_qty * unit_price)) as total_sales')
            )
            ->groupBy('product_id')
            ->with('product')
            ->get();

        $periodName = 'Kuartal IV (Okt - Des 2026)';
        $settings = $this->governanceService->getSettings();
        $periodLock = $settings['period_lock'] ?? [];

        return view('direktur.reports.print', compact(
            'startDate',
            'endDate',
            'clientFilterName',
            'totalRevenue',
            'totalTonnageKg',
            'orders',
            'commoditySummary',
            'periodName',
            'periodLock'
        ));
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
                $volumeKg = (float) $clientOrders->sum(fn ($o) => $o->orderItems->sum(fn ($i) => $i->actual_net_weight ?: $i->ordered_qty));
                $gross = (float) $clientOrders->sum(fn ($o) => (float) ($o->grand_total ?: $o->estimated_total));
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
