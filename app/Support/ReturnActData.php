<?php

namespace App\Support;

/**
 * Contoh dokumen Berita Acara Retur untuk cetak A4.
 *
 * @return array<string, mixed>
 */
class ReturnActData
{
    public static function document(): array
    {
        $items = [
            [
                'code' => 'AG-ROM-A01',
                'name' => 'Selada Romaine Grade A',
                'shipped' => 298.5,
                'returned' => 10.0,
                'accepted' => 288.5,
                'reason' => 'Daun layu & memar akibat gesekan boks pendingin saat transit',
                'price' => 15000,
            ],
            [
                'code' => 'AG-TMT-B02',
                'name' => 'Tomat Beef Super',
                'shipped' => 148.7,
                'returned' => 0.0,
                'accepted' => 148.7,
                'reason' => 'Kondisi prima (Lolos QC Dapur)',
                'price' => 12500,
            ],
            [
                'code' => 'AG-BRK-H01',
                'name' => 'Brokoli Highland Fresh',
                'shipped' => 49.8,
                'returned' => 0.0,
                'accepted' => 49.8,
                'reason' => 'Kondisi prima (Lolos QC Dapur)',
                'price' => 28000,
            ],
        ];

        $totals = [
            'shipped' => array_sum(array_column($items, 'shipped')),
            'returned' => array_sum(array_column($items, 'returned')),
            'accepted' => array_sum(array_column($items, 'accepted')),
        ];

        return [
            'brand' => 'PT AGRO PASTI ADA',
            'company_lines' => [
                'Sentral Distribusi & Logistik Hortikultura',
                'Kawasan Industri Sentul, Jl. Raya Babakan Madang No. 88, Kab. Bogor, Jawa Barat 16810',
                'Telp: (021) 8792-4411 | Email: ops.gpa@agropastiada.co.id',
            ],
            'document' => [
                'title' => 'BERITA ACARA RETUR & SELISIH MUTU',
                'number' => 'BAP-RET-202610-0014',
                'delivery' => 'SJ-GPA-202610-0115',
                'order' => '#ORD-GPA-202610-0042',
                'inspection' => '24 Okt 2026, 06:15 WIB',
                'vehicle' => 'Joko Widodo (B 9421 TX)',
                'location' => 'Central Kitchen Ciracas, PT Kuliner Prima Nusantara',
                'date' => '24 Oktober 2026',
            ],
            'client' => [
                'name' => 'PT Kuliner Prima Nusantara',
                'contact' => 'Hendra Gunawan',
                'role' => 'QC Receiving Dock Lead',
            ],
            'driver' => [
                'name' => 'Joko Widodo',
                'id' => 'DRV-0891',
                'vehicle' => 'B 9421 TX',
            ],
            'coordinator' => [
                'name' => 'Bambang Sugiarto',
                'role' => 'QA Field Dispatch Coordinator',
            ],
            'items' => $items,
            'totals' => $totals,
            'settlement' => [
                'returned_price' => $items[0]['price'],
                'returned_value' => $items[0]['returned'] * $items[0]['price'],
                'price_reference' => 'Harga Kontrak Mingguan AG-ROM-A01',
                'selected_option' => 'Kuota pengganti gratis',
                'schedule' => 'H+1 Subuh, 25 Okt 2026 - Rute Ciracas',
                'alternative' => 'Pemotongan nilai faktur tagihan bulanan (Credit Note)',
            ],
            'clause' => 'Berita acara ini dibuat dengan sadar dan ditandatangani oleh saksi lapangan yang berwenang di titik serah terima. Selisih timbangan dan penolakan mutu telah diperiksa bersama menggunakan timbangan digital standar platform logistik GPA.',
        ];
    }
}
