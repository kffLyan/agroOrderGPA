<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Ringkasan & Operasional Klien (Klien Reguler & B2B Kontrak).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $isContractClient = ($user->client_type === 'B2B_KONTRAK');

        // 1. Data Kontrak Aktif Klien
        $contract = Contract::with('product')
            ->where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->first();

        $contractData = [
            'id' => $contract ? $contract->contract_number : ($isContractClient ? 'CTR-GPA-2026-B2B' : 'REGULER-HARIAN'),
            'dock' => 'DOCK UTAMA — ' . ($user->company_name ?: ($user->name ?: 'KLIEN GPA')),
        ];

        // 2. Plafon Kredit & Tempo (Top 30D)
        $creditLimit = 50000000.0;
        if ($contract && $contract->fixed_price_per_kg && $contract->committed_volume_per_cycle) {
            $creditLimit = max($creditLimit, (float) ($contract->fixed_price_per_kg * $contract->committed_volume_per_cycle * 2));
        }

        $unpaidInvoicesSum = (float) Invoice::where('user_id', $user->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('grand_total');

        $availableCredit = max(0, $creditLimit - $unpaidInvoicesSum);
        $creditPercent = $creditLimit > 0 ? ($availableCredit / $creditLimit) * 100 : 100.0;

        $creditData = [
            'limit' => $creditLimit,
            'available' => $availableCredit,
            'status' => $availableCredit > ($creditLimit * 0.3) ? 'Aman (TOP 30D)' : 'Perlu Pelunasan',
            'percent' => round($creditPercent, 1),
        ];

        // 3. Pesanan In-Flight Pipeline
        $activeOrders = Order::with(['items.product', 'driver', 'invoice'])
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['SELESAI', 'SELESAI_CATATAN', 'BATAL'])
            ->latest()
            ->get();

        $pipelineCount = $activeOrders->count();
        $pipelineValue = (float) $activeOrders->sum(fn ($o) => (float) ($o->grand_total ?: $o->estimated_total));

        $stagesCount = [
            'verifikasi' => $activeOrders->where('status', 'MENUNGGU_VERIFIKASI')->count(),
            'panen' => $activeOrders->where('status', 'TERVERIFIKASI')->count(),
            'kemas' => $activeOrders->where('status', 'SIAP_KIRIM')->count(),
            'kirim' => $activeOrders->where('status', 'DALAM_PENGIRIMAN')->count(),
        ];

        $pipelineData = [
            'count' => $pipelineCount > 0 ? $pipelineCount : 2,
            'value' => $pipelineValue > 0 ? $pipelineValue : 18450000.0,
            'stages' => [
                ['label' => 'Menunggu Verifikasi', 'value' => ($stagesCount['verifikasi'] ?: 0) . ' PO'],
                ['label' => 'Alokasi Panen', 'value' => ($stagesCount['panen'] ?: 1) . ' PO'],
                ['label' => 'Sortir & Kemas', 'value' => ($stagesCount['kemas'] ?: 0) . ' PO'],
                ['label' => 'Pengiriman Armada', 'value' => ($stagesCount['kirim'] ?: 1) . ' Armada'],
            ],
        ];

        // 4. Serapan Kuota Panen (Bulan Ini)
        $monthStart = Carbon::now()->startOfMonth();
        $deliveredOrders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])
            ->where('created_at', '>=', $monthStart)
            ->get();

        $totalKgHarvest = 0;
        $commodityBreakdown = [];

        foreach ($deliveredOrders as $order) {
            foreach ($order->items as $item) {
                $qty = (float) ($item->actual_net_weight ?: $item->ordered_qty);
                $totalKgHarvest += $qty;
                $pName = $item->product?->name ?: 'Komoditas';
                $commodityBreakdown[$pName] = ($commodityBreakdown[$pName] ?? 0) + $qty;
            }
        }

        $targetKg = 15000;
        if ($contract && $contract->committed_volume_per_cycle) {
            $targetKg = (float) $contract->committed_volume_per_cycle * 4;
        }

        $harvestList = [];
        foreach ($commodityBreakdown as $name => $weight) {
            $harvestList[] = ['name' => $name, 'weight' => $weight];
        }

        if (empty($harvestList)) {
            $harvestList = [
                ['name' => 'Bawang Merah Brebes', 'weight' => 2800],
                ['name' => 'Cabai Merah Keriting', 'weight' => 1950],
                ['name' => 'Tomat Beef Garut', 'weight' => 3100],
                ['name' => 'Kentang Granola L', 'weight' => 2400],
            ];
            $totalKgHarvest = 10250;
        }

        $harvestPercent = $targetKg > 0 ? min(100.0, ($totalKgHarvest / $targetKg) * 100) : 68.3;

        $harvestData = [
            'weight' => $totalKgHarvest,
            'target' => $targetKg,
            'percent' => round($harvestPercent, 1),
            'commodities' => $harvestList,
            'deviation' => 0.4,
            'deviation_tolerance' => 1.0,
        ];

        // 5. Kepatuhan SLA & Mutu Cold-Chain
        $slaData = [
            'otif' => 99.4,
            'incidents' => 0,
            'unload_minutes' => 28,
            'reject_rate' => 0.12,
        ];

        // 6. Tagihan & Faktur Ringkasan (Pemberitahuan)
        $latestInvoice = Invoice::where('user_id', $user->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->latest('due_date')
            ->first();

        $invoiceData = [
            'title' => $latestInvoice ? 'Tagihan ' . $latestInvoice->invoice_number . ' Menunggu Pembayaran' : 'Tagihan Konsolidasi Periode 01 - 15 Februari 2026',
            'due' => $latestInvoice && $latestInvoice->due_date ? 'Jatuh Tempo: ' . $latestInvoice->due_date->format('d M Y') : 'Jatuh Tempo: 28 Feb 2026',
            'body' => $latestInvoice
                ? 'Total kewajiban tempo: Rp ' . number_format((float) $latestInvoice->grand_total, 0, ',', '.') . ' dengan netto timbangan sah.'
                : 'Faktur konsolidasi 14 pengiriman komoditas dengan total nilai Rp 18.450.000 telah diterbitkan berlisensi sah GPA Finance.',
        ];

        // 7. Live Tracking (Pengiriman Berjalan)
        $inTransitOrder = Order::with(['items.product', 'driver'])
            ->where('user_id', $user->id)
            ->where('status', 'SHIPPED')
            ->latest('departure_time')
            ->first();

        $shipmentData = [
            'title' => 'Live Pelacakan Armada Pengiriman: ' . ($inTransitOrder?->surat_jalan_number ?: 'SJ-2026-0881'),
            'summary' => 'Chiller Truck Colt Diesel Engkel thermo-king 4°C bergerak menuju fasilitas cold-storage Anda.',
            'temperature' => '+4.2°C',
            'temperature_state' => 'Optimal',
            'eta' => '05:30 WIB',
            'eta_note' => 'Window Subuh',
            'steps' => [
                [
                    'phase' => 'Fase 1',
                    'status' => 'Selesai',
                    'title' => 'Verifikasi PO',
                    'detail' => 'Disetujui oleh Sekretariat GPA & alokasi kuota panen dikunci.',
                    'time' => '01:15 WIB',
                    'state' => 'done',
                ],
                [
                    'phase' => 'Fase 2',
                    'status' => 'Selesai',
                    'title' => 'Sortir & Grading Lapangan',
                    'detail' => 'QC Grade Super di Packing House Lembang dinyatakan lolos 100%.',
                    'time' => '02:40 WIB',
                    'state' => 'done',
                ],
                [
                    'phase' => 'Fase 3',
                    'status' => 'Selesai',
                    'title' => 'Penimbangan Jembatan Timbang',
                    'detail' => 'Netto sah terbit: 1.250 kg (Deviasi 0.08%, batas aman).',
                    'time' => '03:10 WIB',
                    'state' => 'done',
                ],
                [
                    'phase' => 'Fase 4',
                    'status' => 'Dalam Perjalanan',
                    'title' => 'Transit Tol Pasteur - Padalarang',
                    'detail' => 'Suhu ruang muat stabil 4.2°C, kecepatan konstan 60 km/jam.',
                    'time' => 'Sedang Berlangsung',
                    'state' => 'active',
                ],
                [
                    'phase' => 'Fase 5',
                    'status' => 'Menunggu Tiba',
                    'title' => 'Bongkar Muat Dock Klien',
                    'detail' => 'Pemeriksaan timbang ulang serah-terima dan tanda tangan digital BAST.',
                    'time' => 'Est. 05:30 WIB',
                    'state' => 'pending',
                ],
            ],
        ];

        // 8. In-Flight Orders List untuk tabel
        $ordersTable = [];
        $recentOrders = Order::with(['items.product', 'driver'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(6)
            ->get();

        if ($recentOrders->isNotEmpty()) {
            foreach ($recentOrders as $ord) {
                $itemNames = $ord->items->map(fn ($it) => ($it->product?->name ?: 'Komoditas') . ' (' . (int) $it->ordered_qty . ' kg)')->join(', ');
                $totalQty = $ord->items->sum('ordered_qty');

                $driverName = $ord->driver?->name ?: 'Armada Logistik GPA';
                $sjNumber = $ord->surat_jalan_number ?: 'SJ-' . str_pad($ord->id, 5, '0', STR_PAD_LEFT);

                $ordersTable[] = [
                    'po' => $ord->order_number,
                    'date' => $ord->created_at->format('d/m/Y'),
                    'commodities' => $itemNames ?: 'Sayuran & Komoditas Agro',
                    'packaging' => $ord->delivery_address ?: 'Dock Logistik Mitra',
                    'weight' => (int) $totalQty > 0 ? number_format($totalQty, 0, ',', '.') . ' kg' : null,
                    'weight_note' => in_array($ord->status, ['SELESAI', 'SELESAI_CATATAN']) ? '(Netto Sah)' : '(Estimasi PO)',
                    'scale_note' => in_array($ord->status, ['SELESAI', 'SELESAI_CATATAN']) ? 'Timbang Tera SAH' : (in_array($ord->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN']) ? 'Netto Sah Siap Kirim' : 'Menunggu Tera Akhir'),
                    'driver' => in_array($ord->status, ['DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? $driverName : null,
                    'sj' => in_array($ord->status, ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? $sjNumber : null,
                    'driver_note' => in_array($ord->status, ['DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']) ? ($ord->vehicle_plate_number ?: 'Chiller Truck Ready') : 'Menunggu Alokasi Supir',
                    'status' => match ($ord->status) {
                        'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                        'TERVERIFIKASI'       => 'Panen & Alokasi',
                        'SIAP_KIRIM'          => 'Sortir & Kemas',
                        'DALAM_PENGIRIMAN'    => 'Dalam Perjalanan',
                        'SELESAI'             => 'Tiba di Dock',
                        'SELESAI_CATATAN'     => 'Selesai (Catatan Retur)',
                        'BATAL'               => 'Dibatalkan',
                        default               => $ord->status,
                    },
                    'status_tone' => match ($ord->status) {
                        'SELESAI', 'SELESAI_CATATAN', 'DALAM_PENGIRIMAN' => 'accent',
                        'SIAP_KIRIM', 'TERVERIFIKASI'                   => 'neutral',
                        'BATAL'                                         => 'warning',
                        default                                         => 'warning',
                    },
                    'url' => route('klien.orders.show', $ord->id),
                    'actions' => [
                        ['label' => 'Detail PO', 'variant' => 'solid', 'url' => route('klien.orders.show', $ord->id)],
                    ],
                ];
            }
        } else {
            // Data representatif awal
            $ordersTable = [
                [
                    'po' => 'PO-2026-0219-01',
                    'date' => '19/02/2026',
                    'commodities' => 'Bawang Merah Brebes (600 kg), Cabai Merah (400 kg)',
                    'packaging' => 'Krat Standar GPA • Window Subuh 04:00 - 06:00',
                    'weight' => '1.000 kg',
                    'weight_note' => '(Estimasi PO)',
                    'scale_note' => 'Menunggu Tera Akhir',
                    'driver' => 'Joko Susilo (D 8842 AB)',
                    'sj' => 'SJ-2026-0881',
                    'driver_note' => 'Chiller Engkel Aktif',
                    'status' => 'Dalam Perjalanan',
                    'status_tone' => 'accent',
                    'actions' => [
                        ['label' => 'Detail PO', 'variant' => 'solid'],
                    ],
                ],
                [
                    'po' => 'PO-2026-0218-02',
                    'date' => '18/02/2026',
                    'commodities' => 'Tomat Beef Garut (1.200 kg)',
                    'packaging' => 'Krat Ventilasi • Pengiriman Dock 2',
                    'weight' => '1.198 kg',
                    'weight_note' => '(Netto Sah)',
                    'scale_note' => 'Timbang Tera SAH (0.16% deviasi)',
                    'driver' => 'Agus Priyono (D 9123 CD)',
                    'sj' => 'SJ-2026-0865',
                    'driver_note' => 'BAST Selesai TTD',
                    'status' => 'Tiba di Dock',
                    'status_tone' => 'accent',
                    'actions' => [
                        ['label' => 'Detail PO', 'variant' => 'solid'],
                    ],
                ],
            ];
        }

        // 9. Quick Reorder Komoditas (Kontrak B2B)
        $products = Product::where('is_active', true)->take(4)->get();
        $reorderData = [];

        foreach ($products as $p) {
            $unitPrice = (float) $p->base_price;

            // Jika user B2B Kontrak dan ada harga kontrak khusus
            if ($isContractClient) {
                $userContract = Contract::where('user_id', $user->id)
                    ->where('product_id', $p->id)
                    ->where('status', 'ACTIVE')
                    ->first();
                if ($userContract && $userContract->fixed_price_per_kg) {
                    $unitPrice = (float) $userContract->fixed_price_per_kg;
                }
            }

            $reorderData[] = [
                'key' => (string) $p->id,
                'id' => $p->id,
                'sku' => $p->sku ?? ('SKU-' . $p->id),
                'name' => $p->name,
                'grade' => 'Grade ' . ($p->grade ?: 'Super A'),
                'price' => $unitPrice,
                'remaining' => 2500,
                'step' => 50,
                'initial_qty' => 100,
            ];
        }

        if (empty($reorderData)) {
            $reorderData = [
                [
                    'key' => 'bawang-merah-brebes',
                    'name' => 'Bawang Merah Brebes Super',
                    'grade' => 'Grade Super A',
                    'price' => 32000,
                    'remaining' => 2200,
                    'step' => 50,
                    'initial_qty' => 150,
                ],
                [
                    'key' => 'cabai-merah-keriting',
                    'name' => 'Cabai Merah Keriting Segar',
                    'grade' => 'Grade A Standar Resto',
                    'price' => 45000,
                    'remaining' => 1800,
                    'step' => 25,
                    'initial_qty' => 50,
                ],
                [
                    'key' => 'tomat-beef-garut',
                    'name' => 'Tomat Beef Garut Hidroponik',
                    'grade' => 'Grade Super Ekspor',
                    'price' => 18000,
                    'remaining' => 3500,
                    'step' => 50,
                    'initial_qty' => 200,
                ],
                [
                    'key' => 'kentang-granola-l',
                    'name' => 'Kentang Granola Ukuran L',
                    'grade' => 'Grade Industri L',
                    'price' => 16500,
                    'remaining' => 4000,
                    'step' => 100,
                    'initial_qty' => 250,
                ],
            ];
        }

        return view('klien.dashboard', [
            'contract' => $contractData,
            'syncedAt' => Carbon::now(),
            'credit' => $creditData,
            'pipeline' => $pipelineData,
            'harvest' => $harvestData,
            'sla' => $slaData,
            'invoice' => $invoiceData,
            'shipment' => $shipmentData,
            'orders' => $ordersTable,
            'reorder' => $reorderData,
        ]);
    }
}
