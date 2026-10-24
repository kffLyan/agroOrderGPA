<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SuratJalanController extends Controller
{
    public function __invoke(Request $request): View
    {
        $orderId = $request->query('order_id');
        $order = null;

        if ($orderId) {
            $order = Order::with(['user', 'orderItems.product', 'driver'])->find($orderId);
        }

        if (!$order) {
            $order = Order::with(['user', 'orderItems.product', 'driver'])
                ->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'])
                ->latest()
                ->first();
        }

        if (!$order) {
            $order = Order::with(['user', 'orderItems.product', 'driver'])
                ->latest()
                ->first();
        }

        // Data Resmi Koperasi Produsen Green Pasundan Agriculture dari Dokumen Fisik
        $company = [
            'brand'        => 'KOPERASI PRODUSEN GREEN PASUNDAN AGRICULTURE',
            'full_brand'   => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)',
            'legal_number' => 'Nomor AHU-0004853.AH.01.29.TH 2024',
            'address'      => 'Kp. Batukasur RT. 001 RW. 019 Desa Panundaan Kec. Ciwidey',
            'regency'      => 'Kabupaten Bandung (40973)',
            'phone'        => '0812-2323-9133 / 0813-2142-2231',
        ];

        if ($order) {
            $clientName = $order->user?->company_name ?: ($order->user?->name ?: '-');
            $address = $order->delivery_address ?: ($order->user?->address ?: '-');
            $sjNum = $order->surat_jalan_number ?: ('SJ-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
            $poNum = $order->order_number ?: '-';
            $driverName = $order->driver?->name ?: ($order->fleet_assigned ?: '-');
            $coDriverName = '-';
            $vehiclePlate = $order->vehicle_plate_number ?: '-';

            $items = [];
            $totalQty = 0;

            foreach ($order->orderItems as $idx => $item) {
                $qty = (float) $item->ordered_qty;
                $productName = $item->product?->name ?: '-';
                $grade = $item->product?->grade ?: '';
                $itemLabel = $productName . ($grade ? " ({$grade})" : '');

                $totalQty += $qty;

                $items[] = [
                    'no'   => $idx + 1,
                    'name' => $itemLabel,
                    'qty'  => $qty,
                ];
            }

            $dateRaw = $order->departure_time ?: ($order->target_delivery_date ?: $order->created_at);
            $dateFormatted = $dateRaw ? Carbon::parse($dateRaw)->format('d-m-Y') : date('d-m-Y');
            $receivedDate = Carbon::parse($order->arrival_time ?: ($order->departure_time ?: now()))->format('d/m/y');
            $receiverName = $order->received_by_name ?: ($order->user?->pic_name ?: ($order->user?->name ?: '-'));

            return view('prints.surat-jalan', [
                'company'     => $company,
                'brand'       => $company['full_brand'],
                'doc' => [
                    'number'  => $sjNum,
                    'po'      => $poNum,
                    'date'    => $dateFormatted,
                    'to'      => $clientName,
                    'address' => $address,
                    'vehicle' => $vehiclePlate,
                ],
                'items'       => $items,
                'total_qty'   => $totalQty,
                'signatures' => [
                    'receiver'  => ['label' => 'Tanda Terima', 'name' => $receiverName, 'date' => $receivedDate],
                    'co_driver' => ['label' => 'Co Driver', 'name' => $coDriverName, 'date' => $receivedDate],
                    'driver'    => ['label' => 'Driver', 'name' => $driverName, 'date' => $receivedDate],
                ],
                'footer'      => 'Terimakasih atas kepercayaan anda, semoga tetap menjadi pelanggan setia',
            ]);
        }

        // Jika tidak ada order di database sama sekali
        return view('prints.surat-jalan', [
            'company'     => $company,
            'brand'       => $company['full_brand'],
            'doc' => [
                'number'  => '-',
                'po'      => '-',
                'date'    => date('d-m-Y'),
                'to'      => '-',
                'address' => '-',
                'vehicle' => '-',
            ],
            'items'       => [],
            'total_qty'   => 0,
            'signatures' => [
                'receiver'  => ['label' => 'Tanda Terima', 'name' => '-', 'date' => date('d/m/y')],
                'co_driver' => ['label' => 'Co Driver', 'name' => '-', 'date' => date('d/m/y')],
                'driver'    => ['label' => 'Driver', 'name' => '-', 'date' => date('d/m/y')],
            ],
            'footer'      => 'Terimakasih atas kepercayaan anda, semoga tetap menjadi pelanggan setia',
        ]);
    }
}
