<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ArmadaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispatchController extends Controller
{
    public function __construct(
        protected ArmadaService $armadaService
    ) {}

    /**
     * Menampilkan Surat Jalan Digital & Rincian Muatan Supir
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $targetOrderId = $request->query('order_id') ? (int) $request->query('order_id') : null;

        $currentOrder = $this->armadaService->resolveCurrentDeliveryOrder($targetOrderId, $user);

        // Ambil daftar rute / multi-stops dari pesanan yang aktif hari ini
        $activeOrders = Order::with(['user', 'orderItems.product'])
            ->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])
            ->latest()
            ->take(3)
            ->get();

        if ($activeOrders->isEmpty() && $currentOrder) {
            $activeOrders = collect([$currentOrder]);
        }

        $plate = $currentOrder?->vehicle_plate_number ?: '—';

        return view('armada.dispatch', [
            'currentOrder' => $currentOrder,
            'activeOrders' => $activeOrders,
            'plate'        => $plate,
            'activeNav'    => 'dispatch',
        ]);
    }

    /**
     * Memulai pengiriman armada (mengubah status menjadi DALAM_PENGIRIMAN)
     */
    public function startRoute(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();

        if ($order->status !== 'SELESAI') {
            $order->status = 'DALAM_PENGIRIMAN';
            if (!$order->departure_time) {
                $order->departure_time = now();
            }
            if (!$order->driver_id && $user) {
                $order->driver_id = $user->id;
            }
            $order->save();
        }

        return redirect()
            ->route('armada.dispatch', ['order_id' => $order->id])
            ->with('success', 'Pengiriman telah dimulai! Rute pengantaran menuju lokasi penerima dibuka.');
    }
}
