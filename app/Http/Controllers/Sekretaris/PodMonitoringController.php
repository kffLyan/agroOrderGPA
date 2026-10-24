<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        // Metrik Ringkasan
        $allDeliveries = Order::where(function ($q) {
            $q->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'DALAM_PENGIRIMAN', 'SIAP_KIRIM'])
              ->orWhereNotNull('pod_photo_url');
        })->get();

        $totalCompleted = $allDeliveries->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $cleanCount = $allDeliveries->where('status', 'SELESAI')->count();
        $discrepancyCount = $allDeliveries->where('status', 'SELESAI_CATATAN')->count();
        $inTransitCount = $allDeliveries->where('status', 'DALAM_PENGIRIMAN')->count();
        $readyCount = $allDeliveries->where('status', 'SIAP_KIRIM')->count();

        // Total retur kg
        $totalReturnKg = 0;
        foreach ($allDeliveries->where('status', 'SELESAI_CATATAN') as $ord) {
            foreach ($ord->orderItems as $item) {
                $totalReturnKg += (float) ($item->returned_weight ?? 0);
            }
        }

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
