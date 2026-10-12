<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Support\CoordinatorDashboardData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan Konsol Operasional & Kesiapan Panen Koordinator Lapangan.
     */
    public function index(Request $request): View
    {
        $console = CoordinatorDashboardData::for($request->user());

        // Hitung metrik dinamis jika ada order riil di database
        $readyOrdersCount = Order::where('status', 'TERVERIFIKASI')->count();
        $weighedOrdersCount = Order::where('status', 'SIAP_KIRIM')->count();

        if ($readyOrdersCount > 0 || $weighedOrdersCount > 0) {
            foreach ($console['metrics'] as &$metric) {
                if (str_contains(strtolower($metric['label']), 'siap pack') || str_contains(strtolower($metric['label']), 'terverifikasi')) {
                    $metric['badge'] = $readyOrdersCount . ' PO Menunggu';
                }
            }
            unset($metric);
        }

        $viewName = view()->exists('koordinator.dashboard') ? 'koordinator.dashboard' : 'coordinator.dashboard';

        return view($viewName, [
            'operator' => $console['operator'],
            'heading' => $console['heading'],
            'metrics' => $console['metrics'],
            'supply' => $console['supply'],
            'packing' => $console['packing'],
            'gate' => $console['gate'],
            'footer' => $console['footer'],
        ]);
    }
}
