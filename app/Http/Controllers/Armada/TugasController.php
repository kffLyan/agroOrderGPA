<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Services\ArmadaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller Daftar Tugas Harian & Antrean Titik Pengantaran Supir Armada.
 *
 * Penjelasan UKK:
 * Modul ini menampilkan dashboard tugas harian pengemudi logistik:
 * 1. Jumlah titik pengantaran (Siap Kirim, Dalam Perjalanan, Selesai).
 * 2. Total muatan tonase sayur (Kg) yang diangkut pada shift tersebut.
 * 3. Identitas kendaraan dan status kelaikan operasional.
 */
class TugasController extends Controller
{
    public function __construct(
        protected ArmadaService $armadaService
    ) {}

    /**
     * Menampilkan daftar tugas harian dan antrean titik pengantaran armada.
     */
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
