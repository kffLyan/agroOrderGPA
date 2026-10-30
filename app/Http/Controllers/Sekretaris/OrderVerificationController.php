<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sekretaris\RejectOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

// Mengatur verifikasi pesanan masuk oleh Sekretaris (menyetujui atau menolak pesanan)
class OrderVerificationController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Menampilkan antrean pesanan masuk untuk diverifikasi Sekretaris
     */
    public function index(): View
    {
        $realOrders = Order::with([
            'user.contracts' => function ($query) {
                $query->where('status', 'ACTIVE')->orderBy('id', 'asc');
            },
            'orderItems.product',
            'payments',
        ])
            ->where('status', \App\Enums\OrderStatus::MENUNGGU_VERIFIKASI->value)
            ->latest()
            ->get();

        $mappedOrders = \App\Http\Resources\Sekretaris\OrderVerificationResource::collection($realOrders)->resolve();
        $b2bCount = count(array_filter($mappedOrders, fn($o) => $o['channel'] === 'b2b'));
        $waCount = count($mappedOrders) - $b2bCount;

        return view('sekretaris.verification', compact('mappedOrders', 'b2bCount', 'waCount'));
    }

    /**
     * Menyetujui pesanan, mengunci kuota panen, & menerbitkan Invoice tagihan PDF
     */
    public function approve(Request $request, Order $order)
    {
        try {
            $secretaryId = Auth::guard('web')->id();
            $order = $this->orderService->approveOrder($order->id, $secretaryId);

            $message = "Pesanan {$order->order_number} berhasil disetujui. Kuota stok panen telah dikunci, Invoice tagihan PDF telah diterbitkan, dan status pesanan kini Menunggu Pembayaran.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $message,
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'status'       => $order->status,
                ]);
            }

            return redirect()->route('sekretaris.verification')
                ->with('success', $message);
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

    /**
     * Menolak pesanan masuk beserta alasan penolakan
     */
    public function reject(RejectOrderRequest $request, Order $order)
    {
        try {
            $secretaryId = Auth::guard('web')->id();
            $order = $this->orderService->rejectOrder($order->id, $secretaryId, $request->input('rejection_reason'));

            $message = "Pesanan {$order->order_number} telah ditolak.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => $message,
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                ]);
            }

            return redirect()->route('sekretaris.verification')
                ->with('success', $message);
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
