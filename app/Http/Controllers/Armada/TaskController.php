<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Menampilkan daftar tugas harian dan antrean titik pengantaran armada.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Cari pesanan yang ditugaskan ke supir ini
        $orders = Order::with(['orderItems.product', 'user', 'driver'])
            ->where(function ($query) use ($user) {
                $query->where('driver_id', $user?->id)
                    ->orWhereIn('status', ['DALAM_PENGIRIMAN', 'SIAP_KIRIM']);
            })
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->latest()
            ->get();

        // Jika belum ada pesanan aktif sama sekali, ambil pesanan terverifikasi terbaru untuk ditampilkan
        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver'])
                ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])
                ->latest()
                ->take(5)
                ->get();
        }

        // Hitung filter status
        $readyCount = $orders->where('status', 'SIAP_KIRIM')->count();
        $roadCount  = $orders->where('status', 'DALAM_PENGIRIMAN')->count();
        $doneCount  = $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $allCount   = $orders->count();

        // Hitung total muatan dari pesanan aktif
        $totalWeightKg = 0;
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $totalWeightKg += (float) ($item->actual_net_weight ?: $item->ordered_qty ?: 0);
            }
        }

        $activePlate = $orders->first(fn ($o) => !empty($o->vehicle_plate_number))?->vehicle_plate_number ?? '—';

        return view('armada.tasks', [
            'orders'        => $orders,
            'readyCount'    => $readyCount,
            'roadCount'     => $roadCount,
            'doneCount'     => $doneCount,
            'allCount'      => $allCount,
            'totalWeightKg' => $totalWeightKg,
            'activePlate'   => $activePlate,
            'activeNav'     => 'tasks',
        ]);
    }
}
