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
        $user = $request->user();

        $operator = [
            'name' => $user?->name ?? 'Koordinator Lapangan',
            'role' => 'Koordinator Lapangan',
            'code' => '002',
            'initials' => strtoupper(substr($user?->name ?? 'KL', 0, 2)),
        ];

        // 1. Header
        $header = [
            'eyebrow' => 'Pengiriman & Logistik',
            'title_before' => 'Penerbitan Surat Jalan &',
            'title_after' => 'Pengiriman Sayur ke Klien',
            'subtitle' => 'Pencetakan surat jalan resmi berdasarkan hasil timbangan bersih dan penugasan supir pengantar.',
            'actions' => [
                ['key' => 'log', 'label' => 'Riwayat Surat Jalan', 'icon' => 'file-text', 'variant' => 'ghost'],
                ['key' => 'thermal', 'label' => 'Cetak Surat Jalan', 'icon' => 'printer', 'variant' => 'ink', 'badge' => 'thermal'],
            ],
        ];

        // 2. SOP Mandatori
        $sop = [
            'title' => 'ATURAN RESMI PENGIRIMAN',
            'badge' => 'WAJIB TIMBANG BERSIH',
            'body' => 'Surat jalan hanya dapat dicetak setelah berat bersih sayur selesai ditimbang dan dikunci di sistem. Ini memastikan jumlah sayur yang dikirim sama dengan data pesanan.',
            'cert_prefix' => 'STANDAR:',
            'cert_value' => 'MUTU TERJAMIN',
            'lockout_code' => '904',
        ];

        // 3. Query Database
        $drivers = User::where('role', 'ARMADA')->get();
        $orders = Order::with(['orderItems.product', 'user', 'driver'])
            ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM', 'DIKIRIM'])
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver'])->latest()->take(5)->get();
        }

        // 4. Antrean Penerbitan (Queue Items)
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

        // 5. Manifest Rows
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

        $standbyCount = count(array_filter($manifestRows, fn($r) => $r['dock_state'] === 'standby'));
        $movingCount = count(array_filter($manifestRows, fn($r) => $r['dock_state'] === 'moving'));

        $manifestFilters = [
            ['key' => 'all', 'label' => 'SEMUA DOKUMEN (' . count($manifestRows) . ')'],
            ['key' => 'standby', 'label' => 'STANDBY DOCK (' . $standbyCount . ')'],
            ['key' => 'moving', 'label' => 'SEDANG BERJALAN (' . $movingCount . ')'],
        ];

        $manifest = [
            'title' => 'Daftar Pengiriman Armada Hari Ini',
            'subtitle' => 'Pemantauan status pengiriman sayur, nomor surat jalan, dan armada supir pengantar.',
            'filter_label' => 'FILTER STATUS:',
            'filter_default' => 'SEMUA DOKUMEN (' . count($manifestRows) . ')',
            'filters' => $manifestFilters,
            'columns' => [
                'NO. SURAT JALAN',
                'SUPIR & ARMADA',
                'BERAT BERSIH',
                'JADWAL BERANGKAT',
                'STATUS BERKAS / QR',
                'STATUS PARKIR DOCK',
                'TINDAKAN',
            ],
            'rows' => $manifestRows,
        ];

        // 6. KPIs
        $totalTonase = array_sum(array_column($manifestRows, 'net'));
        $kpis = [
            [
                'key' => 'ready_docs',
                'label' => 'SURAT JALAN SIAP CETAK',
                'value' => count($queueItems),
                'unit' => 'Dokumen',
                'unit_tone' => 'quiet',
                'value_tone' => 'ink',
                'icon' => 'file-text',
                'icon_chip' => 'accent-soft',
                'icon_tone' => 'success-deep',
                'foot_label' => 'Total Berat Bersih:',
                'foot_value' => number_format($totalTonase, 2, ',', '.') . ' KG',
                'foot_tone' => 'success',
            ],
            [
                'key' => 'fleet',
                'label' => 'ARMADA MOBIL SIAP JALAN',
                'value' => max(1, $drivers->count()),
                'unit' => 'Mobil Ready',
                'unit_tone' => 'quiet',
                'value_tone' => 'ink',
                'icon' => 'truck',
                'icon_chip' => 'shell',
                'icon_tone' => 'ink',
                'foot_chips' => ['Blind Van Chiller', 'Engkel Box Pendingin'],
            ],
            [
                'key' => 'departure',
                'label' => 'JADWAL BERANGKAT AWAL',
                'value' => '04:30',
                'unit' => 'WIB',
                'unit_tone' => 'success',
                'value_tone' => 'ink',
                'icon' => 'clock',
                'icon_chip' => 'warning-soft',
                'icon_tone' => 'warning-deep',
                'foot_label' => 'Waktu Kirim:',
                'foot_chip' => 'Pagi Hari (Segar)',
            ],
            [
                'key' => 'scale',
                'label' => 'STATUS KESIAPAN SURAT JALAN',
                'value' => '100%',
                'unit' => 'Siap Cetak',
                'unit_tone' => 'ink',
                'value_tone' => 'success',
                'icon' => 'badge-check',
                'icon_chip' => 'accent',
                'icon_tone' => 'success-ink',
                'foot_label' => 'Status Mesin Cetak:',
                'foot_chip' => 'TERHUBUNG & SIAP',
                'foot_dot' => true,
            ],
        ];

        // 7. Batch Operations
        $batch = [
            'title' => 'Cetak & Ekspor Bersama',
            'subtitle' => 'Unduh data manifest pengiriman harian atau cetak semua surat jalan sekaligus.',
            'actions' => [
                ['key' => 'export', 'label' => 'Unduh Laporan Hari Ini (.csv)', 'icon' => 'download', 'variant' => 'ghost'],
                ['key' => 'audit-gate', 'label' => 'Cek Gerbang Keluar Gudang', 'icon' => 'lock', 'variant' => 'ink'],
                ['key' => 'print-all', 'label' => 'Cetak Semua Surat Jalan', 'icon' => 'printer', 'variant' => 'accent', 'wide' => true],
            ],
        ];

        $dispatch = [
            'operator' => $operator,
            'header' => $header,
            'sop' => $sop,
            'kpis' => $kpis,
            'queue' => [
                'title' => 'Antrean Penerbitan Surat Jalan',
                'meta' => count($queueItems) . ' ORDER MENUNGGU DOKUMEN CETAK',
                'items' => $queueItems,
            ],
            'manifest' => $manifest,
            'batch' => $batch,
        ];

        $viewName = view()->exists('koordinator.dispatch') ? 'koordinator.dispatch' : 'coordinator.dispatch';

        return view($viewName, [
            'operator' => $operator,
            'dispatchData' => $dispatch,
        ]);
    }
}
