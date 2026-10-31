<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportPrintService
{
    public function __construct(
        protected GovernanceService $governanceService
    ) {}

    /**
     * Membangun payload cetak Laporan Resmi Eksekutif Penjualan GPA A4.
     *
     * @param  string|null  $startDate
     * @param  string|null  $endDate
     * @param  int|null  $clientFilter
     * @param  string|null  $period
     * @return array<string, mixed>
     */
    public function buildPrintData(?string $startDate = null, ?string $endDate = null, ?int $clientFilter = null, ?string $period = null): array
    {
        $now = now();
        $periodName = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';

        if ($startDate && $endDate) {
            $periodName = 'Periode: ' . date('d F Y', strtotime($startDate)) . ' s.d. ' . date('d F Y', strtotime($endDate));
        } elseif ($period) {
            switch ($period) {
                case 'hari_ini':
                    $startDate = (clone $now)->startOfDay()->toDateString();
                    $endDate   = (clone $now)->endOfDay()->toDateString();
                    $periodName = 'Hari Ini (' . $now->translatedFormat('d F Y') . ')';
                    break;
                case 'minggu_ini':
                    $startDate = (clone $now)->startOfWeek()->toDateString();
                    $endDate   = (clone $now)->endOfWeek()->toDateString();
                    $periodName = 'Minggu Ini (' . (clone $now)->startOfWeek()->translatedFormat('d M') . ' - ' . (clone $now)->endOfWeek()->translatedFormat('d M Y') . ')';
                    break;
                case 'tahun_ini':
                    $startDate = (clone $now)->startOfYear()->toDateString();
                    $endDate   = (clone $now)->endOfYear()->toDateString();
                    $periodName = 'Tahun Ini (' . $now->translatedFormat('Y') . ')';
                    break;
                case 'semua':
                    $startDate = null;
                    $endDate   = null;
                    $periodName = 'Semua Periode Transaksi';
                    break;
                case 'bulan_ini':
                default:
                    $startDate = (clone $now)->startOfMonth()->toDateString();
                    $endDate   = (clone $now)->endOfMonth()->toDateString();
                    $periodName = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
                    break;
            }
        } else {
            // Default periode bulan berjalan
            $startDate = (clone $now)->startOfMonth()->toDateString();
            $endDate   = (clone $now)->endOfMonth()->toDateString();
            $periodName = 'Bulan Ini (' . $now->translatedFormat('F Y') . ')';
        }

        $clientFilterName = null;

        $query = Order::with(['user', 'orderItems.product'])
            ->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'LUNAS']);

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($clientFilter) {
            $query->where('user_id', $clientFilter);
            $client = User::find($clientFilter);
            $clientFilterName = $client ? ($client->company_name ?: $client->name) : null;
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $totalRevenue = (float) $orders->sum('grand_total');
        $totalTonnageKg = (float) OrderItem::whereIn('order_id', $orders->pluck('id'))
            ->sum('ordered_qty');

        $commoditySummary = OrderItem::whereIn('order_id', $orders->pluck('id'))
            ->select(
                'product_id',
                DB::raw('SUM(ordered_qty) as total_weight'),
                DB::raw('SUM(subtotal) as total_sales')
            )
            ->groupBy('product_id')
            ->with('product')
            ->get();

        $settings = $this->governanceService->getSettings();
        $periodLock = $settings['period_lock'] ?? [];

        return compact(
            'startDate',
            'endDate',
            'clientFilterName',
            'totalRevenue',
            'totalTonnageKg',
            'orders',
            'commoditySummary',
            'periodName',
            'periodLock'
        );
    }
}
