<?php

namespace App\Support;

/**
 * Sumber data halaman Katalog Komoditas & Kuota Pasokan Terikat B2B.
 *
 * Seluruh angka di bawah adalah data simulasi yang mencerminkan design
 * "Katalog Komoditas & Kuota Pasokan Terikat B2B". Nanti dapat dipindahkan ke
 * query Eloquent / service layer tanpa mengubah struktur array pada view.
 */
class ClientCatalogData
{
    /**
     * @return array<string, mixed>
     */
    public static function contract(): array
    {
        return [
            'binding' => 'Kontrak Terikat Tahunan',
            'number' => 'CTR-2025/GPA-KPN/04',
            'hub' => 'Jawa Barat',
            'last_sync' => 'Sinkron 04:21 WIB',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function quota(): array
    {
        return [
            'label' => 'Kuota Minggu Ini (W11)',
            'used' => 4100,
            'total' => 5500,
            'percent' => 74.5,
            'remainder' => 1400,
            'footnote' => 'Sisa: 1.400 kg aman',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function credit(): array
    {
        return [
            'label' => 'Plafon Kredit (Top 30d)',
            'available' => 447600000,
            'limit' => 500000000,
            'status' => 'Nol Tunggakan',
            'chip' => 'Siap Order',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function dispatch(): array
    {
        return [
            'label' => 'In-Flight Dispatch Subuh',
            'po' => 'PO #0089',
            'weight' => '794 kg',
            'destination' => 'Dock Central Kitchen Ciracas',
            'eta' => '04:30 WIB',
            'temperature' => '+4.1°C',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rulebook(): array
    {
        return [
            'label' => 'Mandatori B2B Rulebook',
            'rules' => [
                ['code' => 'RULE-01', 'text' => 'Real-time Stock Locking Active'],
                ['code' => 'RULE-04', 'text' => 'Timbang Terima Netto Faktur'],
                ['code' => 'RULE-08', 'text' => 'Validasi Minimum Order Komoditas'],
            ],
            'footer' => 'Audit Ledger: ISO 22000 & GMP Cold-Chain',
        ];
    }

    /**
     * Tab filter kategori beserta jumlah komoditas pada tab tersebut.
     *
     * @return list<array<string, mixed>>
     */
    public static function categories(): array
    {
        $counts = [];

        foreach (self::commodities() as $commodity) {
            $counts[$commodity['category']] = ($counts[$commodity['category']] ?? 0) + 1;
        }

        $categories = [
            ['value' => 'all', 'label' => 'Semua Komoditas', 'count' => array_sum($counts)],
        ];

        foreach (self::categoryLabels() as $value => $label) {
            if (! isset($counts[$value])) {
                continue;
            }

            $categories[] = [
                'value' => $value,
                'label' => $label,
                'count' => $counts[$value],
            ];
        }

        return $categories;
    }

    /**
     * @return array<string, string>
     */
    public static function categoryLabels(): array
    {
        return [
            'veg_leaf' => 'Sayuran Daun',
            'veg_fruit' => 'Sayuran Buah',
            'veg_flower' => 'Sayuran Bunga',
            'fruit_highland' => 'Buah Dataran Tinggi',
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public static function origins(): array
    {
        $origins = [];

        foreach (self::commodities() as $commodity) {
            $origins[$commodity['origin']] = $commodity['origin'];
        }

        return array_map(static fn (string $origin): array => [
            'value' => $origin,
            'label' => $origin,
        ], array_values($origins));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sortOptions(): array
    {
        return [
            ['value' => 'stock', 'label' => 'Urutkan: Sisa Alokasi Terbanyak'],
            ['value' => 'price_asc', 'label' => 'Urutkan: Harga Terendah'],
            ['value' => 'price_desc', 'label' => 'Urutkan: Harga Tertinggi'],
            ['value' => 'moq', 'label' => 'Urutkan: MOQ Termurah'],
            ['value' => 'name', 'label' => 'Urutkan: Nama Komoditas'],
        ];
    }

    /**
     * Kartu komoditas pada katalog + baris tabel rekonsiliasi memakai sumber
     * data yang sama agar angka stok, harga, dan MOQ tidak pernah berbeda.
     *
     * @return list<array<string, mixed>>
     */
    public static function commodities(): array
    {
        return [
            [
                'key' => 'selada_romaine',
                'code' => 'KOM-VEG-001',
                'hub_code' => 'GPA-LEM-01',
                'sku' => 'GPA-VEG-ROM-800',
                'grade' => 'Grade A Horeca',
                'packaging' => 'Krat Plastik Sanitasi 20kg',
                'crate_kg' => 20,
                'pack_label' => 'Krat',
                'category' => 'veg_leaf',
                'category_label' => 'Sayuran Daun',
                'name' => 'Selada Romaine / Lettuce',
                'subtitle' => 'Grade A Horeca · Sentra Lembang (1.200 mdpl)',
                'origin' => 'Lembang Highland',
                'availability' => 'Tersedia',
                'availability_tone' => 'safe',
                'available' => true,
                'stock' => 1250,
                'stock_label' => 'Alokasi Kuota',
                'moq' => 10,
                'price' => 15000,
                'price_note' => 'Lock Harga s/d Des 2025',
                'price_note_tone' => 'success',
                'cold_chain' => '4-8°C Chilled',
                'quota_label' => 'Alokasi Aman',
                'quota_tone' => 'safe',
                'critical' => false,
                'step' => 10,
                'initial_qty' => 50,
                'specs' => [
                    ['label' => 'Alokasi Kuota', 'value' => '1.250 kg', 'tone' => 'default'],
                    ['label' => 'Minimum Order (MOQ)', 'value' => '10 kg', 'tone' => 'default'],
                    ['label' => 'Target Chilled Temp', 'value' => '4°C-8°C', 'tone' => 'success'],
                    ['label' => 'Karakteristik Fisik', 'value' => 'Crispy, Bebas Residu', 'tone' => 'default'],
                ],
            ],
            [
                'key' => 'tomat_beef',
                'code' => 'KOM-VEG-002',
                'hub_code' => 'GPA-CJR-02',
                'sku' => 'GPA-VEG-TMT-050',
                'grade' => 'Grade A Dataran Tinggi',
                'packaging' => 'Krat Bambu / Peti Kayu Halus 25kg',
                'crate_kg' => 25,
                'pack_label' => 'Krat',
                'category' => 'veg_fruit',
                'category_label' => 'Sayuran Buah',
                'name' => 'Tomat Beef Dataran Tinggi',
                'subtitle' => 'Grade A (Daging Tebal) · Sentra Cianjur',
                'origin' => 'Cianjur Agro Valleys',
                'availability' => 'Tersedia',
                'availability_tone' => 'safe',
                'available' => true,
                'stock' => 2100,
                'stock_label' => 'Alokasi Kuota',
                'moq' => 25,
                'price' => 12500,
                'price_note' => 'Lock Harga s/d Des 2025',
                'price_note_tone' => 'success',
                'cold_chain' => '12-15°C Ambient Fan',
                'quota_label' => 'Alokasi Aman',
                'quota_tone' => 'safe',
                'critical' => false,
                'step' => 25,
                'initial_qty' => 100,
                'specs' => [
                    ['label' => 'Alokasi Kuota', 'value' => '2.100 kg', 'tone' => 'default'],
                    ['label' => 'Minimum Order (MOQ)', 'value' => '25 kg', 'tone' => 'default'],
                    ['label' => 'Tingkat Kematangan', 'value' => '80%-85% Turning', 'tone' => 'default'],
                    ['label' => 'Shelf-Life Mandate', 'value' => '7 Hari Ambient / 14 Cold', 'tone' => 'default'],
                ],
            ],
            [
                'key' => 'brokoli_highland',
                'code' => 'KOM-VEG-003',
                'hub_code' => 'GPA-PGL-03',
                'sku' => 'GPA-VEG-BKL-015',
                'grade' => 'Grade Super',
                'packaging' => 'Krat Ventilasi GPA 15kg',
                'crate_kg' => 15,
                'pack_label' => 'Krat',
                'category' => 'veg_flower',
                'category_label' => 'Sayuran Bunga',
                'name' => 'Brokoli Dataran Tinggi',
                'subtitle' => 'Grade Super · Sentra Pangalengan',
                'origin' => 'Pangalengan Ridge',
                'availability' => 'Stok Terbatas',
                'availability_tone' => 'caution',
                'available' => false,
                'stock' => 180,
                'stock_label' => 'Sisa Stok Panen',
                'moq' => 15,
                'price' => 28000,
                'price_note' => 'Buffer Kritis (0.18 Ton)',
                'price_note_tone' => 'caution',
                'cold_chain' => '2-4°C Ice Flake',
                'quota_label' => 'Kritis',
                'quota_tone' => 'caution',
                'critical' => true,
                'step' => 15,
                'initial_qty' => 30,
                'specs' => [
                    ['label' => 'Sisa Stok Panen', 'value' => '180 kg', 'tone' => 'caution'],
                    ['label' => 'Minimum Order (MOQ)', 'value' => '15 kg', 'tone' => 'default'],
                    ['label' => 'Kuntum Florette', 'value' => 'Ketat & Hijau Tua', 'tone' => 'default'],
                    ['label' => 'Inspeksi Lab GPA', 'value' => 'Bebas Residu Organik', 'tone' => 'success'],
                ],
            ],
            [
                'key' => 'stroberi_ciwidey',
                'code' => 'KOM-FRT-004',
                'hub_code' => 'GPA-CWD-04',
                'sku' => 'GPA-FRT-STR-005',
                'grade' => 'Grade AAA Sweet Charlie',
                'packaging' => 'Clamshell 500g Master Box',
                'crate_kg' => 5,
                'pack_label' => 'Master Box',
                'category' => 'fruit_highland',
                'category_label' => 'Buah Dataran Tinggi',
                'name' => 'Stroberi Ciwidey Segar',
                'subtitle' => 'Grade AAA Sweet Charlie · Ciwidey',
                'origin' => 'Ciwidey Farm Cluster',
                'availability' => 'Alokasi Khusus',
                'availability_tone' => 'neutral',
                'available' => false,
                'stock' => 65,
                'stock_label' => 'Stok Ready Chilled',
                'moq' => 5,
                'price' => 45000,
                'price_note' => 'Panen Harian 05:00 WIB',
                'price_note_tone' => 'success',
                'cold_chain' => '1-3°C Pre-Cooled',
                'quota_label' => 'Khusus',
                'quota_tone' => 'neutral',
                'critical' => false,
                'step' => 5,
                'initial_qty' => 10,
                'specs' => [
                    ['label' => 'Stok Ready Chilled', 'value' => '65 kg (Clamshell)', 'tone' => 'default'],
                    ['label' => 'Minimum Order (MOQ)', 'value' => '5 kg', 'tone' => 'default'],
                    ['label' => 'Brix Index', 'value' => '9.5°-10.2° Brix', 'tone' => 'success'],
                    ['label' => 'Packaging Standard', 'value' => 'Mika 500g Foam Lined', 'tone' => 'default'],
                ],
            ],
            [
                'key' => 'kol_segar',
                'code' => 'KOM-VEG-005',
                'hub_code' => 'GPA-LEM-05',
                'sku' => 'GPA-VEG-KOL-050',
                'grade' => 'Organik Padat',
                'packaging' => 'Krat Plastik Sanitasi 50kg',
                'crate_kg' => 50,
                'pack_label' => 'Krat',
                'category' => 'veg_leaf',
                'category_label' => 'Sayuran Daun Bulat',
                'name' => 'Kol Segar Lembang / Kubis Putih',
                'subtitle' => 'Organik Padat · Gudang Buffer Lembang',
                'origin' => 'Lembang Buffer Zone',
                'availability' => 'Tersedia',
                'availability_tone' => 'safe',
                'available' => true,
                'stock' => 3400,
                'stock_label' => 'Alokasi Buffer Stock',
                'moq' => 50,
                'price' => 8000,
                'price_note' => 'Harga Volume Skala Besar',
                'price_note_tone' => 'success',
                'cold_chain' => '15°C Well-Ventilated',
                'quota_label' => 'Alokasi Aman',
                'quota_tone' => 'safe',
                'critical' => false,
                'step' => 50,
                'initial_qty' => 50,
                'specs' => [
                    ['label' => 'Alokasi Buffer Stock', 'value' => '3.400 kg', 'tone' => 'default'],
                    ['label' => 'Minimum Order (MOQ)', 'value' => '50 kg', 'tone' => 'default'],
                    ['label' => 'Ventilasi Pengiriman', 'value' => 'Dry-Box 15°C', 'tone' => 'default'],
                    ['label' => 'Standar Kerapatan', 'value' => 'Krop Bulat Sempurna', 'tone' => 'default'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function adhoc(): array
    {
        return [
            'chip' => 'Program Ad Hoc Tonase',
            'title' => 'Permintaan Pasokan Khusus & Tonase Besar (> 5 Ton)',
            'body' => 'Butuh pasokan di luar alokasi kontrak mingguan? Tim Key Account GPA menyusun surat allotment, jadwal panen terjadwal, dan skema harga bulk untuk batch di atas 5 Ton.',
            'highlights' => [
                'Audit sertifikasi residu pestisida & uji lab berkala',
                'Penetapan lock harga kontrak berjangka 3 - 6 bulan',
                'Dedicated fleet armada cold storage direct sentra',
            ],
            'cta' => 'Ajukan Permintaan Kuota Panen Khusus B2B',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function reconciliation(): array
    {
        return [
            'title' => 'Tabel Rekonsiliasi & Status Gudang Hub Terkini',
            'subtitle' => 'Data terpadu gudang konsolidasi Cianjur Central Hub & Lembang Cold-Hub per 10 menit terakhir.',
            'tolerance' => 'Toleransi Timbang: ±0.5% Actual Net',
            'api_status' => 'API Status: 200 OK',
            'rows' => array_map(
                static fn (array $commodity): array => [
                    'hub_code' => $commodity['hub_code'],
                    'name' => $commodity['name'],
                    'origin' => $commodity['origin'],
                    'stock' => $commodity['stock'],
                    'moq' => $commodity['moq'],
                    'price' => $commodity['price'],
                    'cold_chain' => $commodity['cold_chain'],
                    'quota_label' => $commodity['quota_label'],
                    'quota_tone' => $commodity['quota_tone'],
                    'critical' => $commodity['critical'],
                    'key' => $commodity['key'],
                ],
                self::commodities(),
            ),
            'footer' => 'Rule 04 Compliance: Faktur penagihan mutlak berdasarkan timbangan netto tera sah saat serah terima di loading dock pembeli.',
            'footer_note' => 'Sinkronisasi Protokol: GPA-EDI-XML / HTTPS SHA-256',
        ];
    }
}
