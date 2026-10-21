<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SuratJalanController extends Controller
{
    public function __invoke(Request $request): View
    {
        $orderId = $request->query('order_id');
        $order = null;

        if ($orderId) {
            $order = Order::with(['user', 'orderItems.product'])->find($orderId);
        }

        if (!$order) {
            $order = Order::with(['user', 'orderItems.product'])
                ->whereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])
                ->latest()
                ->first();
        }

        if ($order) {
            $clientName = $order->user?->company_name ?: ($order->user?->name ?: 'PT KULINER PRIMA NUSANTARA');
            $address = $order->delivery_address ?: 'Central Kitchen Ciracas Hub, Jl. Raya Bogor KM 28, Jakarta Timur';
            $sjNum = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
            $poNum = $order->order_number ?: ('#ORD-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
            $driverName = $order->driver_name ?: 'Budi Santoso';
            $vehiclePlate = $order->vehicle_plate ?: 'D 8921 GPA';

            $items = [];
            $totalQty = 0;
            $totalAmount = 0;

            foreach ($order->orderItems as $item) {
                $qty = (float) ($item->actual_net_weight ?: $item->ordered_qty);
                $price = (float) $item->unit_price;
                $amount = (float) ($item->subtotal_final ?: ($qty * $price));

                $totalQty += $qty;
                $totalAmount += $amount;

                $items[] = [
                    'code'   => $item->product?->code ?: ('SKU-' . $item->id),
                    'name'   => $item->product?->name ?: 'Sayur Segar Grade A',
                    'qty'    => $qty,
                    'price'  => $price,
                    'amount' => $amount,
                ];
            }

            if (empty($items)) {
                $items = [
                    [
                        'code'   => 'GPA-ROM-01',
                        'name'   => 'Selada Romaine Super',
                        'qty'    => 497.0,
                        'price'  => 15500,
                        'amount' => 7703500,
                    ],
                ];
                $totalQty = 497.0;
                $totalAmount = 7703500;
            }

            $document = [
                'brand' => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)',
                'doc' => [
                    'number' => $sjNum,
                    'po'     => $poNum,
                    'date'   => ($order->departure_time ?: ($order->created_at ?: now()))->translatedFormat('d F Y'),
                    'time'   => ($order->departure_time ?: ($order->created_at ?: now()))->format('H:i') . ' WIB',
                ],
                'sender' => [
                    'title' => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN',
                    'lines' => [
                        'Sentra Agribisnis Sayuran Segar Dataran Tinggi',
                        'Desa Panundaan, Kec. Ciwidey, Kab. Bandung - Jawa Barat 40973',
                        'Telp: (022) 592-8011 | Email: logistik@agroorder.co.id',
                    ],
                ],
                'recipient' => [
                    'title' => 'TUJUAN PENGIRIMAN / PENERIMA',
                    'name'  => strtoupper($clientName),
                    'lines' => [
                        $address,
                        'PIC: ' . ($order->user?->name ?: 'Petugas Penerima') . ' | HP: ' . ($order->user?->phone ?: '0812-3456-7890'),
                    ],
                ],
                'transporter' => [
                    'title' => 'DETAIL PENGANGKUTAN / EKSPEDISI',
                    'lines' => [
                        'Armada: ' . $vehiclePlate . ' | Type: Reefer Suhu Dingin 4°C Terkontrol',
                        'Supir: ' . $driverName . ' | SIM BII Umum',
                        'Kontak Supir: 0812-8899-7711',
                    ],
                ],
                'items'  => $items,
                'totals' => [
                    'qty'    => $totalQty,
                    'amount' => $totalAmount,
                ],
                'signatures' => [
                    [
                        'label' => 'Diserahkan Oleh',
                        'role'  => 'Koordinator Gudang GPA',
                        'name'  => 'Bambang Sugiarto',
                        'date'  => date('d/m/Y'),
                    ],
                    [
                        'label' => 'Pengangkut / Supir',
                        'role'  => 'Driver Logistik',
                        'name'  => $driverName,
                        'date'  => date('d/m/Y'),
                    ],
                    [
                        'label' => 'Diterima Lengkap Oleh',
                        'role'  => 'Penerima Barang',
                        'name'  => '________________________',
                        'date'  => '____/____/____',
                    ],
                ],
                'footer'     => 'Dokumen ini sah diterbitkan oleh Koperasi Produsen Agro Green Pasundan (GPA). Distribusi Rantai Pasok Segar Agrikultur.',
                'showSheet'  => true,
                'sheetLabel' => 'LEMBAR 1 / 3 (ARSIP PEMBELI)',
            ];

            return view('prints.surat-jalan', $document);
        }

        return view('prints.surat-jalan');
    }
}
