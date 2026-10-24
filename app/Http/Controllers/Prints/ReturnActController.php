<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReturnActController extends Controller
{
    public function __invoke(Request $request): View
    {
        $orderId = $request->query('order_id');
        $order = null;

        if ($orderId) {
            $order = Order::with(['user', 'orderItems.product', 'driver', 'verifier'])->find($orderId);
        }

        if (!$order) {
            $order = Order::with(['user', 'orderItems.product', 'driver', 'verifier'])
                ->whereHas('orderItems', function ($q) {
                    $q->where('returned_weight', '>', 0);
                })
                ->latest()
                ->first();
        }

        if (!$order) {
            $order = Order::with(['user', 'orderItems.product', 'driver', 'verifier'])
                ->latest()
                ->first();
        }

        $items = [];
        $firstReturnedPrice = 0;
        $firstReturnedQty = 0;

        if ($order) {
            foreach ($order->orderItems as $item) {
                $returnedQty = (float) ($item->returned_weight ?: 0);
                $orderedQty = (float) $item->ordered_qty;
                $acceptedQty = max(0, $orderedQty - $returnedQty);
                $unitPrice = (float) $item->unit_price;

                if ($returnedQty > 0 || $order->orderItems->count() <= 3) {
                    if ($firstReturnedPrice === 0 && $returnedQty > 0) {
                        $firstReturnedPrice = $unitPrice;
                        $firstReturnedQty = $returnedQty;
                    }

                    $items[] = [
                        'code'     => $item->product?->code ?: ('PRD-' . str_pad($item->product_id, 3, '0', STR_PAD_LEFT)),
                        'name'     => $item->product?->name ?: 'Komoditas',
                        'shipped'  => $orderedQty,
                        'returned' => $returnedQty,
                        'accepted' => $acceptedQty,
                        'reason'   => $item->return_reason ?: ($returnedQty > 0 ? 'Selisih timbangan / kondisi saat serah terima' : 'Lolos QC Dapur Penerima'),
                        'price'    => $unitPrice,
                    ];
                }
            }
        }

        $totals = [
            'shipped'  => array_sum(array_column($items, 'shipped')),
            'returned' => array_sum(array_column($items, 'returned')),
            'accepted' => array_sum(array_column($items, 'accepted')),
        ];

        $document = [
            'brand' => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)',
            'company_lines' => [
                'Sentra Distribusi & Logistik Hortikultura Dataran Tinggi',
                'Kp. Batukasur RT. 001 RW. 019 Desa Panundaan Kec. Ciwidey, Kab. Bandung 40973',
                'Tlp/ Whatsapp: 0812-2323-9133 / 0813-2142-2231',
            ],
            'document' => [
                'title'      => 'BERITA ACARA RETUR & SELISIH MUTU',
                'number'     => 'BAP-RET-' . ($order ? date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT) : '-'),
                'delivery'   => $order?->surat_jalan_number ?: '-',
                'order'      => $order?->order_number ?: '-',
                'inspection' => ($order?->arrival_time ? Carbon::parse($order->arrival_time)->format('d M Y, H:i') : date('d M Y, H:i')) . ' WIB',
                'vehicle'    => ($order?->driver?->name ?: ($order?->fleet_assigned ?: '-')) . ($order?->vehicle_plate_number ? ' (' . $order->vehicle_plate_number . ')' : ''),
                'location'   => $order?->delivery_address ?: ($order?->user?->address ?: '-'),
                'date'       => date('d F Y'),
            ],
            'client' => [
                'name'    => $order?->user?->company_name ?: ($order?->user?->name ?: '-'),
                'contact' => $order?->received_by_name ?: ($order?->user?->pic_name ?: '-'),
                'role'    => 'QC Receiving Dock Lead / Purchasing',
            ],
            'driver' => [
                'name'    => $order?->driver?->name ?: ($order?->fleet_assigned ?: '-'),
                'id'      => $order?->driver_id ? ('DRV-' . str_pad($order->driver_id, 4, '0', STR_PAD_LEFT)) : '-',
                'vehicle' => $order?->vehicle_plate_number ?: '-',
            ],
            'coordinator' => [
                'name' => $order?->verifier?->name ?: 'Staff Operasional GPA',
                'role' => 'QA Field Dispatch Coordinator',
            ],
            'items'  => $items,
            'totals' => $totals,
            'settlement' => [
                'returned_price'  => $firstReturnedPrice,
                'returned_value'  => $firstReturnedQty * $firstReturnedPrice,
                'price_reference' => 'Harga Kontrak / Dasar Perjanjian GPA',
                'selected_option' => 'Kompensasi kuota pengganti pada siklus pengiriman berikutnya',
                'schedule'        => 'Jadwal distribusi terdekat',
                'alternative'     => 'Pemotongan nilai faktur tagihan bulanan (Credit Note)',
            ],
            'clause' => 'Berita acara ini dibuat dengan sadar dan ditandatangani oleh saksi lapangan yang berwenang di titik serah terima. Selisih timbangan dan penolakan mutu telah diperiksa bersama menggunakan timbangan digital standar platform logistik GPA.',
        ];

        return view('prints.return-act', $document);
    }
}
