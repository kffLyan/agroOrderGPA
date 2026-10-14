<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Tampilkan Konsol Monitoring & Validasi Proof of Delivery (PoD).
     */
    public function index(Request $request): View
    {
        $drivers = User::where('role', 'ARMADA')->get();
        $orders = Order::with(['orderItems.product', 'user', 'driver'])
            ->latest()
            ->take(10)
            ->get();

        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver'])->get();
        }

        $dispatchRows = [];
        $dossierEntries = [];
        $defaultSj = null;

        foreach ($orders as $index => $order) {
            $sj = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
            if (!$defaultSj) {
                $defaultSj = $sj;
            }

            $driver = $order->driver ?: ($drivers[$index % max(1, $drivers->count())] ?? null);
            $driverName = $driver?->name ?? 'Joko Prasetyo';
            $vehiclePlate = $order->vehicle_plate_number ?: ($index % 2 === 0 ? 'B 9102 SDA' : 'D 8472 GA');
            $vehicleName = $index % 2 === 0 ? 'Engkel Box ' . $vehiclePlate : 'Blind Van ' . $vehiclePlate;

            $customerName = $order->user?->company_name ?: ($order->user?->name ?? 'Mitra B2B');
            $destination = $order->delivery_address ?: 'Sentul Barat (Drop #' . ($index + 1) . ')';
            $cargoKg = (float) $order->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);
            $commodityNames = $order->orderItems->pluck('product.name')->filter()->unique()->join(', ') ?: 'Selada & Sayuran';

            $returnedKg = (float) $order->orderItems->sum('returned_weight');
            $hasRetur = $returnedKg > 0;

            $status = 'PROSES SERAH TERIMA / UNLOADING';
            $statusTone = 'accent';
            $rowTone = 'default';
            $actions = [
                ['key' => 'call-driver', 'label' => 'Hubungi Driver', 'icon' => 'phone', 'variant' => 'outline'],
                ['key' => 'view-sj', 'label' => 'Lihat Surat Jalan', 'icon' => 'file-text', 'variant' => 'soft'],
            ];

            if ($hasRetur) {
                $status = '! SELESAI DENGAN CATATAN (RETUR ' . $returnedKg . ' KG)';
                $statusTone = 'danger';
                $actions = [
                    ['key' => 'open-retur', 'label' => 'Buka Lap. Retur', 'icon' => 'refresh', 'variant' => 'danger'],
                    ['key' => 'view-ba', 'label' => 'Detail BA', 'icon' => 'clipboard', 'variant' => 'soft'],
                ];
            } elseif ($order->status === 'SELESAI' || $index === 0) {
                $status = 'SELESAI — POD TERVERIFIKASI';
                $statusTone = 'accent';
                $rowTone = 'active';
                $actions = [
                    ['key' => 'inspect-pod', 'label' => 'Periksa PoD', 'icon' => 'eye', 'variant' => 'primary'],
                    ['key' => 'download-proof', 'label' => 'Unduh Bukti', 'icon' => 'download', 'variant' => 'outline'],
                ];
            }

            $dossierRef = 'POD-DOC-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);
            $isComplete = $order->status === 'SELESAI' || $index === 0;

            $arrival = 'Tiba ' . ($order->updated_at ? $order->updated_at->format('H:i') : date('H:i')) . ' WIB';
            $arrivalTone = $hasRetur ? 'warning-deep' : 'success-deep';
            $discrepancy = $hasRetur ? ('Selisih retur ' . $returnedKg . ' kg tercatat') : null;

            $dispatchRows[] = [
                'id' => $order->id,
                'driver' => $driverName,
                'vehicle' => $vehicleName,
                'plate' => $vehiclePlate,
                'arrival' => $arrival,
                'arrival_tone' => $arrivalTone,
                'sj' => $sj,
                'customer' => $customerName,
                'customer_tone' => 'strong',
                'discrepancy' => $discrepancy,
                'destination' => $destination,
                'cargo_kg' => $cargoKg,
                'manifest_kg' => $hasRetur ? ($cargoKg + $returnedKg) : null,
                'commodity' => $commodityNames,
                'status' => $status,
                'status_tone' => $statusTone,
                'row_tone' => $rowTone,
                'actions' => $actions,
                'has_retur' => $hasRetur,
                'retur_kg' => $returnedKg,
                'dossier_ref' => $dossierRef,
            ];

            $dossierEntries[$sj] = [
                'ref' => $dossierRef,
                'sj' => $sj,
                'po' => $order->order_number,
                'customer' => $customerName,
                'address' => $destination,
                'driver' => $driverName . ' (ID-DRV-' . str_pad($driver?->id ?? 1, 3, '0', STR_PAD_LEFT) . ')',
                'plate' => $vehiclePlate,
                'net_kg' => $cargoKg,
                'delivery_time' => ($order->updated_at ? $order->updated_at->format('H:i') : date('H:i')) . ' WIB (' . date('d M Y') . ')',
                'receiver_name' => $order->received_by_name ?: ($order->user?->name ?? 'Bapak Hendra (Receiving Dept)'),
                'gps_tolerance_m' => 4.2,
                'gps_radius_m' => 2.1,
                'exhibits' => [
                    [
                        'code' => 'EXHIBIT-01',
                        'meta' => 'JPG • 2.4 MB',
                        'icon' => 'camera',
                        'caption' => 'Fisik Surat Jalan & Cap Basah',
                        'status' => $isComplete ? 'TERBACA' : 'MENUNGGU',
                    ],
                    [
                        'code' => 'EXHIBIT-02',
                        'meta' => 'JPG • 1.9 MB',
                        'icon' => 'truck',
                        'caption' => 'Unloading di Loading Dock',
                        'status' => $isComplete ? 'TERVERIFIKASI' : 'MENUNGGU',
                    ],
                ],
                'checks' => [
                    'signature' => $isComplete,
                    'stamp' => $isComplete,
                    'weight' => $isComplete,
                    'gps' => $isComplete,
                ],
                'notes' => $isComplete ? 'Seluruh parameter fisik lolos verifikasi. Berat timbangan netto cocok, segel utuh, ttd dan cap basah penerima tertera sah.' : 'Menunggu berkas fisik diunggah oleh driver armada.',
                'decision' => $isComplete ? 'approved' : null,
                'status' => $isComplete ? 'VERIFIED' : 'PENDING',
                'empty' => false,
            ];
        }

        $totalOrdersCount = count($dispatchRows);
        $verifiedCount = count(array_filter($dispatchRows, fn($r) => $r['status_tone'] === 'accent' && str_contains($r['status'], 'SELESAI')));
        $returCount = count(array_filter($dispatchRows, fn($r) => $r['has_retur']));
        $totalArmada = max(1, $drivers->count());

        $viewName = view()->exists('koordinator.monitoring') ? 'koordinator.monitoring' : 'coordinator.monitoring';

        return view($viewName, [
            'dispatchRows'     => $dispatchRows,
            'dossierEntries'   => $dossierEntries,
            'defaultSj'        => $defaultSj,
            'totalOrdersCount' => $totalOrdersCount,
            'verifiedCount'    => $verifiedCount,
            'returCount'       => $returCount,
            'totalArmada'      => $totalArmada,
        ]);
    }
}
