<?php

namespace App\Support;

final class PublicSupplyChainData
{
    public static function hero(): array
    {
        return [
            'breadcrumb' => ['Beranda', 'Rantai Pasok Terintegrasi'],
            'eyebrow' => 'End-to-End Supply Chain Engine',
            'headline' => [
                ['text' => 'Arsitektur Rantai Pasok Terintegrasi Dari Petani Hingga', 'tone' => 'light'],
                ['text' => 'Dapur Komersial', 'tone' => 'accent', 'underline' => true],
            ],
            'lead' => 'Diagram alur transparan kepatuhan PRD: Pasokan → Penimbangan Riil → Surat Jalan Sah → Cold-Chain → PoD Digital → Faktur Konsolidasi. Seluruh pergerakan komoditas terhubung secara deterministik dengan jaminan audit metrologi legal.',
            'actions' => [
                ['label' => 'Pelajari Alur 6 Tahap', 'href' => '#alur-pasok', 'variant' => 'accent'],
                ['label' => 'Standar Kontrol Mutu', 'href' => '#kontrol-mutu', 'variant' => 'inverse'],
            ],
            'blueprint' => self::blueprint(),
        ];
    }

    public static function blueprint(): array
    {
        return [
            'title' => 'ARCH_BLUEPRINT // PIPELINE',
            'revision' => 'SYS_REV: 2025.2',
            'input' => ['source' => '[Input: Gudang Tera Sah]', 'field' => 'ACTUAL_NETTO'],
            'metrics' => [
                ['icon' => 'scale', 'label' => 'Field Shrinkage Cap', 'value' => '≤ 2.0%'],
                ['icon' => 'thermometer', 'label' => 'Cold Reefer Temp', 'value' => '4°C - 8°C'],
            ],
            'status' => ['label' => 'STATUS: PRD_RULE_STRICT', 'value' => 'AUTO-LOCKED'],
            'checks' => [
                ['label' => 'Metrologi Tera', 'value' => 'Sah Ditjen Metrologi'],
                ['label' => 'Subuh Dispatch', 'value' => '02:00 – 05:30 WIB'],
                ['label' => 'Billing Recon', 'value' => 'Terkunci via e-PoD'],
            ],
        ];
    }

    public static function headlineMetrics(): array
    {
        return [
            ['icon' => 'scale', 'label' => 'Field Shrinkage Cap', 'value' => '≤ 2.0%', 'note' => 'Sesuai Ketentuan PRD Rule 13'],
            ['icon' => 'thermometer', 'label' => 'Reefer Temperature', 'value' => '4°C – 8°C', 'note' => 'IoT Telemetri Aktif 24/7'],
            ['icon' => 'gauge', 'label' => 'Tare Auto-Lock', 'value' => 'Net Weight', 'note' => 'Timbangan Tera Sah Metrologi'],
            ['icon' => 'clipboard', 'label' => 'Settlement Anchor', 'value' => 'Digital PoD', 'note' => 'Dual Key Validasi Lapangan'],
        ];
    }

    public static function nodes(): array
    {
        return [
            ['step' => '01', 'label' => 'Binaan & Buffer', 'active' => false],
            ['step' => '02', 'label' => 'Intake & Lab QC', 'active' => false],
            ['step' => '03', 'label' => 'Timbang Tera Sah', 'active' => true],
            ['step' => '04', 'label' => 'Kunci Surat Jalan', 'active' => false],
            ['step' => '05', 'label' => 'Armada Reefer', 'active' => false],
            ['step' => '06', 'label' => 'Digital PoD & TOP', 'active' => false],
        ];
    }

    public static function stages(): array
    {
        return [
            [
                'stage' => 'Tahap 01',
                'rule' => 'RULE_02 & RULE_03',
                'title' => 'Pasokan Terjamin Petani Binaan & Buffer Stock Terkendali',
                'body' => 'Anti-Overselling engine mengunci alokasi hasil panen langsung dari gapoktan binaan terdaftar. Buffer stock harian terjaga pada toleransi aman untuk mencegah kegagalan pemenuhan PO klien B2B.',
                'token' => '[FARMER_POOL_BUFFER]',
                'caption' => 'Capacity: 12.5 MT / Regional Hub',
                'meta' => [
                    ['label' => 'Kontrak Tanam Petani', 'value' => 'Tervalidasi Digital', 'tone' => 'ink'],
                    ['label' => 'Buffer Safety Stock', 'value' => 'Min 15% Daily Quota', 'tone' => 'success'],
                ],
                'critical' => false,
            ],
            [
                'stage' => 'Tahap 02',
                'rule' => 'REGIONAL_HUBS',
                'title' => 'Intake Gate & Sortir Mutu Hub (Bogor, Cianjur, Lembang)',
                'body' => 'Pemeriksaan fisik langsung saat tiba di hub regional: refraktometer derajat Brics (kemanisan buah/umbi), uji kadar air, pengecekan visual hama, dan sorting grade A/B/C sesuai katalog spesifikasi.',
                'token' => '[INSPECTION: REFRACTOMETER]',
                'caption' => 'Pass Threshold: Grade A Std',
                'meta' => [
                    ['label' => 'Hub Bogor', 'value' => 'Sayur Daun & Umbi', 'tone' => 'ink'],
                    ['label' => 'Cianjur & Lembang', 'value' => 'Hortikultura Tinggi', 'tone' => 'ink'],
                ],
                'critical' => false,
            ],
            [
                'stage' => 'Tahap 03',
                'rule' => '[PRD Rule 04 & 05]',
                'title' => 'Penimbangan Bersih Riil Gudang Tera Sah Metrologi',
                'body' => 'Penimbangan wajib di atas timbangan terkalibrasi segel Metrologi resmi. Sistem melakukan auto-deduction berat wadah (tara). Nilai Actual Net Weight langsung terkunci dan menjadi acuan mutlak tagihan final.',
                'token' => '[SCALE: METROLOGY_SEALED]',
                'caption' => 'NET_KG = GROSS - TARE_STD',
                'meta' => [
                    ['label' => 'Sertifikasi Tera', 'value' => 'Ditjen Metrologi RI', 'tone' => 'ink'],
                    ['label' => 'Manipulasi Data', 'value' => 'Terkunci API (Zero Edit)', 'tone' => 'success'],
                ],
                'critical' => true,
            ],
            [
                'stage' => 'Tahap 04',
                'rule' => 'SJ_AUTOMATION',
                'title' => 'Penerbitan Surat Jalan Sah (SJ-GPA-YYYYMM-XXXX)',
                'body' => 'Surat Jalan diterbitkan secara instan dari timbangan netto tanpa intervensi manual. Menggunakan nomor seri registrasi unik terlacak lengkap dengan rincian lot panen, driver ID, nopol armada, dan jam muat.',
                'token' => '[DOC: SJ-GPA-202502-0891]',
                'caption' => 'Encrypted Barcode & Hash ID',
                'meta' => [
                    ['label' => 'Format Registrasi', 'value' => 'SJ-GPA-YYYYMM-XXXX', 'tone' => 'ink'],
                    ['label' => 'Validasi Netto', 'value' => '100% Cocok Penimbangan', 'tone' => 'success'],
                ],
                'critical' => false,
            ],
            [
                'stage' => 'Tahap 05',
                'rule' => 'COLD_LOGISTICS',
                'title' => 'Distribusi Reefer Dingin (4°C - 8°C) & Serah Terima Subuh',
                'body' => 'Pengangkutan menggunakan armada truk berpendingin aktif. Dipantau IoT temperatur sepanjang jalan. Jadwal pengiriman subuh (02.00 - 05.30 WIB) guaranteeing vegetables arrive fresh before hotel/restaurant kitchen production hours.',
                'token' => '[REEFER_TRUCK: 4.2°C STABLE]',
                'caption' => 'Subuh Delivery Slot (02:00 - 05:30)',
                'meta' => [
                    ['label' => 'Target Kitchen Ready', 'value' => 'Tiba Sebelum 06:00 WIB', 'tone' => 'ink'],
                    ['label' => 'Datalogger GPS', 'value' => 'Realtime Telemetry', 'tone' => 'success'],
                ],
                'critical' => false,
            ],
            [
                'stage' => 'Tahap 06',
                'rule' => 'FINAL_SETTLEMENT',
                'title' => 'Proof of Delivery (PoD) Digital & Faktur Konsolidasi TOP',
                'body' => 'Penerimaan diverifikasi di receiving bay hotel/katering melalui aplikasi supir: tanda tangan digital + stempel basah receiving. Otomatis masuk rekapitulasi Faktur Konsolidasi Bulanan dengan syarat termin pembayaran B2B (TOP 14/30).',
                'token' => '[POD: DIGITAL_STAMP_VERIFIED]',
                'caption' => 'Consolidated Monthly Billing',
                'meta' => [
                    ['label' => 'Dokumen Final', 'value' => 'e-PoD + Tembusan Fisik', 'tone' => 'ink'],
                    ['label' => 'Rekonsiliasi', 'value' => 'Otomatisasi ERP B2B', 'tone' => 'success'],
                ],
                'critical' => false,
            ],
        ];
    }

    public static function qualityControl(): array
    {
        return [
            'title' => 'Parameter Ambang Batas Kontrol Mutu',
            'auditRef' => '[AUDIT_LOG_2025]',
            'columns' => [
                ['key' => 'category', 'label' => 'Kategori Komoditas'],
                ['key' => 'metric', 'label' => 'Standar Metrik'],
                ['key' => 'protocol', 'label' => 'Protokol Toleransi'],
                ['key' => 'status', 'label' => 'Status Rule', 'align' => 'right'],
            ],
            'rows' => [
                [
                    'category' => 'Sayuran Daun (Romaine, Iceberg)',
                    'metric' => '4.0°C – 8.0°C',
                    'protocol' => 'Sensor alarm jika > 8.5°C selama > 25 menit',
                    'status' => ['label' => 'MANDATORY', 'tone' => 'solid'],
                ],
                [
                    'category' => 'Toleransi Susut Sortir Lapangan',
                    'metric' => '≤ 2.0% Netto',
                    'protocol' => 'PRD Rule 13: Melebihi 2.0% wajib Berita Acara',
                    'status' => ['label' => 'RULE_13', 'tone' => 'outline'],
                ],
                [
                    'category' => 'Kalibrasi Timbangan Duduk/Lantai',
                    'metric' => 'Deviasi ≤ 0.05%',
                    'protocol' => 'Tera berkala per 6 bulan dengan sertifikat resmi',
                    'status' => ['label' => 'VERIFIED', 'tone' => 'solid'],
                ],
                [
                    'category' => 'Kadar Air Bawang Merah & Putih',
                    'metric' => '≤ 14% Moisture',
                    'protocol' => 'Uji tusuk probe otomatis di intake hub',
                    'status' => ['label' => 'PASS_GATE', 'tone' => 'outline'],
                ],
                [
                    'category' => 'Kemanisan Buah (Melon, Cherry)',
                    'metric' => '10.5 – 12.0 °Bx',
                    'protocol' => 'Sampel 3 acak per keranjang panen Gapoktan',
                    'status' => ['label' => 'STRICT', 'tone' => 'solid'],
                ],
            ],
            'source' => 'Standard Source: ISO 22000 & GPA Operational Manual',
            'auditReady' => 'AUDIT READY: TRUE',
        ];
    }

    public static function returnProtocol(): array
    {
        return [
            'rule' => 'PRD Protocol // Rule 17',
            'title' => 'Penanganan Retur Parsial di Lokasi',
            'token' => 'RULE_17_EXEC',
            'steps' => [
                [
                    'title' => 'Identifikasi Langsung di Receiving Bay',
                    'body' => 'Jika terdapat item yang cacat fisik (> toleransi) saat serah terima subuh, chef/purchasing mencatat retur parsial pada aplikasi e-PoD sebelum menandatangani berita acara serah terima.',
                ],
                [
                    'title' => 'Penguncian Nilai Bersih Faktur',
                    'body' => 'Item yang diretur secara otomatis mengurangi kuantitas tagihan final. Sistem GPA memblokir perhitungan atas barang yang ditolak sehingga tidak terjadi tagihan silang maupun selisih nota.',
                ],
                [
                    'title' => 'Kompensasi Kuota PO Berikutnya',
                    'body' => 'Kekurangan volume akibat sortir lapangan atau retur langsung dialokasikan ke pengiriman prioritas sore hari atau ditambahkan otomatis ke pesanan hari kerja berikutnya dengan status gratis ongkos kirim.',
                ],
            ],
            'stamp' => [
                'label' => '[Digital_Verification_Stamp]',
                'status' => 'STATUS: PRD_COMPLIANT',
                'note' => 'Surat Jalan: SJ-GPA-YYYYMM-XXXX • Driver & QC Dual Key Cryptographic Signature.',
            ],
        ];
    }

    public static function infrastructure(): array
    {
        return [
            'eyebrow' => 'Physical Infrastructure Ledger',
            'title' => 'Kapasitas Infrastruktur Fisik Terintegrasi',
            'lead' => 'Kekuatan hulu ke hilir yang menjamin pasokan tidak terputus sepanjang tahun.',
            'cards' => [
                [
                    'icon' => 'leaf',
                    'label' => 'Farm Network Integration',
                    'value' => '42 Kelompok Tani Binaan',
                    'body' => 'Kemitraan kontraktual terikat dengan kepastian harga dan panduan pupuk organik untuk menjaga kestabilan pasokan 365 hari tanpa putus.',
                    'note' => 'Cianjur • Sukabumi • Pangalengan • Lembang',
                    'tone' => 'ink',
                ],
                [
                    'icon' => 'truck',
                    'label' => 'Cold-Chain Fleet',
                    'value' => '18 Unit Armada Berpendingin',
                    'body' => 'Dilengkapi pendingin Thermo King dengan insulasi poliuretan densitas tinggi. Suhu terpantau telematika GSM 24/7 langsung ke monitor operasi pusat.',
                    'note' => 'Blind Van Reefer • Cold Box CDE • CDD 6 Roda',
                    'tone' => 'ink',
                ],
                [
                    'icon' => 'gauge',
                    'label' => 'B2B Receiving Accuracy',
                    'value' => '99.8% On-Time Delivery Subuh',
                    'body' => 'Pengantaran subuh terjadwal ketat untuk mengakomodasi jadwal persiapan dapur eksekutif hotel bintang lima dan fasilitas katering industri.',
                    'note' => 'Toleransi Keterlambatan: Max 15 Menit',
                    'tone' => 'success',
                ],
            ],
        ];
    }

    public static function callToAction(): array
    {
        return [
            'eyebrow' => 'Institutional Procurement Ready',
            'headline' => 'Siap Mengintegrasikan Kebutuhan Bahan Baku Restoran & Hotel Anda?',
            'body' => 'Hubungi koordinator rantai pasok kami untuk penjadwalan uji sampel, verifikasi sistem timbangan tera, dan simulasi penagihan konsolidasi dengan skema TOP.',
            'actions' => [
                ['label' => 'Hubungi Tim Operasional GPA', 'route' => 'register', 'variant' => 'accent'],
                ['label' => 'Unduh Dokumen SOP (PDF)', 'modal' => 'sop', 'variant' => 'inverse'],
            ],
            'procedure' => [
                'title' => 'Paket Dokumen SOP Rantai Pasok GPA',
                'description' => 'Dokumen standar operasional yang dilampirkan pada proses onboarding institutional procurement AgroOrder GPA.',
                'items' => [
                    'SOP-PRD-01 — Alokasi Pasokan Petani Binaan & Buffer Stock Harian',
                    'SOP-PRD-02 — Intake Gate, Sortir Mutu, dan Uji Brix Regional Hub',
                    'SOP-PRD-04/05 — Penimbangan Tera Sah Metrologi & Penerbitan Surat Jalan',
                    'SOP-COLD-07 — Pengawetan Cold-Chain dan Slot Pengiriman Subuh',
                    'SOP-POD-09 — e-PoD Digital, Retur Parsial Rule 17, dan Faktur TOP',
                ],
                'footnote' => 'Checksum SHA-256 tiap berkas diverifikasi otomatis sebelum dikirimkan oleh tim Supply Chain GPA.',
            ],
        ];
    }
}
