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
     * @return array<string, mixed>
     */
    public function buildPrintData(?string $startDate = null, ?string $endDate = null, ?int $clientFilter = null): array
    {
        $startDate = $startDate ?: now()->subMonths(3)->startOfMonth()->toDateString();
        $endDate   = $endDate ?: now()->toDateString();
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

        $periodName = 'Kuartal IV (Okt - Des 2026)';
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
