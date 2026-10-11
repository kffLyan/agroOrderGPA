<?php

namespace App\Support;

final class PublicKontakData
{
    public static function hero(): array
    {
        return [
            'badge' => '[ HUBUNGI KAMI // ENTERPRISE SUPPORT & KEMITRAAN ]',
            'headline' => [
                ['text' => 'Kontak & Layanan Pengadaan', 'tone' => 'light'],
                ['text' => 'Agribisnis Terpadu', 'tone' => 'accent'],
            ],
            'lead' => 'Konsultasikan kebutuhan pasokan komoditas segar tonase besar, kontrak suplai B2B, penjadwalan kunjungan audit gudang, atau bantuan teknis integrasi sistem operasional AgroOrder GPA.',
            'sla' => [
                'commitment' => 'RESPONSE COMMITMENT: < 15 MENIT',
                'status' => 'SLA AKTIF',
                'prefix' => 'Tim Procurement & Rantai Pasok GPA siaga melayani',
                'highlight' => 'Senin–Minggu 05:00–22:00 WIB',
                'suffix' => 'untuk penjadwalan dan fleksibilitas rute pengiriman subuh wilayah Jabodetabek & Jawa Barat.',
            ],
        ];
    }

    public static function directory(): array
    {
        return [
            'title' => 'DIREKTORI FASILITAS & HUB',
            'chip' => '[SEC_01: DIRECTORY]',
            'cards' => [
                [
                    'icon' => 'building',
                    'title' => 'Kantor Pusat & Sekretariat GPA',
                    'code' => 'HQ-JKT',
                    'rows' => [
                        ['cells' => [[
                            'label' => 'ALAMAT ADMINISTRATIF:',
                            'lines' => ['Menara Agro Niaga Lt. 8, Jl. TB Simatupang No. 45, Jakarta Selatan', '12530'],
                        ]]],
                        ['divided' => true, 'cells' => [
                            ['label' => 'TELEPON KANTOR:', 'lines' => ['(021) 7829-4091', '(021) 7829-4092']],
                            ['label' => 'JAM OPERASIONAL:', 'lines' => ['Senin – Jumat', '08:00 – 17:00 WIB']],
                        ]],
                        ['divided' => true, 'cells' => [[
                            'label' => 'EMAIL RESMI:',
                            'lines' => ['kemitraan@agropastiada.co.id', 'admin@agroorder.id'],
                        ]]],
                    ],
                ],
                [
                    'icon' => 'truck',
                    'title' => 'Sentral Distribusi & Cold Storage',
                    'code' => 'DC-CIBINONG',
                    'rows' => [
                        ['cells' => [[
                            'label' => 'GUDANG TRANSIT & PACKHOUSE:',
                            'lines' => ['GPA Central Packhouse & Cold-Chain DC, Jl. Raya Mayor Oking No. 118, Cibinong, Kab. Bogor 16918 (Akses Langsung Tol Jagorawi)'],
                        ]]],
                    ],
                    'highlight' => [
                        'label' => 'HOTLINE DISPATCHER SUBUH (24 JAM):',
                        'badge' => 'ACTIVE 24/7',
                        'value' => '+62 811-9284-019',
                        'note' => 'Kapasitas Fasilitas: 85 Ton Chiller & Pre-Cooling Unit',
                    ],
                ],
                [
                    'icon' => 'map-pin',
                    'title' => 'Hub Pengumpulan Pasokan Kebun',
                    'code' => 'FIELD-HUBS',
                    'rows' => [
                        ['accent' => 'left', 'stack' => true, 'cells' => [
                            ['label' => 'HUB SUBANG (DATARAN RENDAH - MENENGAH):', 'lines' => ['Jl. Raya Ciater No. 22, Subang, Jawa Barat']],
                            ['label' => 'HUB LEMBANG & CIANJUR (HIGHLAND VEGETABLES):', 'lines' => ['Jl. Tangkuban Perahu KM 4, Lembang & Pacet, Cianjur']],
                        ]],
                    ],
                    'note' => [
                        'label' => 'PIC KOORDINATOR LAPANGAN:',
                        'value' => 'Pak Rahmat Hidayat & Tim Agronomis Regional Jawa Barat',
                    ],
                ],
            ],
            'channels' => [
                'icon' => 'mail',
                'title' => 'JALUR KOMUNIKASI BERDASARKAN KEBUTUHAN',
                'rows' => [
                    ['title' => 'Kontrak Baru & Legal Suplai', 'subtitle' => 'Bagian Pemasaran & Legal', 'value' => 'pemasaran@agropastiada.co.id'],
                    ['title' => 'Layanan Darurat Kiriman Subuh', 'subtitle' => 'Dispatch Logistik Tol Jagorawi', 'value' => 'dispatch@agroorder.id'],
                    ['title' => 'Penagihan Faktur & Finansial', 'subtitle' => 'Sekretariat Keuangan', 'value' => 'keuangan@agroorder.id'],
                ],
            ],
        ];
    }

    public static function form(): array
    {
        return [
            'docId' => 'DOC ID: REQ-FORM-2025',
            'sla' => 'RESPONSE SLA: 2 HOURS',
            'title' => 'Formulir Kemitraan & Penjajakan Suplai Komoditas',
            'lead' => 'Isi informasi kebutuhan instansi/perusahaan Anda. Tim representatif GPA akan menghubungi dalam waktu maksimal 2 jam kerja.',
            'rows' => [
                ['fields' => [[
                    'name' => 'pic',
                    'label' => '1. NAMA LENGKAP PIC & JABATAN:',
                    'required' => true,
                    'type' => 'text',
                    'placeholder' => 'Contoh: Budi Santoso - Purchasing Manager',
                ]]],
                ['fields' => [[
                    'name' => 'perusahaan',
                    'label' => '2. NAMA PERUSAHAAN / INSTANSI:',
                    'required' => true,
                    'type' => 'text',
                    'placeholder' => 'Contoh: PT Boga Selera Mandiri',
                ]]],
                ['fields' => [[
                    'name' => 'jenis-usaha',
                    'label' => '3. JENIS USAHA:',
                    'required' => true,
                    'type' => 'select',
                    'placeholder' => 'Pilih Klasifikasi Entitas Usaha...',
                ]]],
                ['fields' => [
                    ['name' => 'email', 'label' => '4A. EMAIL BISNIS RESMI:', 'required' => true, 'type' => 'text', 'placeholder' => 'email@perusahaan.com'],
                    ['name' => 'telepon', 'label' => '4B. NO. TELEPON / WHATSAPP:', 'required' => true, 'type' => 'text', 'placeholder' => '+62 812-xxxx-xxxx'],
                ]],
            ],
            'commodities' => [
                'label' => '5. ESTIMASI KEBUTUHAN KOMODITAS & TARGET KUOTA:',
                'options' => [
                    ['label' => 'Selada Keriting', 'checked' => false],
                    ['label' => 'Tomat Beef A', 'checked' => true],
                    ['label' => 'Brokoli High', 'checked' => false],
                    ['label' => 'Stroberi Ciwidey', 'checked' => false],
                    ['label' => 'Kol Putih', 'checked' => false],
                    ['label' => 'Cabai Rawit Merah', 'checked' => false],
                ],
                'volumeLabel' => 'TOTAL TARGET VOLUME / FREKUENSI KIRIM:',
                'volumePlaceholder' => 'Contoh: 500 kg / minggu (3x pengiriman subuh)',
            ],
            'payments' => [
                'label' => '6. SKEMA PEMBAYARAN YANG DIHARAPKAN:',
                'options' => [
                    ['label' => 'TOP 14 / 30 Hari', 'checked' => true],
                    ['label' => 'COD (On Delivery)', 'checked' => false],
                    ['label' => 'CBD (Before Deliv)', 'checked' => false],
                ],
            ],
            'notes' => [
                'label' => '7. SPESIFIKASI MUTU & KEBUTUHAN OPERASIONAL KHUSUS:',
                'placeholder' => 'Jelaskan spesifikasi mutu, batas toleransi timbangan, persyaratan sertifikasi (GAP/Halal), atau jadwal receiving dock...',
            ],
            'submit' => '[ KIRIMKAN PERMOHONAN KEMITRAAN ]',
            'footnote' => 'Seluruh permohonan dilindungi oleh kerahasiaan data NDA & SOP Kemitraan AgroOrder GPA.',
        ];
    }

    public static function schematic(): array
    {
        return [
            'chip' => 'SCHEMATIC LOGISTICS RUNWAY',
            'title' => 'Peta Lokasi & Denah Akses Koridor Logistik Jagorawi',
            'access' => 'ACCESS POINT: GERBANG TOL CIBINONG CITEUREUP (KM 27)',
            'nodes' => [
                ['tone' => 'light', 'label' => 'ORIGIN (FARM HUBS)', 'title' => 'HUB LEMBANG & SUBANG', 'desc' => 'Pre-Cooling & Sortir Grade A/B', 'chip' => 'Dispatch: 18:00 - 21:00 WIB'],
                ['tone' => 'dark', 'label' => 'CENTRAL PACKHOUSE', 'title' => 'DC CIBINONG (KM 27)', 'desc' => 'Cross-dock & QC Verifikasi Timbang', 'chip' => 'Cold Storage: 85 Ton Chiller'],
                ['tone' => 'light', 'label' => 'DESTINATION (B2B CLIENTS)', 'title' => 'RING-1 JAKARTA & HOTEL', 'desc' => 'Central Kitchen & Supermarket', 'chip' => 'Receiving: 04:00 - 07:00 WIB'],
            ],
            'arrows' => [
                ['top' => '2.5 Jam Transit', 'bottom' => 'Via Tol Cipularang'],
                ['top' => '~35 Menit (Subuh)', 'bottom' => 'Tol Jagorawi & Lingkar Luar'],
            ],
            'caption' => '[DIAGRAM SCHEMATIC: Akses Jalur Distribusi Jakarta - Cibinong Packhouse - Lembang Hub]',
            'footnote' => 'Jarak tempuh armada ke Ring-1 Jakarta: ~35 Menit via Tol Lingkar Luar (JORR).',
            'notes' => [
                ['label' => 'PROSEDUR KUNJUNGAN AUDIT:', 'text' => 'Mitra enterprise diperbolehkan melakukan audit sertifikasi mutu dan fasilitas cold chain dengan membuat janji temu H-2 melalui sekretariat GPA.'],
                ['label' => 'AKSES KENDARAAN ARMADA:', 'text' => 'Dock penerimaan sentral mampu menampung manuver 6 armada Colt Diesel Double (CDD) dan kontainer berpendingin 20-feet secara simultan.'],
                ['label' => 'INTEGRASI API ERP:', 'text' => 'Bagi klien jaringan supermarket dengan sistem ERP SAP/Oracle, integrasi sinkronisasi Purchase Order (PO) otomatis dapat dipandu tim IT GPA.'],
            ],
        ];
    }
}
