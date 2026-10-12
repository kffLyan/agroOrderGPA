<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Menampilkan daftar tugas harian dan antrean titik pengantaran armada.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Cari pesanan yang ditugaskan ke supir ini
        $orders = Order::with(['orderItems.product', 'user', 'driver'])
            ->where(function ($query) use ($user) {
                $query->where('driver_id', $user?->id)
                    ->orWhereIn('status', ['DALAM_PENGIRIMAN', 'SIAP_KIRIM']);
            })
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->latest()
            ->get();

        // Jika belum ada pesanan aktif sama sekali, ambil pesanan terverifikasi terbaru untuk ditampilkan
        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver'])
                ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])
                ->latest()
                ->take(5)
                ->get();
        }

        // Hitung filter status
        $readyCount = $orders->where('status', 'SIAP_KIRIM')->count();
        $roadCount  = $orders->where('status', 'DALAM_PENGIRIMAN')->count();
        $doneCount  = $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $allCount   = $orders->count();

        $filters = [
            ['key' => 'all',   'label' => 'SEMUA',   'count' => $allCount,   'active' => true],
            ['key' => 'ready', 'label' => 'SIAP',    'count' => $readyCount, 'active' => false],
            ['key' => 'road',  'label' => 'JALAN',   'count' => $roadCount,  'active' => false],
            ['key' => 'done',  'label' => 'SELESAI', 'count' => $doneCount,  'active' => false],
        ];

        // Hitung total muatan dari pesanan aktif
        $totalWeightKg = 0;
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $totalWeightKg += (float) ($item->net_weight ?? $item->quantity ?? 0);
            }
        }

        $activePlate = $orders->first(fn ($o) => !empty($o->vehicle_plate_number))?->vehicle_plate_number ?? 'B 9284 TDA';

        $operator = [
            'name'     => $user?->name ?: 'Supir Pengiriman',
            'role'     => 'Supir Pengiriman GPA',
            'initials' => strtoupper(substr($user?->name ?: 'SP', 0, 2)),
            'email'    => $user?->email ?: 'armada@greenpasundan.id',
            'plate'    => $activePlate,
        ];

        $header = [
            'brand' => 'AGROORDER GPA',
            'role'  => 'Armada Logistik',
            'plate' => $activePlate,
            'tone'  => 'light',
        ];

        $assignment = [
            'label'          => 'SUPIR BERTUGAS',
            'name'           => $user?->name ?: 'Supir Bertugas',
            'code'           => 'DVR-GPA-' . str_pad($user?->id ?: 1, 2, '0', STR_PAD_LEFT),
            'unit'           => 'Unit CDD Reefer',
            'slot'           => '#03',
            'status_label'   => $roadCount > 0 ? 'DALAM' : 'SIAP',
            'status_tail'    => $roadCount > 0 ? 'PENGIRIMAN' : 'PENGANTARAN',
            'deadline_label' => 'TARGET:',
            'deadline'       => '08:30 WIB',
        ];

        $drops = [];
        $seq = 1;
        foreach ($orders as $order) {
            $orderNetWeight = 0;
            $itemsSummary = [];
            foreach ($order->orderItems as $item) {
                $itemKg = (float) ($item->net_weight ?? $item->quantity ?? 0);
                $orderNetWeight += $itemKg;
                $itemsSummary[] = [
                    'label'  => $item->product?->name ?: 'Komoditas Sayur',
                    'weight' => number_format($itemKg, 1, ',', '.') . ' kg',
                ];
            }

            $state = match ($order->status) {
                'DALAM_PENGIRIMAN'              => 'road',
                'SELESAI', 'SELESAI_CATATAN'   => 'done',
                default                         => 'ready',
            };

            $stateLabel = match ($order->status) {
                'DALAM_PENGIRIMAN'              => 'ON ROAD',
                'SELESAI', 'SELESAI_CATATAN'   => 'SELESAI',
                default                         => 'SIAP KIRIM',
            };

            $timeVal = $order->arrival_time
                ? $order->arrival_time->format('H:i \W\I\B')
                : ($order->departure_time ? $order->departure_time->format('H:i \W\I\B') : '07:30 WIB');

            $sjNum = $order->surat_jalan_number
                ?: ('SJ-' . date('Ymd', strtotime($order->created_at)) . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));

            $isDone = in_array($order->status, ['SELESAI', 'SELESAI_CATATAN']);

            $drops[] = [
                'id'             => $order->id,
                'key'            => 'drop-' . $order->id,
                'sequence_label' => 'DROP KE-' . $seq++,
                'state_label'    => $stateLabel,
                'state'          => $state,
                'sequence_tone'  => $state === 'road' ? 'ink' : 'neutral',
                'time_label'     => $isDone ? 'Tiba' : 'Target',
                'time'           => $timeVal,
                'time_tone'      => $state === 'road' ? 'warning' : 'ink',
                'priority'       => $state === 'road' ? 'PRIORITAS UTAMA' : 'STANDAR',
                'sj'             => $sjNum,
                'client'         => $order->user?->name ?: 'Klien Penerima',
                'address'        => $order->delivery_address ?: 'Gudang Penerima Klien',
                'cargo_label'    => 'RINCIAN MUATAN (' . count($itemsSummary) . ' KOMODITAS)',
                'cargo_net'      => 'NETTO: ' . number_format($orderNetWeight, 1, ',', '.') . ' KG',
                'items'          => $itemsSummary,
                'window_label'   => 'STATUS PENGIRIMAN:',
                'window'         => match ($order->status) {
                    'DALAM_PENGIRIMAN' => 'Armada sedang menuju lokasi bongkar muat',
                    'SELESAI'          => 'Barang telah diterima utuh di lokasi',
                    'SELESAI_CATATAN'  => 'Barang telah diterima dengan catatan retur',
                    default            => 'Menunggu keberangkatan dari gudang sentral',
                },
                'window_tone'    => $order->status === 'DALAM_PENGIRIMAN' ? 'warning' : 'ink',
                'primary'        => [
                    'label'    => $order->status === 'DALAM_PENGIRIMAN'
                        ? 'PROSES SCAN POD'
                        : ($isDone ? 'POD TERSELESAIKAN' : 'BUKA SURAT JALAN'),
                    'icon'     => $order->status === 'DALAM_PENGIRIMAN' ? 'qrcode' : ($isDone ? 'check' : 'file-text'),
                    'locked'   => false,
                    'order_id' => $order->id,
                ],
                'secondary'      => [
                    'label' => 'CEK PETA RUTE',
                    'icon'  => 'map-pin',
                ],
                'muted'          => $isDone,
            ];
        }

        $support = [
            'label'       => 'BANTUAN OPERASIONAL DISPATCH',
            'phone'       => '(021) 890-1234',
            'action'      => 'HUBUNGI',
            'audit_title' => 'CATATAN OPERASIONAL:',
            'audit'       => [
                ['label' => 'Hotline Koordinator Gudang Sentra', 'status' => 'AKTIF 24/7'],
                ['label' => 'Suhu Reefer Standar Sayur Segar', 'status' => '+2°C s.d. +6°C'],
            ],
            'footer'      => 'AGROORDER GPA &bull; SISTEM LOGISTIK ARMADA LAPANGAN',
        ];

        return view('armada.tasks', [
            'operator'         => $operator,
            'header'           => $header,
            'assignment'       => $assignment,
            'filters'          => $filters,
            'queue_title'      => 'Antrean Titik Pengantaran',
            'drops'            => $drops,
            'support'          => $support,
            'activeNav'        => 'tasks',
            'component'        => 'armadaTasks',
            'componentPayload' => $filters,
        ]);
    }
}
