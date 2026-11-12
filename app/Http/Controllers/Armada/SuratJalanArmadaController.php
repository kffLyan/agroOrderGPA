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

        // Validasi keamanan: cegah supir lain membuka surat jalan pesanan milik supir lain via query param
        if ($targetOrderId) {
            $checkOrder = Order::find($targetOrderId);
            if ($checkOrder && $checkOrder->driver_id && $checkOrder->driver_id !== $user->id) {
                abort(403, 'Akses Ditolak: Surat jalan ini ditugaskan kepada supir armada lain.');
            }
        }

        $currentOrder = $this->armadaService->resolveCurrentDeliveryOrder($targetOrderId, $user);

        // Mengambil daftar rute pengantaran aktif supir yang bersangkutan (terisolasi per supir)
        $activeOrders = Order::with(['user', 'orderItems.product'])
            ->where('driver_id', $user->id)
            ->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])
            ->latest('updated_at')
            ->take(5)
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

        $request->validate([
            'vehicle_plate_number' => ['nullable', 'string', 'max:20'],
        ]);

        if ($order->status !== 'SELESAI') {
            $order->status = 'DALAM_PENGIRIMAN';
            if (!$order->departure_time) {
                $order->departure_time = now();
            }
            if (!$order->driver_id && $user) {
                $order->driver_id = $user->id;
            }
            if ($request->filled('vehicle_plate_number')) {
                $order->vehicle_plate_number = strtoupper(trim((string) $request->input('vehicle_plate_number')));
            } elseif (!$order->vehicle_plate_number) {
                $order->vehicle_plate_number = 'D 8192 YZ';
            }
            $order->save();
        }

        return redirect()
            ->route('armada.surat-jalan', ['order_id' => $order->id])
            ->with('success', 'Pengiriman telah dimulai! Rute pengantaran menuju lokasi penerima dibuka dengan armada ' . ($order->vehicle_plate_number ?: 'Truk Chiller GPA') . '.');
    }

    // Memperbarui nomor pelat kendaraan armada pada surat jalan pesanan
    public function updatePlate(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();

        if ($order->driver_id && $order->driver_id !== $user->id) {
            abort(403, 'Akses Ditolak: Pesanan ini ditugaskan kepada supir armada lain.');
        }

        $validated = $request->validate([
            'vehicle_plate_number' => ['required', 'string', 'max:20'],
        ]);

        $order->vehicle_plate_number = strtoupper(trim($validated['vehicle_plate_number']));
        $order->save();

        return back()->with('success', "Nomor pelat armada untuk pesanan #{$order->order_number} berhasil diperbarui menjadi {$order->vehicle_plate_number}.");
    }
}
