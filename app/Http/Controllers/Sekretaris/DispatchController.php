<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispatchController extends Controller
{
    /**
     * Menampilkan konsol Surat Jalan & Penugasan Armada Logistik
     */
    public function index(Request $request): View
    {
        // Ambil data supir armada aktif dari database
        $drivers = User::where('role', 'ARMADA')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ambil pesanan yang relevan untuk penerbitan surat jalan dan logistik
        $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])
            ->whereIn('status', ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])->latest()->take(5)->get();
        }

        $paidOrdersCount = $orders->where('status', \App\Enums\OrderStatus::LUNAS->value)->count();
        $readyOrdersCount = $orders->where('status', \App\Enums\OrderStatus::SIAP_KIRIM->value)->count();
        $shippingOrdersCount = $orders->where('status', \App\Enums\OrderStatus::DALAM_PENGIRIMAN->value)->count();
        $doneOrdersCount = $orders->whereIn('status', [
            \App\Enums\OrderStatus::SELESAI->value,
            \App\Enums\OrderStatus::SELESAI_CATATAN->value,
        ])->count();

        $rows = \App\Http\Resources\Sekretaris\DispatchResource::collection($orders)->resolve();

        return view('sekretaris.dispatch', [
            'drivers'             => $drivers,
            'rows'                => $rows,
            'paidOrdersCount'     => $paidOrdersCount,
            'readyOrdersCount'    => $readyOrdersCount,
            'shippingOrdersCount' => $shippingOrdersCount,
            'doneOrdersCount'     => $doneOrdersCount,
        ]);
    }

    /**
     * Penerbitan Surat Jalan resmi & penugasan supir armada (SOP Penerbitan)
     * Hanya pesanan dengan status LUNAS yang berhak diterbitkan Surat Jalan.
     */
    public function issue(\App\Http\Requests\Sekretaris\AssignDriverRequest $request, Order $order)
    {
        // Validasi ketat gerbang SOP: Hanya pesanan LUNAS yang boleh diterbitkan Surat Jalan
        if ($order->status !== 'LUNAS' && $order->status !== 'SIAP_KIRIM') {
            return back()->withErrors([
                'error' => "Penerbitan Surat Jalan DITOLAK: Pesanan #{$order->order_number} belum berstatus LUNAS. Pembayaran wajib diverifikasi terlebih dahulu sebelum Surat Jalan dapat dicetak."
            ]);
        }

        // Kepatuhan Tata Kelola: Tolak penerbitan Surat Jalan jika plafon kredit klien dibekukan
        $governanceService = app(\App\Services\GovernanceService::class);
        if ($governanceService->isClientFrozen((int) $order->user_id)) {
            $msg = "Penerbitan Surat Jalan DITOLAK: Plafon kredit klien pemilik pesanan ini sedang dibekukan oleh Direktur Utama.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['error' => $msg]);
        }

        $driverId = $request->input('driver_id') ?: ($order->driver_id ?: User::where('role', 'ARMADA')->first()?->id);
        $plate = $request->input('vehicle_plate_number') ?: ($order->vehicle_plate_number ?: 'B 9014 KPA');

        $sjNumber = $order->surat_jalan_number;
        if (!$sjNumber) {
            $prefix = 'SJ-GPA-' . date('Ym') . '-';
            $lastSj = Order::where('surat_jalan_number', 'LIKE', $prefix . '%')->orderBy('id', 'desc')->first();
            $seq = 1;
            if ($lastSj) {
                $seq = ((int) substr($lastSj->surat_jalan_number, -4)) + 1;
            }
            $sjNumber = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            while (Order::where('surat_jalan_number', $sjNumber)->exists()) {
                $seq++;
                $sjNumber = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            }
        }

        $order->update([
            'driver_id'            => $driverId,
            'vehicle_plate_number' => $plate,
            'surat_jalan_number'   => $sjNumber,
            'status'               => 'SIAP_KIRIM',
        ]);

        $driver = User::find($driverId);
        $driverName = $driver?->name ?: 'Armada Logistik GPA';

        $msg = "Surat Jalan {$sjNumber} untuk pesanan #{$order->order_number} berhasil diterbitkan dan ditugaskan ke supir {$driverName}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'message'     => $msg,
                'order'       => $order,
                'driver_name' => $driverName,
                'driver_id'   => $driverId,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Memperbarui / menugaskan supir armada ke pesanan
     */
    public function assignDriver(Request $request, Order $order)
    {

        // SOP Penugasan Armada: Supir HANYA bisa dipilih/diubah SEBELUM Surat Jalan diterbitkan
        if (in_array($order->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) || !empty($order->surat_jalan_number)) {
            $errMessage = "Penugasan supir DITOLAK: Surat Jalan untuk pesanan #{$order->order_number} telah resmi diterbitkan. Supir dan armada telah terkunci permanen dan tidak dapat diubah lagi.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errMessage,
                ], 422);
            }
            return back()->withErrors(['error' => $errMessage]);
        }

        $request->validate([
            'driver_id'            => 'required|exists:users,id',
            'vehicle_plate_number' => 'nullable|string|max:20',
        ]);

        $driver = User::where('role', 'ARMADA')->findOrFail($request->input('driver_id'));

        $updateData = [
            'driver_id' => $driver->id,
        ];

        if ($request->filled('vehicle_plate_number')) {
            $updateData['vehicle_plate_number'] = $request->input('vehicle_plate_number');
        } elseif (!$order->vehicle_plate_number) {
            $updateData['vehicle_plate_number'] = 'B 9014 KPA';
        }

        $order->update($updateData);

        $msg = "Supir armada untuk pesanan #{$order->order_number} berhasil ditugaskan ke {$driver->name}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'message'     => $msg,
                'driver_id'   => $driver->id,
                'driver_name' => $driver->name,
                'plate'       => $order->vehicle_plate_number,
            ]);
        }

        return back()->with('success', $msg);
    }
}
