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
        // Ambil pesanan dari database
        $drivers = User::where('role', 'ARMADA')->get();
        $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])
            ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])->latest()->take(5)->get();
        }

        $readyOrders = $orders->where('status', 'SIAP_KIRIM');
        $shippingOrders = $orders->where('status', 'DALAM_PENGIRIMAN');
        $doneOrders = $orders->where('status', 'SELESAI');

        $readyOrdersCount = count($readyOrders);
        $shippingOrdersCount = count($shippingOrders);
        $doneOrdersCount = count($doneOrders);

        $rows = [];
        foreach ($orders->values() as $index => $order) {
            $clientName = $order->user?->company_name ?: ($order->user?->name ?: 'Mitra Klien GPA');
            $address = $order->delivery_address ?: 'Gudang Utama Klien';
            $estimateWeight = (float) $order->orderItems->sum('ordered_qty');
            $nettoWeight = (float) $order->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);

            $diffKg = round($nettoWeight - $estimateWeight, 2);
            $diffPct = $estimateWeight > 0 ? round(($diffKg / $estimateWeight) * 100, 2) : 0;
            $deviationStr = ($diffKg >= 0 ? '+' : '') . number_format($diffKg, 1, ',', '.') . " kg ({$diffPct}%)";

            $driver = $order->driver;
            $driverName = $driver?->name ?: 'Belum Ditugaskan';
            $plateNumber = $order->vehicle_plate_number ?: '—';
            $armadaName = $driver ? 'Armada Logistik GPA' : 'Menunggu Penugasan';

            $sjNumber = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
            $isReady = $order->status === 'SIAP_KIRIM';

            $itemLines = $order->orderItems->map(function ($it, $lineIdx) {
                $est = (float) $it->ordered_qty;
                $act = (float) ($it->actual_net_weight ?: $it->ordered_qty);
                $diff = round($act - $est, 2);
                return [
                    'no'        => $lineIdx + 1,
                    'commodity' => $it->product?->name ?: 'Komoditas Sayur',
                    'lot'       => 'Wadah Keranjang GPA',
                    'estimate'  => number_format($est, 1, ',', '.') . ' kg',
                    'netto'     => number_format($act, 1, ',', '.') . ' kg',
                    'delta'     => ($diff >= 0 ? '+' : '') . number_format($diff, 1, ',', '.') . ' kg',
                ];
            })->all();

            $rows[] = [
                'id'          => $order->order_number,
                'client'      => $clientName,
                'warehouse'   => 'Gudang Pusat Sentra',
                'dock'        => $address,
                'state'       => $isReady ? 'ready' : 'locked',
                'estimate'    => number_format($estimateWeight, 1, ',', '.') . ' kg',
                'netto'       => number_format($nettoWeight, 1, ',', '.') . ' kg',
                'deviation'   => $deviationStr,
                'revision'    => null,
                'status_chip' => [
                    'label' => $isReady ? 'Timbangan Sah Siap Terbit' : 'Dalam Proses Timbang',
                    'class' => $isReady ? 'bg-accent text-success-ink outline outline-1 outline-success-deep' : 'bg-surface-pill text-ink-body',
                ],
                'tera'        => 'Petugas: ' . ($order->verifier?->name ?: 'Koordinator Gudang'),
                'armada'      => $armadaName,
                'plate'       => $plateNumber,
                'driver'      => $driverName,
                'sj'          => $sjNumber,
                'issued_at'   => ($order->updated_at ?: now())->format('d F Y (H:i') . ' WIB)',
                'handover_at' => 'Gudang Pusat Sentra // Siap Muat',
                'auth_key'    => 'GPA-VERIF-' . substr(hash('sha256', $sjNumber), 0, 8),
                'document'    => [
                    'client'       => $clientName,
                    'drop_point'   => $address,
                    'address'      => $address,
                    'pic'          => 'PIC Klien: ' . ($order->user?->name ?: 'Bagian Penerimaan') . ($order->user?->phone ? ' (' . $order->user->phone . ')' : ''),
                    'armada'       => $armadaName,
                    'plate'        => $plateNumber,
                    'driver'       => $driverName,
                    'sim'          => 'SIM: ' . ($driver?->phone ?: 'Terdata di Sistem'),
                    'chiller'      => 'Suhu Ruang Mobil: 4°C - 6°C (Cold Chain)',
                    'officer'      => $order->verifier?->name ?: 'Koordinator Lapangan',
                    'officer_role' => 'Koordinator Penimbangan Gudang',
                    'officer_note' => 'Sayur telah disortir dan ditimbang sesuai standar mutu.',
                    'estimate'     => number_format($estimateWeight, 1, ',', '.') . ' kg',
                    'netto'        => number_format($nettoWeight, 1, ',', '.') . ' kg',
                    'delta'        => $deviationStr,
                    'lines'        => $itemLines,
                ],
            ];
        }

        return view('sekretaris.dispatch', [
            'rows'                => $rows,
            'readyOrdersCount'    => $readyOrdersCount,
            'shippingOrdersCount' => $shippingOrdersCount,
            'doneOrdersCount'     => $doneOrdersCount,
        ]);
    }
}
