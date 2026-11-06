<?php

namespace App\Http\Controllers\Sekretaris;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sekretaris\TugaskanDriverRequest;
use App\Http\Resources\Sekretaris\SuratJalanResource;
use App\Models\Order;
use App\Models\User;
use App\Services\TataKelolaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// Controller penerbitan Surat Jalan dan penugasan armada logistik untuk Sekretaris
class SuratJalanController extends Controller
{
    // Menampilkan konsol penerbitan surat jalan, penugasan armada logistik, dan rekapitulasi pengiriman
    public function index(Request $request): View
    {
        // Mengambil seluruh supir armada logistik aktif dari database untuk opsi penugasan
        $drivers = User::where('role', 'ARMADA')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Mengambil pesanan yang relevan untuk penerbitan surat jalan dan logistik
        $orders = Order::with(['orderItems.product', 'user.contracts', 'driver', 'verifier'])
            ->where(function ($query) {
                $query->whereIn('status', ['LUNAS', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])
                  ->orWhere(function ($subQuery) {
                      $subQuery->where('status', 'MENUNGGU_PEMBAYARAN')
                          ->whereHas('user', function ($userQuery) {
                              $userQuery->where('client_type', 'B2B_KONTRAK')
                                ->orWhereHas('contracts', fn($contract) => $contract->where('status', 'ACTIVE'));
                          });
                  });
            })
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])->latest()->take(5)->get();
        }

        $paidOrdersCount     = $orders->where('status', OrderStatus::LUNAS->value)->count();
        $readyOrdersCount    = $orders->where('status', OrderStatus::SIAP_KIRIM->value)->count();
        $shippingOrdersCount = $orders->where('status', OrderStatus::DALAM_PENGIRIMAN->value)->count();
        $doneOrdersCount     = $orders->whereIn('status', [
            OrderStatus::SELESAI->value,
            OrderStatus::SELESAI_CATATAN->value,
        ])->count();

        $rows = SuratJalanResource::collection($orders)->resolve();

        return view('sekretaris.surat-jalan', [
            'drivers'             => $drivers,
            'rows'                => $rows,
            'paidOrdersCount'     => $paidOrdersCount,
            'readyOrdersCount'    => $readyOrdersCount,
            'shippingOrdersCount' => $shippingOrdersCount,
            'doneOrdersCount'     => $doneOrdersCount,
        ]);
    }

    // Menerbitkan nomor surat jalan resmi secara atomik dan menugaskan supir armada untuk pengantaran pesanan
    public function issue(TugaskanDriverRequest $request, Order $order)
    {
        $isContractClient = $order->user && (
            $order->user->client_type === 'B2B_KONTRAK' || 
            $order->user->contracts()->where('status', 'ACTIVE')->exists()
        );

        // Memvalidasi kepatuhan SOP bahwa pesanan harus berstatus lunas, siap kirim, atau kontrak B2B
        if ($order->status !== 'LUNAS' && $order->status !== 'SIAP_KIRIM' && !$isContractClient) {
            return back()->withErrors([
                'error' => "Penerbitan Surat Jalan DITOLAK: Pesanan #{$order->order_number} belum berstatus LUNAS. Pembayaran wajib diverifikasi terlebih dahulu sebelum Surat Jalan dapat dicetak."
            ]);
        }

        // Memeriksa pembekuan plafon kredit klien agar pengiriman tertahan jika terkena sanksi tata kelola
        $tataKelolaService = app(TataKelolaService::class);
        if ($tataKelolaService->isClientFrozen((int) $order->user_id)) {
            $message = "Penerbitan Surat Jalan DITOLAK: Plafon kredit klien pemilik pesanan ini sedang dibekukan oleh Direktur Utama.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->withErrors(['error' => $message]);
        }

        $driverId = $request->input('driver_id') ?: ($order->driver_id ?: User::where('role', 'ARMADA')->first()?->id);
        $vehiclePlateNumber = $request->input('vehicle_plate_number') ?: ($order->vehicle_plate_number ?: 'B 9014 KPA');

        $suratJalanNumber = $order->surat_jalan_number;
        if (!$suratJalanNumber) {
            $prefix = 'SJ-GPA-' . date('Ym') . '-';
            $suratJalanNumber = Cache::lock('surat_jalan_sequence_lock', 5)->block(3, function () use ($prefix) {
                $lastSuratJalan = Order::where('surat_jalan_number', 'LIKE', $prefix . '%')->orderBy('id', 'desc')->first();
                $nextSequence = 1;
                if ($lastSuratJalan) {
                    $nextSequence = ((int) substr($lastSuratJalan->surat_jalan_number, -4)) + 1;
                }
                $newSuratJalanNumber = $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
                while (Order::where('surat_jalan_number', $newSuratJalanNumber)->exists()) {
                    $nextSequence++;
                    $newSuratJalanNumber = $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
                }
                return $newSuratJalanNumber;
            });
        }

        $order->update([
            'driver_id'            => $driverId,
            'vehicle_plate_number' => $vehiclePlateNumber,
            'surat_jalan_number'   => $suratJalanNumber,
            'status'               => 'SIAP_KIRIM',
        ]);

        $driver = User::find($driverId);
        $driverName = $driver?->name ?: 'Armada Logistik GPA';

        $message = "Surat Jalan {$suratJalanNumber} untuk pesanan #{$order->order_number} berhasil diterbitkan dan ditugaskan ke supir {$driverName}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'message'     => $message,
                'order'       => $order,
                'driver_name' => $driverName,
                'driver_id'   => $driverId,
                'plate'       => $order->vehicle_plate_number ?: $vehiclePlateNumber,
                'sj_number'   => $suratJalanNumber,
            ]);
        }

        return back()->with('success', $message);
    }

    // Menetapkan supir armada sebelum surat jalan diterbitkan dan mengunci armada secara permanen
    public function assignDriver(Request $request, Order $order)
    {
        // Memastikan penugasan supir armada hanya dilakukan sebelum surat jalan resmi diterbitkan
        if (in_array($order->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) || !empty($order->surat_jalan_number)) {
            $errorMessage = "Penugasan supir DITOLAK: Surat Jalan untuk pesanan #{$order->order_number} telah resmi diterbitkan. Supir dan armada telah terkunci permanen dan tidak dapat diubah lagi.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 422);
            }
            return back()->withErrors(['error' => $errorMessage]);
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

        $message = "Supir armada untuk pesanan #{$order->order_number} berhasil ditugaskan ke {$driver->name}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'     => true,
                'message'     => $message,
                'driver_id'   => $driver->id,
                'driver_name' => $driver->name,
                'plate'       => $order->vehicle_plate_number,
            ]);
        }

        return back()->with('success', $message);
    }
}
