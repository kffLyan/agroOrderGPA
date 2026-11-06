<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PrintBeritaAcaraReturController extends Controller
{
    // Menyiapkan cetak Berita Acara Retur (BAR) dan selisih mutu timbangan fisik untuk dokumentasi serah terima
    public function __invoke(Request $request): View
    {
        $orderId = $request->query('order_id');
        $order   = $this->resolveOrder($orderId);

        // Anti-IDOR: Jika Klien mengakses Berita Acara Retur, pastikan pesanan ini miliknya
        if (auth()->user()?->role === 'KLIEN' && (!$order || $order->user_id !== auth()->id())) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk melihat Berita Acara Retur pesanan lain.');
        }

        $document = $this->buildPrintData($order);

        return view('prints.return-act', $document);
    }

    // Mengambil objek pesanan yang memiliki catatan komplain retur atau pesanan terbaru untuk dicetak
    public function resolveOrder(?string $orderId): ?Order
    {
        if ($orderId) {
            $order = Order::with(['user', 'orderItems.product', 'driver', 'verifier'])->find($orderId);
            if ($order) {
                return $order;
            }
        }

        return Order::with(['user', 'orderItems.product', 'driver', 'verifier'])
            ->whereHas('orderItems', fn ($query) => $query->where('returned_weight', '>', 0))
            ->latest()
            ->first()
            ?? Order::with(['user', 'orderItems.product', 'driver', 'verifier'])->latest()->first();
    }

    // Membangun payload data terstruktur untuk lembar cetak Berita Acara Retur dan penyelesaian kompensasi
    public function buildPrintData(?Order $order): array
    {
        $items = [];
        $returPrice = 0.0;
        $returQty = 0.0;

        if ($order) {
            foreach ($order->orderItems as $item) {
                $retur = (float) ($item->returned_weight ?: 0);
                $ordered = (float) $item->ordered_qty;
                $accepted = max(0, $ordered - $retur);
                $price = (float) $item->unit_price;

                if ($retur > 0 || $order->orderItems->count() <= 3) {
                    if ($returPrice === 0.0 && $retur > 0) {
                        $returPrice = $price;
                        $returQty = $retur;
                    }

                    $items[] = [
                        'code'     => $item->product?->code ?: ('PRD-' . str_pad((string) $item->product_id, 3, '0', STR_PAD_LEFT)),
                        'name'     => $item->product?->name ?: 'Komoditas',
                        'shipped'  => $ordered,
                        'returned' => $retur,
                        'accepted' => $accepted,
                        'reason'   => $item->return_reason ?: ($retur > 0 ? 'Selisih timbangan / kondisi saat serah terima' : 'Lolos QC Dapur Penerima'),
                        'price'    => $price,
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
                'returned_price'  => $returPrice,
                'returned_value'  => $returQty * $returPrice,
                'price_reference' => 'Harga Kontrak / Dasar Perjanjian GPA',
                'selected_option' => 'Kompensasi kuota pengganti pada siklus pengiriman berikutnya',
                'schedule'        => 'Jadwal distribusi terdekat',
                'alternative'     => 'Pemotongan nilai faktur tagihan bulanan (Credit Note)',
            ],
            'clause' => 'Berita acara ini dibuat dengan sadar dan ditandatangani oleh saksi lapangan yang berwenang di titik serah terima. Selisih timbangan dan penolakan mutu telah diperiksa bersama menggunakan timbangan digital standar platform logistik GPA.',
        ];
    }
}
