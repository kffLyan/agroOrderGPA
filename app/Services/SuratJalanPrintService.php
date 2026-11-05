<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class SuratJalanPrintService
{
    // Resolusi entitas pesanan aktif atau pesanan siap kirim terbaru untuk pencetakan dokumen Surat Jalan.
    public function resolveOrder(?string $orderId): ?Order
    {
        if ($orderId) {
            $order = Order::with(['user', 'orderItems.product', 'driver'])->find($orderId);
            if ($order) {
                return $order;
            }
        }

        return Order::with(['user', 'orderItems.product', 'driver'])
            ->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])
            ->latest()
            ->first()
            ?? Order::with(['user', 'orderItems.product', 'driver'])->latest()->first();
    }

    // Membangun payload data cetak Surat Jalan resmi pengiriman komoditas dengan format standar GPA.
    public function buildPrintData(?Order $order): array
    {
        $company = [
            'brand'        => 'KOPERASI PRODUSEN GREEN PASUNDAN AGRICULTURE',
            'full_brand'   => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)',
            'legal_number' => 'Nomor AHU-0004853.AH.01.29.TH 2024',
            'address'      => 'Kp. Batukasur RT. 001 RW. 019 Desa Panundaan Kec. Ciwidey',
            'regency'      => 'Kabupaten Bandung (40973)',
            'phone'        => '0812-2323-9133 / 0813-2142-2231',
        ];

        $clientName   = $order?->user?->company_name ?: ($order?->user?->name ?: '-');
        $address      = $order?->delivery_address ?: ($order?->user?->address ?: '-');
        $sjNumber     = $order ? ($order->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT))) : '-';
        $orderNumber  = $order?->order_number ?: '-';
        $driverName   = $order?->driver?->name ?: ($order?->fleet_assigned ?: '-');
        $coDriverName = '-';
        $plate        = $order?->vehicle_plate_number ?: '-';

        $items    = [];
        $totalQty = 0.0;

        if ($order) {
            foreach ($order->orderItems as $index => $item) {
                $qty         = (float) $item->ordered_qty;
                $productName = $item->product?->name ?: '-';
                $grade       = $item->product?->grade ?: '';
                $itemLabel   = $productName . ($grade ? " ({$grade})" : '');

                $totalQty += $qty;

                $items[] = [
                    'no'   => $index + 1,
                    'name' => $itemLabel,
                    'qty'  => $qty,
                ];
            }
        }

        $rawDate       = $order?->departure_time ?: ($order?->target_delivery_date ?: $order?->created_at);
        $dateFormatted = $rawDate ? Carbon::parse($rawDate)->format('d-m-Y') : date('d-m-Y');
        $receivedDate  = Carbon::parse($order?->arrival_time ?: ($order?->departure_time ?: now()))->format('d/m/y');
        $receiverName  = $order?->received_by_name ?: ($order?->user?->pic_name ?: ($order?->user?->name ?: '-'));

        return [
            'company'    => $company,
            'brand'      => $company['full_brand'],
            'doc' => [
                'number'  => $sjNumber,
                'po'      => $orderNumber,
                'date'    => $dateFormatted,
                'to'      => $clientName,
                'address' => $address,
                'vehicle' => $plate,
            ],
            'items'      => $items,
            'total_qty'  => $totalQty,
            'signatures' => [
                'receiver'  => ['label' => 'Tanda Terima', 'name' => $receiverName, 'date' => $receivedDate],
                'co_driver' => ['label' => 'Kernet Armada', 'name' => $coDriverName, 'date' => $receivedDate],
                'driver'    => ['label' => 'Supir Armada', 'name' => $driverName, 'date' => $receivedDate],
            ],
            'footer'     => 'Terimakasih atas kepercayaan anda, semoga tetap menjadi pelanggan setia',
        ];
    }
}
