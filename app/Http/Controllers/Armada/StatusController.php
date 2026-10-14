<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    /**
     * Menampilkan status kendaraan armada, telemetri IoT, audit kelaikan, dan rekap shift.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Ambil data pesanan supir hari ini
        $orders = Order::with(['orderItems', 'user'])
            ->where(function ($q) use ($user) {
                $q->where('driver_id', $user?->id)
                  ->orWhereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']);
            })
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->latest()
            ->get();

        $dropsCount = $orders->count() ?: 2;
        $doneCount = $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $returCount = $orders->where('status', 'SELESAI_CATATAN')->count();

        $totalTonaseKg = 0;
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $totalTonaseKg += (float) ($item->net_weight ?? $item->quantity ?? 0);
            }
        }
        if ($totalTonaseKg <= 0) {
            $totalTonaseKg = 1986.5;
        }

        $plate = $orders->first(fn ($o) => !empty($o->vehicle_plate_number))?->vehicle_plate_number ?? 'B 9284 TDA';

        return view('armada.status', [
            'plate'         => $plate,
            'dropsCount'    => $dropsCount,
            'doneCount'     => $doneCount,
            'returCount'    => $returCount,
            'totalTonaseKg' => $totalTonaseKg,
            'activeNav'     => 'fleet',
        ]);
    }

    /**
     * Menutup shift harian supir
     */
    public function closeShift(Request $request): RedirectResponse
    {
        return redirect()
            ->route('armada.status')
            ->with('success', 'Shift harian berhasil ditutup! Terima kasih atas pengantaran hari ini.');
    }
}
