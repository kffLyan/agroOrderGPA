<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan Dashboard Konsol Operasional & Tata Kelola Administrasi Sekretaris.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today();

        // 1. Ambil data pesanan riil dari database
        $allOrders = Order::with(['user', 'orderItems.product', 'payments', 'driver'])
            ->latest()
            ->get();

        $pendingOrders = $allOrders->where('status', 'MENUNGGU_VERIFIKASI');
        $verifiedOrders = $allOrders->where('status', 'TERVERIFIKASI');
        $readyOrders = $allOrders->where('status', 'SIAP_KIRIM');
        $shippingOrders = $allOrders->where('status', 'DALAM_PENGIRIMAN');
        $completedOrders = $allOrders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN']);

        // 2. Pembayaran menunggu verifikasi
        $pendingPaymentsCount = Payment::whereIn('status', ['MENUNGGU_VERIFIKASI', 'PENDING'])->count();

        $totalTodayCount = $allOrders->where('created_at', '>=', $today)->count();

        // 3. Antrean Verifikasi Pesanan Masuk (Queue Table)
        $queueRows = [];
        $ordersToDisplay = $pendingOrders->isNotEmpty() ? $pendingOrders->take(6) : $allOrders->take(4);

        if ($ordersToDisplay->isNotEmpty()) {
            foreach ($ordersToDisplay as $ord) {
                $clientName = $ord->user?->company_name ?: ($ord->user?->name ?: 'Mitra Klien GPA');
                $isContract = ($ord->user?->client_type === 'B2B_KONTRAK');

                $itemsList = $ord->orderItems->map(function ($it) {
                    return [
                        'name'   => $it->product?->name ?: 'Komoditas Sayur',
                        'weight' => number_format((float) $it->ordered_qty, 0, ',', '.') . ' kg',
                        'class'  => 'font-medium text-ink',
                    ];
                })->all();

                $estTotal = (float) ($ord->grand_total ?: $ord->estimated_total);

                $queueRows[] = [
                    'id'          => $ord->id,
                    'po'          => $ord->order_number,
                    'client'      => $clientName,
                    'po_chip'     => [
                        'label' => $isContract ? 'KONTRAK B2B' : 'REGULER',
                        'class' => $isContract ? 'bg-accent text-success-ink font-bold' : 'bg-surface-pill text-ink-body',
                    ],
                    'terms'       => [
                        'label' => $isContract ? 'Term of Payment (Tempo 30D)' : 'Transfer Bank Tunai',
                        'class' => $isContract ? 'font-semibold text-success-deep' : 'text-ink-body',
                    ],
                    'items'       => $itemsList,
                    'estimate'    => 'Rp ' . number_format($estTotal, 0, ',', '.'),
                    'status'      => [
                        'label' => match ($ord->status) {
                            'MENUNGGU_VERIFIKASI' => 'Menunggu Verifikasi',
                            'TERVERIFIKASI'       => 'Telah Diverifikasi',
                            'SIAP_KIRIM'          => 'Siap Kirim',
                            'DALAM_PENGIRIMAN'    => 'Dalam Armada',
                            default               => $ord->status,
                        },
                        'class' => match ($ord->status) {
                            'MENUNGGU_VERIFIKASI' => 'border-warning/40 bg-warning-wash font-bold text-warning-deep',
                            'TERVERIFIKASI'       => 'border-accent bg-accent/30 font-bold text-success-deep',
                            default               => 'border-line-soft bg-surface-pill text-ink',
                        },
                        'icon'  => match ($ord->status) {
                            'MENUNGGU_VERIFIKASI' => 'clock',
                            'TERVERIFIKASI'       => 'check',
                            default               => 'package',
                        },
                    ],
                    'status_note' => [
                        'label' => $ord->status === 'MENUNGGU_VERIFIKASI' ? ('Masuk ' . $ord->created_at->diffForHumans()) : 'Operasional Berjalan',
                        'class' => $ord->status === 'MENUNGGU_VERIFIKASI' ? 'font-semibold text-warning-deep' : 'text-ink-quiet',
                    ],
                    'actions'     => [
                        [
                            'label'   => 'Verifikasi',
                            'tone'    => 'ink',
                            'intent'  => 'approve',
                            'message' => "PO {$ord->order_number} disetujui & kuota panen dikunci.",
                            'url'     => route('sekretaris.verification'),
                        ],
                        [
                            'label'   => 'Detail',
                            'tone'    => 'default',
                            'intent'  => 'detail',
                            'message' => "Membuka rincian pesanan {$ord->order_number}.",
                            'url'     => route('sekretaris.verification'),
                        ],
                    ],
                ];
            }
        }

        // 4. Status Kesiapan Buffer Stock Panen
        $products = Product::where('is_active', true)->take(4)->get();
        $stockRows = [];

        foreach ($products as $p) {
            $currentStock = (float) HarvestBatch::where('product_id', $p->id)
                ->where('available_quantity', '>', 0)
                ->sum('available_quantity');
            $maxCapacity = 3000.0;
            $percent = min(100, round(($currentStock / $maxCapacity) * 100));
            $isLow = $currentStock < 500;
            $supplierName = HarvestBatch::where('product_id', $p->id)->latest('batch_date')->value('supplier_name') ?? 'Petani Mitra GPA';

            $stockRows[] = [
                'name'       => $p->name,
                'alert'      => $isLow,
                'card_class' => $isLow ? 'border-danger/30 bg-danger-wash/10' : 'border-line-soft bg-surface-shell',
                'chip'       => [
                    'label' => $isLow ? 'KRITIS (< 500 kg)' : 'AMAN (' . number_format($currentStock, 0, ',', '.') . ' kg)',
                    'class' => $isLow ? 'bg-danger-wash text-danger font-bold' : 'bg-accent/40 text-success-deep font-bold',
                ],
                'percent'    => max(5, $percent),
                'fill_class' => $isLow ? 'bg-danger' : 'bg-success-deep',
                'foot'       => [
                    [
                        'label' => 'Pasokan: ' . $supplierName,
                        'class' => 'text-ink-body',
                    ],
                    [
                        'label' => 'Kapasitas: ' . $percent . '%',
                        'class' => $isLow ? 'font-bold text-danger' : 'font-bold text-success-deep',
                    ],
                ],
            ];
        }

        return view('sekretaris.dashboard', [
            'queueRows'            => $queueRows,
            'stockRows'            => $stockRows,
            'allOrdersCount'       => $allOrders->count(),
            'pendingOrdersCount'   => $pendingOrders->count(),
            'verifiedOrdersCount'  => $verifiedOrders->count(),
            'readyOrdersCount'     => $readyOrders->count(),
            'shippingOrdersCount'  => $shippingOrders->count(),
            'completedOrdersCount' => $completedOrders->count(),
            'totalTodayCount'      => $totalTodayCount,
            'pendingPaymentsCount' => $pendingPaymentsCount,
        ]);
    }
}
