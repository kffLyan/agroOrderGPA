<?php

return [

    'form' => [
        'code' => 'REG-GPA-A',
        'revision' => '2025.Q2',
        'title' => 'Formulir Pendaftaran Entitas Klien Baru',
        'subtitle' => 'Sistem Penerimaan Pasokan Komoditas Pertanian Segar Jawa Barat (Gudang Induk GPA Ciwidey, Lembang, Subang)',
        'status' => 'DRAFT TERBUKA',
    ],

    'modes' => [
        'enterprise' => [
            'code' => 'B2B',
            'name' => 'Klien Kontrak B2B (Enterprise)',
            'description' => '4 Tahap Verifikasi Dokumen Legalitas (NIB, NPWP, Akta), Kuota Panen Terkunci, Fasilitas TOP 14-30 Hari. Untuk pabrik, hotel, dan katering skala volume di atas 1 Ton per minggu.',
            'sla' => 'SLA VALIDASI: 2-3 HARI KERJA',
            'cta' => 'Pindah ke Mode Enterprise',
        ],
        'reguler' => [
            'code' => 'REG',
            'name' => 'Klien Reguler / Non-Kontrak (Instan)',
            'description' => 'Aktivasi Instan via WhatsApp OTP, Tanpa Syarat NIB/NPWP, Akses Stok Harian Bebas (ATP), Bayar Tunai/Transfer/QRIS H+0. Cocok untuk kafe, resto cepat saji, dan cloud kitchen.',
            'sla' => 'SLA VALIDASI: INSTAN (<= 2 MENIT)',
            'badge' => 'MODE AKTIF: 1 TAHAP CEPAT',
        ],
    ],

    'zones' => [
        'BDG_RAYA' => [
            'code' => 'ZONA-02',
            'label' => 'Bandung Raya & Cimahi',
            'hub' => 'Hub Dago',
            'cutoff' => 'Cutoff pemesanan 20:00 WIB',
        ],
        'JABODETABEK' => [
            'code' => 'ZONA-01',
            'label' => 'Jabodetabek Cold-Hub',
            'hub' => 'Hub Cikarang',
            'cutoff' => 'Cutoff pemesanan 19:00 WIB',
        ],
        'SKB_CJR' => [
            'code' => 'ZONA-03',
            'label' => 'Sukabumi & Cianjur',
            'hub' => 'Hub Cianjur',
            'cutoff' => 'Cutoff pemesanan 18:00 WIB',
        ],
    ],

    'windows' => [
        'PRIORITAS_1' => [
            'label' => '03:30 - 05:30 WIB',
            'note' => 'Prioritas Resto',
        ],
        'PRIORITAS_2' => [
            'label' => '05:30 - 07:30 WIB',
            'note' => 'Shift Pagi Standar',
        ],
        'AFTERNOON' => [
            'label' => '14:00 - 16:00 WIB',
            'note' => 'Replenish Sore',
        ],
    ],

    'vehicles' => [
        'VAN' => [
            'label' => 'Mobil Blindvan / Pickup Box Kecil',
            'capacity' => 'Maks. 1.000 kg',
        ],
        'CDE' => [
            'label' => 'Truk Engkel CDE (4 Roda Box)',
            'capacity' => 'Maks. 3.000 kg',
        ],
        'CDD' => [
            'label' => 'Truk Double CDD (6 Roda Box)',
            'capacity' => 'Maks. 8.000 kg',
        ],
        'MOTOR' => [
            'label' => 'Khusus Roda Dua / Gang Sempit',
            'capacity' => 'Maks. 80 kg',
        ],
    ],

    'commodities' => [
        'selada_romaine' => [
            'name' => 'Selada Romaine / Lettuce',
            'moq' => 10,
            'unit' => 'kg',
            'origin' => 'Lembang Cold-Hub',
            'grade' => 'Toleransi susut basah: +/-2%',
        ],
        'tomat_beef' => [
            'name' => 'Tomat Beef Dataran Tinggi',
            'moq' => 25,
            'unit' => 'kg',
            'origin' => 'Ciwidey Pack-house',
            'grade' => 'Grade A Super (diameter 7-9 cm)',
        ],
        'brokoli_super' => [
            'name' => 'Brokoli Super Highland',
            'moq' => 15,
            'unit' => 'kg',
            'origin' => 'Lembang / Cibodas',
            'grade' => 'Crown fresh cut, vacuum shrink ready',
        ],
        'stroberi_ciwidey' => [
            'name' => 'Stroberi Ciwidey Segar',
            'moq' => 5,
            'unit' => 'kg',
            'origin' => 'Ciwidey South Slope',
            'grade' => 'Kemasan keranjang berventilasi 500 g',
        ],
        'kol_segar' => [
            'name' => 'Kol Segar Lembang',
            'moq' => 50,
            'unit' => 'kg',
            'origin' => 'Subang / Lembang',
            'grade' => 'Daun terluar dikupas bersih, padat',
        ],
    ],

    'payment_methods' => [
        'transfer' => [
            'name' => 'Transfer Bank Manual (BCA / Mandiri Giro GPA)',
            'badge' => 'DIREKOMENDASIKAN RESTO',
            'description' => 'Upload bukti transfer pasca penimbangan riil gudang melalui bot WhatsApp atau web portal. Verifikasi finance admin <= 15 menit sebelum truk armada diberangkatkan dari Hub.',
            'hint' => 'Nomor rekening resmi diinformasikan pada invoice faktur sementara (Proforma).',
            'settlement' => 'H+0',
        ],
        'qris' => [
            'name' => 'QRIS Statis Resmi AgroOrder GPA',
            'description' => 'Scan kode QRIS resmi GPA langsung dari mobile banking atau e-wallet saat Surat Penyerahan Bahan (SPB) diterbitkan subuh hari. Bukti bayar terhubung instan dengan nomor PO.',
            'hint' => 'QRIS statis berlaku untuk semua bank e-wallet dan m-banking nasional.',
            'settlement' => 'H+0',
        ],
        'cod' => [
            'name' => 'Cash On Delivery (COD Titik Bongkar Dapur)',
            'description' => 'Bayar tunai pas saat armada tiba di dock penerimaan. Titipan uang diserahkan langsung ke supir ekspedisi resmi berseragam GPA bersamaan penandatanganan fisik lembar PoD.',
            'hint' => 'Sediakan uang pas sesuai nilai proforma untuk mempercepat serah terima.',
            'settlement' => 'H+0',
        ],
    ],

    'matrix' => [
        [
            'label' => 'Waktu Aktivasi & Syarat',
            'reguler' => '<= 2 Menit (Verifikasi OTP WhatsApp, Tanpa NIB)',
            'enterprise' => '2-3 Hari Kerja (Audit Legalitas NIB, NPWP, Survey Dapur)',
            'highlight' => true,
        ],
        [
            'label' => 'Alokasi & Kuota Stok',
            'reguler' => 'Stok Harian Terbuka (Available to Promise / ATP)',
            'enterprise' => 'Prioritas Panen Terkunci (Dedicated Block Kebun Mitra)',
        ],
        [
            'label' => 'Skema Harga Komoditas',
            'reguler' => 'Harga Grosir Dinamis Harian (Spot Price Jabar)',
            'enterprise' => 'Harga Kontrak Stabil Terkunci 30 - 90 Hari (Fixed Index)',
        ],
        [
            'label' => 'Termin Pembayaran',
            'reguler' => 'H+0 (Tunai / Transfer Manual / QRIS)',
            'enterprise' => 'Fasilitas Kredit Tempo (TOP 14 Hari s/d 30 Hari)',
            'highlight' => true,
        ],
        [
            'label' => 'Target Skala Usaha',
            'reguler' => 'Restoran Independen, Kafe, Katering Kecil (10 - 200 kg)',
            'enterprise' => 'Chain F&B Nasional, Pabrik Pangan, Hotel Bintang (> 1 Ton)',
        ],
    ],
];
