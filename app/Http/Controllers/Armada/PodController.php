<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PodController extends Controller
{
    /**
     * Menampilkan formulir dan konsol bukti terima digital (Proof of Delivery / PoD).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Cari pesanan yang menjadi target PoD
        $targetOrderId = $request->query('order_id');
        $query = Order::with(['orderItems.product', 'user', 'driver']);

        if ($targetOrderId) {
            $currentOrder = $query->find($targetOrderId);
        } else {
            $currentOrder = $query->where(function ($q) use ($user) {
                $q->where('driver_id', $user?->id)
                  ->orWhereIn('status', ['DALAM_PENGIRIMAN', 'SIAP_KIRIM']);
            })
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->latest()
            ->first();
        }

        if (!$currentOrder) {
            $currentOrder = Order::with(['orderItems.product', 'user', 'driver'])->latest()->first();
        }

        $plate = $currentOrder?->vehicle_plate_number ?: 'B 9284 TDA';

        return view('armada.pod', [
            'currentOrder' => $currentOrder,
            'plate'        => $plate,
            'activeNav'    => 'pod',
        ]);
    }

    /**
     * Menyimpan dokumen bukti terima pengiriman (Proof of Delivery)
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'condition'        => 'nullable|string',
            'signature'        => 'nullable|string',
            'received_by_name' => 'nullable|string',
            'manifest_photo'   => 'nullable|image|max:10240',
            'dock_photo'       => 'nullable|image|max:10240',
        ]);

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

        // Simpan perubahan ke pesanan
        $order->status = ($condition === 'discrepancy') ? 'SELESAI_CATATAN' : 'SELESAI';
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
