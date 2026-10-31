<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class ReturnActPrintService
{
    /**
     * Cari entitas pesanan terkait untuk dokumen Berita Acara Retur.
     */
    public function resolveOrder(?string $orderId): ?Order
    {
        if ($orderId) {
            $order = Order::with(['user', 'orderItems.product', 'driver', 'verifier'])->find($orderId);
            if ($order) {
                return $order;
            }
        }

        return Order::with(['user', 'orderItems.product', 'driver', 'verifier'])
            ->whereHas('orderItems', fn ($q) => $q->where('returned_weight', '>', 0))
            ->latest()
            ->first()
            ?? Order::with(['user', 'orderItems.product', 'driver', 'verifier'])->latest()->first();
    }

    /**
     * Membangun payload dokumen terstruktur untuk Berita Acara Retur & Selisih Mutu (BAP).
     *
     * @return array<string, mixed>
     */
    public function buildPrintData(?Order $order): array
    {
        $items              = [];
        $firstReturnedPrice = 0.0;
        $firstReturnedQty   = 0.0;

        if ($order) {
            foreach ($order->orderItems as $item) {
                $returnedQty = (float) ($item->returned_weight ?: 0);
                $orderedQty  = (float) $item->ordered_qty;
                $acceptedQty = max(0, $orderedQty - $returnedQty);
                $unitPrice   = (float) $item->unit_price;

                if ($returnedQty > 0 || $order->orderItems->count() <= 3) {
                    if ($firstReturnedPrice === 0.0 && $returnedQty > 0) {
                        $firstReturnedPrice = $unitPrice;
                        $firstReturnedQty   = $returnedQty;
                    }

                    $items[] = [
                        'code'     => $item->product?->code ?: ('PRD-' . str_pad((string) $item->product_id, 3, '0', STR_PAD_LEFT)),
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
            'shipped'  => (float) array_sum(array_column($items, 'shipped')),
            'returned' => (float) array_sum(array_column($items, 'returned')),
            'accepted' => (float) array_sum(array_column($items, 'accepted')),
        ];

        return [
            'brand'         => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)',
            'company_lines' => [
                'Sentra Distribusi & Logistik Hortikultura Dataran Tinggi',
                'Kp. Batukasur RT. 001 RW. 019 Desa Panundaan Kec. Ciwidey, Kab. Bandung 40973',
                'Tlp/ Whatsapp: 0812-2323-9133 / 0813-2142-2231',
            ],
            'document' => [
                'title'      => 'BERITA ACARA RETUR & SELISIH MUTU',
                'number'     => 'BAP-RET-' . ($order ? date('Ym') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) : '-'),
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
                'role'    => 'Staf Penerima / Purchasing Klien',
            ],
            'driver' => [
                'name'    => $order?->driver?->name ?: ($order?->fleet_assigned ?: '-'),
                'id'      => $order?->driver_id ? ('ARM-' . str_pad((string) $order->driver_id, 4, '0', STR_PAD_LEFT)) : '-',
                'vehicle' => $order?->vehicle_plate_number ?: '-',
            ],
            'coordinator' => [
                'name' => $order?->verifier?->name ?: 'Staff Operasional GPA',
                'role' => 'Koordinator Lapangan & QA Dispatch GPA',
            ],
            'items'      => $items,
            'totals'     => $totals,
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
    }
}
