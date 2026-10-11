<?php

namespace App\Support;

final class PublicAboutData
{
    public static function hero(): array
    {
        return [
            'eyebrow' => 'Profil Korporat & Infrastruktur Rantai Pasok Agribisnis',
            'headline' => [
                ['text' => 'Membangun', 'tone' => 'light'],
                ['text' => "Kepastian\nPasokan", 'tone' => 'accent', 'underline' => true],
                ['text' => "& Transparansi\nRadikal Agribisnis Indonesia", 'tone' => 'light'],
            ],
            'lead' => 'Latar belakang pendirian PT Agro Pasti Ada (AgroOrder GPA) sebagai solusi atas masalah struktural rantai pasok agribisnis: fluktuasi pasokan liar, susut timbangan diam-diam (phantom weight), dan mutasi kualitas komoditas segar di sektor HORECA (Hotel, Restaurant, Catering) dan ritel modern.',
            'emphasis' => 'phantom weight',
            'assurances' => [
                'Zero-Phantom Weight Standard',
                'Calibrated Legal Metrology Tera (UU No. 2/1981)',
                'Dawn-Run Subuh Logistics 03:00 - 05:30 WIB',
            ],
            'actions' => [
                ['label' => 'Jelajahi 4 Pilar Operasional', 'href' => '#tentang-gpa', 'variant' => 'accent'],
                ['label' => 'Lihat Spesifikasi Mutu', 'href' => '#komoditas', 'variant' => 'outline'],
            ],
            'pipeline' => self::pipeline(),
        ];
    }

    public static function pipeline(): array
    {
        return [
            'revision' => 'REV 2025.1',
            'stages' => [
                [
                    'step' => '01',
                    'title' => '14 Kelompok Tani Highland',
                    'location' => 'Cianjur, Lembang, Pangalengan',
                    'metric' => '1,400 mdpl',
                ],
                [
                    'step' => '02',
                    'title' => 'Central Hub & Tera Lab',
                    'location' => '45 Ton Cold Buffer • 0° - 4°C',
                    'metric' => 'IoT Tera',
                ],
                [
                    'step' => '03',
                    'title' => 'Dawn-Run Reefer Fleet',
                    'location' => '03:00 - 05:30 WIB Line-Haul',
                    'metric' => 'Reefer Truck',
                ],
                [
                    'step' => '04',
                    'title' => 'HORECA Kitchen Dock',
                    'location' => 'Digital e-PoD Instant Audit',
                    'metric' => 'Zero-Shrink',
                ],
            ],
            'audit' => 'AUDIT STATUS: FULLY ENCRYPTED',
            'claim' => '100% TRACEABLE',
        ];
    }

    public static function stats(): array
    {
        return [
            ['label' => 'Commodity Focus', 'value' => '5 Komoditas', 'note' => 'Inti Standar Horeca Teruji'],
            ['label' => 'Phantom Weight', 'value' => '0% Toleransi', 'note' => 'Selisih Timbang Fiktif'],
            ['label' => 'Volume Capacity', 'value' => '482+ Ton', 'note' => 'Realisasi Pasokan YTD 2025'],
            ['label' => 'Punctuality SLA', 'value' => '99.4% On-Time', 'note' => 'SLA Dapur Komersial Subuh'],
        ];
    }

    public static function pillars(): array
    {
        return [
            [
                'step' => '01',
                'tag' => 'Hulu Produksi',
                'title' => 'Kemitraan 14 Kelompok Tani Binaan Dataran Tinggi',
                'body' => 'Ekosistem binaan di Cianjur, Lembang, dan Pangalengan dengan kontrak off-take jaminan harga dasar, pendampingan Good Agricultural Practices (GAP), dan audit residu pestisida berkala sebelum masa petik.',
                'meta' => [
                    ['label' => 'Lokasi', 'value' => 'Lembang - Bandung'],
                    ['label' => 'Kontrak', 'value' => 'Guaranteed Base Price'],
                ],
            ],
            [
                'step' => '02',
                'tag' => 'Cold Chain',
                'title' => 'Buffer Stock Mandiri & Kontrol Termal Cold-Chain',
                'body' => 'Kapasitas 45 Ton di Sentral Hub Utama dengan regulasi suhu presisi 0°C hingga 4°C. Menjaga berat jenis alami selada dan sayur daun agar tidak layu atau mengalami dehidrasi jaringan tanaman.',
                'meta' => [
                    ['label' => 'Kapasitas Hub', 'value' => '45.000 KG'],
                    ['label' => 'Suhu Preserve', 'value' => '0.0°C - 4.0°C'],
                ],
            ],
            [
                'step' => '03',
                'tag' => 'Metrologi Tera',
                'title' => 'Stasiun Timbang Tera Sah Metrologi Legal',
                'body' => 'Penimbangan otomatis terintegrasi IoT tanpa interferensi manual. Timbangan dikalibrasi berkala sesuai standar legal metrologi dengan pencetakan barcode tiket timbang riil yang terikat pada batch pesanan.',
                'meta' => [
                    ['label' => 'Sertifikasi', 'value' => 'Tera UU No. 2/1981'],
                    ['label' => 'Input Type', 'value' => 'Direct Sensor IoT'],
                ],
            ],
            [
                'step' => '04',
                'tag' => 'Last-Mile Subuh',
                'title' => 'Armada Reefer Berpendingin & E-PoD Terenkripsi',
                'body' => 'Jadwal pengiriman subuh 03:00 - 05:30 WIB tepat sebelum shift operasional executive chef dimulai. Dilengkapi validasi e-Proof of Delivery terenkripsi dengan bukti foto dan stempel basah digital.',
                'meta' => [
                    ['label' => 'Window SLA', 'value' => '03:00 - 05:30 WIB'],
                    ['label' => 'Validasi', 'value' => 'Digital Signed e-PoD'],
                ],
            ],
        ];
    }

    public static function commodities(): array
    {
        return [
            'standard' => 'GPA-SPEC-HORECA-2025',
            'columns' => [
                ['key' => 'commodity', 'label' => 'Kode & Nama Komoditas'],
                ['key' => 'grade', 'label' => 'Standar Grade Mutu Fisik'],
                ['key' => 'weighing', 'label' => 'Metode Timbang Riil (Netto Sah)'],
                ['key' => 'storage', 'label' => 'Kontrol Cold Storage'],
                ['key' => 'certification', 'label' => 'Sertifikasi & Kepatuhan'],
            ],
            'rows' => [
                [
                    'commodity' => ['sku' => 'SKU-GPA-ROM-01', 'name' => 'Selada Romaine / Lettuce Grade A', 'origin' => 'Hidroponik & Dataran Tinggi Lembang'],
                    'grade' => 'Daun renyah utuh, warna hijau terang, tinggi 22-26cm, bebas bercak kuning (tipburn < 1%), akar bersih potong rapi.',
                    'weighing' => 'Stasiun Tera 0.05g tare box keranjang, netto tercetak barcode e-Ticket.',
                    'storage' => ['range' => '1.5°C - 3.5°C', 'note' => 'RH 90-95%'],
                    'certification' => ['Residu < MRL', 'GAP Certified'],
                ],
                [
                    'commodity' => ['sku' => 'SKU-GPA-TMT-02', 'name' => 'Tomat Beef / Sayur Super', 'origin' => 'Dataran Tinggi Cianjur Gede-Pangrango'],
                    'grade' => 'Diameter 75-90mm, tingkat kematangan 80-85% (firm skin), bebas luka mekanis, kaliks utuh berwarna hijau segar.',
                    'weighing' => 'Sortir timbang per koli 10 kg, zero-loss shrinkage guarantee.',
                    'storage' => ['range' => '8.0°C - 10.0°C', 'note' => 'Anti-chilling injury'],
                    'certification' => ['Uji Lab Nitrat < 0.02', 'Halal MUI'],
                ],
                [
                    'commodity' => ['sku' => 'SKU-GPA-BRK-03', 'name' => 'Brokoli Highland Grade A', 'origin' => 'Pangalengan Sub-alpine 1.400 mdpl'],
                    'grade' => 'Kuntum rapat padat (tight florets), warna hijau gelap kebiruan, batang pendek potong maksimal 3cm dari dasar kepala.',
                    'weighing' => 'Ice-packed pre-weight deduction; pencatatan netto tanpa memperhitungkan es pendingin.',
                    'storage' => ['range' => '0.5°C - 2.0°C', 'note' => 'Top-iced storage'],
                    'certification' => ['Pestisida Bebas OP/SP', 'NKV Verified'],
                ],
                [
                    'commodity' => ['sku' => 'SKU-GPA-STR-04', 'name' => 'Stroberi Ciwidey Segar', 'origin' => 'Kebun Mitra Ciwidey Patuha'],
                    'grade' => 'Brix index min 9.5°, ukuran seragam (25-30g/buah), kulit mulus 95% merah merata, kelopak hijau utuh tanpa busuk kapang.',
                    'weighing' => 'Kemasan clamshell venting hole ber-seal pengaman, timbangan netto presisi tinggi.',
                    'storage' => ['range' => '1.0°C - 2.5°C', 'note' => 'Rapid forced-air'],
                    'certification' => ['HACCP Packing', 'Organic Compliant'],
                ],
                [
                    'commodity' => ['sku' => 'SKU-GPA-KOL-05', 'name' => 'Kol Segar Lembang / Kubis Putih', 'origin' => 'Sentra Hortikultura Lembang-Parongpong'],
                    'grade' => 'Krop padat bulat sempurna, daun luar terkelupas bersih (maks 2 lapis pelindung transit), bobot 1.2 - 1.8 kg per butir.',
                    'weighing' => 'Timbang curah palletized dock otomatis dengan audit toleransi batas 0.0%.',
                    'storage' => ['range' => '2.0°C - 4.0°C', 'note' => 'Ambient ventilation'],
                    'certification' => ['Standard GAP No. 89', 'Bebas Residu Logam'],
                ],
            ],
            'note' => '* Seluruh timbangan diuji dan disegel oleh Dinas Metrologi Legal Jawa Barat & DKI Jakarta.',
            'guarantee' => 'Toleransi Kerusakan Transit < 0.5% (Refund Otomatis)',
        ];
    }

    public static function legalities(): array
    {
        return [
            'auditRef' => '[PRD APP-GPA.MD SEC 22 AUDIT CHECK]',
            'documents' => [
                [
                    'label' => 'Nomor Induk Berusaha (OSS RBA)',
                    'value' => '0220109983941',
                    'body' => 'Klasifikasi Baku Lapangan Usaha Indonesia (KBLI): Perdagangan Besar Sayuran & Buah Segar, Transportasi Logistik Berpendingin.',
                ],
                [
                    'label' => 'Status Perpajakan Republik Indonesia',
                    'value' => '42.819.330.1-914.000',
                    'body' => 'Wajib Pajak Badan Terdaftar & Pengusaha Kena Pajak (PKP) Resmi. Menerbitkan e-Faktur Pajak resmi pada setiap transaksi instansi.',
                ],
                [
                    'label' => 'Pengesahan Akta Pendirian',
                    'value' => 'AHU-0042918.AH.01.01.2023',
                    'body' => 'Keputusan Menteri Hukum dan Hak Asasi Manusia Republik Indonesia mengenai Pengesahan Badan Hukum Perseroan Terbatas PT Agro Pasti Ada.',
                ],
                [
                    'label' => 'Kalibrasi Tera & Sanitasi',
                    'value' => 'SK-TERA: 510/PKTN/ML/XI/2024',
                    'body' => 'Nomor Kontrol Sanitasi & Kelaikan Fasilitas Cold Chain. Berita Acara Tera Sah Metrologi Legal No. Urut Kalibrasi 2024-JBR-8829.',
                ],
            ],
            'package' => [
                'title' => 'Paket Kelengkapan Legalitas Korporasi (Vendor Onboarding)',
                'body' => 'Berkas digital resmi siap audit mencakup NIB, SK Menkumham, NPWP/SPPKP, Surat Keterangan Domisili Usaha, Sertifikat Tera Metrologi, dan Rekening Escrow Bank BUMN terverifikasi.',
                'checklist' => [
                    'NIB OSS-RBA & KBLI',
                    'Akta Pendirian Terpengesahkan Kemenkumham',
                    'NPWP / SPPKP Bertraktas PKP',
                    'Sertifikat Tera Metrologi Legal',
                    'Rekening Escrow Bank BUMN',
                ],
                'action' => 'Unduh Berkas Profil Korporasi (PDF)',
                'checksum' => 'SHA-256 CHECK: e3b0c44298fc1c149afbf4c8996fb924',
            ],
        ];
    }

    public static function cycle(): array
    {
        return [
            'timelineRef' => '[Timeline: Dawn-Cycle Logistics Protocol]',
            'steps' => [
                [
                    'step' => '01',
                    'time' => '14:00 - 16:00 WIB',
                    'title' => 'Cut-Off Order & Sinkronisasi Panen',
                    'body' => 'Penerimaan PO harian via portal klien GPA. Algoritma menyinkronkan kuota langsung ke 14 kelompok tani untuk instruksi pemetikan sore hari.',
                    'gateway' => 'Gateway: ERP API Automation',
                ],
                [
                    'step' => '02',
                    'time' => '18:00 - 21:00 WIB',
                    'title' => 'Sortir, QC Brix & Timbangan Tera Hub',
                    'body' => 'Muatan panen tiba di Sentral Hub. Pengecekan standar mutu fisik, uji brix acak, serta penimbangan netto legal tersertifikasi secara transparan.',
                    'gateway' => 'Hub: Pre-Cooling 2.0°C',
                ],
                [
                    'step' => '03',
                    'time' => '23:00 - 03:00 WIB',
                    'title' => 'Loading Armada Reefer Line-Haul',
                    'body' => 'Penataan peti aerasi ke dalam armada reefer berpendingin stabil. Rute dioptimalkan GPS untuk menghindari kemacetan dan guncangan muatan.',
                    'gateway' => 'Transit: GPS & Temp Logging',
                ],
                [
                    'step' => '04',
                    'time' => '03:30 - 05:30 WIB',
                    'title' => 'Receiving Dock & Serah Terima e-PoD',
                    'body' => 'Penyerahan komoditas ke receiving team hotel/restoran sebelum subuh selesai. Konfirmasi timbang ulang bersama dan penerbitan bukti serah terima digital.',
                    'gateway' => 'Final: Stamp & Signed e-PoD',
                ],
            ],
        ];
    }

    public static function callToAction(): array
    {
        return [
            'eyebrow' => '// Invitation for Institutional Procurement',
            'headline' => 'Siap Mengeliminasi Selisih Timbang & Kerugian Pasokan di Dapur Anda?',
            'body' => 'Bergabunglah dengan jaringan perhotelan bintang 4-5, jaringan restoran cepat saji, dan katering korporat yang telah beralih ke ekosistem terukur AgroOrder GPA. Dapatkan jaminan harga terkunci dan nol persen susut timbangan.',
            'actions' => [
                ['label' => 'Ajukan Pertemuan & Uji Timbang Sampel', 'href' => 'register', 'variant' => 'accent'],
                ['label' => 'Konsultasi Tim Rantai Pasok GPA', 'href' => 'login', 'variant' => 'outline'],
            ],
            'hotline' => [
                'label' => 'Hotline Pengadaan:',
                'value' => '+62 (21) 8840-9281 (08:00 - 18:00 WIB)',
            ],
        ];
    }
}
