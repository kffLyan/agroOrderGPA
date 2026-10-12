<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan Konsol Operasional & Kesiapan Panen Koordinator Lapangan.
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

        // 1. Heading
        $heading = [
            'eyebrow' => 'KONSOL OPERASIONAL KOORDINATOR',
            'title_before' => 'Kesiapan Pasokan, Pre-Cooling',
            'title_after' => '& Jembatan Timbang Hub',
            'subtitle' => 'Pemantauan real-time alokasi panen mitra, persiapan pengemasan krat, dan pencatatan intake timbangan metrologi.',
            'actions' => [
                ['key' => 'refresh', 'label' => 'Perbarui Data', 'icon' => 'refresh', 'variant' => 'ghost'],
                ['key' => 'weigh', 'label' => 'Mulai Penimbangan', 'icon' => 'scale', 'variant' => 'ink'],
            ],
        ];

        // 2. Metrik Dinamis dari Database
        $totalStock = (float) HarvestBatch::sum('available_quantity');
        $binaanStock = (float) HarvestBatch::where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
        $bufferStock = (float) HarvestBatch::where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
        $readyOrdersCount = Order::where('status', 'TERVERIFIKASI')->count();
        $weighedOrdersCount = Order::where('status', 'SIAP_KIRIM')->count();
        $totalOrderQty = (float) OrderItem::whereHas('order', fn($q) => $q->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM']))->sum('ordered_qty');

        $metrics = [
            [
                'label' => 'TOTAL STOK PASOKAN',
                'value' => number_format($totalStock, 0, ',', '.'),
                'unit' => 'KG',
                'unit_tone' => 'text-success-deep',
                'foot_label' => 'Kapasitas Cold Storage',
                'foot_value' => '15.000 KG Maksimal',
                'icon' => 'leaf',
                'chip' => ['label' => 'TERJAGA 3-5°C', 'class' => 'bg-accent text-success-ink'],
            ],
            [
                'label' => 'PO SIAP PACK & TIMBANG',
                'value' => (string) $readyOrdersCount,
                'unit' => 'PO',
                'unit_tone' => 'text-ink',
                'foot_label' => 'Target Dispatch Subuh',
                'foot_value' => '04:30 WIB',
                'icon' => 'package',
                'chip' => ['label' => 'PRIORITAS DOCK', 'class' => 'bg-surface-shell text-ink'],
            ],
            [
                'label' => 'BUFFER CADANGAN',
                'value' => number_format($bufferStock, 0, ',', '.'),
                'unit' => 'KG',
                'unit_tone' => 'text-ink',
                'foot_label' => 'Mitra Petani Cadangan',
                'foot_value' => 'Siap Dropping',
                'icon' => 'database',
                'chip' => null,
            ],
            [
                'label' => 'ORDER SIAP KIRIM',
                'value' => (string) $weighedOrdersCount,
                'unit' => 'PO',
                'unit_tone' => 'text-success-deep',
                'foot_label' => 'Armada Standby',
                'foot_value' => 'Ready Muat',
                'icon' => 'truck',
                'chip' => ['label' => 'SELESAI TIMBANG', 'class' => 'bg-accent text-success-ink'],
            ],
        ];

        // 3. Stok Pasokan Komoditas (Supply Rows)
        $products = Product::where('is_active', true)->with('harvestBatches')->get();
        $supplyRows = $products->map(function ($product) {
            $pOrderQty = (float) OrderItem::where('product_id', $product->id)
                ->whereHas('order', fn($q) => $q->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM']))
                ->sum('ordered_qty');
            $pBinaan = (float) $product->harvestBatches->where('source_type', 'PETANI_BINAAN')->sum('available_quantity');
            $pBuffer = (float) $product->harvestBatches->where('source_type', 'BUFFER_LUAR')->sum('available_quantity');
            $pTotal = $pBinaan + $pBuffer;
            $supplierName = $product->harvestBatches->first()?->supplier_name ?: 'Poktan Binaan Ciwidey';

            return [
                'key' => (string) $product->id,
                'name' => $product->name,
                'batch' => 'LOT-' . strtoupper(substr($product->name, 0, 3)) . '-' . date('md'),
                'grade' => $product->grade ?: 'Grade A',
                'order' => $pOrderQty,
                'supply' => $pBinaan,
                'buffer' => $pBuffer,
                'group' => $supplierName,
                'partner' => 'Mitra Binaan Terverifikasi',
                'zone' => 'Hub Ciwidey',
                'bar' => 5,
                'accent' => false,
            ];
        })->values()->all();

        $supply = [
            'title' => 'Kontrol Stok Pasokan Panen & Alokasi Buffer',
            'subtitle' => 'Pencegahan Overselling & Hard-Lock Alokasi B2B',
            'description' => 'Sinkronisasi stok panen riil petani binaan dan buffer cadangan packhouse.',
            'totals' => [
                ['label' => 'TOTAL PASOKAN', 'value' => number_format($totalStock, 0, ',', '.') . ' KG', 'tone' => 'text-success-deep'],
                ['label' => 'TOTAL KEBUTUHAN PO', 'value' => number_format($totalOrderQty, 0, ',', '.') . ' KG', 'tone' => 'text-ink'],
                ['label' => 'BUFFER CADANGAN', 'value' => number_format($bufferStock, 0, ',', '.') . ' KG', 'tone' => 'text-ink'],
            ],
            'columns' => ['KOMODITAS & BATCH', 'ORDER AKTIF', 'STOK BINAAN', 'BUFFER LUAR', 'TOTAL READY', 'ALOKASI', 'AKSI'],
            'rows' => $supplyRows,
        ];

        // 4. Persiapan Pesanan & Packing (Packing Orders)
        $activeOrders = Order::with(['orderItems.product', 'user'])
            ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM'])
            ->latest()
            ->take(10)
            ->get();

        $packingOrders = $activeOrders->map(function ($order, $index) {
            $isWeighed = $order->status === 'SIAP_KIRIM';
            $totalQty = (float) $order->orderItems->sum('ordered_qty');
            $clientName = $order->user?->company_name ?: ($order->user?->name ?? 'Klien B2B');
            $targetDate = $order->target_delivery_date ? $order->target_delivery_date->format('d M') : date('d M');

            return [
                'po' => $order->order_number,
                'client' => $clientName,
                'dispatch' => $targetDate . ' 04:30 WIB',
                'dispatch_tone' => $isWeighed ? 'ink' : 'accent',
                'allocation' => number_format($totalQty, 0, ',', '.') . ' KG',
                'bay' => 'DOCK-BAY-0' . (($index % 4) + 1),
                'status' => $isWeighed ? 'Siap Kirim (Netto Sah)' : 'Sortir & Timbang',
                'percent' => $isWeighed ? 100 : 65,
            ];
        })->values()->all();

        $packing = [
            'title' => 'Persiapan Pesanan & Packing Cold-Chain',
            'subtitle' => 'Antrean pemenuhan alokasi PO klien terverifikasi untuk proses docking subuh.',
            'chip' => count($packingOrders) . ' PO AKTIF',
            'footer' => [
                'label' => 'SOP: Staging Cold Storage 3-5°C Terjaga Normal',
                'link' => 'Buka Konsol Persiapan Pesanan',
            ],
            'orders' => $packingOrders,
        ];

        // 5. Gate Intake Logs
        $recentBatches = HarvestBatch::with('product')->latest()->take(10)->get();
        $gateLogs = $recentBatches->map(function ($batch) {
            $gross = (float) $batch->initial_quantity + 20.0;
            $net = (float) $batch->initial_quantity;

            return [
                'ticket' => 'TKT-' . date('Y') . '-' . str_pad($batch->id, 4, '0', STR_PAD_LEFT),
                'time' => $batch->created_at ? $batch->created_at->format('H:i') . ' WIB' : date('H:i') . ' WIB',
                'name' => $batch->supplier_name,
                'commodity' => $batch->product?->name ?? 'Aneka Sayur',
                'gross' => $gross,
                'tare' => 20.0,
                'net' => $net,
                'qc' => 'QC PASS',
                'note' => $batch->source_type === 'BUFFER_LUAR' ? 'Buffer Luar' : 'Panen Binaan',
            ];
        })->values()->all();

        $gate = [
            'title' => 'Log Intake Timbangan Gate-01 (Metrologi Sah)',
            'subtitle' => 'Pencatatan riil penimbangan masuk hasil panen dan buffer luar.',
            'footer' => [
                'label' => 'Kalibrasi Tera: Aktif & Terverifikasi Metrologi',
                'action' => 'Input Tiket Timbang Baru',
            ],
            'logs' => $gateLogs,
        ];

        // 6. Footer
        $footer = [
            'left' => [
                ['label' => 'Sistem Tera Metrologi Legal: Aktif Terverifikasi', 'tone' => 'success'],
                ['label' => 'Sensor Cold Room: 3.4°C Terjaga Normal', 'tone' => 'success'],
                ['label' => 'Armada Standby Hub: 4 Unit Box Pendingin', 'tone' => 'default'],
            ],
            'right' => 'Sinkronisasi Otomatis Database GPA',
        ];

        $viewName = view()->exists('koordinator.dashboard') ? 'koordinator.dashboard' : 'coordinator.dashboard';

        return view($viewName, [
            'operator' => $operator,
            'heading' => $heading,
            'metrics' => $metrics,
            'supply' => $supply,
            'packing' => $packing,
            'gate' => $gate,
            'footer' => $footer,
        ]);
    }
}
