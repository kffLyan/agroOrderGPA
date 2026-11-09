<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ArmadaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller status kelaikan kendaraan dan shift untuk Armada
class StatusKendaraanController extends Controller
{
    public function __construct(
        protected ArmadaService $armadaService
    ) {}

    // Menampilkan status kendaraan armada, kapasitas muatan, dan rekap shift
    public function index(Request $request): View
    {
        $orders = $this->armadaService->getActiveOrdersForDriver(Auth::user());

        return view('armada.status-kendaraan', [
            'plate'         => $this->armadaService->resolveVehiclePlate($orders),
            'dropsCount'    => $orders->count(),
            'doneCount'     => $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count(),
            'returCount'    => $orders->where('status', 'SELESAI_CATATAN')->count(),
            'totalTonaseKg' => $this->armadaService->calculateTotalWeightKg($orders),
            'activeNav'     => 'fleet',
        ]);
    }

    // Memperbarui nomor pelat kendaraan armada pada seluruh pesanan aktif supir
    public function updatePlate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_plate_number' => ['required', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        $plate = strtoupper(trim($validated['vehicle_plate_number']));

        // Perbarui plat nomor pada seluruh pesanan aktif supir yang belum selesai
        Order::where('driver_id', $user->id)
            ->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN'])
            ->update(['vehicle_plate_number' => $plate]);

        return back()->with('success', "Nomor pelat kendaraan armada berhasil diperbarui menjadi {$plate}.");
    }
}
