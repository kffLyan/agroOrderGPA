<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HarvestPlanController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with(['orderItems.product', 'user'])
            ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM'])
            ->latest()
            ->take(10)
            ->get();

        $totalOrders    = $orders->count();
        $siapKirimCount = $orders->where('status', 'SIAP_KIRIM')->count();
        $totalKg        = (float) $orders->flatMap->orderItems->sum('ordered_qty');
        $pctDone        = $totalOrders > 0 ? round(($siapKirimCount / $totalOrders) * 100, 1) : 0;

        $queueRows = $orders->values()->map(function ($order, $index) {
            $isSiap    = $order->status === 'SIAP_KIRIM';
            $stage     = $isSiap ? 4 : 2;
            $stageTone = $isSiap ? 'accent-soft' : 'pill';
            $totalQty  = (float) $order->orderItems->sum('ordered_qty');
            $kratTotal = max(1, (int) ceil($totalQty / 20));
            $kratDone  = $isSiap ? $kratTotal : (int) ceil($kratTotal * 0.7);
            $product   = $order->orderItems->first()?->product;
            $bay       = 'STG-BAY-' . chr(65 + ($index % 4)) . ($index % 2 + 1);
            $station   = 'MEJA ' . str_pad($index % 5 + 1, 2, '0', STR_PAD_LEFT);

            return [
                'po'             => $order->order_number,
                'bay'            => $bay,
                'station'        => $station,
                'buyer'          => $order->user?->name ?? '-',
                'commodity'      => $product?->name ?? 'Komoditas',
                'commodity_note' => '[Grade A]',
                'commodity_tone' => 'success',
                'krat_done'      => $kratDone,
                'krat_total'     => $kratTotal,
                'est_kg'         => $totalQty,
                'stage'          => $stage,
                'stage_tone'     => $stageTone,
                'afkir'          => null,
                'action'         => $isSiap ? 'select' : 'inspect',
                'action_label'   => $isSiap ? 'Pilih' : 'Inspeksi',
                'row_tone'       => $isSiap ? 'default' : 'active',
            ];
        })->all();

        $fallbackRow = [
            'po' => 'BELUM-ADA-ORDER', 'bay' => '-', 'station' => '-',
            'buyer' => 'Belum ada order aktif', 'commodity' => '-',
            'commodity_note' => null, 'commodity_tone' => 'body',
            'krat_done' => 0, 'krat_total' => 0, 'est_kg' => 0,
            'stage' => 1, 'stage_tone' => 'pill',
            'afkir' => null, 'action' => 'select', 'action_label' => 'Pilih', 'row_tone' => 'default',
        ];

        if (empty($queueRows)) {
            $queueRows = [$fallbackRow];
        }

        $viewName = view()->exists('koordinator.harvest-plan') ? 'koordinator.harvest-plan' : 'coordinator.harvest-plan';

        return view($viewName, [
            'queueRows'      => $queueRows,
            'totalOrders'    => $totalOrders,
            'siapKirimCount' => $siapKirimCount,
            'totalKg'        => $totalKg,
            'pctDone'        => $pctDone,
        ]);
    }
}