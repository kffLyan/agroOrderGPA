<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ArmadaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller Surat Jalan Digital & Rincian Muatan Supir Armada.
 *
 * Penjelasan UKK:
 * Modul ini menampilkan lembar Surat Jalan digital bagi supir armada berpendingin:
 * 1. Rincian muatan komoditas, berat timbang, dan dock penerima.
 * 2. Tombol mulai jalan: mengubah status pesanan menjadi DALAM_PENGIRIMAN
 *    dan mencatat waktu keberangkatan armada (departure time).
 */
class SuratJalanArmadaController extends Controller
{
    public function __construct(
        protected ArmadaService $armadaService
    ) {}

    /**
     * Menampilkan Surat Jalan Digital & Rincian Muatan Supir.
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

        return view('armada.surat-jalan', [
            'currentOrder' => $currentOrder,
            'activeOrders' => $activeOrders,
            'plate'        => $plate,
            'activeNav'    => 'dispatch',
        ]);
    }

    /**
     * Memulai pengiriman armada (mengubah status menjadi DALAM_PENGIRIMAN).
     */
    public function startRoute(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();

        // Anti-IDOR: Jika pesanan sudah ditugaskan ke supir lain, tolak aksesnya
        if ($order->driver_id && $order->driver_id !== $user->id) {
            abort(403, 'Akses Ditolak: Pesanan ini ditugaskan kepada supir armada lain.');
        }

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
            ->route('armada.surat-jalan', ['order_id' => $order->id])
            ->with('success', 'Pengiriman telah dimulai! Rute pengantaran menuju lokasi penerima dibuka.');
    }
}
