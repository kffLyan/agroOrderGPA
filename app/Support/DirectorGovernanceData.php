<?php

namespace App\Support;

use App\Models\User;

/**
 * Sumber data "Pengaturan Tata Kelola" (rule engine & audit trail forensik).
 *
 * PRD SEC 16, 18, 20 & 21: lima parameter statutori mengunci otomatisasi ERP
 * dan pergudangan, setiap perubahan parameter tersimpan pada append-only ledger
 * berantai SHA-256, dan dua protokol kritis (master freeze serta delegasi
 * sementara) hanya dapat dijalankan melalui dua lapis otorisasi bertingkat.
 *
 * Seluruh angka diturunkan dari satu set parameter sehingga ringkasan metric,
 * kartu konfigurasi, dan jejak audit selalu saling cocok.
 */
class DirectorGovernanceData
{
    /**
     * Jumlah rule hard-guard yang dipantau pada periode berjalan.
     */
    public const ENFORCED_RULES = 4;

    /**
     * Plafon kredit korporat maksimum yang boleh diekspos ke satu grup.
     */
    public const CREDIT_CEILING = 2_500_000_000;

    /**
     * Plafon kredit yang sudah terpakai pada periode berjalan.
     */
    public const CREDIT_USED = 1_185_000_000;

    /**
     * Margin bersih minimum seluruh kontrak B2B.
     */
    public const MARGIN_FLOOR = 15.0;

    /**
     * Toleransi deviasi susut maksimum yang masih boleh diproses.
     */
    public const SHRINK_HARD_LIMIT = 2.0;

    /**
     * Toleransi deviasi susut yang mulai memicu peringatan.
     */
    public const SHRINK_WARNING = 1.5;

    /**
     * Plafon kredit default untuk klien baru yang belum diaudit.
     */
    public const NEW_CLIENT_PLAFON = 100_000_000;

    /**
     * Term of payment default untuk klien baru.
     */
    public const NEW_CLIENT_TOP = 30;

    /**
     * Skor kredit minimum sebelum TOP fleksibel boleh diberikan.
     */
    public const MIN_CREDIT_SCORE = 75;

    /**
     * Jumlah kontrak yang telah disahkan dalam audit trail.
     */
    public const APPROVED_CONTRACTS = 3;

    /**
     * Total aksi terverifikasi pada ledger forensik.
     */
    public const AUDIT_TOTAL = 1_482;

    /**
     * Baris audit yang ditampilkan pada halaman ini.
     */
    public const AUDIT_PAGE_SIZE = 5;

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => DirectorDashboardData::operator($user),
            'header' => self::header(),
            'metrics' => self::metrics(),
            'parameters' => self::parameters(),
            'audit' => self::audit(),
            'controls' => self::controls(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'Pengaturan Tata Kelola',
            'title' => 'Pengaturan Tata Kelola, Kebijakan Bisnis & Audit Trail Sistem',
            'subtitle' => 'Konsol Otorisasi Kebijakan Utama Direksi, Kontrol Batas Kredit Korporat, Kalibrasi Batas Toleransi Mutu, & Log Forensik Keamanan Transaksi. Seluruh perubahan diproteksi append-only SHA-256 ledger.',
            'period' => 'PERIODE: OKTOBER 2026',
            'export_label' => 'Export Full Audit Log (.CSV)',
            'access_label' => 'Kelola Akun Pengguna & RBAC',
            'integrity_label' => 'Uji Integritas Kriptografi',
            'lock_label' => 'Kunci Sistem Periode',
        ];
    }

    /**
     * Empat metric verifikasi kepatuhan rule engine.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function metrics(): array
    {
        return [
            [
                'key' => 'ledger',
                'eyebrow' => 'METRIC #01 // INTEGRITAS LEDGER',
                'chip' => 'ACTIVE GUARD',
                'chip_tone' => 'success',
                'value' => '100% VALID',
                'note' => 'SHA-256 Checksum Verified',
                'foot_label' => 'Anomali Terdeteksi',
                'foot_value' => '0 Anomali (Zero Breach)',
                'foot_tone' => 'success',
            ],
            [
                'key' => 'credit',
                'eyebrow' => 'METRIC #02 // KONTROL KREDIT B2B',
                'chip' => 'CEILING LIMIT',
                'chip_tone' => 'neutral',
                'value' => self::amount(self::CREDIT_CEILING),
                'note' => 'Plafon kredit korporat maksimum',
                'bar_width' => self::exposurePercent(),
                'foot_label' => 'Terutilisasi',
                'foot_value' => self::amount(self::CREDIT_USED),
                'foot_tone' => 'success',
                'foot_note' => self::exposurePercent().'% Exposure Aman',
            ],
            [
                'key' => 'approval',
                'eyebrow' => 'METRIC #03 // DIR APPROVAL STATUS',
                'chip' => 'VERIFIED',
                'chip_tone' => 'success',
                'value' => self::APPROVED_CONTRACTS.' Kontrak Disetujui',
                'note' => 'Threshold Policy Margin Min. '.self::decimal(self::MARGIN_FLOOR).'%',
                'foot_label' => 'Pending Otorisasi Dir',
                'foot_value' => '0 Pending Dir (Clear)',
                'foot_tone' => 'success',
            ],
            [
                'key' => 'guard',
                'eyebrow' => 'METRIC #04 // HARD-GUARD COMPLIANCE',
                'chip' => 'POLICY ENGINE',
                'chip_tone' => 'neutral',
                'value' => '0 Pelanggaran',
                'note' => 'Hard-Guard Rules 02, 03, 05, 06 Enforced',
                'foot_label' => 'Status Intersepsi',
                'foot_value' => '100% Intercepted',
                'foot_tone' => 'ink',
            ],
        ];
    }

    /**
     * Lima parameter bisnis yang mengunci otomatisasi validasi sistem.
     *
     * @return array<string, mixed>
     */
    public static function parameters(): array
    {
        return [
            'title' => 'Konfigurasi Parameter Bisnis & Rule Engine',
            'subtitle' => '5 Parameter Statutori Tata Kelola yang Mengunci Otomatisasi Validasi Sistem ERP & Pergudangan',
            'action' => 'Edit Parameter Kebijakan (PIN Master)',
            'action_note' => 'Pengubahan parameter memerlukan PIN Master Direktur Utama.',
            'cards' => [
                [
                    'rule' => 'RULE 04 & 13',
                    'status' => 'ENFORCED',
                    'status_tone' => 'success',
                    'title' => 'Toleransi Deviasi Susut Gudang',
                    'description' => 'Ambang batas perbedaan timbangan asal versus timbangan terima di fasilitas pergudangan.',
                    'facts' => [
                        ['label' => 'Maks Hard Limit:', 'value' => self::decimal(self::SHRINK_HARD_LIMIT).'%', 'tone' => 'ink'],
                        ['label' => 'Warning Threshold:', 'value' => self::decimal(self::SHRINK_WARNING).'%', 'tone' => 'warning'],
                        ['label' => 'Action Triggered:', 'value' => 'Auto-Hold Invoice', 'tone' => 'danger'],
                    ],
                    'foot_note' => 'SLA Validasi: Real-Time',
                    'config_id' => 'CFG-DEV-0413',
                ],
                [
                    'rule' => 'SECTION 15.3',
                    'status' => 'ENFORCED',
                    'status_tone' => 'success',
                    'title' => 'Plafon Kredit Default TOP Klien Baru',
                    'description' => 'Kebijakan limit terms of payment (TOP) untuk mitra agribisnis atau buyer yang belum diaudit Direksi.',
                    'facts' => [
                        ['label' => 'Default Plafon:', 'value' => self::amount(self::NEW_CLIENT_PLAFON), 'tone' => 'ink'],
                        ['label' => 'Term of Payment:', 'value' => self::NEW_CLIENT_TOP.' Hari Kalender', 'tone' => 'ink'],
                        ['label' => 'Min Skor Kredit:', 'value' => self::MIN_CREDIT_SCORE.' Poin Sistem', 'tone' => 'success'],
                    ],
                    'foot_note' => 'Escalation: Direksi Keuangan',
                    'config_id' => 'CFG-KRD-1503',
                ],
                [
                    'rule' => 'RULE 05',
                    'status' => 'MANDATORY',
                    'status_tone' => 'success',
                    'title' => 'Syarat Wajib Terbit Surat Jalan',
                    'description' => 'Gerbang verifikasi fisik dan digital sebelum armada angkut logistik diizinkan keluar depot/gudang.',
                    'facts' => [
                        ['label' => 'Net Weight Tera Sah:', 'value' => 'Mandatori Otomatis', 'tone' => 'success'],
                        ['label' => 'Lolos QC Laboratorium:', 'value' => '100% Grade A/B', 'tone' => 'ink'],
                        ['label' => 'Dispensasi Bypass:', 'value' => '0 Toleransi (Dilarang)', 'tone' => 'danger'],
                    ],
                    'foot_note' => 'Interlock: Gate Barrier IoT',
                    'config_id' => 'CFG-DISP-0005',
                ],
                [
                    'rule' => 'SECTION 16',
                    'status' => 'ENFORCED',
                    'status_tone' => 'success',
                    'title' => 'Batas Margin Minimum Penjualan B2B',
                    'description' => 'Floor margin proteksi profitabilitas bruto komoditas pangan segar terhadap fluktuasi harga lelang petani.',
                    'facts' => [
                        ['label' => 'Margin Minimum Statutori:', 'value' => self::decimal(self::MARGIN_FLOOR).'%', 'tone' => 'ink'],
                        ['label' => 'Order < '.self::decimal(self::MARGIN_FLOOR).'%:', 'value' => 'Auto-Eskalasi Direktur', 'tone' => 'warning'],
                        ['label' => 'Sales Bypass Right:', 'value' => 'Locked (No Access)', 'tone' => 'danger'],
                    ],
                    'foot_note' => 'Formula: Net Realisasi COGS',
                    'config_id' => 'CFG-MRG-1600',
                ],
                [
                    'rule' => 'RULE 02 & 03',
                    'status' => 'IMMUTABLE CORE',
                    'status_tone' => 'success',
                    'title' => 'Anti-Overselling Guard (Buffer Fisik + Buffer Mitra)',
                    'description' => 'Mencegah komitmen kontrak jual melampaui agregasi stok fisik gudang dan buffer pasokan mitra tervalidasi sensor silo.',
                    'facts' => [
                        ['label' => 'Alokasi Buffer Fisik:', 'value' => 'Standby Real-Time WMS', 'tone' => 'ink'],
                        ['label' => 'Agregasi Mitra Tani:', 'value' => 'Telemetri Terverifikasi', 'tone' => 'ink'],
                        ['label' => 'Intervensi Sistem:', 'value' => 'Auto-Block Order Terlampaui', 'tone' => 'danger'],
                    ],
                    'foot_note' => 'Latency Verifikasi: < 35ms',
                    'config_id' => 'CFG-OVS-0203',
                ],
            ],
        ];
    }

    /**
     * Jejak audit forensik berantai SHA-256.
     *
     * @return array<string, mixed>
     */
    public static function audit(): array
    {
        return [
            'title' => 'Tabel Audit Trail Forensik & Riwayat Aksi Direksi / Admin',
            'subtitle' => 'Append-Only Cryptographic Ledger berantai SHA-256. Setiap modifikasi kebijakan dan otorisasi tersimpan permanen.',
            'search_placeholder' => 'Cari Dokumen / Hash...',
            'filter_label' => 'Filter',
            'ledger_status' => 'LEDGER STATUS: CONTINUOUS APPEND // PREVIOUS BLOCK HASH MATCHED 100%',
            'columns' => [
                'TIMESTAMP / WIB',
                'AKTOR & USER ID',
                'KATEGORI AKSI',
                'DOKUMEN TERKAIT',
                'RINCIAN PERUBAHAN / KEPUTUSAN',
                'STATUS HASH SHA-256',
            ],
            'rows' => self::auditRows(),
            'shown_label' => 'Menampilkan '.self::AUDIT_PAGE_SIZE.' dari '.number_format(self::AUDIT_TOTAL, 0, ',', '.').' Aksi Terverifikasi',
            'page_label' => 'Halaman 1 dari 297',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function auditRows(): array
    {
        return [
            [
                'date' => '24-OKT-2024',
                'time' => '15:18:02 WIB',
                'actor' => 'Ir. H. Sudarto',
                'actor_meta' => 'UID: DIR-001 [CLEARANCE L4]',
                'action' => 'APPROVAL KONTRAK',
                'action_tone' => 'success',
                'document' => 'KTR-B2B-2024-089',
                'detail' => 'Persetujuan Khusus Kontrak B2B PT Pangan Nusantara Raya (Margin 16.2%, Plafon TOP Rp 450M, Tenor 45 Hari).',
                'hash' => '7f83b165...e92a',
                'block' => 'BLOCK #4412 [SEALED]',
                'blocked' => false,
            ],
            [
                'date' => '24-OKT-2024',
                'time' => '14:02:44 WIB',
                'actor' => 'Nurhayati S.Ak',
                'actor_meta' => 'UID: FIN-LEAD-03 [L3]',
                'action' => 'REKONSILIASI KAS',
                'action_tone' => 'neutral',
                'document' => 'INV-GPA-202410-0089',
                'detail' => 'Kliring Pembayaran Invoice Termin 2 Rp 215.000.000 via VA Mandiri Corporat, Selisih Kliring Rp 0.',
                'hash' => 'b94d27b9...1348',
                'block' => 'BLOCK #4411 [SEALED]',
                'blocked' => false,
            ],
            [
                'date' => '24-OKT-2024',
                'time' => '11:15:00 WIB',
                'actor' => 'Ir. H. Sudarto',
                'actor_meta' => 'UID: DIR-001 [CLEARANCE L4]',
                'action' => 'SYSTEM GOVERNANCE',
                'action_tone' => 'dark',
                'document' => 'Q3-2024-LEDGER',
                'detail' => 'Eksekusi Audit Freeze Q3 2024: Penutupan Mutasi Jurnal Buku Besar Agribisnis Kuartal 3. Status Finalized.',
                'hash' => '1188e6dd...5672',
                'block' => 'BLOCK #4410 [SEALED]',
                'blocked' => false,
            ],
            [
                'date' => '24-OKT-2024',
                'time' => '09:40:12 WIB',
                'actor' => 'Agung W.',
                'actor_meta' => 'UID: WMS-SUPER-01 [L2]',
                'action' => 'BUFFER OVERRIDE',
                'action_tone' => 'neutral',
                'document' => 'STK-TMT-LEMBANG-04',
                'detail' => 'Penyesuaian Kalibrasi Buffer Tomat Beef Pasca Sortir Gudang Lembang (+240 KG dari panen kemitraan Gapoktan).',
                'hash' => '4c8a2b10...99aa',
                'block' => 'BLOCK #4409 [SEALED]',
                'blocked' => false,
            ],
            [
                'date' => '24-OKT-2024',
                'time' => '08:12:55 WIB',
                'actor' => 'System Auto-Guard',
                'actor_meta' => 'DAEMON CORE RULE 02',
                'action' => 'SECURITY BLOCK',
                'action_tone' => 'danger',
                'document' => 'ORD-GPA-202410-0112',
                'detail' => 'Pencegahan Otomatis Overselling: Permintaan PO Melebihi Buffer Fisik Kentang Granola Gudang Ciwidey (Defisit 1.8 MT).',
                'hash' => 'e3b0c442...9823',
                'block' => 'BLOCK #4408 [INTERCEPTED]',
                'blocked' => true,
            ],
        ];
    }

    /**
     * Dua protokol kritis tata kelola.
     *
     * @return array<string, mixed>
     */
    public static function controls(): array
    {
        return [
            [
                'key' => 'freeze',
                'protocol' => 'CRITICAL SAFETY PROTOCOL #01',
                'protocol_tone' => 'danger',
                'status' => 'STATUS: STANDBY READY',
                'status_tone' => 'success',
                'title' => 'Master Freeze Switch (Stock Opname & Audit Khusus)',
                'description' => 'Menghentikan seluruh transaksi input PO, penerbitan surat jalan, rekonsiliasi kas, dan mutasi komoditas secara instan di seluruh node gudang saat stock opname tahunan atau investigasi fraud berlangsung.',
                'facts' => [
                    ['label' => 'Cakupan Freeze:', 'value' => 'All 4 Warehouse Nodes & ERP Sales Gateway'],
                    ['label' => 'Syarat Buka Kunci:', 'value' => 'Dual Digital Signature (Direktur Utama & Komisaris)'],
                ],
                'authority' => 'OTORISASI KHUSUS DIREKTUR',
                'action' => 'Aktifkan Master Freeze',
            ],
            [
                'key' => 'succession',
                'protocol' => 'GOVERNANCE SUCCESSION #02',
                'protocol_tone' => 'success',
                'status' => 'MODE: MANDAT DINAS LUAR',
                'status_tone' => 'ink',
                'title' => 'Delegasi Wewenang Sementara (Plt Direksi)',
                'description' => 'Konfigurasi mandat otorisasi otomatis kepada Pejabat Pelaksana Tugas (Plt) ketika Direksi bertugas ke sentra pertanian luar pulau. Akses dibatasi ketat dengan plafon limit transaksi spesifik.',
                'facts' => [
                    ['label' => 'Penerima Mandat Aktif:', 'value' => 'Nurhayati S.Ak (VP Finance & Controller)'],
                    ['label' => 'Plafon Otorisasi Plt:', 'value' => 'Maksimum '.self::amount(350_000_000).' / Kontrak', 'tone' => 'success'],
                    ['label' => 'Masa Berlaku Mandat:', 'value' => 'Otomatis Berakhir dalam 72 Jam'],
                ],
                'authority' => 'PROTOKOL TATA KELOLA PRD SEC 21',
                'action' => 'Atur Plt Direksi',
            ],
        ];
    }

    /**
     * Persentase eksposur kredit dalam bentuk siap tampil.
     */
    public static function exposurePercent(): string
    {
        return self::decimal(round(self::CREDIT_USED / self::CREDIT_CEILING * 100, 1));
    }

    private static function amount(int $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    private static function decimal(float $value): string
    {
        return number_format($value, 1, '.', '');
    }
}
