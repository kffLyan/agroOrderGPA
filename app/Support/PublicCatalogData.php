<?php

namespace App\Support;

/**
 * Sumber data halaman publik Katalog Komoditas Agribisnis.
 *
 * Seluruh angka di bawah adalah data simulasi yang mencerminkan design
 * "Katalog Komoditas Agribisnis / Pasokan Terstandarisasi & Franco Resmi".
 * Nanti dapat dipindahkan ke query Eloquent / service layer tanpa mengubah
 * struktur array pada view.
 */
class PublicCatalogData
{
    /**
     * @return array<string, mixed>
     */
    public static function breadcrumb(): array
    {
        return [
            'items' => [
                ['label' => 'Beranda', 'href' => '/'],
                ['label' => 'Katalog Komoditas Agribisnis'],
            ],
            'note' => 'Pasokan Terstandarisasi & Franco Resmi',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        return [
            'badge' => 'Katalog Resmi & Harga Franco Gudang',
            'title' => 'Katalog Komoditas Agribisnis',
            'lead' => 'Ketersediaan pasokan panen segar langsung dari Petani Binaan & Buffer Stock Terpadu PT Agro Pasti Ada. Harga transparan per kilogram franco packhouse terverifikasi.',
            'assurances' => [
                'Harga Franco Gudang / siap muat',
                'Sinkronisasi stok packhouse real-time',
                'Spesifikasi grade & cold-chain terbuka',
            ],
            'sync' => [
                'label' => 'Sinkronisasi Stok',
                'status' => 'LIVE',
                'age' => '3 mnt lalu',
                'nodeLabel' => 'Node',
                'node' => 'Ciawi, Pacet & Pengalengan',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function toolbar(): array
    {
        return [
            'categoryLabel' => 'Kategori:',
            'categories' => [
                ['value' => 'all', 'label' => 'Semua Komoditas'],
                ['value' => 'veg_leaf', 'label' => 'Sayuran Daun'],
                ['value' => 'veg_fruit', 'label' => 'Sayuran Buah'],
                ['value' => 'veg_flower', 'label' => 'Sayuran'],
                ['value' => 'fruit_fresh', 'label' => 'Buah Segar'],
            ],
            'availableLabel' => 'Tampilkan Hanya Stok Tersedia',
            'searchLabel' => 'Cari',
            'searchPlaceholder' => 'Cari nama komoditas, SKU, atau gudang franco…',
            'sortLabel' => 'Urutkan:',
            'sortOptions' => [
                ['value' => 'stock', 'label' => 'Stok Terbanyak'],
                ['value' => 'price_asc', 'label' => 'Harga Terendah'],
                ['value' => 'price_desc', 'label' => 'Harga Tertinggi'],
                ['value' => 'moq', 'label' => 'MOQ Terendah'],
                ['value' => 'name', 'label' => 'Nama Komoditas'],
            ],
            'totalLabel' => 'Total:',
            'totalSuffix' => 'Komoditas Tertera',
            'total' => 5,
            'emptyState' => 'Tidak ada komoditas yang cocok dengan filter Anda. Coba ubah kategori atau kata kunci pencarian.',
            'resetLabel' => 'Reset Filter',
        ];
    }

    /**
     * Kartu komoditas pada katalog publik. Bentuk item mengikuti kontrak yang
     * dipakai komponen Alpine `clientCatalog` sehingga draft PO pindah mulus ke
     * halaman keranjang.
     *
     * @return list<array<string, mixed>>
     */
    public static function commodities(): array
    {
        return [
            [
                'key' => 'selada_romaine',
                'code' => 'KOM-VEG-001',
                'hub_code' => 'GPA-CJR-01',
                'sku' => 'SKU-SLD-ROM-01',
                'grade' => 'Grade A Super',
                'packaging' => 'Krat Plastik Sanitasi 10 kg',
                'crate_kg' => 10,
                'pack_label' => 'Krat',
                'category' => 'veg_leaf',
                'category_label' => 'Sayuran Daun',
                'name' => 'Selada Romaine / Lettuce',
                'image_path' => 'lettuce.jpg',
                'subtitle' => 'Selada Romaine Segar · Franco Gudang Cianjur',
                'botanical' => 'Selada Romaine Segar',
                'latin' => 'Lactuca sativa L. var. longifolia',
                'origin' => 'Gudang Cianjur',
                'franco' => 'Franco Gudang Cianjur',
                'badges' => ['Grade A Super', 'Horeca'],
                'available' => true,
                'stock' => 1250,
                'stock_label' => '1.250 kg',
                'stock_note' => null,
                'stock_tone' => 'safe',
                'moq' => 10,
                'unit' => 'kg',
                'extra' => ['label' => 'Grade Kualitas', 'value' => 'Super / Horeca Grade'],
                'price' => 15000,
                'price_label' => 'Harga Satuan',
                'cold_chain' => '4-7°C Cold Room',
                'step' => 10,
                'initial_qty' => 50,
            ],
            [
                'key' => 'tomat_beef',
                'code' => 'KOM-VEG-002',
                'hub_code' => 'GPA-CJR-02',
                'sku' => 'SKU-TMT-BEEF-02',
                'grade' => 'Grade A',
                'packaging' => 'Krat Bambu / Peti Kayu 25 kg',
                'crate_kg' => 25,
                'pack_label' => 'Krat',
                'category' => 'veg_fruit',
                'category_label' => 'Sayuran Buah',
                'name' => 'Tomat Beef / Sayur',
                'image_path' => 'tomato.jpg',
                'subtitle' => 'Tomat Beef Pilihan · Franco Gudang Lembang',
                'botanical' => 'Tomat Beef Pilihan',
                'latin' => 'Solanum lycopersicum',
                'origin' => 'Gudang Lembang',
                'franco' => 'Franco Gudang Lembang',
                'badges' => ['Grade A', 'Kematangan 85%'],
                'available' => true,
                'stock' => 180,
                'stock_label' => '180 kg',
                'stock_note' => 'Terbatas',
                'stock_tone' => 'caution',
                'moq' => 25,
                'unit' => 'kg',
                'extra' => ['label' => 'Tingkat Kematangan', 'value' => '80% - 85% Segar'],
                'price' => 12500,
                'price_label' => 'Harga Satuan',
                'cold_chain' => '12-15°C Ambient Fan',
                'step' => 25,
                'initial_qty' => 25,
            ],
            [
                'key' => 'brokoli_super',
                'code' => 'KOM-VEG-003',
                'hub_code' => 'GPA-PGL-03',
                'sku' => 'SKU-BRK-SPR-03',
                'grade' => 'Grade Super',
                'packaging' => 'Krat Ventilasi GPA 15 kg',
                'crate_kg' => 15,
                'pack_label' => 'Krat',
                'category' => 'veg_flower',
                'category_label' => 'Sayuran',
                'name' => 'Brokoli Super',
                'image_path' => 'broccoli.jpg',
                'subtitle' => 'Brokoli Kuntum Padat · Franco Gudang Pangalengan',
                'botanical' => 'Brokoli Kuntum Padat',
                'latin' => 'Brassica oleracea var. italica',
                'origin' => 'Gudang Pangalengan',
                'franco' => 'Franco Gudang Pangalengan',
                'badges' => ['Grade Super'],
                'available' => true,
                'stock' => 450,
                'stock_label' => '450 kg',
                'stock_note' => null,
                'stock_tone' => 'safe',
                'moq' => 15,
                'unit' => 'kg',
                'extra' => ['label' => 'Sortir', 'value' => 'Kuntum Bebas Ulat'],
                'price' => 28000,
                'price_label' => 'Harga Satuan',
                'cold_chain' => '2-4°C Ice Flake',
                'step' => 15,
                'initial_qty' => 15,
            ],
            [
                'key' => 'stroberi_ciwidey',
                'code' => 'KOM-FRT-004',
                'hub_code' => 'GPA-BDG-04',
                'sku' => 'SKU-STR-CIW-04',
                'grade' => 'Premium Sweet',
                'packaging' => 'Mika 500 g perforated / Master Box',
                'crate_kg' => 5,
                'pack_label' => 'Master Box',
                'category' => 'fruit_fresh',
                'category_label' => 'Buah Segar',
                'name' => 'Stroberi Ciwidey Segar',
                'image_path' => 'strawberry.jpg',
                'subtitle' => 'Stroberi Dataran Tinggi · Franco Gudang Bandung',
                'botanical' => 'Stroberi Dataran Tinggi',
                'latin' => 'Fragaria × ananassa',
                'origin' => 'Gudang Bandung',
                'franco' => 'Franco Gudang Bandung',
                'badges' => ['Premium Sweet'],
                'available' => true,
                'stock' => 65,
                'stock_label' => '65 kg',
                'stock_note' => 'Terbatas',
                'stock_tone' => 'caution',
                'moq' => 5,
                'unit' => 'kg / pack',
                'extra' => ['label' => 'Packaging', 'value' => 'Mika 500g perforated'],
                'price' => 45000,
                'price_label' => 'Harga Satuan',
                'cold_chain' => '1-3°C Pre-Cooled',
                'step' => 5,
                'initial_qty' => 5,
            ],
            [
                'key' => 'kol_putih',
                'code' => 'KOM-VEG-005',
                'hub_code' => 'GPA-LEM-05',
                'sku' => 'SKU-KOL-PUT-05',
                'grade' => 'Organik Padat',
                'packaging' => 'Krat Plastik Sanitasi 50 kg',
                'crate_kg' => 50,
                'pack_label' => 'Krat',
                'category' => 'veg_leaf',
                'category_label' => 'Sayuran Daun',
                'name' => 'Kol Putih / Kubis',
                'image_path' => 'cabbage.jpg',
                'subtitle' => 'Kol Putih Segar · Franco Gudang Lembang',
                'botanical' => 'Kol Putih Segar',
                'latin' => 'Brassica oleracea var. capitata',
                'origin' => 'Gudang Lembang',
                'franco' => 'Franco Gudang Lembang',
                'badges' => [],
                'available' => false,
                'stock' => 0,
                'stock_label' => 'Stok Habis (0 kg)',
                'stock_note' => null,
                'stock_tone' => 'off',
                'moq' => 50,
                'unit' => 'kg',
                'extra' => ['label' => 'Estimasi Panen', 'value' => 'Batch 4 (+4 Hari)'],
                'price' => 8000,
                'price_label' => 'Harga Referensi',
                'cold_chain' => '15°C Well-Ventilated',
                'step' => 50,
                'initial_qty' => 0,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function contract(): array
    {
        return [
            'title' => 'Permintaan Pasokan Khusus?',
            'body' => 'Butuh pasokan tonase besar di luar buffer stock harian? Ajukan kontrak pasokan terikat lewat formulir pengadaan agribisnis B2B — tim Key Account GPA menyiapkan alokasi panen terjadwal, lock harga kontrak, dan jadwal pengiriman subuh.',
            'note' => 'Program Ad Hoc Tonase · Di luar buffer stock harian · Minimal 5 ton per batch',
            'cta' => ['label' => 'Request Kontrak Pasokan', 'href' => 'register'],
        ];
    }

    /**
     * Preview percobaan PRD Rule 08: validasi minimum order pada drawer pesanan.
     *
     * @return array<string, mixed>
     */
    public static function validation(): array
    {
        return [
            'title' => 'Validasi Minimum Order (PRD Rule 08)',
            'chip' => 'Drawer Preview',
            'commodityKey' => 'selada_romaine',
            'intro' => 'Komoditas Aktif Diverifikasi:',
            'commodity' => 'Selada Romaine / Lettuce (SKU-SLD-ROM-01)',
            'ruleLabel' => 'Ketentuan Minimal Order:',
            'ruleValue' => '10 kg',
            'simulation' => [
                'label' => 'Simulasi Input Pemesanan:',
                'unit' => 'kg',
                'hint' => '(Coba ubah ke < 10 kg atau >= 10 kg)',
                'warningTitle' => 'Peringatan Sistem GPA:',
                'warningBody' => 'Validasi Minimum Order: Pemesanan Selada minimal 10 kg. Jika input di bawah 10 kg, sistem menampilkan pesan kuantitas kurang dan tombol pesanan dinonaktifkan.',
                'statusUnder' => 'Status: TOMBOL NONAKTIF / ORDER_UNDER_LIMIT',
                'readyNote' => 'Kuantitas memenuhi MOQ — komoditas siap ditambahkan ke pesanan.',
                'statusReady' => 'Status: TOMBOL AKTIF / ORDER_READY',
            ],
            'logisticsTitle' => 'Rincian Logistik & Penanganan / Cold-Chain',
            'logistics' => [
                ['label' => 'Suhu Simpan', 'value' => '4°C - 7°C (Cold Room)'],
                ['label' => 'Kemasan Transport', 'value' => 'Keranjang Crate 10 kg'],
                ['label' => 'Masa Simpan Optimal', 'value' => '3 - 4 Hari sejak Panen'],
                ['label' => 'Toleransi Susut', 'value' => 'Maks. 2% saat Timbang Terima'],
            ],
            'download' => 'Unduh Spesifikasi Teknis (PDF)',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function cart(): array
    {
        return [
            'title' => 'Pesanan Sementara',
            'itemsLabel' => 'Item Terpilih',
            'emptyTitle' => 'Keranjang masih kosong',
            'emptyBody' => 'Tambahkan komoditas dari katalog — ringkasan pesanan Anda tampil di sini sebelum dikirim ke verifikasi admin GPA.',
            'hintLabel' => 'Catatan',
            'hint' => 'Harga bersifat indikatif franco gudang dan dikunci ulang oleh admin GPA saat verifikasi PO.',
            'subtotalLabel' => 'Subtotal (sementara)',
            'cta' => 'Lanjut ke Keranjang & Pemesanan',
            'removeLabel' => 'Hapus',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'title' => 'Aturan Bisnis #01 & #02',
            'subtitle' => 'Protokol Transaksi AgroOrder GPA',
            'badge' => 'Terverifikasi',
            'body' => 'Katalog dapat diakses publik tanpa login untuk memantau harga, stok, dan spesifikasi komoditas. Pembuatan pesanan, penetapan kuota, serta proses pembayaran hanya dapat dilakukan oleh akun Mitra B2B terverifikasi dengan RBAC aktif.',
            'actions' => [
                ['label' => 'Pelajari Prosedur Mitra', 'href' => 'public.supply-chain', 'variant' => 'secondary'],
                ['label' => 'Masuk / Login Mitra B2B', 'href' => 'login', 'variant' => 'accent'],
            ],
        ];
    }
}
