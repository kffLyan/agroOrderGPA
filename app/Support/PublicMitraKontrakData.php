<?php

namespace App\Support;

final class PublicMitraKontrakData
{
    /**
     * @return array<string, mixed>
     */
    public static function hero(): array
    {
        return [
            'breadcrumb' => ['Beranda', 'Portofolio Kemitraan & Klien Kontrak'],
            'badge' => 'ENTERPRISE B2B ACCREDITATION REGISTRY',
            'headline' => [
                ['text' => 'Portofolio Kemitraan & Klien Kontrak Terverifikasi', 'tone' => 'light'],
                ['text' => 'AgroOrder GPA', 'tone' => 'accent'],
            ],
            'lead' => 'Daftar klien korporat dan kelompok tani binaan yang berjalan dengan kontrak terverifikasi. Kepatuhan pasokan, mutu komoditas, dan ketepatan kirim dipantau lewat indikator berkala.',
            'actions' => [
                ['label' => 'Ajukan Draf Kontrak B2B', 'href' => 'register', 'variant' => 'accent', 'icon' => 'navigation'],
                ['label' => 'Format Template PKS', 'modal' => 'template-pks', 'variant' => 'inverse', 'icon' => 'file-text'],
            ],
            'index' => self::servingIndex(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function servingIndex(): array
    {
        return [
            'title' => 'Supply Verification Index',
            'ref' => 'SVI-2025.2 // AUDIT REVIEW',
            'metrics' => [
                ['label' => 'Fulfillment SLA', 'value' => '99.4%', 'note' => '14.3 ton / bulan terkirim riil'],
                ['label' => 'Volume Bulanan', 'value' => '480+ Ton', 'note' => 'Audit: 08 Sep 2025'],
                ['label' => 'Residu Pestisida', 'value' => '0.00 ppm', 'note' => 'Hasil Lab Uji IOC'],
                ['label' => 'Reefer Aktif', 'value' => '2°C-4°C', 'note' => 'Pantauan Remote IoT'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function clients(): array
    {
        return [
            'eyebrow' => 'PORTFOLIO KONTRAK AKTIF B2B',
            'title' => 'Klien & Kontrak Aktif',
            'lead' => 'Tiga sektor utama yang menopang volume pasokan harian. Setiap kontrak tercatat dengan kode GRD (GPA Registered Deal) yang terverifikasi sistem.',
            'chips' => ['3 Sektor Aktif', 'Kontrak Terverifikasi'],
            'cards' => [
                [
                    'sector' => '01',
                    'grd' => 'GRD: AERONAUTICAL',
                    'title' => 'Inflight Catering & Central Kitchen',
                    'name' => 'Nusantara Inflight & Central Kitchen',
                    'contract' => 'GRD-2024-0031',
                    'body' => 'Pasokan sayuran segar harian untuk dapur pusat dan catering penerbangan dengan standar higienitas ketat.',
                    'details' => [
                        ['label' => 'Komoditas Utama:', 'value' => 'Selada Romaine, Tomat Beef', 'tone' => 'default'],
                        ['label' => 'Volume Pasokan:', 'value' => '400 kg / kirim harian', 'tone' => 'default'],
                        ['label' => 'Term Pembayaran:', 'value' => 'TOP 30 Hari', 'tone' => 'success'],
                    ],
                    'badge' => 'Kontrak Aktif',
                    'slaValue' => '99.4%',
                ],
                [
                    'sector' => '02',
                    'grd' => 'GRD: LUXURY HORECA',
                    'title' => 'Hotel & Hospitalitas B2B',
                    'name' => 'Grand Luxe Boutique Hotel Group',
                    'contract' => 'GRD-2024-0018',
                    'body' => 'Pengadaan komoditas segar untuk dapur hotel dan banquet dengan sortasi estetik grade A dan cold-chain ketat.',
                    'details' => [
                        ['label' => 'Komoditas Utama:', 'value' => 'Brokoli, Stroberi, Selada', 'tone' => 'default'],
                        ['label' => 'Volume Pasokan:', 'value' => '500 kg / kirim subuh', 'tone' => 'default'],
                        ['label' => 'Term Pembayaran:', 'value' => 'TOP 14 Hari', 'tone' => 'success'],
                    ],
                    'badge' => 'Kontrak Aktif',
                    'slaValue' => '99.8%',
                ],
                [
                    'sector' => '03',
                    'grd' => 'GRD: RETAIL FRESH',
                    'title' => 'Retail Modern & Supermarket',
                    'name' => 'Retail Fresh Mart Group',
                    'contract' => 'GRD-2024-0027',
                    'body' => 'Jaringan supermarket dengan pasokan sayur siap jual di rak dingin, kemasan berlabel, dan masa simpan terjaga.',
                    'details' => [
                        ['label' => 'Komoditas Utama:', 'value' => 'Sayur Daun, Umbi, Buah', 'tone' => 'default'],
                        ['label' => 'Volume Pasokan:', 'value' => '850 kg / kirim subuh', 'tone' => 'default'],
                        ['label' => 'Term Pembayaran:', 'value' => 'TOP 21 Hari', 'tone' => 'success'],
                    ],
                    'badge' => 'Kontrak Aktif',
                    'slaValue' => '99.7%',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function poktan(): array
    {
        return [
            'eyebrow' => 'JARINGAN HULU PETANI BINAAN',
            'title' => 'Kelompok Tani Binaan',
            'lead' => 'Kelompok tani binaan yang memasok panen langsung ke hub GPA. Seluruh lahan terdaftar dengan kontrak kemitraan dan jaminan harga dasar.',
            'total' => [
                'label' => 'Total Lahan Binaan',
                'value' => '142.5 Hektare',
                'note' => '3 wilayah binaan · 221 petani anggota',
            ],
            'cards' => [
                [
                    'name' => 'Gapoktan Cipanas Berkah',
                    'region' => 'Kab. Cianjur',
                    'area' => '42.7 Ha',
                    'commodities' => ['Tomat Beef & Sayur', 'Selada Romaine'],
                    'members' => '72 petani anggota',
                ],
                [
                    'name' => 'Poktan Mandiri Lembang',
                    'region' => 'Kab. Bandung Barat',
                    'area' => '48.2 Ha',
                    'commodities' => ['Brokoli Highland', 'Kol Putih Segar'],
                    'members' => '85 petani anggota',
                ],
                [
                    'name' => 'Kemitraan Petani Ciwidey',
                    'region' => 'Kab. Bandung Selatan',
                    'area' => '51.6 Ha',
                    'commodities' => ['Stroberi Dataran Tinggi', 'Sayur Daun Pre-Cooled'],
                    'members' => '64 petani anggota',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function onboarding(): array
    {
        return [
            'eyebrow' => 'B2B ONBOARDING FLOW',
            'title' => 'Alur Onboarding Kontrak B2B',
            'lead' => 'Empat tahap dari pengajuan hingga pasokan berjalan. Proses umumnya selesai dalam 5-7 hari kerja.',
            'phases' => [
                [
                    'step' => 'FASE_01',
                    'tag' => 'Registrasi',
                    'title' => 'Pengajuan & Dokumen',
                    'body' => 'Lengkapi profil perusahaan dan dokumen legalitas, lalu ajukan draf kontrak melalui formulir B2B.',
                    'meta' => ['label' => 'Estimasi', 'value' => '1 Hari Kerja'],
                ],
                [
                    'step' => 'FASE_02',
                    'tag' => 'Verifikasi',
                    'title' => 'Uji Sampel & Harga',
                    'body' => 'Tim QA mengirim sampel komoditas, menyepakati grade mutu, lalu mengunci harga kuartalan.',
                    'meta' => ['label' => 'Proses', 'value' => '2-3 Hari Kerja'],
                ],
                [
                    'step' => 'FASE_03',
                    'tag' => 'Kontrak',
                    'title' => 'PKS & Alokasi Kuota',
                    'body' => 'Penandatanganan PKS, alokasi kuota pasokan mingguan, lalu integrasi penagihan faktur.',
                    'meta' => ['label' => 'Dokumen', 'value' => '1 Hari Kerja'],
                ],
                [
                    'step' => 'FASE_04',
                    'tag' => 'Go-Live',
                    'title' => 'Kirim & Monitoring',
                    'body' => 'Pengiriman subuh dimulai, kepatuhan SLA dan mutu dipantau berkala oleh tim GPA.',
                    'meta' => ['label' => 'Jadwal', 'value' => 'Subuh 02:00-05:30 WIB'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function callToAction(): array
    {
        return [
            'headline' => 'Siap Berlangganan Pasokan Kontrak Terverifikasi?',
            'body' => 'Ajukan draf kontrak B2B atau minta format template PKS sebagai referensi awal tim pengadaan Anda.',
            'actions' => [
                ['label' => 'Ajukan Draf Kontrak B2B', 'href' => 'register', 'variant' => 'accent', 'icon' => 'send'],
                ['label' => 'Format Template PKS', 'modal' => 'template-pks', 'variant' => 'inverse', 'icon' => 'file-text'],
            ],
            'hotline' => [
                'label' => 'Hotline Kemitraan:',
                'value' => '+62 (062) 8123-4567 (08:00-18:00 WIB)',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function templatePks(): array
    {
        return [
            'title' => 'Format Template PKS Pasokan Komoditas',
            'description' => 'Kerangka perjanjian kerja sama pasokan standar antara vendor dan buyer. Dikirim sebagai referensi awal sebelum negosiasi.',
            'items' => [
                'Jangka waktu kontrak dan harga kuartalan',
                'Kuota pasokan mingguan dan grade mutu',
                'Metode timbang netto riil dan retur parsial',
                'Jadwal kirim subuh dan sanksi keterlambatan',
                'Faktur, termin TOP, dan rekonsiliasi e-PoD',
            ],
            'footnote' => 'Kontrak ditandatangani digital dan diverifikasi sistem sebelum pengiriman dimulai.',
        ];
    }

    public static function disclaimer(): string
    {
        return 'Daftar klien, kelompok tani, dan angka pada halaman ini adalah data simulasi untuk demo. Kode kontrak GRD hanya contoh format penomoran.';
    }
}