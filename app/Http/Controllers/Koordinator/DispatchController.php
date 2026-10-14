<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DispatchController extends Controller
{
    /**
     * Tampilkan Konsol Surat Jalan & Dispatch Armada Logistik.
     */
    public function index(Request $request): View
    {
        $drivers = User::where('role', 'ARMADA')->get();
        $orders = Order::with(['orderItems.product', 'user', 'driver'])
            ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM', 'DIKIRIM'])
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver'])->latest()->take(5)->get();
        }

        // Antrean Penerbitan (Queue Items)
        $queueItems = $orders->values()->map(function ($order, $index) use ($drivers) {
            $netWeight = (float) $order->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);
            $crates = max(1, (int) ceil($netWeight / 20));
            $tara = (float) ($crates * 2.0);
            $gross = $netWeight + $tara;

            $driver = $order->driver ?: ($drivers[$index % max(1, $drivers->count())] ?? null);
            $driverName = $driver?->name ?? 'Joko Prasetyo';
            $driverCode = 'ID-DRV-' . str_pad($driver?->id ?? 1, 3, '0', STR_PAD_LEFT);

            $clientName = $order->user?->company_name ?: ($order->user?->name ?? 'Katering Mitra');
            $address = $order->delivery_address ?: 'Sentul Hub Depot';
            $commodityNames = $order->orderItems->pluck('product.name')->filter()->unique()->join(', ') ?: 'Sayuran Segar';
            $sjNumber = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));

            return [
                'po' => 'PO #' . $order->order_number,
                'customer' => $clientName . ' (' . $address . ')',
                'status_chip' => $order->status === 'SIAP_KIRIM' ? 'TERKUNCI / VALID' : 'SIAP CETAK SJ',
                'net' => $netWeight,
                'tara' => $tara,
                'gross' => $gross,
                'driver' => $driverName,
                'driver_id' => $driverCode . ' (GPA Logistik)',
                'vehicle' => $index % 2 === 0 ? 'Engkel Box' : 'Blind Van Chiller',
                'plate' => $order->vehicle_plate_number ?: ($index % 2 === 0 ? 'B 9102 SDA' : 'D 8472 GA'),
                'commodity' => 'Komoditas: ' . $commodityNames,
                'dock' => 'TIMBANGAN DOCK #' . (($index % 3) + 1),
                'lock_stamp' => ($order->updated_at ? $order->updated_at->format('Y-m-d H:i') : date('Y-m-d H:i')) . ' WIB',
                'sj' => $sjNumber,
                'issue_label' => 'TERBITKAN & CETAK ' . $sjNumber,
                'issued_label' => $sjNumber . ' SUDAH TERBIT',
            ];
        })->all();

        // Manifest Rows
        $manifestRows = $orders->values()->map(function ($order, $index) use ($drivers) {
            $netWeight = (float) $order->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);
            $driver = $order->driver ?: ($drivers[$index % max(1, $drivers->count())] ?? null);
            $driverName = $driver?->name ?? 'Joko Prasetyo';
            $sjNumber = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
            $isMoving = in_array($order->status, ['DIKIRIM', 'SELESAI']);

            return [
                'id' => $sjNumber,
                'ref' => $order->order_number,
                'driver' => $driverName,
                'plate' => $order->vehicle_plate_number ?: ($index % 2 === 0 ? 'B 9102 SDA' : 'D 8472 GA'),
                'vehicle' => $index % 2 === 0 ? 'Engkel Box' : 'Blind Van',
                'net' => $netWeight,
                'net_tone' => 'success',
                'time' => ($order->target_delivery_date ? $order->target_delivery_date->format('H:i') : '04:30') . ' WIB',
                'slot' => 'DAWN SLOT #' . (($index % 3) + 1),
                'slot_tone' => 'success',
                'copies' => '3 RANGKAP OK',
                'copies_tone' => 'ok',
                'qr' => 'QR VALID',
                'qr_icon' => true,
                'dock' => $isMoving ? 'MENUJU LOKASI' : 'STANDBY DOCK #' . (($index % 2) + 1),
                'dock_state' => $isMoving ? 'moving' : 'standby',
                'row_tone' => $isMoving ? 'highlight' : 'default',
                'action' => $isMoving ? 'track' : 'handover',
                'action_label' => $isMoving ? 'Lacak GPS Supir' : 'Serahkan Tugas ke Supir',
            ];
        })->all();

        $totalTonase = array_sum(array_column($manifestRows, 'net'));
        $fleetCount = max(1, $drivers->count());

        $viewName = view()->exists('koordinator.dispatch') ? 'koordinator.dispatch' : 'coordinator.dispatch';

        return view($viewName, [
            'queueItems'   => $queueItems,
            'manifestRows' => $manifestRows,
            'totalTonase'  => $totalTonase,
            'fleetCount'   => $fleetCount,
        ]);
    }
}
