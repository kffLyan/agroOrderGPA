<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data ringkasan & operasional dashboard Klien B2B.
 *
 * Seluruh angka di bawah adalah data simulasi yang mencerminkan design system
 * "Dashboard Ringkasan & Operasional Klien B2B". Nanti dapat dipindahkan ke
 * query Eloquent / service layer tanpa mengubah struktur array pada view.
 */
class ClientDashboardData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(User $user): array
    {
        return [
            'client' => self::client($user),
            'contract' => self::contract($user),
            'credit' => self::credit(),
            'pipeline' => self::pipeline(),
            'harvest' => self::harvest(),
            'sla' => self::sla(),
            'invoice' => self::invoice(),
            'shipment' => self::shipment(),
            'orders' => self::orders(),
            'reorder' => self::reorder(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function client(?User $user): array
    {
        if ($user === null) {
            return self::demoClient();
        }

        $name = $user->business_name ?: $user->name;

        return [
            'name' => $name,
            'initials' => self::initials($name),
            'tier' => 'Tier-1 Enterprise Partner',
            'code' => 'B2B-KPN-8821',
            'tax_id' => 'SKB Pajak: Aktif',
            'person' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
        ];
    }

    /**
     * Data klien cadangan untuk mode pratinjau frontend tanpa sesi login.
     *
     * @return array<string, string>
     */
    private static function demoClient(): array
    {
        $name = 'PT Kuliner Prima Nusantara';

        return [
            'name' => $name,
            'initials' => self::initials($name),
            'tier' => 'Tier-1 Enterprise Partner',
            'code' => 'B2B-KPN-8821',
            'tax_id' => 'SKB Pajak: Aktif',
            'person' => 'Budi Setiawan',
            'email' => 'purchasing@kulinerprima.co.id',
            'phone' => '+6281234567890',
            'address' => 'Jl. Ir. H. Juanda No. 182, Dago, Coblong, Kota Bandung 40135',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function contract(): array
    {
        return [
            'active' => true,
            'id' => 'CTR-GPA-B2B-2024-08',
            'dock' => 'Central Hub Jakarta Timur',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function credit(): array
    {
        return [
            'label' => 'Plafon Kredit & Tempo (Top 30d)',
            'available' => 447600000,
            'limit' => 500000000,
            'status' => 'Lancar',
            'percent' => 89.5,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function pipeline(): array
    {
        return [
            'label' => 'Pesanan In-Flight Pipeline',
            'count' => 3,
            'value' => 18240000,
            'stages' => [
                ['label' => 'Verifikasi Admin PO', 'value' => '1 PO'],
                ['label' => 'Sortir & Timbang Chiller', 'value' => '1 PO'],
                ['label' => 'Pengiriman Armada Truk', 'value' => '1 PO (Reefer)'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function harvest(): array
    {
        return [
            'label' => 'Serapan Kuota Panen (Bulan Ini)',
            'weight' => 4820,
            'target' => 6000,
            'percent' => 80.3,
            'commodities' => [
                ['name' => 'Selada', 'weight' => 2100],
                ['name' => 'Tomat', 'weight' => 1400],
                ['name' => 'Brokoli', 'weight' => 850],
                ['name' => 'Stroberi', 'weight' => 470],
            ],
            'deviation' => -0.8,
            'deviation_tolerance' => 2.0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function sla(): array
    {
        return [
            'label' => 'Kepatuhan SLA & Mutu Cold-Chain',
            'otif' => 99.4,
            'incidents' => 0,
            'unload_minutes' => 18,
            'reject_rate' => 0.12,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function invoice(): array
    {
        return [
            'title' => 'Pemberitahuan Penagihan: Faktur Tempo Siap Ditinjau',
            'due' => 'Jatuh Tempo 15 Nov 2024',
            'number' => 'INV-GPA-202410-04',
            'total' => 38650000,
            'body' => 'Faktur #INV-GPA-202410-04 senilai Rp 38.650.000 telah diterbitkan untuk batch pengiriman minggu ke-4 Oktober.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function shipment(): array
    {
        return [
            'title' => 'Live Tracking: Pengiriman Hari Ini & Status Dock',
            'summary' => 'Truk Reefer B 9421 TX — Rute Cikajang (Pangalengan) menuju Dock Jakarta Timur',
            'temperature' => '+4.1°C',
            'temperature_state' => 'Optimal',
            'eta' => '04:30 WIB',
            'eta_note' => 'Subuh',
            'steps' => [
                [
                    'state' => 'done',
                    'phase' => 'Langkah 01',
                    'status' => 'Selesai',
                    'title' => 'Verifikasi PO',
                    'detail' => 'PO #ORD-0089 disetujui sistem.',
                    'time' => '18:15 WIB',
                ],
                [
                    'state' => 'done',
                    'phase' => 'Langkah 02',
                    'status' => 'Selesai',
                    'title' => 'Sortir & Timbang',
                    'detail' => 'Netto Sah: 794.20 kg di Station A.',
                    'time' => '20:30 WIB',
                ],
                [
                    'state' => 'done',
                    'phase' => 'Langkah 03',
                    'status' => 'Selesai',
                    'title' => 'SJ Diterbitkan',
                    'detail' => 'SJ-GPA-202410-0112',
                    'time' => '21:10 WIB',
                ],
                [
                    'state' => 'active',
                    'phase' => 'Langkah 04',
                    'status' => 'Aktif',
                    'title' => 'Dalam Perjalanan',
                    'detail' => 'Truk Reefer B 9421 TX',
                    'time' => 'ETA: 04:30 WIB Subuh',
                ],
                [
                    'state' => 'pending',
                    'phase' => 'Langkah 05',
                    'status' => 'Menunggu',
                    'title' => 'PoD Diterima Dock',
                    'detail' => 'Bongkar muat & QC dock klien.',
                    'time' => 'Estimasi 05:00 WIB',
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function orders(): array
    {
        return [
            [
                'po' => 'ORD-GPA-202410-0089',
                'date' => '24 Okt 2024',
                'commodities' => 'Selada Romaine (450kg), Brokoli (200kg)',
                'packaging' => 'Kemasan: Krat Ventilasi GPA 25kg',
                'weight' => '794.20 kg',
                'weight_note' => 'Netto',
                'scale_note' => 'Timbang Sah Gudang #01',
                'driver' => 'Joko Susilo (B 9421 TX)',
                'sj' => 'SJ-GPA-202410-0112',
                'driver_note' => null,
                'status' => 'Dalam Pengiriman',
                'status_tone' => 'accent',
                'actions' => [
                    ['label' => 'Lacak', 'variant' => 'solid'],
                    ['label' => 'Lihat SJ', 'variant' => 'outline'],
                ],
            ],
            [
                'po' => 'ORD-GPA-202410-0092',
                'date' => '25 Okt 2024',
                'commodities' => 'Tomat Beef (600kg), Wortel (300kg)',
                'packaging' => 'Kemasan: Peti Kayu Cold-Treated',
                'weight' => null,
                'weight_note' => 'Proses Sortir Gudang',
                'scale_note' => null,
                'driver' => 'Armada Reefer B 9180 PXR',
                'sj' => null,
                'driver_note' => 'Menunggu Timbang Netto',
                'status' => 'Packing & Sortir',
                'status_tone' => 'neutral',
                'actions' => [
                    ['label' => 'Detail Mutu', 'variant' => 'outline'],
                ],
            ],
            [
                'po' => 'ORD-GPA-202410-0095',
                'date' => '25 Okt 2024',
                'commodities' => 'Stroberi Ciwidey Grade A (200kg)',
                'packaging' => 'Kemasan: Clamshell 500gr dalam Master Box',
                'weight' => 'Est 200 kg',
                'weight_note' => null,
                'scale_note' => null,
                'driver' => null,
                'sj' => null,
                'driver_note' => 'Menunggu Alokasi Armada',
                'status' => 'Verifikasi PO Admin',
                'status_tone' => 'warning',
                'actions' => [
                    ['label' => 'Detail Draft', 'variant' => 'outline'],
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function reorder(): array
    {
        return [
            [
                'key' => 'selada_romaine',
                'grade' => 'Grade A · Hidroponik',
                'name' => 'Selada Romaine',
                'price' => 15000,
                'remaining' => 900,
                'step' => 25,
                'initial_qty' => 100,
            ],
            [
                'key' => 'tomat_beef',
                'grade' => 'Super Red · Greenhouse',
                'name' => 'Tomat Beef',
                'price' => 12500,
                'remaining' => 600,
                'step' => 25,
                'initial_qty' => 150,
            ],
            [
                'key' => 'brokoli_highland',
                'grade' => 'Grade AA · Lembang',
                'name' => 'Brokoli Highland',
                'price' => 28000,
                'remaining' => 350,
                'step' => 10,
                'initial_qty' => 50,
            ],
            [
                'key' => 'kol_segar',
                'grade' => 'Grade A · Pangalengan',
                'name' => 'Kol Segar',
                'price' => 8000,
                'remaining' => 1200,
                'step' => 25,
                'initial_qty' => 200,
            ],
        ];
    }

    private static function initials(string $value): string
    {
        $words = preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $letters = array_map(static fn ($word) => mb_substr($word, 0, 1), array_slice($words, 0, 2));

        return mb_strtoupper(implode('', $letters)) ?: 'GP';
    }
}
