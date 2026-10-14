<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
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
        $pendingPaymentsCount = Payment::where('status', 'PENDING')->count();

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
                    'items'       => $itemsList ?: [
                        ['name' => 'Komoditas Pangan Hortikultura', 'weight' => '1.000 kg', 'class' => 'font-medium text-ink']
                    ],
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
                        'label' => $ord->status === 'MENUNGGU_VERIFIKASI' ? 'Sisa SLA: 45 Menit' : 'Operasional Berjalan',
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
        } else {
            $queueRows = [
                [
                    'id'          => 1,
                    'po'          => 'ORD-GPA-202610-0001',
                    'client'      => 'PT Sumber Pangan Sejahtera',
                    'po_chip'     => ['label' => 'KONTRAK B2B', 'class' => 'bg-accent text-success-ink font-bold'],
                    'terms'       => ['label' => 'Term of Payment (Tempo 30D)', 'class' => 'font-semibold text-success-deep'],
                    'items'       => [
                        ['name' => 'Bawang Merah Brebes Super', 'weight' => '600 kg', 'class' => 'font-medium text-ink'],
                        ['name' => 'Cabai Merah Keriting', 'weight' => '400 kg', 'class' => 'font-medium text-ink'],
                    ],
                    'estimate'    => 'Rp 37.200.000',
                    'status'      => [
                        'label' => 'Menunggu Verifikasi',
                        'class' => 'border-warning/40 bg-warning-wash font-bold text-warning-deep',
                        'icon'  => 'clock',
                    ],
                    'status_note' => ['label' => 'Sisa SLA: 38 Menit', 'class' => 'font-semibold text-warning-deep'],
                    'actions'     => [
                        ['label' => 'Verifikasi', 'tone' => 'ink', 'intent' => 'approve', 'message' => 'PO disetujui.', 'url' => route('sekretaris.verification')],
                        ['label' => 'Detail', 'tone' => 'default', 'intent' => 'detail', 'message' => 'Buka detail.', 'url' => route('sekretaris.verification')],
                    ],
                ],
            ];
        }

        // 4. Status Kesiapan Buffer Stock Panen
        $products = Product::where('is_active', true)->take(4)->get();
        $stockRows = [];

        foreach ($products as $p) {
            $currentStock = (float) $p->stock;
            $maxCapacity = 3000.0;
            $percent = min(100, round(($currentStock / $maxCapacity) * 100));
            $isLow = $currentStock < 500;

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
                        'label' => 'Origin: ' . ($p->origin ?: 'Lembang & Ciwidey'),
                        'class' => 'text-ink-body',
                    ],
                    [
                        'label' => 'Kapasitas: ' . $percent . '%',
                        'class' => $isLow ? 'font-bold text-danger' : 'font-bold text-success-deep',
                    ],
                ],
            ];
        }

        if (empty($stockRows)) {
            $stockRows = [
                [
                    'name'       => 'Selada Romaine Hydroponic Super',
                    'alert'      => false,
                    'card_class' => 'border-line-soft bg-surface-shell',
                    'chip'       => ['label' => 'AMAN (2.250 kg)', 'class' => 'bg-accent/40 text-success-deep font-bold'],
                    'percent'    => 75,
                    'fill_class' => 'bg-success-deep',
                    'foot'       => [
                        ['label' => 'Origin: Lembang', 'class' => 'text-ink-body'],
                        ['label' => 'Kapasitas: 75%', 'class' => 'font-bold text-success-deep'],
                    ],
                ],
                [
                    'name'       => 'Brokoli Super Lembang',
                    'alert'      => true,
                    'card_class' => 'border-danger/30 bg-danger-wash/10',
                    'chip'       => ['label' => 'KRITIS (420 kg)', 'class' => 'bg-danger-wash text-danger font-bold'],
                    'percent'    => 20,
                    'fill_class' => 'bg-danger',
                    'foot'       => [
                        ['label' => 'Origin: Lembang', 'class' => 'text-ink-body'],
                        ['label' => 'Kapasitas: 20%', 'class' => 'font-bold text-danger'],
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
