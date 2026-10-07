<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data konsol penerbitan Surat Jalan & penugasan armada (Sekretaris).
 *
 * Seluruh nilai di bawah adalah data simulasi yang mencerminkan design
 * "Penerbitan Dokumen Resmi Surat Jalan & Penugasan Armada Logistik"
 * (Rule 05 hard-gate + Rule 06 revisi neto).
 */
class SecretaryDispatchData
{
    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => SecretaryDashboardData::operator($user),
            'heading' => self::heading(),
            'metrics' => self::metrics(),
            'rule05' => self::rule05(),
            'queue' => self::queue(),
            'letterhead' => self::letterhead(),
            'security' => self::security(),
            'signatures' => self::signatures(),
            'release' => self::release(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function heading(): array
    {
        return [
            'eyebrow' => 'Sub-04 // Modul Surat Jalan & Penugasan Armada',
            'title_before' => 'Penerbitan Dokumen Resmi Surat Jalan &',
            'title_after' => 'Penugasan Armada Logistik',
            'subtitle_before' => 'Otorisasi penerbitan Surat Jalan (format:',
            'subtitle_format' => 'SJ-GPA-YYYYMM-XXXX',
            'subtitle_after' => ') berbasis Actual Net Weight penimbangan gudang terkunci (PRD App-GPA.md Section 10, Rule 04 & Rule 05).',
            'badge' => 'Timbangan Digital Dock #01 & #02 Terhubung',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function metrics(): array
    {
        return [
            [
                'key' => 'ready',
                'label' => 'Surat Jalan Siap Terbit',
                'value' => '4 Dokumen',
                'note' => 'Actual net weight telah diverifikasi koordinator gudang',
                'icon' => 'file-text',
                'icon_tile' => 'bg-accent/30',
                'icon_class' => 'text-success-deep',
                'foot_label' => 'Dock Tera Valid',
                'foot_class' => 'text-success-deep',
                'foot_dot' => 'bg-success-deep',
            ],
            [
                'key' => 'transit',
                'label' => 'Dalam Pengiriman Supir',
                'value' => '8 Armada',
                'note' => 'Status real-time rute logistik Jabodetabek & Banten',
                'icon' => 'truck',
                'icon_tile' => 'bg-surface-track',
                'icon_class' => 'text-ink',
                'foot_label' => 'Telemetri Aktif',
                'foot_class' => 'text-ink-quiet',
                'foot_dot' => 'bg-ink-quiet',
            ],
            [
                'key' => 'pod',
                'label' => 'Menunggu Verifikasi POD',
                'value' => '5',
                'value_after' => 'Pengiriman Tiba',
                'note' => 'Menunggu bukti tanda tangan & stempel fisik penerima',
                'icon' => 'package',
                'icon_tile' => 'bg-warning-soft/30',
                'icon_class' => 'text-warning-deep',
                'foot_label' => 'Dokumen Dropoff',
                'foot_class' => 'text-warning',
                'foot_dot' => 'bg-warning',
            ],
            [
                'key' => 'done',
                'label' => 'Selesai Hari Ini',
                'value' => '11 Transaksi',
                'note' => 'Arsip Surat Jalan terhubung ke antrean faktur tempo',
                'icon' => 'check-circle',
                'icon_tile' => 'bg-surface-pill',
                'icon_class' => 'text-ink',
                'foot_label' => 'Ledger Tertutup',
                'foot_class' => 'text-ink-quiet',
                'foot_dot' => 'bg-ink-quiet',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rule05(): array
    {
        return [
            'chip' => 'Kepatuhan Sistem Mutlak',
            'title' => 'Syarat Mutlak Penerbitan Surat Jalan (Rule 05)',
            'body_before' => 'Tombol cetak & rilis Surat Jalan hanya aktif bila status penimbangan berstatus',
            'gate' => 'Sah Tera Gudang',
            'body_after' => '. Dilarang keras menerbitkan Surat Jalan berbasis kuantitas estimasi pemesanan (Order Quantity). Selisih netto timbangan otomatis merevisi invoice penagihan pada PRD Rule 06.',
            'protocol_label' => 'Protokol Keamanan',
            'protocol_value' => 'Integrity Hash: SHA-256',
            'protocol_note' => 'Audit Stamp: Auto-Sync',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function queue(): array
    {
        return [
            'title' => 'Antrean Penerbitan Dokumen SJ (Menunggu Dispatch Admin)',
            'subtitle' => 'Pemisahan ketat State Ready vs State Locked sesuai protokol validitas timbang tera gudang.',
            'chip' => 'Filter: Semua Gudang (SUB-04)',
            'columns' => [
                'identity' => 'Identitas PO & Klien',
                'estimate' => 'Estimasi Order',
                'netto' => 'Netto Riil Sah (Timbangan)',
                'status' => 'Status Validasi',
                'fleet' => 'Armada & Supir',
                'action' => 'Otorisasi Surat Jalan',
            ],
            'warehouses' => ['Semua Gudang (SUB-04)', 'Gudang Subang', 'Gudang Serpong', 'Gudang Pulogadung'],
            'rows' => self::rows(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function rows(): array
    {
        return [
            [
                'id' => 'ORD-GPA-202410-092',
                'client' => 'PT Kuliner Prima',
                'warehouse' => 'Gudang Subang',
                'dock' => 'CK Ciracas, Jakarta Timur',
                'state' => 'ready',
                'estimate' => '450.0 kg',
                'netto' => '447.2 kg',
                'deviation' => '-2.8 kg (-0.62%)',
                'revision' => 'Revisi otomatis Rule 06',
                'status_chip' => [
                    'label' => 'Sah Tera Gudang #02',
                    'class' => 'bg-accent text-ink outline outline-1 outline-success-deep',
                ],
                'tera' => 'Tera: Ir. Bambang Sutrisno',
                'armada' => 'Isuzu Elf Box',
                'plate' => 'B 9421 TX',
                'driver' => 'Joko Widodo',
                'sj' => 'SJ-GPA-202410-0115',
                'issued_at' => '24 Oktober 2024 (08:40 WIB)',
                'handover_at' => 'Gudang Subang // 08:38 WIB',
                'auth_key' => 'AGRO-HASH-89102-SYS4',
                'document' => self::document(
                    client: 'PT Kuliner Prima Nusantara',
                    drop_point: 'Central Kitchen (CK) Ciracas Blok B-08',
                    address: 'Jl. Raya Ciracas No. 45, Jakarta Timur, DKI Jakarta 13740',
                    pic: 'PIC Lapangan: Sdr. Rian Pramono (0812-8891-2311)',
                    armada: 'Isuzu Elf Box Pendingin (Reefer)',
                    plate: 'B 9421 TX',
                    driver: 'Joko Widodo',
                    sim: 'SIM BII Umum',
                    chiller: '4.2° C (Terkontrol)',
                    officer: 'Ir. Bambang Sutrisno',
                    officer_role: 'Koordinator Gudang GPA',
                    officer_note: 'Gudang Subang // 08:38 WIB',
                    estimate: '450.0 kg',
                    netto: '447.2 kg',
                    delta: '-2.8 kg',
                    lines: [
                        [
                            'no' => '01',
                            'name' => 'Selada Romaine Super (Hydroponic Grade A)',
                            'meta' => 'Panen: Subang Farm Hub-01 // Target Suhu: 5°C',
                            'lot' => '15 Krat Plastik (LOT-RS-2410-09)',
                            'estimate' => '300.0 kg',
                            'netto' => '298.5 kg',
                            'delta' => '-1.5 kg',
                        ],
                        [
                            'no' => '02',
                            'name' => 'Tomat Beef Pilihan (Premium Greenhouse)',
                            'meta' => 'Panen: Lembang Highland Hub // Target Suhu: 10°C',
                            'lot' => '10 Dus Ventilasi (LOT-TB-2410-44)',
                            'estimate' => '150.0 kg',
                            'netto' => '148.7 kg',
                            'delta' => '-1.3 kg',
                        ],
                    ],
                ),
            ],
            [
                'id' => 'ORD-GPA-202410-096',
                'client' => 'CV Boga Lestari Mandiri',
                'warehouse' => 'Gudang Serpong',
                'dock' => 'Gudang Logistik Serpong',
                'state' => 'ready',
                'estimate' => '1.200.0 kg',
                'netto' => '1.192.5 kg',
                'deviation' => '-7.5 kg (-0.63%)',
                'revision' => 'Revisi otomatis Rule 06',
                'status_chip' => [
                    'label' => 'Sah Tera Gudang #01',
                    'class' => 'bg-accent text-ink outline outline-1 outline-success-deep',
                ],
                'tera' => 'Tera: Dwi Haryanto',
                'armada' => 'Hino Dutro Reefer',
                'plate' => 'B 9081 CKA',
                'driver' => 'Hendra Setiawan',
                'sj' => 'SJ-GPA-202410-0118',
                'issued_at' => '24 Oktober 2024 (09:05 WIB)',
                'handover_at' => 'Gudang Serpong // 09:02 WIB',
                'auth_key' => 'AGRO-HASH-89118-SYS4',
                'document' => self::document(
                    client: 'CV Boga Lestari Mandiri',
                    drop_point: 'Gudang Logistik Serpong Blok C-12',
                    address: 'Jl. Raya Serpong km. 07, Tangerang, Banten 15810',
                    pic: 'PIC Lapangan: Sdri. Nur Aisyah (0813-2245-7781)',
                    armada: 'Hino Dutro Reefer (Suhu 2°C - 6°C)',
                    plate: 'B 9081 CKA',
                    driver: 'Hendra Setiawan',
                    sim: 'SIM B1 Umum',
                    chiller: '3.6° C (Terkontrol)',
                    officer: 'Dwi Haryanto',
                    officer_role: 'Koordinator Gudang GPA',
                    officer_note: 'Gudang Serpong // 09:01 WIB',
                    estimate: '1.200.0 kg',
                    netto: '1.192.5 kg',
                    delta: '-7.5 kg',
                    lines: [
                        [
                            'no' => '01',
                            'name' => 'Brokoli Highland Curled Head (Grade A)',
                            'meta' => 'Panen: Ciwidey Highland Hub // Target Suhu: 4°C',
                            'lot' => '24 Krat Plastik (LOT-BR-2410-31)',
                            'estimate' => '700.0 kg',
                            'netto' => '696.4 kg',
                            'delta' => '-3.6 kg',
                        ],
                        [
                            'no' => '02',
                            'name' => 'Wortel Bebek Organik (Sertifikasi)',
                            'meta' => 'Panen: Subang Farm Hub-02 // Target Suhu: 8°C',
                            'lot' => '10 Krat Plastik (LOT-WR-2410-18)',
                            'estimate' => '500.0 kg',
                            'netto' => '496.1 kg',
                            'delta' => '-3.9 kg',
                        ],
                    ],
                ),
            ],
            [
                'id' => 'ORD-GPA-202410-098',
                'client' => 'PT Katering Rasa Nusantara',
                'warehouse' => 'Gudang Pulogadung',
                'dock' => 'Kawasan Industri Pulogadung',
                'state' => 'pending',
                'estimate' => '350.0 kg',
                'netto' => null,
                'deviation' => null,
                'revision' => null,
                'netto_chip' => [
                    'label' => 'Belum Selesai Timbang',
                    'class' => 'bg-warning-soft text-warning-ink outline outline-1 outline-warning/40',
                ],
                'netto_note' => 'IoT Sensor Pending Lock',
                'status_chip' => [
                    'label' => 'Proses Timbang (Dock #02 Sibuk)',
                    'class' => 'bg-surface-disabled text-ink-body outline outline-1 outline-line-board',
                ],
                'tera' => 'Operator: Antrean Timbangan 02',
                'armada' => null,
                'plate' => null,
                'driver' => null,
                'fleet_note' => 'Belum Dialokasikan',
                'fleet_sub' => 'Menunggu rilis surat jalan',
                'sj' => 'SJ-GPA-202410-0121',
                'issued_at' => '24 Oktober 2024 (09:20 WIB)',
                'handover_at' => 'Gudang Pulogadung // 09:18 WIB',
                'auth_key' => 'AGRO-HASH-89121-SYS4',
                'document' => self::document(
                    client: 'PT Katering Rasa Nusantara',
                    drop_point: 'Dapur Industri Pulogadung Blok F-03',
                    address: 'Jl. Raya Pulogadung No. 88, Jakarta Timur, DKI Jakarta 11730',
                    pic: 'PIC Lapangan: Sdr. Bagus Prasetyo (0857-9012-3344)',
                    armada: 'Menunggu Alokasi Armada',
                    plate: '-',
                    driver: 'Menunggu Penugasan',
                    sim: '-',
                    chiller: 'Menunggu Input Sensor',
                    officer: 'Menunggu Tera Gudang',
                    officer_role: 'Koordinator Gudang GPA',
                    officer_note: 'Gudang Pulogadung // 09:18 WIB',
                    estimate: '350.0 kg',
                    netto: null,
                    delta: null,
                    lines: [
                        [
                            'no' => '01',
                            'name' => 'Kol Putih Organik (Sertifikasi)',
                            'meta' => 'Panen: Ciwidey Sayati // Target Suhu: 5°C',
                            'lot' => '14 Krat Plastik (LOT-KP-2410-07)',
                            'estimate' => '350.0 kg',
                            'netto' => null,
                            'delta' => null,
                        ],
                    ],
                ),
            ],
        ];
    }

    /**
     * @param  array<int, array<string, string|null>>  $lines
     * @return array<string, mixed>
     */
    protected static function document(
        string $client,
        string $drop_point,
        string $address,
        string $pic,
        string $armada,
        string $plate,
        string $driver,
        string $sim,
        string $chiller,
        string $officer,
        string $officer_role,
        string $officer_note,
        string $estimate,
        ?string $netto,
        ?string $delta,
        array $lines,
    ): array {
        return [
            'client' => $client,
            'drop_point' => $drop_point,
            'address' => $address,
            'pic' => $pic,
            'armada' => $armada,
            'plate' => $plate,
            'driver' => $driver,
            'sim' => $sim,
            'chiller' => $chiller,
            'officer' => $officer,
            'officer_role' => $officer_role,
            'officer_note' => $officer_note,
            'estimate' => $estimate,
            'netto' => $netto,
            'delta' => $delta,
            'lines' => $lines,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function letterhead(): array
    {
        return [
            'company' => 'PT Agro Pasti Ada',
            'address' => 'Divisi Distribusi Rantai Pasok Segar Agrikultur Nasional',
            'address_line' => 'Jl. Pergudangan Agroniaga Kav. 14, Subang, Jawa Barat 41285',
            'contact' => 'Telp: (0260) 412-9088 | NPWP: 01.345.890.4-421.000',
            'doc_label' => 'Surat Jalan & Pengantar Barang',
            'columns' => [
                'no' => 'No',
                'commodity' => 'Nama Komoditas Segar',
                'lot' => 'Kemasan / Lot ID',
                'estimate' => 'Estimasi PO',
                'netto' => 'Netto Riil Sah (Tera)',
                'delta' => 'Selisih Netto',
            ],
            'lines_label' => 'Rincian Komoditas Fisik Sah (Timbangan Tera Dock):',
            'total_label' => 'Total Akumulasi Sah:',
            'legal_label' => 'Catatan Hukum Logistik:',
            'legal_note' => 'Penerimaan barang wajib dihitung berdasarkan timbangan sah tercantum di atas. Kekurangan fisik akibat susut alami telah dikompensasi secara otomatis ke dalam sistem pemotongan tagihan faktur tempo sesuai ketetapan SOP Kontrak GPA Pasal 14.',
            'stamp_top' => 'Otorisasi Sah Gudang',
            'stamp_mid' => 'Sah Netto Tera',
            'stamp_low' => 'Digital Weight Secure Lock',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function security(): array
    {
        return [
            'preview_title' => 'Preview Draf Format Resmi Surat Jalan',
            'standard_chip' => 'Standar Resmi PRD Section 10',
            'print_label' => 'Cetak Bukti Fisik',
            'secure_mark' => '*SJ-GPA-202410-0115-SECURE*',
            'auth_prefix' => 'Authenticity Key:',
            'page' => 'Halaman 1 dari 1 — Dokumen Logistik Terkendali',
            'copies' => 'Salinan 1: Klien / Salinan 2: Pengangkut / Salinan 3: Arsip Penagihan GPA',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function signatures(): array
    {
        return [
            [
                'label' => 'Diserahkan Oleh',
                'role' => 'Koordinator Gudang GPA',
                'stamp' => '[Terverifikasi Digital]',
                'name' => 'Ir. Bambang Sutrisno',
                'note' => 'Gudang Subang // 08:38 WIB',
            ],
            [
                'label' => 'Pengangkut Armada',
                'role' => 'Supir Logistik',
                'stamp' => '(Tanda tangan saat serah terima dock)',
                'name' => 'Joko Widodo',
                'note' => 'SIM: 8810293102 // B 9421 TX',
            ],
            [
                'label' => 'Diterima Lengkap Oleh',
                'role' => 'Penerima Barang Dock',
                'stamp' => '(Stempel & Tanda Tangan Penerima)',
                'name' => '(............................................)',
                'note' => 'Nama Jelas & Stempel Perusahaan',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function release(): array
    {
        return [
            'title' => 'Rilis dan Selesaikan Surat Jalan Ini? (Rule 05 Enforced)',
            'body_before' => 'Otorisasi akan mengunci timbangan riil',
            'body_after' => ', menerbitkan QR armada supir, dan memperbarui status pesanan menjadi DISPATCH READY.',
            'draft_label' => 'Simpan Draft',
            'issue_label' => 'Konfirmasi & Terbitkan SJ Resmi',
        ];
    }
}
