<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Http\Requests\Armada\StorePodRequest;
use App\Models\Order;
use App\Services\ArmadaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PodController extends Controller
{
    public function __construct(
        protected ArmadaService $armadaService
    ) {}

    /**
     * Menampilkan formulir dan konsol bukti terima digital (Proof of Delivery / PoD).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $targetOrderId = $request->query('order_id') ? (int) $request->query('order_id') : null;

        $currentOrder = $this->armadaService->resolveCurrentDeliveryOrder($targetOrderId, $user);
        $plate = $currentOrder?->vehicle_plate_number ?: '—';

        return view('armada.pod', [
            'currentOrder' => $currentOrder,
            'plate'        => $plate,
            'activeNav'    => 'pod',
        ]);
    }

    /**
     * Menyimpan dokumen bukti terima pengiriman (Proof of Delivery)
     */
    public function store(StorePodRequest $request, Order $order): RedirectResponse
    {
        $user = Auth::user();

        // Anti-IDOR: Jika pesanan sudah ditugaskan ke supir lain, tolak aksesnya
        if ($order->driver_id && $order->driver_id !== $user->id) {
            abort(403, 'Akses Ditolak: Pesanan ini ditugaskan kepada supir armada lain.');
        }

        $condition = $request->input('condition', 'clear');
        $receiverName = $request->input('received_by_name');

        $podPath = null;
        if ($request->hasFile('manifest_photo')) {
            $file = $request->file('manifest_photo');
            $filename = 'pod_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pod'), $filename);
            $podPath = 'uploads/pod/' . $filename;
        } elseif ($request->hasFile('dock_photo')) {
            $file = $request->file('dock_photo');
            $filename = 'dock_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pod'), $filename);
            $podPath = 'uploads/pod/' . $filename;
        }

        $hasReturn = false;
        if ($request->has('returns') && is_array($request->input('returns'))) {
            foreach ($request->input('returns') as $itemId => $returnData) {
                $returnedWeight = (float) ($returnData['returned_weight'] ?? 0);
                $returnReason = $returnData['return_reason'] ?? null;

                if ($returnedWeight > 0) {
                    $hasReturn = true;
                    $order->orderItems()->where('id', $itemId)->update([
                        'returned_weight' => $returnedWeight,
                        'return_reason'   => $returnReason,
                    ]);
                }
            }
        }

        // Simpan status pesanan dan catatan kedatangan
        $order->status = ($condition === 'discrepancy' || $hasReturn) ? 'SELESAI_CATATAN' : 'SELESAI';
        $order->arrival_time = now();
        if ($receiverName) {
            $order->received_by_name = $receiverName;
        }
        if ($podPath) {
            $order->pod_photo_url = $podPath;
        }
        if (!$order->driver_id && $user) {
            $order->driver_id = $user->id;
        }
        $order->save();

        return redirect()
            ->route('armada.tasks')
            ->with('success', 'Bukti serah terima (PoD) berhasil disimpan! Pesanan #' . $order->order_number . ' telah selesai diantarkan.');
    }
}
