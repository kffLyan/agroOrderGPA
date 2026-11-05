<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Services\ArmadaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller daftar tugas harian dan rute pengantaran untuk Armada
class TugasController extends Controller
{
    public function __construct(
        protected ArmadaService $armadaService
    ) {}

    // Menampilkan daftar penugasan muatan logistik, antrean pengiriman, dan status pengantaran supir
    public function index(Request $request): View
    {
        $orders = $this->armadaService->getActiveOrdersForDriver(Auth::user());

        return view('armada.tugas', [
            'orders'        => $orders,
            'readyCount'    => $orders->where('status', 'SIAP_KIRIM')->count(),
            'roadCount'     => $orders->where('status', 'DALAM_PENGIRIMAN')->count(),
            'doneCount'     => $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count(),
            'allCount'      => $orders->count(),
            'totalWeightKg' => $this->armadaService->calculateTotalWeightKg($orders),
            'activePlate'   => $this->armadaService->resolveVehiclePlate($orders),
            'activeNav'     => 'tasks',
        ]);
    }
}
