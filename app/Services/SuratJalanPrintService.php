<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class SuratJalanPrintService
{
    /**
     * Resolusi pesanan untuk cetak Surat Jalan resmi.
     */
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

    /**
     * Membangun payload cetak Surat Jalan Pengiriman.
     *
     * @return array<string, mixed>
     */
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
        $sjNum        = $order ? ($order->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT))) : '-';
        $poNum        = $order?->order_number ?: '-';
        $driverName   = $order?->driver?->name ?: ($order?->fleet_assigned ?: '-');
        $coDriverName = '-';
        $vehiclePlate = $order?->vehicle_plate_number ?: '-';

        $items    = [];
        $totalQty = 0.0;

        if ($order) {
            foreach ($order->orderItems as $idx => $item) {
                $qty         = (float) $item->ordered_qty;
                $productName = $item->product?->name ?: '-';
                $grade       = $item->product?->grade ?: '';
                $itemLabel   = $productName . ($grade ? " ({$grade})" : '');

                $totalQty += $qty;

                $items[] = [
                    'no'   => $idx + 1,
                    'name' => $itemLabel,
                    'qty'  => $qty,
                ];
            }
        }

        $dateRaw       = $order?->departure_time ?: ($order?->target_delivery_date ?: $order?->created_at);
        $dateFormatted = $dateRaw ? Carbon::parse($dateRaw)->format('d-m-Y') : date('d-m-Y');
        $receivedDate  = Carbon::parse($order?->arrival_time ?: ($order?->departure_time ?: now()))->format('d/m/y');
        $receiverName  = $order?->received_by_name ?: ($order?->user?->pic_name ?: ($order?->user?->name ?: '-'));

        return [
            'company'    => $company,
            'brand'      => $company['full_brand'],
            'doc' => [
                'number'  => $sjNum,
                'po'      => $poNum,
                'date'    => $dateFormatted,
                'to'      => $clientName,
                'address' => $address,
                'vehicle' => $vehiclePlate,
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
