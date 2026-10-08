<?php

namespace App\Support;

final class PublicHomeData
{
    public static function statusBar(): array
    {
        return [
            'node' => 'GPA-NODE: ACTIVE',
            'site' => 'Hub Sentral & Gudang Konsolidasi Panundaan',
            'metrics' => [
                ['label' => 'Aktual Timbangan Tera:', 'value' => 'Aktif 100%'],
                ['label' => 'Buffer Sync:', 'value' => 'Terkalibrasi Realtime'],
            ],
        ];
    }

    public static function navigation(): array
    {
        return [
            ['label' => 'Beranda', 'href' => '/beranda'],
            ['label' => 'Tentang GPA', 'href' => '/tentang-gpa'],
            ['label' => 'Rantai Pasok', 'href' => 'public.supply-chain'],
            ['label' => 'Komoditas', 'href' => 'public.catalog'],
            ['label' => 'Mitra Kontrak', 'href' => '/mitra-kontrak'],
            ['label' => 'Galeri', 'href' => '/galeri'],
            ['label' => 'Kontak', 'href' => '/kontak'],
        ];
    }

    public static function hero(): array
    {
        return [
            'badge' => 'GPA PRODUCTION & DISTRIBUTION PROTOCOL V3.2',
            'headline' => [
                ['text' => "Sistem Rantai Pasok &\nPemesanan Agribisnis Terpadu:", 'tone' => 'light'],
                ['text' => "Dari Petani Hingga Dapur\nKomersial", 'tone' => 'accent'],
            ],
            'lead' => 'Solusi terintegrasi agrikultur: pasokan panen langsung kelompok tani binaan, manajemen buffer stock anti-selisih, penimbangan riil gudang tera metrologi sah, bukti serah terima digital (e-PoD), dan penagihan tempo B2B yang presisi dan akuntabel.',
            'actions' => [
                ['label' => 'Eksplorasi Katalog Komoditas', 'href' => 'public.catalog', 'variant' => 'accent', 'icon' => 'cart'],
                ['label' => 'Lihat Galeri Operasional', 'href' => '#galeri', 'variant' => 'outline', 'icon' => 'camera'],
            ],
            'note' => [
                'title' => 'Protokol Anti-Overselling:',
                'body' => 'Setiap DO (Delivery Order) divalidasi langsung terhadap timbangan fisik sebelum rute pengiriman logistik subuh diterbitkan.',
            ],
            'schematic' => [
                'label' => '[SCHEMATIC: GPA_CORE_FLOW_ENGINE]',
                'title' => 'Farm-to-Table Core Architecture',
                'id' => 'ID: AGRO-FLOW-2025',
                'flow' => [
                    ['step' => '01', 'title' => 'Petani Binaan & Buffer', 'body' => 'Panen terencana di sentra binaan dan pengamanan buffer stock.', 'tag' => 'Hulu Terjamin', 'tone' => 'light'],
                    ['step' => '02', 'title' => 'QC Timbang Riil', 'body' => 'Stasiun timbang tera metrologi sah & kalibrasi bluetooth real-time.', 'tag' => 'Tera Metrologi', 'tone' => 'success'],
                    ['step' => '03', 'title' => 'PoD Foto & Stempel Fisik', 'body' => 'Serah terima subuh via aplikasi e-PoD dua sisi di receiving dock.', 'tag' => 'Anti-Selisih', 'tone' => 'muted'],
                    ['step' => '04', 'title' => 'Faktur B2B (TOP / Manual)', 'body' => 'Invoicing otomatis berbasis bobot aktual dan lampiran bukti digital.', 'tag' => 'Audit-Ready', 'tone' => 'ink', 'highlighted' => true],
                ],
                'footer' => [
                    'label' => 'Transparansi Fisik & Digital',
                    'value' => 'SLA Akurasi: 99.8%',
                ],
            ],
        ];
    }

    public static function sop(): array
    {
        return [
            'eyebrow' => 'SOP & METODOLOGI OPERASIONAL',
            'icon' => 'clipboard',
            'headline' => 'Prinsip Operasional Rantai Pasok Terintegrasi',
            'lead' => 'Enam tahapan terkoordinasi secara ketat demi menjamin mutu hortikultura dan akuntabilitas transaksi harian bagi mitra komersial.',
            'cards' => [
                [
                    'step' => '01',
                    'stage' => 'HULU PERTANIAN',
                    'title' => 'Pasokan Terjamin & Buffer Stock',
                    'body' => "Jaringan petani binaan dataran tinggi Pacet dan Lembang\nmemastikan ketersediaan komoditas segar tanpa terputus.\nDilengkapi buffer stock cadangan pada cold storage hub.",
                    'metric' => ['label' => 'Safety Stock Buffer', 'value' => '15% Terkunci'],
                ],
                [
                    'step' => '02',
                    'stage' => 'INBOUND ORDER',
                    'title' => 'Pemesanan Multi-Kanal B2B',
                    'body' => "Pemesanan dapat diajukan secara fleksibel melalui portal\nweb B2B terotentikasi, integrasi API sistem enterprise, atau\nvia hotline WhatsApp terverifikasi.",
                    'metric' => ['label' => 'Cut-Off Pemesanan', 'value' => 'Pukul 13:00 WIB'],
                ],
                [
                    'step' => '03',
                    'stage' => 'ALOKASI FISIK',
                    'title' => 'Verifikasi & Kontrol Kuota',
                    'body' => "Order diperiksa terhadap batas kredit kontrak dan kuota\npasokan fisik yang tersedia. Menjamin pesanan yang\ndisetujui pasti terkirim 100%.",
                    'metric' => ['label' => 'Validasi DO', 'value' => 'Sistem Otomatis'],
                ],
                [
                    'step' => '04',
                    'stage' => 'QC & METROLOGI',
                    'title' => 'Sortir Mutu & Timbangan Riil',
                    'body' => "Sortasi manual lapis dua di gudang konsolidasi.\nPenimbangan menggunakan timbangan digital tersertifikasi\nmetrologi legal sah Kementerian Perdagangan RI.",
                    'metric' => ['label' => 'Standar Timbang', 'value' => 'Net Riil 100%'],
                ],
                [
                    'step' => '05',
                    'stage' => 'DISTRIBUSI SUBUH',
                    'title' => 'Logistik Subuh & PoD Digital',
                    'body' => "Pengiriman subuh memastikan sayuran tiba sebelum jam\noperasional dapur komersial. Serah terima diverifikasi via\nfoto fisik dan tanda tangan digital (e-PoD).",
                    'metric' => ['label' => 'Jendela Kirim', 'value' => '03:30 - 05:30 WIB'],
                ],
                [
                    'step' => '06',
                    'stage' => 'FINANCIAL SETTLEMENT',
                    'title' => 'Rekapitulasi Faktur & Tagihan',
                    'body' => "Faktur tagihan diterbitkan otomatis berdasarkan bobot riil\nyang tertera pada e-PoD sah. Tidak ada selisih antara\nproduk yang diterima dan jumlah tagihan.",
                    'metric' => ['label' => 'Invoicing Akurat', 'value' => 'Sinkron e-PoD'],
                ],
            ],
        ];
    }

    public static function catalog(): array
    {
        return [
            'eyebrow' => 'KATALOG PANEN TERKINI',
            'icon' => 'leaf',
            'headline' => 'Komoditas Inti Pertanian GPA',
            'lead' => 'Daftar komoditas siap pesan langsung oleh pembeli korporat maupun publik tanpa wajib login portal.',
            'chips' => [
                ['label' => 'Update Panen: 04:00 WIB', 'tone' => 'light'],
                ['label' => 'Toleransi <2%', 'tone' => 'ink'],
            ],
            'cards' => [
                [
                    'sku' => 'SKU-VEG-ROM01',
                    'name' => 'Selada Romaine / Lettuce',
                    'grade' => 'Grade A Super',
                    'gradeTone' => 'ink',
                    'image' => '[IMG: ROMAINE_LETTUCE_CRISP]',
                    'caption' => 'Hydroponic / highland clean leaf',
                    'body' => "Daun hijau tebal, renyah tanpa getah pahit. Bebas akar\nberlumpur, pencucian awal hidroponik siap pakai untuk salad\nbar.",
                    'minOrder' => '10 Kg',
                    'status' => 'Tersedia (Ready Hub)',
                    'statusTone' => 'success',
                    'price' => 'Rp 18.500',
                ],
                [
                    'sku' => 'SKU-VEG-TOM02',
                    'name' => 'Tomat Beef & Sayur',
                    'grade' => 'Grade Super',
                    'gradeTone' => 'ink',
                    'image' => '[IMG: TOMATO_BEEF_SELECTION]',
                    'caption' => 'Firm skin / low moisture loss',
                    'body' => "Daging buah tebal, kadar air seimbang, sortir kematangan 80-\n90% untuk daya simpan maksimal di cold storage komersial.",
                    'minOrder' => '25 Kg',
                    'status' => 'Tersedia (Panen Harian)',
                    'statusTone' => 'success',
                    'price' => 'Rp 14.000',
                ],
                [
                    'sku' => 'SKU-VEG-BRK03',
                    'name' => 'Brokoli Super Segar',
                    'grade' => 'Premium Grade',
                    'gradeTone' => 'ink',
                    'image' => '[IMG: BROCCOLI_HIGHLAND_CROP]',
                    'caption' => 'Dense florets / trimmed stem',
                    'body' => "Kuntum bunga padat berwarna hijau tua pekat, batang\ndipangkas proporsional. Bebas ulat dan residu kimia aman\nBPOM.",
                    'minOrder' => '15 Kg',
                    'status' => 'Tersedia (Pasokan Tinggi)',
                    'statusTone' => 'success',
                    'price' => 'Rp 26.000',
                ],
                [
                    'sku' => 'SKU-FRT-STR04',
                    'name' => 'Stroberi Dataran Tinggi',
                    'grade' => 'Specialty Fresh',
                    'gradeTone' => 'accent',
                    'image' => '[IMG: HIGHLAND_STRAWBERRY_CRATE]',
                    'caption' => 'Cushioned basket packing',
                    'body' => "Warna merah merata, tekstur kokoh, rasa manis-asam\nseimbang. Dikemas dalam keranjang ventilasi berbusa\npelindung benturan.",
                    'minOrder' => '5 Kg',
                    'status' => 'Kuota Terbatas (Buffer Only)',
                    'statusTone' => 'warning',
                    'price' => 'Rp 55.000',
                ],
                [
                    'sku' => 'SKU-VEG-CLL05',
                    'name' => 'Kol Putih / Cabbage Segar',
                    'grade' => 'Grade Standar B2B',
                    'gradeTone' => 'ink',
                    'image' => '[IMG: CABBAGE_BULK_CRATE]',
                    'caption' => 'Solid heart / peel-ready',
                    'body' => "Krop padat, bobot berat per butir merata, daun luar bersih.\nPilihan utama katering skala besar, industri makanan olahan,\ndan hotel.",
                    'minOrder' => '20 Kg',
                    'status' => 'Stok Melimpah (Pusat Hub)',
                    'statusTone' => 'success',
                    'price' => 'Rp 9.500',
                ],
            ],
            'banner' => [
                'tag' => '[CUSTOM B2B COMMODITY]',
                'title' => "Permintaan Khusus atau Tonase\nBesar?",
                'body' => "GPA melayani pengadaan sayuran daun, bumbu basah,\numbi-umbian, dan buah musiman lainnya dengan spesifikasi\nkontrak pasokan jangka panjang.",
                'points' => [
                    'Kontrak harga flat per kuartal',
                    'Standar sertifikasi residu pestisida',
                    'Jadwal kirim subuh terdedikasi',
                ],
                'action' => 'Hubungi Desk Pengadaan',
            ],
        ];
    }

    public static function governance(): array
    {
        return [
            'eyebrow' => 'PROTOKOL AKUNTABILITAS B2B',
            'icon' => 'shield',
            'headline' => 'Keunggulan Tata Kelola & Akuntabilitas Anti-Selisih',
            'lead' => 'Tiga pilar fundamental dalam sistem operasi GPA yang menjamin transparansi fisik dan administrasi keuangan bagi mitra bisnis.',
            'cards' => [
                [
                    'icon' => 'scale',
                    'title' => 'Actual Weight as Single Truth',
                    'body' => "Penimbangan di gudang konsolidasi menggunakan\ntimbangan tera resmi yang terkalibrasi berkala oleh Balai\nMetrologi. Angka timbangan tercatat langsung tanpa\nrekayasa manual.",
                    'bullets' => [
                        'Tera sah Kementerian Perdagangan RI',
                        'Integrasi IoT Bluetooth logger otomatis',
                    ],
                    'badge' => 'Zero Phantom Weight Guarantee',
                ],
                [
                    'icon' => 'check-circle',
                    'title' => 'Integrated Proof of Delivery',
                    'body' => "Setiap serah terima di pintu kitchen mitra diverifikasi via\naplikasi driver mobile: dokumentasi foto komoditas, foto\nnota timbang fisik, dan tanda tangan digital receiving staff.",
                    'bullets' => [
                        'Validasi ganda saat serah terima',
                        'e-PoD tersimpan abadi di cloud archive',
                    ],
                    'badge' => '100% Digital Verifiable',
                ],
                [
                    'icon' => 'banknote',
                    'title' => 'Flexible B2B Settlement',
                    'body' => "Dukungan termin pembayaran Term of Payment (TOP 14\nhingga 30 hari) khusus untuk mitra korporat resmi dengan\ninvoice audit-ready dan faktur pajak PPN standar.",
                    'bullets' => [
                        'Faktur pajak elektronik PKP terdaftar',
                        'Rekonsiliasi tagihan otomatis tanpa selisih',
                    ],
                    'badge' => 'Corporate Invoicing Ready',
                ],
            ],
        ];
    }

    public static function partners(): array
    {
        return [
            'eyebrow' => 'PORTFOLIO KONTRAK AKTIF // B2B ENTERPRISE',
            'icon' => 'badge-check',
            'headline' => 'Mitra & Klien Kontrak Terverifikasi',
            'lead' => 'Entitas komersial terkemuka yang mempercayakan kontinuitas pasokan segar harian, stabilitas harga kuartalan, dan jaminan berat riil 100%.',
            'sla' => [
                ['label' => 'SLA Kepatuhan Pasokan:', 'value' => '99.4%', 'accent' => true],
                ['label' => 'Model Pembayaran:', 'value' => 'TOP 14 - 30 Hari', 'accent' => false],
            ],
            'cards' => [
                [
                    'contract' => 'B2B-1082',
                    'exp' => 'Exp: Des 2026',
                    'initials' => 'HGP',
                    'name' => 'Hotel Grand Pangrango Bogor',
                    'body' => "Pasokan harian sayuran segar untuk operasional banquet\ndan main restaurant dengan spesifikasi sortasi estetik ketat.",
                    'details' => [
                        ['label' => 'Komoditas:', 'value' => 'Selada & Brokoli', 'tone' => 'default'],
                        ['label' => 'Volume Pasokan:', 'value' => '350 kg / kirim (3x/mgg)', 'tone' => 'default'],
                        ['label' => 'Term Finansial:', 'value' => 'TOP 14 Hari', 'tone' => 'success'],
                    ],
                    'slaValue' => '99.8%',
                    'contractTone' => 'light',
                ],
                [
                    'contract' => 'B2B-1094',
                    'exp' => 'Exp: Mar 2027',
                    'initials' => 'RA',
                    'name' => 'Royal Ambarrukmo Resort',
                    'body' => "Pengadaan komoditas segar kelas fine dining berstandar\nhigienitas sertifikasi internasional dan toleransi sortasi\nminimal.",
                    'details' => [
                        ['label' => 'Komoditas:', 'value' => 'Selada, Berry, Tomat', 'tone' => 'default'],
                        ['label' => 'Volume Pasokan:', 'value' => '500 kg / kirim (Subuh)', 'tone' => 'default'],
                        ['label' => 'Term Finansial:', 'value' => 'TOP 30 Hari', 'tone' => 'success'],
                    ],
                    'slaValue' => '99.5%',
                    'contractTone' => 'light',
                ],
                [
                    'contract' => 'B2B-0821',
                    'exp' => 'Exp: Nov 2026',
                    'initials' => 'BMC',
                    'name' => 'PT Boga Mandiri Cipta (Cikarang)',
                    'body' => "Penyedia makanan industrial terintegrasi untuk ribuan\npekerja pabrik dengan kebutuhan tonase harian stabil.",
                    'details' => [
                        ['label' => 'Komoditas:', 'value' => 'Kol, Tomat, Brokoli', 'tone' => 'default'],
                        ['label' => 'Volume Pasokan:', 'value' => '1.200 kg / kirim (CDD)', 'tone' => 'default'],
                        ['label' => 'Term Finansial:', 'value' => 'TOP 30 Hari', 'tone' => 'success'],
                    ],
                    'slaValue' => '99.7%',
                    'contractTone' => 'ink',
                ],
                [
                    'contract' => 'B2B-2019',
                    'exp' => 'Exp: Jan 2027',
                    'initials' => 'NIC',
                    'name' => 'Nusantara Inflight & Catering',
                    'body' => "Pengadaan komoditas segar dengan jadwal ketat bandara\nSoekarno-Hatta (CGK), inspeksi pestisida ketat dan\nsertifikasi HACCP.",
                    'details' => [
                        ['label' => 'Komoditas:', 'value' => 'Selada Romaine, Berry', 'tone' => 'default'],
                        ['label' => 'Volume Pasokan:', 'value' => '400 kg / kirim (Harian)', 'tone' => 'default'],
                        ['label' => 'Term Finansial:', 'value' => 'TOP 30 Hari', 'tone' => 'success'],
                    ],
                    'slaValue' => '99.9%',
                    'contractTone' => 'light',
                ],
                [
                    'contract' => 'B2B-1140',
                    'exp' => 'Exp: Agu 2026',
                    'initials' => 'SMR',
                    'name' => 'PT Segar Makmur Ritelindo',
                    'body' => "Jaringan 18 gerai ritel modern di area Jabodetabek dengan\nkebutuhan sayuran pre-washed dan pre-packed siap display\nrak dingin.",
                    'details' => [
                        ['label' => 'Komoditas:', 'value' => '5 Komoditas Inti GPA', 'tone' => 'default'],
                        ['label' => 'Volume Pasokan:', 'value' => '850 kg / kirim (Subuh)', 'tone' => 'default'],
                        ['label' => 'Term Finansial:', 'value' => 'TOP 21 Hari', 'tone' => 'success'],
                    ],
                    'slaValue' => '99.6%',
                    'contractTone' => 'ink',
                ],
                [
                    'contract' => 'B2B-1205',
                    'exp' => 'Exp: Okt 2026',
                    'initials' => 'AFD',
                    'name' => 'Agrimart Fresh Direct',
                    'body' => "Platform e-grocery B2B2C dengan model pengiriman\nterjadwal dan integrasi pelacakan batch panen transparan\nuntuk konsumen premium.",
                    'details' => [
                        ['label' => 'Komoditas:', 'value' => 'Stroberi & Romaine', 'tone' => 'default'],
                        ['label' => 'Volume Pasokan:', 'value' => '250 kg / kirim (Harian)', 'tone' => 'default'],
                        ['label' => 'Term Finansial:', 'value' => 'TOP 14 Hari', 'tone' => 'success'],
                    ],
                    'slaValue' => '99.4%',
                    'contractTone' => 'light',
                ],
            ],
            'cta' => [
                'title' => 'Ingin Mengajukan Kontrak Pengadaan Komoditas Skala Tonase?',
                'body' => 'Dapatkan penawaran harga kuartalan tetap, audit sampel gratis, dan batas kredit TOP sesuai profil usaha Anda.',
                'action' => 'Ajukan Pengadaan Kontrak →',
            ],
        ];
    }

    public static function sectors(): array
    {
        return [
            'eyebrow' => 'SPESIFIKASI SEKTOR INDUSTRI',
            'icon' => 'grid',
            'headline' => 'Solusi Pengadaan Berdasarkan Sektor Industri',
            'lead' => 'Disesuaikan dengan tantangan unik masing-masing sektor: volume tinggi pabrikan, estetika ketat hotel bintang lima, hingga masa simpan rak ritel.',
            'cards' => [
                [
                    'code' => 'SEKTOR 01',
                    'icon' => 'clipboard',
                    'title' => 'Katering & Restoran Skala Besar',
                    'body' => "Membutuhkan kepastian kuantitas ratusan kilogram harian\ndengan ukuran dan kaliber potongan yang seragam agar\nproses prep dapur massal berjalan efisien tanpa waktu\nterbuang.",
                    'items' => [
                        'Standar bobot per krop terkalibrasi',
                        'Pengiriman dini hari sebelum prep subuh',
                        'Penyesuaian volume saat event khusus',
                    ],
                    'focus' => 'Fokus: Kapasitas Tonase & Efisiensi Prep',
                ],
                [
                    'code' => 'SEKTOR 02',
                    'icon' => 'building',
                    'title' => 'Hotel & Hospitalitas B2B',
                    'body' => "Menuntut kesegaran visual prima dan higienitas bersertifikat\nuntuk sajian fine dining serta buffet bintang empat dan lima\ndengan toleransi cacat daun minimal.",
                    'items' => [
                        'Sortasi estetik Grade A tanpa cacat',
                        'Pre-cooling dingin menjaga kerenyahan daun',
                        'Toleransi klaim penggantian langsung < 2 jam',
                    ],
                    'focus' => 'Fokus: Kualitas Visual & Mutu Premium',
                ],
                [
                    'code' => 'SEKTOR 03',
                    'icon' => 'cart',
                    'title' => 'Jaringan Pasar Modern / Supermarket',
                    'body' => "Membutuhkan komoditas dengan masa simpan rak (shelf-\nlife) optimal, kemasan krat higienis berstempel, barcode SKU\nyang sinkron, dan kontinuitas pengiriman harian.",
                    'items' => [
                        'Kemasan boks berlabel barcode SKU riil',
                        'Penanganan rantai dingin memperpanjang shelf-life',
                        'Format faktur pajak siap integrasi ERP ritel',
                    ],
                    'focus' => 'Fokus: Shelf-Life & Standardisasi Kemasan',
                ],
            ],
        ];
    }

    public static function gallery(): array
    {
        return [
            'eyebrow' => 'OPERATIONAL ARCHIVE // EVIDENCE & VERIFICATION',
            'icon' => 'camera',
            'headline' => 'Galeri & Dokumentasi Operasional GPA',
            'lead' => 'Arsip visual proses rantai pasok harian dari kebun petani binaan, stasiun cold storage transit, tera metrologi, hingga serah terima di pintu kitchen mitra enterprise.',
            'chips' => [
                ['label' => 'Audit Log 2025', 'tone' => 'light'],
                ['label' => '100% Terverifikasi', 'tone' => 'ink'],
            ],
            'tabs' => [
                ['key' => 'semua', 'label' => 'Semua Dokumentasi'],
                ['key' => 'petani', 'label' => 'Petani & Panen'],
                ['key' => 'fasilitas', 'label' => 'Fasilitas & Gudang'],
                ['key' => 'mutu', 'label' => 'Uji Mutu & Timbangan'],
                ['key' => 'logistik', 'label' => 'Logistik & Distribusi'],
            ],
            'cards' => [
                [
                    'category' => 'petani',
                    'categoryLabel' => 'PETANI & PANEN',
                    'categoryTone' => 'ink',
                    'location' => 'Cisarua, Bogor',
                    'image' => '[IMG: DOK_KEBUN_CISARUA_01]',
                    'caption' => 'Sistem Talang DFT & Pencahayaan Alami',
                    'title' => 'Kebun Hidroponik Selada Romaine Cisarua',
                    'body' => 'Panen harian 250 kg daun selada romaine bersih tanpa residu pestisida.',
                    'meta' => ['label' => 'Batch: ', 'value' => 'GPA-CSR-2025'],
                    'footer' => 'Audit: Lolos Uji Lab',
                ],
                [
                    'category' => 'fasilitas',
                    'categoryLabel' => 'FASILITAS & GUDANG',
                    'categoryTone' => 'success',
                    'location' => 'Ciracas, Jaktim',
                    'image' => '[IMG: DOK_COLD_STORAGE_CRC_04]',
                    'caption' => 'Kamar Suhu 6°C - 10°C / Racking Boks',
                    'title' => 'Gudang Transit Cold Storage Ciracas',
                    'body' => 'Fasilitas pra-pendinginan dengan racking krat HDPE standar pangan steril.',
                    'meta' => ['label' => 'Kapasitas Buffer: ', 'value' => '12 Ton'],
                    'footer' => 'Food Grade Chamber',
                ],
                [
                    'category' => 'mutu',
                    'categoryLabel' => 'UJI MUTU & TIMBANGAN',
                    'categoryTone' => 'ink',
                    'location' => 'Metrologi Legal',
                    'image' => '[IMG: DOK_KALIBRASI_TERA_06]',
                    'caption' => 'Stempel Segel Tera Sah Dinas Metrologi',
                    'title' => 'Kalibrasi Tera Sah Digital Loadcell',
                    'body' => 'Pemeriksaan berkala memastikan presisi loadcell tanpa deviasi timbangan.',
                    'meta' => ['label' => 'Stempel Tera: ', 'value' => 'Berlaku s/d 2026'],
                    'footer' => 'Deviasi: 0.00%',
                ],
                [
                    'category' => 'logistik',
                    'categoryLabel' => 'LOGISTIK & DISTRIBUSI',
                    'categoryTone' => 'ink',
                    'location' => 'Armada Subuh',
                    'image' => '[IMG: DOK_ARMADA_REEFER_08]',
                    'caption' => 'Armada Box Terisolasi Termal GSM',
                    'title' => 'Pemberangkatan Reefer Transit Dini Hari',
                    'body' => 'Rute subuh memastikan sayuran tidak terpapar panas matahari siang.',
                    'meta' => ['label' => 'Monitoring Suhu: ', 'value' => '+4°C s.d +7°C'],
                    'footer' => 'GPS Telemetri On',
                ],
                [
                    'category' => 'petani',
                    'categoryLabel' => 'PETANI & PANEN',
                    'categoryTone' => 'success',
                    'location' => 'Pacet, Cianjur',
                    'image' => '[IMG: DOK_POKTAN_PACET_03]',
                    'caption' => 'Pemetikan Selektif Kematangan 85%',
                    'title' => 'Sortasi Lapangan Tomat Beef Pacet',
                    'body' => 'Sortasi awal langsung di bedeng perkebunan sebelum dibawa ke packhouse.',
                    'meta' => ['label' => 'Kelompok Tani: ', 'value' => 'Mitra Mandiri 04'],
                    'footer' => 'SOP GAP Terdaftar',
                ],
                [
                    'category' => 'logistik',
                    'categoryLabel' => 'LOGISTIK & DISTRIBUSI',
                    'categoryTone' => 'ink',
                    'location' => 'Receiving Dock',
                    'image' => '[IMG: DOK_EPOD_RECEIVING_11]',
                    'caption' => 'Penimbangan Ulang & Tanda Tangan e-PoD',
                    'title' => 'Serah Terima e-PoD Hotel Grand Pangrango',
                    'body' => 'Verifikasi bobot riil disaksikan receiving officer dan stempel resmi digital.',
                    'meta' => ['label' => 'Waktu Serah: ', 'value' => '04:18 WIB'],
                    'footer' => 'Status: Verified Clean',
                ],
                [
                    'category' => 'mutu',
                    'categoryLabel' => 'UJI MUTU & TIMBANGAN',
                    'categoryTone' => 'success',
                    'location' => 'Lab Uji Agro',
                    'image' => '[IMG: DOK_UJI_RESIDU_LAB_15]',
                    'caption' => 'Spektrofotometri Cepat Residu Pestisida',
                    'title' => 'Uji Laboratorium Bebas Residu Kimia',
                    'body' => 'Pemeriksaan berkala sampel acak sayuran memenuhi standar higienitas BPOM.',
                    'meta' => ['label' => 'Hasil Uji: ', 'value' => 'ND (Not Detected)'],
                    'footer' => 'Ambang Batas Aman',
                ],
                [
                    'category' => 'fasilitas',
                    'categoryLabel' => 'FASILITAS & GUDANG',
                    'categoryTone' => 'ink',
                    'location' => 'Packhouse Ciawi',
                    'image' => '[IMG: DOK_PACKHOUSE_CW_19]',
                    'caption' => 'Meja Stainless Steel Food Grade 304',
                    'title' => 'Area Trimming & Grading Bersih Ciawi',
                    'body' => 'Pembersihan daun kotor, perapihan tangkai, dan penempatan ke krat berlabel.',
                    'meta' => ['label' => 'Kapasitas: ', 'value' => '4.5 Ton / Hari'],
                    'footer' => 'Sterilisasi UV Aktif',
                ],
                [
                    'category' => 'petani',
                    'categoryLabel' => 'PETANI & PANEN',
                    'categoryTone' => 'success',
                    'location' => 'Pangalengan, Bdg',
                    'image' => '[IMG: DOK_KEBUN_BROKOLI_22]',
                    'caption' => 'Ketinggian 1.420 mdpl / Kabut Pagi',
                    'title' => 'Panen Brokoli Kuntum Padat Pangalengan',
                    'body' => 'Pemotongan tangkai seragam dan pengepakan langsung ke keranjang berbusa.',
                    'meta' => ['label' => 'Grade Hasil: ', 'value' => 'Premium 95%'],
                    'footer' => 'Sertifikasi Organik Parsial',
                ],
            ],
            'cta' => [
                'title' => 'Standar SOP & Sertifikasi',
                'body' => 'Seluruh aktivitas operasional GPA tunduk pada sertifikasi Good Agricultural Practices (GAP), sertifikasi metrologi legal, dan kepatuhan faktur pajak resmi.',
                'action' => 'Unduh Ringkasan Kepatuhan →',
            ],
        ];
    }

    public static function footer(): array
    {
        return [
            'company' => 'PT Green Pasundan Agriculture',
            'tagline' => 'Mitra rantai pasok hortikultura terpercaya untuk sektor Horeka, Katering Korporat, dan Ritel Modern. Mengedepankan kepastian pasokan harian, kejujuran bobot metrologis riil, dan ketepatan waktu pengiriman subuh.',
            'identity' => [
                'Badan Usaha: PT Green Pasundan Agriculture',
                'NIB OSS-RBA: 1234567890 • KBLI 12345',
                'Status Pajak: Pengusaha Kena Pajak (PKP Terdaftar)',
            ],
            'hubs' => [
                ['label' => 'Central Packhouse Panundaan', 'address' => 'Kp. Batukasur, Panundaan, Kec. Ciwidey, Kabupaten Bandung, Jawa Barat 40973'],
                ['label' => 'Hub Distribusi Panundaan', 'address' => 'Kp. Batukasur, Panundaan, Kec. Ciwidey, Kabupaten Bandung, Jawa Barat 40973'],
            ],
            'contacts' => [
                'Call Center: (062) 8123-4567',
                'WhatsApp Dispatch: +62 812-3456-789',
            ],
            'navigation' => [
                ['label' => 'Beranda Utama', 'href' => '/beranda'],
                ['label' => 'Tentang GPA', 'href' => '/tentang-gpa'],
                ['label' => 'Komoditas', 'href' => 'public.catalog'],
                ['label' => 'Rantai Pasok Terintegrasi', 'href' => 'public.supply-chain'],
                ['label' => 'Portofolio Kemitraan', 'href' => '/mitra-kontrak'],
                ['label' => 'Sertifikat Tera Metrologi', 'href' => '/tentang-gpa#legalitas'],
                ['label' => 'Panduan B2B Onboarding', 'href' => '/tentang-gpa#cta'],
                ['label' => 'Syarat & Ketentuan Pasokan', 'href' => '/tentang-gpa#rantai-pasok'],
            ],
            'copyright' => '© 2026 PT Green Pasundan Agriculture. Hak Cipta Dilindungi Undang-Undang. AgroOrder GPA System v2.4.',
            'status' => 'Semua Server Packhouse Operasional',
            'policies' => ['Kebijakan Privasi', 'Integritas Metrologi'],
        ];
    }
}
