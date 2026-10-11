<?php

namespace App\Support;

final class PublicGalleryData
{
    public static function hero(): array
    {
        return [
            'badge' => 'OPERATIONAL ARCHIVE // FIELD & COLD-CHAIN AUDIT',
            'headline' => [
                ['text' => 'Galeri & Dokumentasi Operasional', 'tone' => 'light'],
                ['text' => 'Rantai Pasok GPA', 'tone' => 'accent'],
            ],
            'lead' => 'Transparansi penuh dokumentasi visual dan verifikasi mutu: dari kebun petani binaan Jawa Barat, stasiun sortasi & cold storage +4°C, kalibrasi metrologi timbangan digital tera resmi, hingga receiving dock klien B2B secara real-time.',
            'actions' => [
                ['label' => 'Telusuri Arsip Foto & Video', 'href' => '#arsip', 'variant' => 'accent', 'icon' => 'camera'],
                ['label' => 'Protokol Verifikasi Fisik', 'href' => '#protokol', 'variant' => 'outline', 'icon' => 'shield'],
            ],
            'feed' => [
                'title' => 'LIVE FEED SYNC // ARCHIVE METADATA',
                'latency' => 'LATENCY: 42ms',
                'rows' => [
                    ['label' => 'HARVEST REGION', 'value' => 'Lembang & Ciwidey', 'note' => '12 Mitra Poktan Aktif'],
                    ['label' => 'SCALE CALIBRATION', 'value' => 'Mettler-Toledo C35', 'note' => 'Tera Valid ±0.05%'],
                    ['label' => 'CHILLER ROOM', 'value' => '+3.8°C (Set +4.0°C)', 'note' => '92% Rel. Humidity'],
                    ['label' => 'DIGITAL PoD', 'value' => '100% Barcode Matched', 'note' => 'Zero Tare Discrepancy'],
                ],
                'hash' => 'SECURITY HASH: 8f9b-12d4-e67c-99a0',
                'status' => 'LIVE CONNECTED',
            ],
        ];
    }

    public static function metrics(): array
    {
        return [
            ['label' => 'DOKUMEN TERUNGGAH', 'value' => '1.420+', 'note' => 'Arsip Foto & Video Terverifikasi', 'icon' => 'upload'],
            ['label' => 'AKURASI TIMBANGAN', 'value' => '100%', 'note' => 'Tera Timbangan Sah Metrologi', 'icon' => 'scale'],
            ['label' => 'KEPATUHAN TERMAL', 'value' => '0 Kontaminasi', 'note' => 'Cold-Chain +4°C Terpantau IoT', 'icon' => 'thermometer'],
            ['label' => 'KETEPATAN DISTRIBUSI', 'value' => '99.4%', 'note' => 'On-Time Dawn Delivery (03-06 WIB)', 'icon' => 'truck'],
        ];
    }

    public static function filter(): array
    {
        return [
            'tabs' => [
                ['key' => 'semua', 'label' => 'Semua Dokumentasi', 'count' => 48],
                ['key' => 'kebun', 'label' => 'Kebun & Panen', 'count' => 12],
                ['key' => 'gudang', 'label' => 'Gudang & Sortir', 'count' => 10],
                ['key' => 'mutu', 'label' => 'Uji Mutu & Timbangan', 'count' => 14],
                ['key' => 'logistik', 'label' => 'Logistik Reefer & PoD', 'count' => 12],
            ],
            'searchPlaceholder' => 'Cari nomor batch, lokasi kebun, atau armada...',
            'sortLabel' => 'Urutan: Terbaru (Oktober 2024)',
        ];
    }

    public static function summary(): array
    {
        return [
            'visible' => 6,
            'total' => 48,
            'unit' => 'ENTRI ARSIP LAPANGAN TERVERIFIKASI',
            'format' => 'FORMAT: OFFICIAL AUDIT CARD [GPA-SPEC: v2.4.1]',
        ];
    }

    public static function entries(): array
    {
        return [
            [
                'category' => 'kebun',
                'categoryLabel' => 'KEBUN & PANEN',
                'meta' => '05:30 WIB',
                'image' => '[IMG: PANEN_SELADA_LEMBANG_FARM]',
                'dim' => 'DIM: 1200x800px | ISO: 400 | RATIO 3:2',
                'title' => 'Panen Pagi Selada Romaine & Keriting (Lembang, Kab. Bandung Barat)',
                'rows' => [
                    ['label' => 'Batch ID', 'value' => 'PAN-202410-042', 'tone' => 'brand'],
                    ['label' => 'Petani Binaan', 'value' => 'Kelompok Tani Barokah', 'tone' => 'ink'],
                    ['label' => 'Suhu Lapangan', 'value' => '17°C (Ambient)', 'tone' => 'ink'],
                ],
                'body' => 'Pemotongan manual dengan pisau steril, pembersihan daun afkir langsung di bedengan, dan penempatan ke keranjang krat berpori untuk mencegah memar mekanis.',
                'badge' => 'TERA_VALID',
                'action' => 'Lihat Detail Foto',
                'search' => 'panen pagi selada romaine keriting lembang kabupaten bandung barat pan-202410-042 kelompok tani barokah suhu lapangan',
            ],
            [
                'category' => 'mutu',
                'categoryLabel' => 'UJI MUTU & TIMBANGAN',
                'meta' => '06:15 WIB',
                'image' => '[IMG: DOKUMENTASI_TIMBANGAN_DIGITAL_METTLER]',
                'dim' => 'DIM: 1200x800px | SER: METTLER-TOLEDO-C35',
                'title' => 'Kalibrasi Harian & Penimbangan Riil Netto (Central Packhouse Bogor)',
                'rows' => [
                    ['label' => 'Alat Timbang', 'value' => 'Mettler Toledo C3500', 'tone' => 'brand'],
                    ['label' => 'Toleransi Metrologi', 'value' => '±0.05% (Tera 2024)', 'tone' => 'success'],
                    ['label' => 'Operator QA-QC', 'value' => 'Tim Verifikasi Metrologi', 'tone' => 'ink'],
                ],
                'body' => 'Penimbangan otomatis keranjang tara vs netto sesuai SOP metrologi GPA, memastikan berat riil terverifikasi digital sebelum Surat Jalan diterbitkan.',
                'badge' => 'CAL: VERIFIED',
                'action' => 'Sertifikat Tera',
                'search' => 'kalibrasi harian penimbangan riil netto central packhouse bogor mettler toledo c3500 tera metrologi',
            ],
            [
                'category' => 'gudang',
                'categoryLabel' => 'GUDANG & SORTIR',
                'meta' => '24 Jam Monitoring',
                'image' => '[IMG: SORTIR_COLD_ROOM_STORAGE]',
                'dim' => 'DIM: 1200x800px | SENSOR: IOT-CLD-MON',
                'title' => 'Penataan Cold Storage & Kamar Pendingin +4°C (DC Ciracas)',
                'rows' => [
                    ['label' => 'Sensor IoT ID', 'value' => 'CLD-03-MON', 'tone' => 'brand'],
                    ['label' => 'Suhu Rata-rata', 'value' => '+3.8°C (Set +4.0°C)', 'tone' => 'success'],
                    ['label' => 'Kelembaban Relatif', 'value' => '92% RH', 'tone' => 'ink'],
                ],
                'body' => 'Buffer stock sayuran daun dan buah disimpan dalam precooling unit untuk menjaga kesegaran seluler sebelum dispatch pengiriman subuh.',
                'badge' => 'TEMP: +3.8°C',
                'action' => 'Log Sensor IoT',
                'search' => 'penataan cold storage kamar pendingin dc ciracas cld-03-mon kelembaban rh',
            ],
            [
                'category' => 'logistik',
                'categoryLabel' => 'LOGISTIK & DOCK',
                'meta' => '03:15 WIB',
                'image' => '[IMG: DISPATCH_ARMADA_REEFER_TRUCK]',
                'dim' => 'DIM: 1200x800px | PLATE: B 9421 TX',
                'title' => 'Loading Subuh Truk Reefer B 9421 TX Menuju Central Kitchen Ciracas',
                'rows' => [
                    ['label' => 'Armada / Spek', 'value' => 'Isuzu Giga Chiller (+4°C)', 'tone' => 'brand'],
                    ['label' => 'Driver Bertugas', 'value' => 'Pak Joko Susilo', 'tone' => 'ink'],
                    ['label' => 'Waktu Berangkat', 'value' => '03:15 WIB (On-Schedule)', 'tone' => 'success'],
                ],
                'body' => 'Pengecekan segel muatan, termometer datalogger box, dan kelengkapan Surat Jalan cetak rangkap 3 berbarcode sesuai standar rantai pasok.',
                'badge' => 'SEAL: #GPA-8812',
                'action' => 'Rute Armada',
                'search' => 'loading subuh truk reefer b 9421 tx central kitchen ciracas isuzu giga joko susilo surat jalan',
            ],
            [
                'category' => 'logistik',
                'categoryLabel' => 'LOGISTIK & DOCK',
                'meta' => '04:22 WIB',
                'image' => '[IMG: SERAH_TERIMA_POD_RECEIVING_DOCK]',
                'dim' => 'DIM: 1200x800px | SJ-GPA-202410-0112',
                'title' => 'Serah Terima Digital PoD di Loading Dock Hotel Grand Pangrango',
                'rows' => [
                    ['label' => 'Surat Jalan', 'value' => 'SJ-GPA-202410-0112', 'tone' => 'brand'],
                    ['label' => 'PIC Penerima', 'value' => 'Chef Danang (Head Kitchen)', 'tone' => 'ink'],
                    ['label' => 'Jam Tiba / BAP', 'value' => '04:22 WIB (0 Discrepancy)', 'tone' => 'success'],
                ],
                'body' => 'Verifikasi fisik bersama, penandatanganan berita acara serah terima digital, dan upload bukti stempel basah penerima tanpa sengketa timbangan.',
                'badge' => 'POD: SIGNED',
                'action' => 'Lembar PoD',
                'search' => 'serah terima digital pod loading dock hotel grand pangrango sj-gpa-202410-0112 chef danang',
            ],
            [
                'category' => 'mutu',
                'categoryLabel' => 'UJI MUTU & TIMBANGAN',
                'meta' => 'Sampling 5% Lot',
                'image' => '[IMG: UJI_LAB_RESIDU_PESTISIDA]',
                'dim' => 'DIM: 1200x800px | REPORT: LAB-QC-2024-88',
                'title' => 'Rapid Test Residu Pestisida & Higienitas Komoditas Tomat & Brokoli',
                'rows' => [
                    ['label' => 'Lab Reference', 'value' => 'LAB-QC-2024-88', 'tone' => 'brand'],
                    ['label' => 'Hasil Analisa', 'value' => 'Bebas Residu (Non-Detect)', 'tone' => 'success'],
                    ['label' => 'Kepatuhan Standar', 'value' => 'SNI Pangan Segar B2B', 'tone' => 'ink'],
                ],
                'body' => 'Sampling acak 5% dari setiap kedatangan pasokan petani untuk menjamin kepatuhan food safety katering penerbangan dan hotel bintang 5.',
                'badge' => 'SAFETY: PASSED',
                'action' => 'Laporan Lab',
                'search' => 'rapid test residu pestisida higienitas komoditas tomat brokoli lab-qc-2024-88 sni pangan segar',
            ],
        ];
    }

    public static function pagination(): array
    {
        return [
            'summary' => 'HALAMAN 1 DARI 8 (TOTAL 48 DATA BUKTI DOKUMENTASI)',
            'prev' => 'SEBELUMNYA',
            'next' => 'BERIKUTNYA',
            'current' => 1,
            'pages' => [1, 2, 3, 4, 'gap', 8],
        ];
    }

    public static function callToAction(): array
    {
        return [
            'badge' => 'PROTOKOL VERIFIKASI FISIK & KEABSAHAN DIGITAL GPA',
            'title' => 'Standar Keterbukaan & Integritas Dokumen Rantai Pasok',
            'lead' => 'Seluruh rekaman visual, sertifikat tera kalibrasi metrologi timbangan digital, dan form berita acara serah terima fisik (PoD) tersimpan dalam audit log terenkripsi. Mitra korporasi B2B dapat mengajukan inspeksi langsung ke Central Packhouse atau kalibrasi on-site armada reefer.',
            'checks' => [
                'Tera sah Dinas Perdagangan & Metrologi',
                'Surat Jalan berbarcode enkripsi',
                'Hash digital anti-manipulasi data',
            ],
            'actions' => [
                ['label' => '[ Ajukan Kunjungan / Audit Fisik ]', 'href' => 'register', 'variant' => 'accent', 'icon' => 'calendar'],
                ['label' => 'Unduh Profil Arsip (PDF)', 'modal' => 'profil-arsip', 'variant' => 'secondary', 'icon' => 'download'],
            ],
        ];
    }

    public static function archiveProfile(): array
    {
        return [
            'title' => 'Profil Arsip Dokumentasi GPA',
            'description' => 'Ringkasan berkas bukti dokumentasi lapangan [GPA-SPEC: v2.4.1] siap dikirim ke email korporat terdaftar.',
            'items' => [
                '48 bukti dokumentasi foto & video lapangan terverifikasi',
                'Sertifikat tera metrologi & log kalibrasi timbangan digital',
                'Rekaman cold-chain IoT kamar pendingin +4°C (92% RH)',
                'Berita acara serah terima digital (PoD) berbarcode',
                'Laporan uji residu pestisida LAB-QC-2024-88',
            ],
            'footnote' => 'Berkas dikirim terenkripsi dan berlaku sebagai lampiran audit bagi mitra kontrak aktif. Seluruh entri arsip pada halaman ini merupakan data simulasi untuk demo.',
        ];
    }
}
