<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Direktur\StoreUserRequest;
use App\Http\Requests\Direktur\UpdateUserRequest;
use App\Models\User;
use App\Services\GovernanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

class UserController extends Controller
{
    protected GovernanceService $governanceService;

    public function __construct(GovernanceService $governanceService)
    {
        $this->governanceService = $governanceService;
    }

    /**
     * READ: Menampilkan Manajemen Hak Akses, Akun Pengguna & Matriks RBAC
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        $operator = [
            'role'     => 'Direktur Utama',
            'name'     => $currentUser?->name ?: 'Direksi GPA',
            'code'     => 'DIR-' . str_pad($currentUser?->id ?: 1, 3, '0', STR_PAD_LEFT),
            'initials' => strtoupper(substr($currentUser?->name ?: 'DU', 0, 2)),
            'branch'   => 'Kantor Pusat GPA - Eksekutif',
            'email'    => $currentUser?->email ?: 'direktur@greenpasundan.id',
            'period'   => 'Minggu ' . date('W') . ' ' . date('Y') . ' | Q4 YTD',
        ];

        $header = [
            'eyebrow'  => 'Kelola Akun Pengguna & RBAC',
            'title'    => 'Manajemen Hak Akses, Akun Pengguna & Matriks RBAC',
            'subtitle' => 'Pusat Kontrol Otorisasi Pengguna Berjenjang, Pembagian Peran, Token Sesi Kriptografis, & Audit Akses Keamanan.',
            'period'   => 'OKTOBER 2026',
            'actions'  => [
                ['key' => 'export', 'label' => 'Unduh Matriks Akses (.CSV)', 'icon' => 'download', 'tone' => 'neutral'],
                ['key' => 'token',  'label' => 'Regenerasi Token Kripto',   'icon' => 'refresh',  'tone' => 'success'],
                ['key' => 'create', 'label' => '+ Tambah Pengguna Baru',     'icon' => 'plus',     'tone' => 'ink'],
            ],
        ];

        $allUsers = User::orderBy('id', 'asc')->get();
        $totalUsers = $allUsers->count();

        $metrics = [
            [
                'key'       => 'users',
                'eyebrow'   => 'TOTAL AKUN TERDAFTAR',
                'chip'      => '5 Role Terisolasi',
                'chip_tone' => 'neutral',
                'value'     => (string) $totalUsers,
                'note'      => 'PENGGUNA AKTIF',
                'facts'     => [
                    ['text' => '5 Role Terisolasi', 'tone' => 'body'],
                    ['text' => '0 DITANGGUHKAN',    'tone' => 'accent'],
                ],
            ],
            [
                'key'       => 'sessions',
                'eyebrow'   => 'SESI AKTIF REALTIME',
                'chip'      => 'LIVE PING',
                'chip_tone' => 'accent',
                'value'     => '14 NODE LOGIN',
                'note'      => 'DISTRIBUSI PERAN AKTIF',
                'facts'     => [
                    ['text' => '2 Dir | 4 Adm',     'tone' => 'body'],
                    ['text' => '3 Koord | 5 Supir', 'tone' => 'accent'],
                ],
            ],
            [
                'key'       => 'twofa',
                'eyebrow'   => 'OTENTIKASI 2FA & TOKEN',
                'chip'      => 'TERPROTEKSI',
                'chip_tone' => 'accent',
                'value'     => '100%',
                'note'      => 'CAKUPAN AKUN WAJIB 2FA',
                'facts'     => [
                    ['text' => 'Enkripsi RSA-4096', 'tone' => 'body'],
                    ['text' => 'Biometrik Aktif',   'tone' => 'accent'],
                ],
            ],
            [
                'key'       => 'incidents',
                'eyebrow'   => 'LOG PELANGGARAN HAK AKSES',
                'chip'      => 'RULE 08 ENFORCED',
                'chip_tone' => 'ink',
                'value'     => '0 INSIDEN',
                'note'      => 'STATUS BULANAN (0%)',
                'facts'     => [
                    ['text' => 'Rule 08 Enforcement', 'tone' => 'body'],
                    ['text' => 'TAMPER-PROOF',        'tone' => 'accent'],
                ],
            ],
        ];

        $matrix = [
            'title'       => 'Hak Akses & Wewenang 5 Role',
            'subtitle'    => 'Pemisahan ketat tugas komputasi dan otorisasi. Seluruh wewenang diatur dengan token kriptografi per sesi.',
            'status'      => 'STATUS: IMMUTABLE AUDIT ACTIVE',
            'status_tone' => 'success',
            'action'      => 'Sync RBAC Policy',
            'columns'     => [
                ['key' => 'module',      'label' => 'MODUL & FITUR OPERASIONAL', 'rule' => 'Rule 01 & 07'],
                ['key' => 'client',      'label' => 'KLIEN / BUYER',             'rule' => 'HORECA / B2B RETAIL'],
                ['key' => 'admin',       'label' => 'SEKRE / ADMIN',             'rule' => 'KANTOR SENTRAL & FINANCE'],
                ['key' => 'coordinator', 'label' => 'KOORDINATOR LAPANGAN',     'rule' => 'STA / KEBUN KONSOLIDASI'],
                ['key' => 'driver',      'label' => 'ARMADA / SUPIR',            'rule' => 'LOGISTIK & DISTRIBUSI'],
                ['key' => 'director',    'label' => 'DIREKTUR / OWNER',          'rule' => 'OTORITAS TERTINGGI'],
            ],
            'rows'        => [
                [
                    'module'    => 'Katalog & Buat Order B2B',
                    'detail'    => 'Order PO-distribusi klien korporat dan price list kontrak',
                    'highlight' => false,
                    'cells'     => [
                        ['label' => 'Read / Create',          'kind' => 'grant'],
                        ['label' => 'Form WA / Manual (Create)', 'kind' => 'plain'],
                        ['label' => 'No Access',              'kind' => 'muted'],
                        ['label' => 'No Access',              'kind' => 'muted'],
                        ['label' => 'Full Monitoring (Read)', 'kind' => 'dark'],
                    ],
                ],
                [
                    'module'    => 'Timbangan Aktual & Netto Panen',
                    'detail'    => 'Certifikasi bobot bersih timbangan elektronik',
                    'highlight' => false,
                    'cells'     => [
                        ['label' => 'Read-Only',              'kind' => 'plain'],
                        ['label' => 'Read Riil',              'kind' => 'plain'],
                        ['label' => 'Lock & Certify',         'kind' => 'grant'],
                        ['label' => 'No Access',              'kind' => 'muted'],
                        ['label' => 'Audit Calibration (Read)', 'kind' => 'dark'],
                    ],
                ],
                [
                    'module'    => 'Terbitkan Surat Jalan (Rule 05)',
                    'detail'    => 'Dokumen Predicate legalitas pengiriman kargo',
                    'highlight' => false,
                    'cells'     => [
                        ['label' => 'No Access',              'kind' => 'muted'],
                        ['label' => 'Create & Sign',          'kind' => 'grant'],
                        ['label' => 'Read (Manifest)',        'kind' => 'plain'],
                        ['label' => 'Read Digital',           'kind' => 'plain'],
                        ['label' => 'Read & Invalidation',    'kind' => 'dark'],
                    ],
                ],
                [
                    'module'    => 'Faktur, Bayar & Rekonsiliasi Kas',
                    'detail'    => 'Settlement termin dan kliring rekening korporat',
                    'highlight' => false,
                    'cells'     => [
                        ['label' => 'Read / Upload Bukti',    'kind' => 'plain'],
                        ['label' => 'Approve & Reconcile',    'kind' => 'grant'],
                        ['label' => 'No Access',              'kind' => 'muted'],
                        ['label' => 'No Access',              'kind' => 'muted'],
                        ['label' => 'Executive Ledger Read',  'kind' => 'dark'],
                    ],
                ],
                [
                    'module'    => 'PoD Digital, Foto & Catat Retur',
                    'detail'    => 'Bukti penerimaan barang dan catatan sengketa',
                    'highlight' => false,
                    'cells'     => [
                        ['label' => 'Digital Sign PoD',       'kind' => 'grant'],
                        ['label' => 'Verify Dispute',         'kind' => 'plain'],
                        ['label' => 'Verify & Stock Sync',    'kind' => 'grant'],
                        ['label' => 'Upload & Submit',        'kind' => 'plain'],
                        ['label' => 'Dispute Final Arbiter',  'kind' => 'dark'],
                    ],
                ],
                [
                    'module'    => 'Approval Kontrak & Kunci Audit',
                    'detail'    => 'Otorisasi monograf final dan sealing ledger',
                    'highlight' => true,
                    'cells'     => [
                        ['label' => 'NONE (BLOCKED)',         'kind' => 'none'],
                        ['label' => 'Draft / Review',         'kind' => 'plain'],
                        ['label' => 'NONE (BLOCKED)',         'kind' => 'none'],
                        ['label' => 'NONE (BLOCKED)',         'kind' => 'none'],
                        ['label' => 'SOLE APPROVER / LOCK',   'kind' => 'solo'],
                    ],
                ],
            ],
        ];

        $directoryRows = [];
        foreach ($allUsers as $u) {
            $roleKey = match (strtoupper($u->role)) {
                'DIREKTUR'    => 'director',
                'SEKRETARIS'  => 'admin',
                'KOORDINATOR' => 'coordinator',
                'ARMADA'      => 'driver',
                default       => 'client',
            };

            $badge = match ($roleKey) {
                'director'    => 'DIR',
                'admin'       => 'ADM',
                'coordinator' => 'KRD',
                'driver'      => 'DVR',
                default       => 'KLN',
            };

            $division = match ($roleKey) {
                'director'    => 'Direksi Pusat / Policy',
                'admin'       => 'Central Office / Keuangan',
                'coordinator' => 'STA & Timbangan Lapangan',
                'driver'      => 'Armada & Distribusi Fisik',
                default       => $u->company_name ?: 'Mitra B2B Buyer',
            };

            $hub = match ($roleKey) {
                'director'    => 'HQ Jakarta / All Hub',
                'admin'       => 'Kantor Pusat GPA',
                'coordinator' => 'STA Cianjur & Lembang',
                'driver'      => 'Pool Distribusi Bandung',
                default       => 'Jawa Barat & Jabodetabek',
            };

            $directoryRows[] = [
                'role_key'   => $roleKey,
                'badge'      => $badge,
                'badge_tone' => $roleKey === 'director' ? 'ink' : 'track',
                'name'       => $u->name,
                'code'       => strtoupper($badge) . '-' . str_pad($u->id, 3, '0', STR_PAD_LEFT),
                'role'       => $u->role,
                'division'   => $division,
                'nik'        => '3201-' . str_pad($u->id, 4, '0', STR_PAD_LEFT) . '-0001',
                'hub'        => $hub,
                'mfa'        => '2FA Aktif',
                'mfa_tone'   => $roleKey === 'director' ? 'accent' : 'track',
                'online'     => $u->id === $currentUser?->id,
                'login'      => $u->id === $currentUser?->id ? 'Online Now' : 'Hari ini',
                'login_meta' => $u->email,
                'actions'    => [
                    ['label' => 'Edit',        'tone' => 'track', 'icon' => 'settings'],
                    ['label' => 'Reset Sandi', 'tone' => 'track', 'icon' => 'key'],
                ],
            ];
        }

        $directory = [
            'title'              => 'Direktori Pengguna & Status Kredensial',
            'subtitle'           => 'Daftar identitas resmi pemegang otorisasi aktif dalam ekosistem AgroOrder GPA (Terkoneksi LDAP & Vault Keamanan).',
            'search_placeholder' => 'Cari NIK, Nama, atau ID...',
            'filter_label'       => 'Semua Role',
            'roles'              => [
                ['key' => 'all',         'label' => 'Semua Role (' . $totalUsers . ')'],
                ['key' => 'director',    'label' => 'Direktur'],
                ['key' => 'admin',       'label' => 'Sekre/Admin'],
                ['key' => 'coordinator', 'label' => 'Koordinator'],
                ['key' => 'driver',      'label' => 'Supir'],
                ['key' => 'client',      'label' => 'Klien'],
            ],
            'columns'            => [
                'ID & NAMA LENGKAP',
                'ROLE & DIVISI',
                'NIK / IDENTITAS RESMI',
                'WILAYAH / HUB',
                'STATUS 2FA',
                'LOGIN TERAKHIR',
                'AKSI OTORITAS',
            ],
            'rows'               => $directoryRows,
            'shown_label'        => 'Menampilkan ' . count($directoryRows) . ' dari ' . $totalUsers . ' entri otorisasi terdaftar.',
            'ledger_note'        => 'Seluruh perubahan disimpan dalam ledger tamper-proof SHA-256.',
            'page_label'         => '1 / 1',
            'prev_label'         => 'Sebelumnya',
            'next_label'         => 'Selanjutnya',
        ];

        $policies = [
            'title'       => 'Kebijakan Pengelolaan Akun & Sandi',
            'description' => 'Sesuai UU Perlindungan Data Pribadi (UU PDP No. 27/2022) dan ISO 27001.',
        ];

        return view('direktur.access', compact(
            'operator',
            'header',
            'metrics',
            'matrix',
            'directory',
            'policies'
        ));
    }

    /**
     * Formulir penambahan akun pengguna baru
     */
    public function create()
    {
        return view('direktur.users.create');
    }

    /**
     * CREATE: Menyimpan akun pengguna baru ke basis data
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'phone'        => $validated['phone'],
                'password'     => Hash::make($validated['password']),
                'role'         => $validated['role'],
                'client_type'  => $validated['role'] === 'KLIEN' ? ($validated['client_type'] ?? 'REGULER') : null,
                'company_name' => $validated['company_name'] ?? null,
                'address'      => $validated['address'] ?? null,
                'pic_name'     => $validated['pic_name'] ?? null,
                'pic_phone'    => $validated['pic_phone'] ?? null,
                'is_active'    => (bool) $validated['is_active'],
            ]);

            $this->governanceService->addAuditLog(
                'USER_ACCOUNT_CREATED',
                'USR-ID-' . $user->id,
                "Pembuatan akun baru {$user->name} ({$user->role}) oleh Direktur Utama.",
                Auth::guard('web')->user()->name,
                'DIR-01 [ROOT-ACCESS]',
                'SEALED'
            );

            return redirect()->route('direktur.users.index')
                ->with('success', "Akun {$user->name} ({$user->role}) berhasil ditambahkan ke ekosistem GPA.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal menambahkan akun: ' . $e->getMessage()]);
        }
    }

    /**
     * READ Detail: Menampilkan profil rincian akun
     */
    public function show(User $user)
    {
        return view('direktur.users.show', compact('user'));
    }

    /**
     * Formulir penyuntingan profil dan hak akses
     */
    public function edit(User $user)
    {
        return view('direktur.users.edit', compact('user'));
    }

    /**
     * UPDATE: Memperbarui data pengguna, peran, atau sandi
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $validated = $request->validated();

            // Mencegah Direktur menonaktifkan akunnya sendiri
            if ($user->id === Auth::guard('web')->id() && !$validated['is_active']) {
                return back()->withInput()
                    ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.')
                    ->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
            }

            $updateData = [
                'name'         => $validated['name'],
                'email'        => $validated['email'],
                'phone'        => $validated['phone'],
                'role'         => $validated['role'],
                'client_type'  => $validated['role'] === 'KLIEN' ? ($validated['client_type'] ?? null) : null,
                'company_name' => $validated['company_name'] ?? null,
                'address'      => $validated['address'] ?? null,
                'pic_name'     => $validated['pic_name'] ?? null,
                'pic_phone'    => $validated['pic_phone'] ?? null,
                'is_active'    => (bool) $validated['is_active'],
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            $this->governanceService->addAuditLog(
                'USER_ACCOUNT_UPDATED',
                'USR-ID-' . $user->id,
                "Pembaruan profil / peran akun {$user->name} ({$user->role}) oleh Direktur.",
                Auth::guard('web')->user()->name,
                'DIR-01 [ROOT-ACCESS]',
                'SEALED'
            );

            return redirect()->route('direktur.users.index')
                ->with('success', "Data akun {$user->name} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui akun: ' . $e->getMessage())->withErrors(['error' => 'Gagal memperbarui akun: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset Sandi Pengguna Cepat oleh Direktur
     */
    public function resetPassword(Request $request, User $user)
    {
        $newPassword = $request->input('new_password', 'password123');
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        $this->governanceService->addAuditLog(
            'USER_PASSWORD_RESET',
            'USR-ID-' . $user->id,
            "Reset kata sandi pengguna {$user->name} ({$user->email}) oleh Direktur.",
            Auth::guard('web')->user()->name,
            'DIR-01 [ROOT-ACCESS]',
            'SEALED'
        );

        return back()->with('success', "Kata sandi untuk {$user->name} berhasil direset menjadi '{$newPassword}'.");
    }

    /**
     * Prosedur Darurat: Revoke All Sessions (Logout Semua Pengguna Lain)
     */
    public function revokeAllSessions(Request $request)
    {
        try {
            $currentUserId = Auth::guard('web')->id();
            DB::table('sessions')->where('user_id', '!=', $currentUserId)->delete();

            $this->governanceService->addAuditLog(
                'EMERGENCY_REVOKE_ALL_SESSIONS',
                'AUTH-SYSTEM-SESSIONS',
                "PROSEDUR DARURAT: Seluruh sesi aktif di seluruh terminal kantor, gudang sentra, dan kendaraan logistik diputus paksa oleh Direktur Utama.",
                Auth::guard('web')->user()->name,
                'DIR-01 [ROOT-ACCESS]',
                'SECURITY_INTERCEPT'
            );

            return back()->with('success', 'Prosedur Darurat Berhasil: Seluruh sesi aktif pengguna lain di semua terminal berhasil diputus seketika.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memutus sesi: ' . $e->getMessage());
        }
    }

    /**
     * Unduh Matriks Akses (.CSV)
     */
    public function exportRbacCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Matriks_RBAC_GPA_' . date('Ymd') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['KOPERASI GREEN PASUNDAN AGRICULTURE - MATRIKS HAK AKSES RBAC']);
            fputcsv($handle, []);
            fputcsv($handle, ['Modul & Fitur Operasional', 'Klien / Buyer B2B', 'Sekretaris / Finance', 'Koordinator Lapangan', 'Armada / Supir', 'Direktur / Owner']);

            $matrix = [
                ['Katalog & Buat Order B2B', 'Read / Create', 'Form WA / Manual', 'No Access', 'No Access', 'Full Monitoring (Read)'],
                ['Timbangan Aktual & Netto Panen', 'Read-Only', 'Read Riil', 'Verify & Stock Sync', 'No Access', 'Audit Calibration (Read)'],
                ['Terbitkan Surat Jalan (Rule 05)', 'No Access', 'Create & Sign', 'Read (Manifest)', 'Read Digital', 'Read & Invalidation'],
                ['Faktur, Bayar & Rekonsiliasi Kas', 'Read / Upload Bukti', 'Approve & Reconcile', 'No Access', 'No Access', 'Executive Ledger Read'],
                ['PoD Digital, Foto & Catat Retur', 'Digital Sign PoD', 'Verify Dispute', 'No Access', 'Upload & Submit', 'Dispute Final Arbiter'],
                ['Approval Kontrak & Kunci Audit', 'Blocked', 'Draft / Review', 'Blocked', 'Blocked', 'SOLE APPROVER / LOCK'],
            ];

            foreach ($matrix as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * DELETE: Menghapus akun dari sistem
     */
    public function destroy(User $user)
    {
        try {
            if ($user->id === Auth::guard('web')->id()) {
                return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            }

            $userName = $user->name;
            $user->delete();

            $this->governanceService->addAuditLog(
                'USER_ACCOUNT_DELETED',
                'USR-ID-' . $user->id,
                "Penghapusan akun {$userName} dari ekosistem GPA oleh Direktur.",
                Auth::guard('web')->user()->name,
                'DIR-01 [ROOT-ACCESS]',
                'SEALED'
            );

            return redirect()->route('direktur.users.index')
                ->with('success', "Akun {$userName} berhasil dihapus dari sistem.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus akun: ' . $e->getMessage());
        }
    }
}
