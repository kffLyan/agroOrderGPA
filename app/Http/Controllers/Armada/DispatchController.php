<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispatchController extends Controller
{
    /**
     * Menampilkan Surat Jalan Digital & Rincian Muatan Supir
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Cari pesanan yang menjadi target surat jalan
        $targetOrderId = $request->query('order_id');
        $query = Order::with(['orderItems.product', 'user', 'driver', 'verifier']);

        if ($targetOrderId) {
            $currentOrder = $query->find($targetOrderId);
        } else {
            $currentOrder = $query->where(function ($q) use ($user) {
                $q->where('driver_id', $user?->id)
                  ->orWhereIn('status', ['DALAM_PENGIRIMAN', 'SIAP_KIRIM']);
            })
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->latest()
            ->first();
        }

        // Fallback jika belum ada pesanan aktif
        if (!$currentOrder) {
            $currentOrder = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])->latest()->first();
        }

        // Ambil daftar rute / multi-stops dari pesanan yang aktif hari ini
        $activeOrders = Order::with(['user', 'orderItems'])
            ->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])
            ->latest()
            ->take(3)
            ->get();

        if ($activeOrders->isEmpty() && $currentOrder) {
            $activeOrders = collect([$currentOrder]);
        }

        $stops = [];
        $stopIndex = 1;
        foreach ($activeOrders as $ord) {
            $ordNetWeight = 0;
            foreach ($ord->orderItems as $item) {
                $ordNetWeight += (float) ($item->net_weight ?? $item->quantity ?? 0);
            }

            $isActive = $currentOrder && $ord->id === $currentOrder->id;
            $stops[] = [
                'id'          => $ord->id,
                'badge'       => 'STOP #' . $stopIndex++,
                'badge_tone'  => $isActive ? 'ink' : 'muted',
                'dot'         => $ord->status === 'DALAM_PENGIRIMAN' ? 'success' : 'quiet',
                'status'      => match ($ord->status) {
                    'DALAM_PENGIRIMAN' => 'DALAM PENGIRIMAN',
                    'SELESAI'          => 'SELESAI TERKIRIM',
                    default            => 'SIAP KIRIM',
                },
                'status_tone' => $isActive ? 'accent' : 'muted',
                'number'      => $ord->surat_jalan_number ?: ('SJ-' . date('Ymd', strtotime($ord->created_at)) . '-' . str_pad($ord->id, 4, '0', STR_PAD_LEFT)),
                'number_tone' => $isActive ? 'strong' : 'muted',
                'client'      => $ord->user?->name ?: 'Klien Penerima',
                'client_tone' => $isActive ? 'strong' : 'muted',
                'weight_label'=> 'Muatan Bersih:',
                'weight'      => number_format($ordNetWeight, 1, ',', '.') . ' kg',
                'weight_tone' => $isActive ? 'strong' : 'muted',
                'active'      => $isActive,
            ];
        }

        $plate = $currentOrder?->vehicle_plate_number ?: 'B 9284 TDA';

        $operator = [
            'name'     => $user?->name ?: 'Supir Pengiriman',
            'role'     => 'Supir Pengiriman GPA',
            'initials' => strtoupper(substr($user?->name ?: 'SP', 0, 2)),
            'email'    => $user?->email ?: 'armada@greenpasundan.id',
            'plate'    => $plate,
        ];

        $header = [
            'brand' => 'AGROORDER GPA',
            'role'  => 'Armada Logistik',
            'plate' => $plate,
            'tone'  => 'light',
        ];

        $intro = [
            'title'    => 'Surat Jalan Digital & Rincian<br>Muatan Supir',
            'subtitle' => 'Verifikasi kesesuaian fisik muatan & segel timbangan tera sebelum keberangkatan.',
        ];

        $route = [
            'label' => 'RUTE PENGANTARAN (' . count($stops) . ' TITIK)',
            'badge' => 'MULTI-DROP HARIAN',
            'stops' => $stops,
        ];

        $sjNumber = $currentOrder?->surat_jalan_number
            ?: ('SJ-' . date('Ymd', strtotime($currentOrder?->created_at ?: now())) . '-' . str_pad($currentOrder?->id ?: 1, 4, '0', STR_PAD_LEFT));

        $poNumber = $currentOrder?->order_number ?: ('ORD-GPA-' . date('Ym') . '-0001');

        $dispatchDate = $currentOrder?->departure_time ? $currentOrder->departure_time->format('d M Y') : date('d M Y');
        $dispatchTime = $currentOrder?->departure_time ? $currentOrder->departure_time->format('H:i \W\I\B') : '06:00 WIB';

        $letter = [
            'validated'     => 'TERVALIDASI DISPATCH GUDANG',
            'number'        => $sjNumber,
            'po_label'      => 'Ref PO: ',
            'po_number'     => '#' . $poNumber,
            'dispatch_label'=> 'WAKTU DISPATCH',
            'dispatch_date' => $dispatchDate,
            'dispatch_time' => $dispatchTime,
            'chain'         => [
                [
                    'icon'       => 'building',
                    'shell'      => 'muted',
                    'role_label' => 'PENGIRIM LOGISTIK',
                    'role_tone'  => 'muted',
                    'name'       => 'Green Pasundan Agrikultur',
                    'address'    => 'Sentral Transhipment Hub - Gudang Dock 03',
                ],
                [
                    'icon'       => 'map-pin',
                    'shell'      => 'accent',
                    'role_label' => 'DOKUMEN PENERIMA',
                    'role_tone'  => 'success',
                    'name'       => $currentOrder?->user?->name ?: 'Klien Penerima Resmi',
                    'address'    => $currentOrder?->delivery_address ?: 'Alamat Penerimaan Klien',
                ],
            ],
            'pic'           => [
                'label' => 'PENANGGUNG JAWAB PENERIMA',
                'name'  => $currentOrder?->received_by_name ?: ($currentOrder?->user?->name ?: 'Bapak Hendra (Kepala Gudang)'),
                'phone' => $currentOrder?->user?->phone ?: '0812-3456-7890',
            ],
        ];

        // Rincian muatan produk dari order items
        $cargoItems = [];
        $totalEst = 0;
        $totalNetto = 0;
        $totalCrates = 0;
        $totalTare = 0;

        if ($currentOrder && $currentOrder->orderItems->isNotEmpty()) {
            foreach ($currentOrder->orderItems as $item) {
                $estKg = (float) $item->quantity;
                $nettoKg = (float) ($item->net_weight ?? $item->quantity);
                $deltaKg = $nettoKg - $estKg;
                $deltaPercent = $estKg > 0 ? ($deltaKg / $estKg) * 100 : 0;
                $crates = (int) ($item->crates ?? ceil($nettoKg / 20));
                $tare = (float) ($item->tare_weight ?? ($crates * 2.0));

                $totalEst += $estKg;
                $totalNetto += $nettoKg;
                $totalCrates += $crates;
                $totalTare += $tare;

                $deviationSign = $deltaKg >= 0 ? '+' : '';
                $deviationStr = $deviationSign . number_format($deltaKg, 1, ',', '.') . ' kg';
                $rateStr = '(' . $deviationSign . number_format($deltaPercent, 2, ',', '.') . '%)';

                $cargoItems[] = [
                    'sku'        => 'SKU: ' . ($item->product?->code ?: ('PROD-' . $item->id)),
                    'name'       => $item->product?->name ?: 'Sayur Segar Berkualitas',
                    'spec'       => 'Standar Mutu Super &bull; ' . $crates . ' Krat Steril',
                    'badge'      => 'LOLOS UJI',
                    'columns'    => [
                        ['label' => 'Est PO',    'label_tone' => 'muted',   'value' => number_format($estKg, 1, ',', '.') . ' kg', 'value_tone' => 'strong'],
                        ['label' => 'Netto Sah', 'label_tone' => 'success', 'value' => number_format($nettoKg, 1, ',', '.') . ' kg', 'value_tone' => 'strong'],
                        [
                            'label'      => 'Deviasi',
                            'label_tone' => 'muted',
                            'value'      => $deviationStr,
                            'value_tail' => $rateStr,
                            'value_tone' => 'success',
                        ],
                    ],
                    'containers' => 'Wadah: ' . $crates . ' Krat',
                    'tare'       => 'Tara: ' . number_format($tare, 1, ',', '.') . ' kg',
                ];
            }
        }

        $totalDelta = $totalNetto - $totalEst;
        $totalDeltaPercent = $totalEst > 0 ? ($totalDelta / $totalEst) * 100 : 0;
        $totalDeltaSign = $totalDelta >= 0 ? '+' : '';

        $cargo = [
            'rule'  => 'TIMBANGAN BERSIH TERA GUDANG SAH',
            'stamp' => [
                'title' => 'Stempel Timbangan Digital Tera Sah',
                'body'  => 'Tera Sah Digital Transhipment Sentral Hub &bull; Sertifikasi Mutu',
            ],
            'items' => $cargoItems,
            'total' => [
                'label'      => 'TOTAL AKUMULASI TIMBANG SAH',
                'columns'    => [
                    [
                        'label'      => 'Est Awal',
                        'label_tone' => 'muted',
                        'values'     => [number_format($totalEst, 1, ',', '.') . ' kg'],
                        'value_tone' => 'plain',
                    ],
                    [
                        'label'      => 'Total Netto Sah',
                        'label_tone' => 'accent',
                        'values'     => [number_format($totalNetto, 1, ',', '.') . ' kg'],
                        'value_tone' => 'accent',
                    ],
                    [
                        'label'      => 'Deviasi Sah',
                        'label_tone' => 'muted',
                        'values'     => [
                            $totalDeltaSign . number_format($totalDelta, 1, ',', '.') . ' kg',
                            '(' . $totalDeltaSign . number_format($totalDeltaPercent, 2, ',', '.') . '%)'
                        ],
                        'value_tone' => 'accent',
                    ],
                ],
                'containers' => 'Wadah: ' . $totalCrates . ' Krat (' . number_format($totalTare, 1, ',', '.') . ' kg Tara)',
                'cold_chain' => '+3.8°C (Suhu Cold Chain Optimal)',
            ],
        ];

        $isStarted = $currentOrder && in_array($currentOrder->status, ['DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']);

        $gatePass = [
            'title'               => 'GATE PASS KELUAR GUDANG',
            'expiry_label'        => 'EXP:',
            'expiry_minutes'      => 45,
            'token'               => 'TOKEN: GPA-' . ($currentOrder?->id ?: '1') . '-SEC',
            'hint'                => 'Tunjukkan ke pos security gerbang sentral hub & dock penerima.',
            'seal'                => [
                'label' => 'STATUS SEGEL BOKS KENDARAAN',
                'value' => 'Segel Digital Aktif (Terkunci Otomatis)',
            ],
            'action'              => $isStarted ? 'PENGIRIMAN SEDANG BERJALAN' : 'MULAI PENGIRIMAN',
            'action_note'         => 'Status GPS Logbook & Pencatat Suhu Reefer Aktif Terkoneksi — Status: Dalam Pengiriman',
            'report'              => 'Lapor Kendala / Keterlambatan',
            'order_id'            => $currentOrder?->id,
            'is_started'          => $isStarted,
            'pic_phone'           => $currentOrder?->user?->phone ?: '0812-3456-7890',
            'destination_address' => $currentOrder?->delivery_address ?: '',
        ];

        return view('armada.dispatch', [
            'operator'         => $operator,
            'header'           => $header,
            'intro'            => $intro,
            'route'            => $route,
            'letter'           => $letter,
            'cargo'            => $cargo,
            'gatePass'         => $gatePass,
            'activeNav'        => 'dispatch',
            'component'        => 'armadaDispatch',
            'componentPayload' => $gatePass,
        ]);
    }

    /**
     * Memulai pengiriman armada (mengubah status menjadi DALAM_PENGIRIMAN)
     */
    public function startRoute(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();

        if ($order->status !== 'SELESAI') {
            $order->status = 'DALAM_PENGIRIMAN';
            if (!$order->departure_time) {
                $order->departure_time = now();
            }
            if (!$order->driver_id && $user) {
                $order->driver_id = $user->id;
            }
            $order->save();
        }

        return redirect()
            ->route('armada.dispatch', ['order_id' => $order->id])
            ->with('success', 'Pengiriman telah dimulai! Rute pengantaran menuju lokasi penerima dibuka.');
    }
}
