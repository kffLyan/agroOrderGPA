<?php

namespace App\Support;

use App\Models\User;

/**
 * Konsol manajemen hak akses, akun pengguna, dan matriks RBAC.
 */
class DirectorAccessData
{
    /**
     * Jumlah akun resmi yang terdaftar pada seluruh ekosistem.
     */
    public const REGISTERED_USERS = 48;

    /**
     * Jumlah sesi login yang masih aktif secara realtime.
     */
    public const ACTIVE_SESSIONS = 14;

    /**
     * Jumlah insiden pelanggaran hak akses pada periode berjalan.
     */
    public const ACCESS_INCIDENTS = 0;

    /**
     * Cakupan 2FA seluruh akun wajib.
     */
    public const TWO_FA_COVERAGE = '100%';

    /**
     * Perangkat lapangan yang terdaftar pada whitelist GPA.
     */
    public const FIELD_DEVICES = 28;

    /**
     * Jumlah baris direktori pengguna yang ditampilkan per halaman.
     */
    public const USER_PAGE_SIZE = 6;

    /**
     * @return array<string, mixed>
     */
    public static function for(?User $user = null): array
    {
        return [
            'operator' => DirectorDashboardData::operator($user),
            'header' => self::header(),
            'metrics' => self::metrics(),
            'matrix' => self::matrix(),
            'directory' => self::directory(),
            'policies' => self::policies(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function header(): array
    {
        return [
            'eyebrow' => 'Kelola Akun Pengguna & RBAC',
            'title' => 'Manajemen Hak Akses, Akun Pengguna & Matriks RBAC',
            'subtitle' => 'Pusat Kontrol Otorisasi Pengguna Berjenjang, Pembagian Peran (Client, Admin, Koordinator, Supir, Direksi), Token Sesi Kriptografis, & Audit Akses Keamanan (PRD Rule 08 Hak Akses Tidak Boleh Dicampur).',
            'period' => 'OKTOBER 2026',
            'actions' => [
                ['key' => 'export', 'label' => 'Unduh Matriks Akses (.CSV)', 'icon' => 'download', 'tone' => 'neutral'],
                ['key' => 'token', 'label' => 'Regenerasi Token Kripto', 'icon' => 'refresh', 'tone' => 'success'],
                ['key' => 'create', 'label' => '+ Tambah Pengguna Baru', 'icon' => 'plus', 'tone' => 'ink'],
            ],
        ];
    }

    /**
     * Empat metric keamanan identitas dan akses.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function metrics(): array
    {
        return [
            [
                'key' => 'users',
                'eyebrow' => 'TOTAL AKUN TERDAFTAR',
                'chip' => '5 Role Terisolasi',
                'chip_tone' => 'neutral',
                'value' => (string) self::REGISTERED_USERS,
                'note' => 'PENGGUNA AKTIF',
                'facts' => [
                    ['text' => '5 Role Terisolasi', 'tone' => 'body'],
                    ['text' => '0 DITANGGUHKAN', 'tone' => 'accent'],
                ],
            ],
            [
                'key' => 'sessions',
                'eyebrow' => 'SESI AKTIF REALTIME',
                'chip' => 'LIVE PING',
                'chip_tone' => 'accent',
                'value' => self::ACTIVE_SESSIONS.' NODE LOGIN',
                'note' => 'DISTRIBUSI PERAN AKTIF',
                'facts' => [
                    ['text' => '2 Dir | 4 Adm', 'tone' => 'body'],
                    ['text' => '3 Koord | 5 Supir', 'tone' => 'accent'],
                ],
            ],
            [
                'key' => 'twofa',
                'eyebrow' => 'OTENTIKASI 2FA & TOKEN',
                'chip' => 'TERPROTEKSI',
                'chip_tone' => 'accent',
                'value' => self::TWO_FA_COVERAGE,
                'note' => 'CAKUPAN AKUN WAJIB 2FA',
                'facts' => [
                    ['text' => 'Enkripsi RSA-4096', 'tone' => 'body'],
                    ['text' => 'Biometrik Aktif', 'tone' => 'accent'],
                ],
            ],
            [
                'key' => 'incidents',
                'eyebrow' => 'LOG PELANGGARAN HAK AKSES',
                'chip' => 'RULE 08 ENFORCED',
                'chip_tone' => 'ink',
                'value' => self::ACCESS_INCIDENTS.' INSIDEN',
                'note' => 'STATUS BULANAN (0%)',
                'facts' => [
                    ['text' => 'Rule 08 Enforcement', 'tone' => 'body'],
                    ['text' => 'TAMPER-PROOF', 'tone' => 'accent'],
                ],
            ],
        ];
    }

    /**
     * Matriks pemisahan wewenang lima peran.
     *
     * @return array<string, mixed>
     */
    public static function matrix(): array
    {
        return [
            'title' => 'Hak Akses & Wewenang 5 Role',
            'subtitle' => 'Pemisahan ketat tugas komputasi dan otorisasi. Seluruh wewenang diatur dengan token kriptografi per sesi.',
            'status' => 'STATUS: IMMUTABLE AUDIT ACTIVE',
            'status_tone' => 'success',
            'action' => 'Sync RBAC Policy',
            'columns' => [
                ['key' => 'module', 'label' => 'MODUL & FITUR OPERASIONAL', 'rule' => 'Rule 01 & 07'],
                ['key' => 'client', 'label' => 'KLIEN / BUYER', 'rule' => 'HORECA / B2B RETAIL'],
                ['key' => 'admin', 'label' => 'SEKRE / ADMIN', 'rule' => 'KANTOR SENTRAL & FINANCE'],
                ['key' => 'coordinator', 'label' => 'KOORDINATOR LAPANGAN', 'rule' => 'STA / KEBUN KONSOLIDASI'],
                ['key' => 'driver', 'label' => 'ARMADA / SUPIR', 'rule' => 'LOGISTIK & DISTRIBUSI'],
                ['key' => 'director', 'label' => 'DIREKTUR / OWNER', 'rule' => 'OTORITAS TERTINGGI'],
            ],
            'rows' => [
                [
                    'module' => 'Katalog & Buat Order B2B',
                    'detail' => 'Order PO-distribusi klien korporat dan price list kontrak',
                    'highlight' => false,
                    'cells' => [
                        ['label' => 'Read / Create', 'kind' => 'grant'],
                        ['label' => 'Form WA / Manual (Create)', 'kind' => 'plain'],
                        ['label' => 'No Access', 'kind' => 'muted'],
                        ['label' => 'No Access', 'kind' => 'muted'],
                        ['label' => 'Full Monitoring (Read)', 'kind' => 'dark'],
                    ],
                ],
                [
                    'module' => 'Timbangan Aktual & Netto Panen',
                    'detail' => 'Certifikasi bobot bersih timbangan elektronik',
                    'highlight' => false,
                    'cells' => [
                        ['label' => 'Read-Only', 'kind' => 'plain'],
                        ['label' => 'Read Riil', 'kind' => 'plain'],
                        ['label' => 'Lock & Certify', 'kind' => 'grant'],
                        ['label' => 'No Access', 'kind' => 'muted'],
                        ['label' => 'Audit Calibration (Read)', 'kind' => 'dark'],
                    ],
                ],
                [
                    'module' => 'Terbitkan Surat Jalan (Rule 05)',
                    'detail' => 'Dokumen Predicate legalitas pengiriman kargo',
                    'highlight' => false,
                    'cells' => [
                        ['label' => 'No Access', 'kind' => 'muted'],
                        ['label' => 'Create & Sign', 'kind' => 'grant'],
                        ['label' => 'Read (Manifest)', 'kind' => 'plain'],
                        ['label' => 'Read Digital', 'kind' => 'plain'],
                        ['label' => 'Read & Invalidation', 'kind' => 'dark'],
                    ],
                ],
                [
                    'module' => 'Faktur, Bayar & Rekonsiliasi Kas',
                    'detail' => 'Settlement termin dan kliring rekening korporat',
                    'highlight' => false,
                    'cells' => [
                        ['label' => 'Read / Upload Bukti', 'kind' => 'plain'],
                        ['label' => 'Approve & Reconcile', 'kind' => 'grant'],
                        ['label' => 'No Access', 'kind' => 'muted'],
                        ['label' => 'No Access', 'kind' => 'muted'],
                        ['label' => 'Executive Ledger Read', 'kind' => 'dark'],
                    ],
                ],
                [
                    'module' => 'PoD Digital, Foto & Catat Retur',
                    'detail' => 'Bukti penerimaan barang dan catatan sengketa',
                    'highlight' => false,
                    'cells' => [
                        ['label' => 'Digital Sign PoD', 'kind' => 'grant'],
                        ['label' => 'Verify Dispute', 'kind' => 'plain'],
                        ['label' => 'Verify & Stock Sync', 'kind' => 'grant'],
                        ['label' => 'Upload & Submit', 'kind' => 'plain'],
                        ['label' => 'Dispute Final Arbiter', 'kind' => 'dark'],
                    ],
                ],
                [
                    'module' => 'Approval Kontrak & Kunci Audit',
                    'detail' => 'Otorisasi monograf final dan sealing ledger',
                    'highlight' => true,
                    'cells' => [
                        ['label' => 'NONE (BLOCKED)', 'kind' => 'none'],
                        ['label' => 'Draft / Review', 'kind' => 'plain'],
                        ['label' => 'NONE (BLOCKED)', 'kind' => 'none'],
                        ['label' => 'NONE (BLOCKED)', 'kind' => 'none'],
                        ['label' => 'SOLE APPROVER / LOCK', 'kind' => 'solo'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Direktori pemegang otorisasi beserta status kredensial.
     *
     * @return array<string, mixed>
     */
    public static function directory(): array
    {
        return [
            'title' => 'Direktori Pengguna & Status Kredensial',
            'subtitle' => 'Daftar identitas resmi pemegang otorisasi aktif dalam ekosistem AgroOrder GPA (Terkoneksi LDAP & Vault Keamanan).',
            'search_placeholder' => 'Cari NIK, Nama, atau ID...',
            'filter_label' => 'Semua Role',
            'roles' => [
                ['key' => 'all', 'label' => 'Semua Role (5)'],
                ['key' => 'director', 'label' => 'Direktur'],
                ['key' => 'admin', 'label' => 'Sekre/Admin'],
                ['key' => 'coordinator', 'label' => 'Koordinator'],
                ['key' => 'driver', 'label' => 'Supir'],
                ['key' => 'client', 'label' => 'Klien'],
            ],
            'columns' => [
                'ID & NAMA LENGKAP',
                'ROLE & DIVISI',
                'NIK / IDENTITAS RESMI',
                'WILAYAH / HUB',
                'STATUS 2FA',
                'LOGIN TERAKHIR',
                'AKSI OTORITAS',
            ],
            'rows' => self::directoryRows(),
            'shown_label' => 'Menampilkan '.self::USER_PAGE_SIZE.' dari '.self::REGISTERED_USERS.' entri otorisasi terdaftar.',
            'ledger_note' => 'Seluruh perubahan disimpan dalam ledger tamper-proof SHA-256.',
            'page_label' => '1 / 8',
            'prev_label' => 'Sebelumnya',
            'next_label' => 'Selanjutnya',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function directoryRows(): array
    {
        return [
            [
                'role_key' => 'director',
                'badge' => 'DIR',
                'badge_tone' => 'ink',
                'name' => 'Ahmad Sanusi, S.P.',
                'code' => 'DIR-01 // ROOT-ACCESS',
                'role' => 'Direktur Utama',
                'division' => 'Direksi Pusat / Policy',
                'nik' => '3201-1978-0812-0001',
                'hub' => 'HQ Jakarta / All Hub',
                'mfa' => 'Hardware YubiKey',
                'mfa_tone' => 'accent',
                'online' => true,
                'login' => 'Online Now',
                'login_meta' => 'IP: 10.240.0.12 (VPN Secure)',
                'actions' => [['label' => 'Kelola Wewenang', 'tone' => 'ink', 'icon' => 'key']],
            ],
            [
                'role_key' => 'admin',
                'badge' => 'ADM',
                'badge_tone' => 'track',
                'name' => 'Nurhayati, S.Ak',
                'code' => 'ADM-002',
                'role' => 'Head of Finance & Sekre',
                'division' => 'Central Office / Keuangan',
                'nik' => '3271-1985-0423-0004',
                'hub' => 'Kantor Pusat GPA',
                'mfa' => 'TOTP App (2FA)',
                'mfa_tone' => 'track',
                'online' => false,
                'login' => '10 mnt lalu',
                'login_meta' => 'Terminal Fin-01',
                'actions' => [
                    ['label' => 'Reset Sandi', 'tone' => 'track', 'icon' => 'key'],
                    ['label' => 'Edit', 'tone' => 'track', 'icon' => 'settings'],
                ],
            ],
            [
                'role_key' => 'admin',
                'badge' => 'SEC',
                'badge_tone' => 'track',
                'name' => 'Siti Rahmawati',
                'code' => 'OP-4091',
                'role' => 'Sekretariat Desk',
                'division' => 'Operasional & Surat Jalan',
                'nik' => '3213-1992-1102-0008',
                'hub' => 'Konsolidasi Jabar',
                'mfa' => '2FA Aktif',
                'mfa_tone' => 'accent',
                'online' => true,
                'login' => 'Online Now',
                'login_meta' => 'Subang Desk Node',
                'actions' => [['label' => 'Edit Role', 'tone' => 'track', 'icon' => 'settings']],
            ],
            [
                'role_key' => 'coordinator',
                'badge' => 'KRD',
                'badge_tone' => 'track',
                'name' => 'Agung Wicaksono',
                'code' => 'KRD-004',
                'role' => 'Supply Coordinator',
                'division' => 'STA & Timbangan Lapangan',
                'nik' => '3201-1989-0715-0012',
                'hub' => 'STA-04 / Subang/Bogor',
                'mfa' => 'Biometrik Lapangan',
                'mfa_tone' => 'track',
                'online' => true,
                'login' => 'Online Now',
                'login_meta' => 'Timbangan Tablet #02',
                'actions' => [['label' => 'Audit Log', 'tone' => 'track', 'icon' => 'file-text']],
            ],
            [
                'role_key' => 'driver',
                'badge' => 'DVR',
                'badge_tone' => 'track',
                'name' => 'Joko Prasetyo',
                'code' => 'DVR-GPA-08',
                'role' => 'Driver Reefer #03',
                'division' => 'Armada Pendingin Logistik',
                'nik' => '3302-1994-0319-0021',
                'hub' => 'Pangkalan Armada Bogor',
                'mfa' => 'Device Binding IMEI',
                'mfa_tone' => 'track',
                'online' => false,
                'login' => '5 mnt lalu',
                'login_meta' => 'GPS Tel: B-9014-KPA',
                'actions' => [['label' => 'Lihat Sesi', 'tone' => 'track', 'icon' => 'eye']],
            ],
            [
                'role_key' => 'client',
                'badge' => 'CLI',
                'badge_tone' => 'track',
                'name' => 'Chef Ridwan',
                'code' => 'CLI-B2B-1082',
                'role' => 'Hotel Grand Pangrango',
                'division' => 'Klien B2B Horeca / Buyer',
                'nik' => '3271-1981-1205-0019',
                'hub' => 'Bogor City Portal',
                'mfa' => 'OTP Email / WA',
                'mfa_tone' => 'track',
                'online' => false,
                'login' => '2 jam lalu',
                'login_meta' => 'Web Client Portal',
                'actions' => [['label' => 'Detail Klien', 'tone' => 'track', 'icon' => 'users']],
            ],
        ];
    }

    /**
     * Kebijakan sesi, whitelist perangkat, dan prosedur darurat.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function policies(): array
    {
        return [
            [
                'key' => 'session',
                'icon' => 'clock',
                'title' => 'Kebijakan Sesi & Idle Timeout',
                'items' => [
                    [
                        'title' => 'Auto Logout Direksi & Admin (15 Menit)',
                        'description' => 'Terminal tanpa interaksi akan otomatis terputus setelah 900 detik.',
                        'status' => 'MANDATORY',
                        'status_tone' => 'success',
                    ],
                    [
                        'title' => 'Single Active Node Policy',
                        'description' => 'Login pada lokasi atau peramban baru akan menutup sesi sebelumnya untuk mencegah duplikasi kredensial.',
                        'status' => 'AKTIF',
                        'status_tone' => 'ink',
                    ],
                ],
                'foot_note' => 'Idle Timeout: 900 Detik (15 Menit)',
            ],
            [
                'key' => 'hardware',
                'icon' => 'lock',
                'title' => 'Hardware Whitelisting & IMEI',
                'items' => [
                    [
                        'title' => 'IMEI Binding Supir & Koordinator',
                        'description' => 'Aplikasi lapangan hanya boleh berjalan pada perangkat yang telah terdaftar.',
                        'status' => 'ENFORCED',
                        'status_tone' => 'success',
                    ],
                    [
                        'title' => 'Biometric Timbangan Sync',
                        'description' => 'Hasil timbangan wajib direkam dengan sidik jari koordinator lapangan.',
                        'status' => 'AKTIF',
                        'status_tone' => 'ink',
                    ],
                ],
                'foot_note' => 'Total '.self::FIELD_DEVICES.' Perangkat Lapangan Terdaftar dalam Whitelist GPA',
            ],
            [
                'key' => 'emergency',
                'icon' => 'alert-triangle',
                'title' => 'Prosedur Darurat Otoritas',
                'description' => 'Gunakan protokol ini ketika terjadi serangan siber, kebocoran kredensial, atau transaksi tidak wajar pada saat tutup buku.',
                'warning' => 'PERINGATAN: Tindakan ini akan seketika memutus '.self::ACTIVE_SESSIONS.' sesi aktif di seluruh terminal kantor, gudang sentra, dan kendaraan distribusi.',
                'action' => 'REVOKE ALL SESSIONS (LOGOUT SEMUA PENGGUNA)',
            ],
        ];
    }
}
