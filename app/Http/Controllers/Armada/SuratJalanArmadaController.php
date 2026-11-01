<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ArmadaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller surat jalan digital dan rincian muatan untuk Armada
class SuratJalanArmadaController extends Controller
{
    public function __construct(
        protected ArmadaService $armadaService
    ) {}

    // Menampilkan lembar digital surat jalan, manifest muatan sayur, dan multi-drop lokasi tujuan supir
    public function index(Request $request): View
    {
        $user = Auth::user();
        $targetOrderId = $request->query('order_id') ? (int) $request->query('order_id') : null;

        $currentOrder = $this->armadaService->resolveCurrentDeliveryOrder($targetOrderId, $user);

        // Mengambil daftar rute pengantaran aktif hari ini untuk panduan multi-tujuan supir
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

    // Memulai rute pengiriman armada dan mencatat waktu keberangkatan (departure time) dari sentra
    public function startRoute(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();

        // Mencegah akses tidak sah (Anti-IDOR) jika pesanan telah ditugaskan kepada supir armada lain
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
