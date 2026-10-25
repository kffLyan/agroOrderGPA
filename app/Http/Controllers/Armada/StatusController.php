<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Services\ArmadaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    public function __construct(
        protected ArmadaService $armadaService
    ) {}

    /**
     * Menampilkan status kendaraan armada, telemetri IoT, audit kelaikan, dan rekap shift.
     */
    public function index(Request $request): View
    {
        $orders = $this->armadaService->getActiveOrdersForDriver(Auth::user());

        return view('armada.status', [
            'plate'         => $this->armadaService->resolveVehiclePlate($orders),
            'dropsCount'    => $orders->count(),
            'doneCount'     => $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count(),
            'returCount'    => $orders->where('status', 'SELESAI_CATATAN')->count(),
            'totalTonaseKg' => $this->armadaService->calculateTotalWeightKg($orders),
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
