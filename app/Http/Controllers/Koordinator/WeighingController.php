<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
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
        $user = $request->user();

        $operator = [
            'name' => $user?->name ?? 'Koordinator Lapangan',
            'role' => 'Koordinator Lapangan',
            'code' => '002',
            'initials' => strtoupper(substr($user?->name ?? 'KL', 0, 2)),
        ];

        // Ambil pesanan aktif dari database
        $orderQuery = Order::with(['orderItems.product', 'user', 'verifier']);
        $realOrder = null;

        if ($request->has('order_id')) {
            $realOrder = (clone $orderQuery)->find($request->input('order_id'));
        }

        if (!$realOrder) {
            $realOrder = (clone $orderQuery)->where('status', 'TERVERIFIKASI')->first()
                ?? (clone $orderQuery)->where('status', 'SIAP_KIRIM')->first()
                ?? (clone $orderQuery)->latest()->first();
        }

        $orderId = $realOrder?->id ?? 1;
        $orderCode = $realOrder?->order_number ?? 'PO-GPA-2026-0001';
        $clientName = $realOrder?->user?->company_name ?: ($realOrder?->user?->name ?? 'Grand Hotel Bandung');
        $commodities = $realOrder?->orderItems?->pluck('product.name')->filter()->unique()->join(', ') ?: 'Selada Romaine Grade A Super';
        $estimate = (float) ($realOrder?->orderItems?->sum('ordered_qty') ?: 500.0);
        $firstItem = $realOrder?->orderItems?->first();
        $unitRate = (float) ($firstItem?->unit_price ?: 18000.0);
        $isWeighed = $realOrder?->status === 'SIAP_KIRIM';
        $actualNet = (float) ($realOrder?->orderItems?->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty) ?: $estimate);
        $crates = max(1, (int) ceil($actualNet / 20));
        $tareEach = 2.0;
        $tareCrates = (float) ($crates * $tareEach);
        $gross = $actualNet + $tareCrates;
        $verifierName = $realOrder?->verifier?->name ?? 'Sekretaris Operasional';
        $sjNumber = $realOrder?->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($orderId, 4, '0', STR_PAD_LEFT));

        $afkirKg = max(0, round($estimate * 0.006, 2));
        $cleanKg = $actualNet;
        $cleanPercent = ($estimate > 0) ? round(($cleanKg / $estimate) * 100, 2) . '%' : '100%';
        $varianceKg = $actualNet - $estimate;
        $variancePct = $estimate > 0 ? round(($varianceKg / $estimate) * 100, 2) : 0;
        $finalAmount = $actualNet * $unitRate;
        $estAmount = $estimate * $unitRate;
        $diffAmount = $finalAmount - $estAmount;

        $viewName = view()->exists('koordinator.weighing') ? 'koordinator.weighing' : 'coordinator.weighing';

        return view($viewName, [
            'orderId'      => $orderId,
            'orderCode'    => $orderCode,
            'clientName'   => $clientName,
            'commodities'  => $commodities,
            'estimate'     => $estimate,
            'unitRate'     => $unitRate,
            'isWeighed'    => $isWeighed,
            'actualNet'    => $actualNet,
            'crates'       => $crates,
            'tareEach'     => $tareEach,
            'tareCrates'   => $tareCrates,
            'gross'        => $gross,
            'verifierName' => $verifierName,
            'sjNumber'     => $sjNumber,
            'varianceKg'   => $varianceKg,
            'variancePct'  => $variancePct,
            'finalAmount'  => $finalAmount,
            'estAmount'    => $estAmount,
            'diffAmount'   => $diffAmount,
            'cleanKg'      => $cleanKg,
            'cleanPercent' => $cleanPercent,
            'afkirKg'      => $afkirKg,
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

                if ($order->orderItems->count() <= 1) {
                    $item = $order->orderItems->first();
                    if ($item) {
                        $item->update([
                            'actual_net_weight' => $actualWeight,
                            'subtotal_final' => $actualWeight * (float) $item->unit_price,
                            'weighed_by' => $user?->id,
                            'weighed_at' => now(),
                        ]);
                    }
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
