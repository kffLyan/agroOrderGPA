<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\CoordinatorWeighingData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeighingController extends Controller
{
    /**
     * Tampilkan Konsol Penimbangan Aktual & Validasi Sortir Gudang.
     */
    public function index(Request $request): View
    {
        $console = CoordinatorWeighingData::for($request->user());

        // Ambil pesanan riil jika ada (prioritas: TERVERIFIKASI, lalu SIAP_KIRIM, atau order terbaru)
        $orderQuery = Order::with(['orderItems.product', 'user']);
        $realOrder = null;

        if ($request->has('order_id')) {
            $realOrder = (clone $orderQuery)->find($request->input('order_id'));
        }

        if (!$realOrder) {
            $realOrder = (clone $orderQuery)->where('status', 'TERVERIFIKASI')->first()
                ?? (clone $orderQuery)->where('status', 'SIAP_KIRIM')->first()
                ?? (clone $orderQuery)->latest()->first();
        }

        if ($realOrder) {
            $console['order']['id'] = $realOrder->id;
            $console['order']['code'] = $realOrder->order_number;
            $console['order']['client'] = $realOrder->user?->name ?? $console['order']['client'];

            $firstItem = $realOrder->orderItems->first();
            if ($firstItem) {
                $totalOrdered = (float) $realOrder->orderItems->sum('ordered_qty');
                $console['order']['estimate'] = $totalOrdered > 0 ? $totalOrdered : 800.00;
                $console['order']['rate'] = 'Rp ' . number_format((float) $firstItem->unit_price, 0, ',', '.');
                $console['order']['commodity'] = 'Komoditas: ' . ($firstItem->product?->name ?? 'Selada Romaine Grade A Super');
            }

            if ($realOrder->status === 'SIAP_KIRIM') {
                $console['stages'][1]['state'] = 'done';
                $console['stages'][2]['state'] = 'active';
            }
        }

        $viewName = view()->exists('koordinator.weighing') ? 'koordinator.weighing' : 'coordinator.weighing';

        return view($viewName, [
            'operator' => $console['operator'],
            'header' => $console['header'],
            'stages' => $console['stages'],
            'rules' => $console['rules'],
            'order' => $console['order'],
            'terminal' => $console['terminal'],
            'grading' => $console['grading'],
            'evidence' => $console['evidence'],
            'reconciliation' => $console['reconciliation'],
            'financial' => $console['financial'],
            'audit' => $console['audit'],
            'actions' => $console['actions'],
        ]);
    }

    /**
     * Kunci dan simpan data penimbangan aktual netto riil (PRD Rule 04 & 05).
     */
    public function storeWeighing(Request $request, $order_id): JsonResponse|RedirectResponse
    {
        $order = Order::with('orderItems')->findOrFail($order_id);

        DB::transaction(function () use ($request, $order) {
            $user = $request->user();

            if ($request->has('items') && is_array($request->input('items'))) {
                $request->validate([
                    'items' => 'required|array',
                    'items.*.id' => 'required|exists:order_items,id',
                    'items.*.actual_net_weight' => 'required|numeric|min:0.01',
                ]);

                foreach ($request->input('items') as $itemInput) {
                    $item = $order->orderItems->firstWhere('id', $itemInput['id']);
                    if ($item) {
                        $actual = (float) $itemInput['actual_net_weight'];
                        $item->update([
                            'actual_net_weight' => $actual,
                            'subtotal_final' => $actual * (float) $item->unit_price,
                            'weighed_by' => $user?->id,
                            'weighed_at' => now(),
                        ]);
                    }
                }
            } else {
                $request->validate([
                    'actual_net_weight' => 'required|numeric|min:0.01',
                ]);

                $actualWeight = (float) $request->input('actual_net_weight');

                if ($order->orderItems->count() === 1) {
                    $item = $order->orderItems->first();
                    $item->update([
                        'actual_net_weight' => $actualWeight,
                        'subtotal_final' => $actualWeight * (float) $item->unit_price,
                        'weighed_by' => $user?->id,
                        'weighed_at' => now(),
                    ]);
                } else {
                    $totalOrdered = (float) $order->orderItems->sum('ordered_qty');
                    foreach ($order->orderItems as $item) {
                        $ratio = $totalOrdered > 0 ? ((float) $item->ordered_qty / $totalOrdered) : (1 / max(1, $order->orderItems->count()));
                        $itemActual = round($actualWeight * $ratio, 2);
                        $item->update([
                            'actual_net_weight' => $itemActual,
                            'subtotal_final' => $itemActual * (float) $item->unit_price,
                            'weighed_by' => $user?->id,
                            'weighed_at' => now(),
                        ]);
                    }
                }
            }

            // Recalculate order grand total from items
            $order->refresh();
            $grandTotal = $order->orderItems->sum(function ($it) {
                return $it->subtotal_final ?? ($it->actual_net_weight * $it->unit_price);
            });

            $order->update([
                'grand_total' => $grandTotal,
                'status' => 'SIAP_KIRIM',
            ]);
        });

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data timbangan netto riil berhasil dikunci. Status pesanan kini SIAP_KIRIM.',
                'order' => $order->fresh(['orderItems']),
            ]);
        }

        return redirect()->back()->with('success', 'Data timbangan netto riil berhasil dikunci. Status pesanan kini SIAP_KIRIM.');
    }
}
