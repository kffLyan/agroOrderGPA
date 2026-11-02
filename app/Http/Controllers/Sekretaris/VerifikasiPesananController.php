<?php

namespace App\Http\Controllers\Sekretaris;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sekretaris\TolakPesananRequest;
use App\Http\Resources\Sekretaris\VerifikasiPesananResource;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Controller verifikasi pesanan masuk (PO) untuk Sekretaris
class VerifikasiPesananController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    // Menampilkan daftar antrean verifikasi purchase order (PO) klien B2B dan pesanan WhatsApp yang menunggu persetujuan
    public function index(): View
    {
        $pendingOrders = Order::with([
            'user.contracts' => function ($query) {
                $query->where('status', 'ACTIVE')->orderBy('id', 'asc');
            },
            'orderItems.product',
            'payments',
        ])
            ->where('status', OrderStatus::MENUNGGU_VERIFIKASI->value)
            ->latest()
            ->get();

        $formattedOrders = VerifikasiPesananResource::collection($pendingOrders)->resolve();
        $b2bCount = count(array_filter($formattedOrders, fn($orderItem) => ($orderItem['channel'] ?? '') === 'b2b'));
        $waCount  = count($formattedOrders) - $b2bCount;

        return view('sekretaris.verifikasi', [
            'mappedOrders' => $formattedOrders,
            'b2bCount'     => $b2bCount,
            'waCount'      => $waCount,
        ]);
    }

    // Menyetujui purchase order, mengunci alokasi stok komoditas di gudang, dan menerbitkan faktur tagihan resmi
    public function approve(Request $request, Order $order)
    {
        try {
            $secretaryId = Auth::guard('web')->id();
            
            $manualShippingCost = ($request->has('shipping_cost') && is_numeric($request->input('shipping_cost')))
                ? max(0.0, (float) $request->input('shipping_cost'))
                : null;

            $approvedOrder = $this->orderService->approveOrder($order->id, $secretaryId, $manualShippingCost);

            $message = "Pesanan {$approvedOrder->order_number} berhasil disetujui. Kuota stok panen telah dikunci, Invoice tagihan telah diterbitkan, dan status pesanan kini Menunggu Pembayaran.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $message,
                    'order_id'     => $approvedOrder->id,
                    'order_number' => $approvedOrder->order_number,
                    'status'       => $approvedOrder->status,
                ]);
            }

            return redirect()->route('sekretaris.verifikasi')->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // Menolak purchase order dengan mencantumkan alasan pembatalan dan melepaskan reservasi stok jika ada
    public function reject(TolakPesananRequest $request, Order $order)
    {
        try {
            $secretaryId     = Auth::guard('web')->id();
            $rejectionReason = $request->input('rejection_reason');

            $rejectedOrder = $this->orderService->rejectOrder($order->id, $secretaryId, $rejectionReason);

            $message = "Pesanan {$rejectedOrder->order_number} telah ditolak.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $message,
                    'order_id'     => $rejectedOrder->id,
                    'order_number' => $rejectedOrder->order_number,
                ]);
            }

            return redirect()->route('sekretaris.verifikasi')->with('success', $message);
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
