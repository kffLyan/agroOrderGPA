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

        // Antrean Penerbitan (Queue Items)
        $queueItems = $orders->values()->map(function ($order, $index) {
            $netWeight = (float) $order->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);
            $crates = max(1, (int) ceil($netWeight / 20));
            $tara = (float) ($crates * 2.0);
            $gross = $netWeight + $tara;

            $driver = $order->driver;
            $driverName = $driver?->name ?: 'Belum Ditugaskan';
            $driverCode = $driver ? ('ID-DRV-' . str_pad($driver->id, 3, '0', STR_PAD_LEFT)) : '—';

            $clientName = $order->user?->company_name ?: ($order->user?->name ?? 'Klien');
            $address = $order->delivery_address ?: '—';
            $commodityNames = $order->orderItems->pluck('product.name')->filter()->unique()->join(', ') ?: '—';
            $sjNumber = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));

            return [
                'po' => 'PO #' . $order->order_number,
                'customer' => $address !== '—' ? ($clientName . ' (' . $address . ')') : $clientName,
                'status_chip' => $order->status === 'SIAP_KIRIM' ? 'TERKUNCI / VALID' : 'SIAP CETAK SJ',
                'net' => $netWeight,
                'tara' => $tara,
                'gross' => $gross,
                'driver' => $driverName,
                'driver_id' => $driver ? ($driverCode . ' (GPA Logistik)') : '—',
                'vehicle' => $order->vehicle_type ?: 'Armada Logistik',
                'plate' => $order->vehicle_plate_number ?: '—',
                'commodity' => 'Komoditas: ' . $commodityNames,
                'dock' => 'TIMBANGAN DOCK #' . (($index % 3) + 1),
                'lock_stamp' => ($order->updated_at ? $order->updated_at->format('Y-m-d H:i') : date('Y-m-d H:i')) . ' WIB',
                'sj' => $sjNumber,
                'issue_label' => 'TERBITKAN & CETAK ' . $sjNumber,
                'issued_label' => $sjNumber . ' SUDAH TERBIT',
            ];
        })->all();

        // Manifest Rows
        $manifestRows = $orders->values()->map(function ($order, $index) {
            $netWeight = (float) $order->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);
            $driver = $order->driver;
            $driverName = $driver?->name ?: 'Belum Ditugaskan';
            $sjNumber = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
            $isMoving = in_array($order->status, ['DIKIRIM', 'SELESAI']);

            return [
                'id' => $sjNumber,
                'ref' => $order->order_number,
                'driver' => $driverName,
                'plate' => $order->vehicle_plate_number ?: '—',
                'vehicle' => $order->vehicle_type ?: 'Armada Logistik',
                'net' => $netWeight,
                'net_tone' => 'success',
                'time' => $order->target_delivery_date ? ($order->target_delivery_date->format('H:i') . ' WIB') : '—',
                'slot' => 'SLOT #' . (($index % 3) + 1),
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
        $fleetCount = $drivers->count();

        $viewName = view()->exists('koordinator.dispatch') ? 'koordinator.dispatch' : 'coordinator.dispatch';

        return view($viewName, [
            'queueItems'   => $queueItems,
            'manifestRows' => $manifestRows,
            'totalTonase'  => $totalTonase,
            'fleetCount'   => $fleetCount,
        ]);
    }
}
