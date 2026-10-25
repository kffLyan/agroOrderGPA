<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PodMonitoringController extends Controller
{
    /**
     * Menampilkan konsol pemantauan Bukti Serah Terima (Proof of Delivery / PoD)
     * dan status pengantaran armada logistik ke dock klien.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');
        $driverFilter = $request->query('driver_id', 'all');
        $search = trim($request->query('search', ''));

        $query = Order::with(['orderItems.product', 'user', 'driver', 'verifier', 'invoice'])
            ->where(function ($q) {
                $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'DALAM_PENGIRIMAN', 'SIAP_KIRIM'])
                  ->orWhereNotNull('pod_photo_url');
            });

        // Filter status
        if ($statusFilter === 'clean') {
            $query->where('status', 'SELESAI');
        } elseif ($statusFilter === 'discrepancy') {
            $query->where('status', 'SELESAI_CATATAN');
        } elseif ($statusFilter === 'in_transit') {
            $query->where('status', 'DALAM_PENGIRIMAN');
        } elseif ($statusFilter === 'ready') {
            $query->where('status', 'SIAP_KIRIM');
        }

        // Filter supir armada
        if ($driverFilter !== 'all' && is_numeric($driverFilter)) {
            $query->where('driver_id', $driverFilter);
        }

        // Pencarian teks
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('surat_jalan_number', 'LIKE', "%{$search}%")
                  ->orWhere('received_by_name', 'LIKE', "%{$search}%")
                  ->orWhere('vehicle_plate_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('company_name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('driver', function ($dq) use ($search) {
                      $dq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderByRaw("CASE 
                WHEN status IN ('SELESAI', 'SELESAI_CATATAN') THEN 1 
                WHEN status = 'DALAM_PENGIRIMAN' THEN 2 
                ELSE 3 
            END")
            ->latest('arrival_time')
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        // Metrik Ringkasan dihitung langsung via SQL agregasi efisien (anti OOM)
        $baseMetricsQuery = Order::where(function ($q) {
            $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'DALAM_PENGIRIMAN', 'SIAP_KIRIM'])
              ->orWhereNotNull('pod_photo_url');
        });

        $cleanCount       = (clone $baseMetricsQuery)->where('status', 'SELESAI')->count();
        $discrepancyCount = (clone $baseMetricsQuery)->where('status', 'SELESAI_CATATAN')->count();
        $totalCompleted   = $cleanCount + $discrepancyCount;
        $inTransitCount   = (clone $baseMetricsQuery)->where('status', 'DALAM_PENGIRIMAN')->count();
        $readyCount       = (clone $baseMetricsQuery)->where('status', 'SIAP_KIRIM')->count();

        // Total retur kg dihitung langsung dari basis data tanpa memuat seluruh model ke memori
        $totalReturnKg = (float) OrderItem::whereHas('order', function ($q) {
            $q->where('status', 'SELESAI_CATATAN');
        })->sum('returned_weight');

        // Daftar supir untuk dropdown filter
        $drivers = User::where('role', 'ARMADA')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sekretaris.pod-monitoring', [
            'orders'           => $orders,
            'drivers'          => $drivers,
            'statusFilter'     => $statusFilter,
            'driverFilter'     => $driverFilter,
            'search'           => $search,
            'totalCompleted'   => $totalCompleted,
            'cleanCount'       => $cleanCount,
            'discrepancyCount' => $discrepancyCount,
            'inTransitCount'   => $inTransitCount,
            'readyCount'       => $readyCount,
            'totalReturnKg'    => $totalReturnKg,
        ]);
    }
}
