<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

// Controller pemantauan bukti serah terima (PoD) untuk Sekretaris
class MonitoringPodController extends Controller
{
    // Menampilkan konsol pemantauan serah terima logistik (PoD), tanda tangan digital, foto serah terima, dan klaim retur barang
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'all');
        $driverFilter = $request->query('driver_id', 'all');
        $search       = trim($request->query('search', ''));

        $query = Order::with(['orderItems.product', 'user', 'driver', 'verifier', 'invoice'])
            ->where(function ($subQuery) {
                $subQuery->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'DALAM_PENGIRIMAN', 'SIAP_KIRIM'])
                         ->orWhereNotNull('pod_photo_url');
            });

        // Memfilter status pesanan berdasarkan tahap pengiriman atau penyelesaian
        if ($statusFilter === 'clean') {
            $query->where('status', 'SELESAI');
        } elseif ($statusFilter === 'discrepancy') {
            $query->where('status', 'SELESAI_CATATAN');
        } elseif ($statusFilter === 'in_transit') {
            $query->where('status', 'DALAM_PENGIRIMAN');
        } elseif ($statusFilter === 'ready') {
            $query->where('status', 'SIAP_KIRIM');
        }

        // Memfilter data berdasarkan supir armada logistik yang ditugaskan
        if ($driverFilter !== 'all' && is_numeric($driverFilter)) {
            $query->where('driver_id', $driverFilter);
        }

        // Pencarian multi-kolom berdasarkan nomor pesanan, surat jalan, penerima, atau plat armada
        if (!empty($search)) {
            $query->where(function ($filterQuery) use ($search) {
                $filterQuery->where('order_number', 'LIKE', "%{$search}%")
                            ->orWhere('surat_jalan_number', 'LIKE', "%{$search}%")
                            ->orWhere('received_by_name', 'LIKE', "%{$search}%")
                            ->orWhere('vehicle_plate_number', 'LIKE', "%{$search}%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'LIKE', "%{$search}%")
                                          ->orWhere('company_name', 'LIKE', "%{$search}%")
                                          ->orWhere('phone', 'LIKE', "%{$search}%")
                                          ->orWhere('pic_name', 'LIKE', "%{$search}%")
                                          ->orWhere('pic_phone', 'LIKE', "%{$search}%");
                            })
                            ->orWhereHas('driver', function ($driverQuery) use ($search) {
                                $driverQuery->where('name', 'LIKE', "%{$search}%");
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
            ->paginate(10)
            ->withQueryString();

        // Menghitung metrik ringkasan pengiriman (bersih, komplain retur, dalam rute) secara efisien
        $baseMetricsQuery = Order::where(function ($metricQuery) {
            $metricQuery->whereIn('status', ['SELESAI', 'SELESAI_CATATAN', 'DALAM_PENGIRIMAN', 'SIAP_KIRIM'])
                        ->orWhereNotNull('pod_photo_url');
        });

        $cleanCount       = (clone $baseMetricsQuery)->where('status', 'SELESAI')->count();
        $discrepancyCount = (clone $baseMetricsQuery)->where('status', 'SELESAI_CATATAN')->count();
        $totalCompleted   = $cleanCount + $discrepancyCount;
        $inTransitCount   = (clone $baseMetricsQuery)->where('status', 'DALAM_PENGIRIMAN')->count();
        $readyCount       = (clone $baseMetricsQuery)->where('status', 'SIAP_KIRIM')->count();

        // Mengakumulasi total bobot komoditas yang diretur atau diklaim rusak saat serah terima di lokasi
        $totalReturnKg = (float) OrderItem::whereHas('order', function ($orderQuery) {
            $orderQuery->where('status', 'SELESAI_CATATAN');
        })->sum('returned_weight');

        // Mengambil daftar supir armada yang aktif untuk opsi dropdown penyaringan
        $drivers = User::where('role', 'ARMADA')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('sekretaris.monitoring-pod', [
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
