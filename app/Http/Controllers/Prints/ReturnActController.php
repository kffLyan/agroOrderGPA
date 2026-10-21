<?php

namespace App\Http\Controllers\Prints;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReturnActController extends Controller
{
    public function __invoke(Request $request): View
    {
        $items = [
            [
                'code'     => 'GPA-ROM-A01',
                'name'     => 'Selada Romaine Grade A',
                'shipped'  => 298.5,
                'returned' => 10.0,
                'accepted' => 288.5,
                'reason'   => 'Daun layu & memar akibat gesekan boks pendingin saat transit',
                'price'    => 15000,
            ],
            [
                'code'     => 'GPA-TMT-B02',
                'name'     => 'Tomat Beef Super',
                'shipped'  => 148.7,
                'returned' => 0.0,
                'accepted' => 148.7,
                'reason'   => 'Kondisi prima (Lolos QC Dapur)',
                'price'    => 12500,
            ],
            [
                'code'     => 'GPA-BRK-H01',
                'name'     => 'Brokoli Highland Fresh',
                'shipped'  => 49.8,
                'returned' => 0.0,
                'accepted' => 49.8,
                'reason'   => 'Kondisi prima (Lolos QC Dapur)',
                'price'    => 28000,
            ],
        ];

        $totals = [
            'shipped'  => array_sum(array_column($items, 'shipped')),
            'returned' => array_sum(array_column($items, 'returned')),
            'accepted' => array_sum(array_column($items, 'accepted')),
        ];

        $document = [
            'brand' => 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)',
            'company_lines' => [
                'Sentra Distribusi & Logistik Hortikultura Dataran Tinggi',
                'Desa Panundaan, Kec. Ciwidey, Kab. Bandung, Jawa Barat 40973',
                'Telp: (022) 592-8011 | Email: ops.gpa@agroorder.co.id',
            ],
            'document' => [
                'title'      => 'BERITA ACARA RETUR & SELISIH MUTU',
                'number'     => 'BAP-RET-' . date('Ym') . '-0014',
                'delivery'   => 'SJ-GPA-' . date('Ym') . '-0115',
                'order'      => '#ORD-GPA-' . date('Ym') . '-0042',
                'inspection' => date('d M Y') . ', 06:15 WIB',
                'vehicle'    => 'Budi Santoso (D 8921 GPA)',
                'location'   => 'Central Kitchen Ciracas, PT Kuliner Prima Nusantara',
                'date'       => date('d F Y'),
            ],
            'client' => [
                'name'    => 'PT Kuliner Prima Nusantara',
                'contact' => 'Hendra Gunawan',
                'role'    => 'QC Receiving Dock Lead',
            ],
            'driver' => [
                'name'    => 'Budi Santoso',
                'id'      => 'DRV-0891',
                'vehicle' => 'D 8921 GPA',
            ],
            'coordinator' => [
                'name' => 'Bambang Sugiarto',
                'role' => 'QA Field Dispatch Coordinator',
            ],
            'items'  => $items,
            'totals' => $totals,
            'settlement' => [
                'returned_price'  => $items[0]['price'],
                'returned_value'  => $items[0]['returned'] * $items[0]['price'],
                'price_reference' => 'Harga Kontrak Mingguan GPA-ROM-A01',
                'selected_option' => 'Kuota pengganti gratis pada siklus berikutnya',
                'schedule'        => 'H+1 Subuh, Pengiriman Rute Ciracas',
                'alternative'     => 'Pemotongan nilai faktur tagihan bulanan (Credit Note)',
            ],
            'clause' => 'Berita acara ini dibuat dengan sadar dan ditandatangani oleh saksi lapangan yang berwenang di titik serah terima. Selisih timbangan dan penolakan mutu telah diperiksa bersama menggunakan timbangan digital standar platform logistik GPA.',
        ];

        return view('prints.return-act', $document);
    }
}
