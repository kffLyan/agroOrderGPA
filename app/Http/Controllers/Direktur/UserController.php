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
        $roleFilter = $request->query('role');
        $search = $request->query('search');

        $users = User::query()
            ->when($roleFilter, function ($query, $roleFilter) {
                return $query->where('role', $roleFilter);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('company_name', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $roleCounts = [
            'ALL'         => User::count(),
            'DIREKTUR'    => User::where('role', 'DIREKTUR')->count(),
            'SEKRETARIS'  => User::where('role', 'SEKRETARIS')->count(),
            'KOORDINATOR' => User::where('role', 'KOORDINATOR')->count(),
            'ARMADA'      => User::where('role', 'ARMADA')->count(),
            'KLIEN'       => User::where('role', 'KLIEN')->count(),
        ];

        // Sesi aktif realtime dari tabel sessions jika ada
        $activeSessionsCount = 14;
        try {
            $dbSessions = DB::table('sessions')->count();
            if ($dbSessions > 0) {
                $activeSessionsCount = $dbSessions;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // Matriks Hak Akses 5 Peran (PRD Section 6 & 7.1)
        $rbacMatrix = [
            [
                'feature' => 'Katalog & Buat Order B2B',
                'klien' => 'Read / Create',
                'sekre' => 'Form WA / Manual (Create)',
                'koord' => 'No Access',
                'armada' => 'No Access',
                'direktur' => 'Full Monitoring (Read)',
            ],
            [
                'feature' => 'Timbangan Aktual & Netto Panen',
                'klien' => 'Read-Only',
                'sekre' => 'Read Riil',
                'koord' => 'Verify & Stock Sync',
                'armada' => 'No Access',
                'direktur' => 'Audit Calibration (Read)',
            ],
            [
                'feature' => 'Terbitkan Surat Jalan (Rule 05)',
                'klien' => 'No Access',
                'sekre' => 'Create & Sign',
                'koord' => 'Read (Manifest)',
                'armada' => 'Read Digital',
                'direktur' => 'Read & Invalidation',
            ],
            [
                'feature' => 'Faktur, Bayar & Rekonsiliasi Kas',
                'klien' => 'Read / Upload Bukti',
                'sekre' => 'Approve & Reconcile',
                'koord' => 'No Access',
                'armada' => 'No Access',
                'direktur' => 'Executive Ledger Read',
            ],
            [
                'feature' => 'PoD Digital, Foto & Catat Retur',
                'klien' => 'Digital Sign PoD',
                'sekre' => 'Verify Dispute',
                'koord' => 'No Access',
                'armada' => 'Upload & Submit',
                'direktur' => 'Dispute Final Arbiter',
            ],
            [
                'feature' => 'Approval Kontrak & Kunci Audit',
                'klien' => 'Blocked',
                'sekre' => 'Draft / Review',
                'koord' => 'Blocked',
                'armada' => 'Blocked',
                'direktur' => 'SOLE APPROVER / LOCK',
            ],
        ];

        return view('direktur.users.index', compact(
            'users',
            'roleFilter',
            'search',
            'roleCounts',
            'activeSessionsCount',
            'rbacMatrix'
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
