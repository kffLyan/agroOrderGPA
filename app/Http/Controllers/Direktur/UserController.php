<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Direktur\SimpanUserRequest;
use App\Http\Requests\Direktur\PerbaruiUserRequest;
use App\Models\User;
use App\Services\TataKelolaService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Controller pengelolaan akun pengguna dan hak akses (RBAC) oleh Direktur Utama
class UserController extends Controller
{
    protected TataKelolaService $tataKelolaService;

    public function __construct(TataKelolaService $tataKelolaService)
    {
        $this->tataKelolaService = $tataKelolaService;
    }

    // Menampilkan daftar seluruh akun pengguna dengan filter peran (RBAC) dan pencarian kata kunci
    public function index(Request $request)
    {
        $totalUsers = User::count();

        $userQuery = User::orderBy('id', 'asc');
        if ($request->filled('role')) {
            $userQuery->where('role', strtoupper($request->query('role')));
        }
        if ($request->filled('search')) {
            $search = $request->query('search');
            // Filter pencarian berdasarkan kecocokan nama pengguna, alamat email, atau nomor telepon
            $userQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        $allUsers = $userQuery->get();

        return view('direktur.users.index', compact('allUsers', 'totalUsers'));
    }

    // Menampilkan formulir pendaftaran akun pengguna baru di lingkungan koperasi GPA
    public function create()
    {
        return view('direktur.users.create');
    }

    // Simpan akun pengguna baru ke database dan catat riwayatnya ke audit log tata kelola
    public function store(SimpanUserRequest $request)
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

            $this->tataKelolaService->addAuditLog(
                'USER_ACCOUNT_CREATED',
                'USR-ID-' . $user->id,
                "Pembuatan akun baru {$user->name} ({$user->role}) oleh Direktur Utama.",
                Auth::guard('web')->user()->name,
                'DIR-01 [ROOT-ACCESS]',
                'SEALED'
            );

            return redirect()->route('direktur.users.index')
                ->with('success', "Akun {$user->name} ({$user->role}) berhasil ditambahkan ke ekosistem GPA.");
        } catch (Exception $exception) {
            return back()->withInput()->withErrors(['error' => 'Gagal menambahkan akun: ' . $exception->getMessage()]);
        }
    }

    // Menampilkan rincian detail profil akun pengguna dan hak akses operasionalnya
    public function show(User $user)
    {
        return view('direktur.users.show', compact('user'));
    }

    // Menampilkan formulir penyuntingan profil pengguna, peran, atau pengaturan kata sandi
    public function edit(User $user)
    {
        return view('direktur.users.edit', compact('user'));
    }

    // Perbarui data profil akun serta cegah Direktur menonaktifkan akunnya sendiri
    public function update(PerbaruiUserRequest $request, User $user)
    {
        try {
            $validated = $request->validated();

            // Mencegah Direktur menonaktifkan akunnya sendiri agar sistem tidak terkunci
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

            $this->tataKelolaService->addAuditLog(
                'USER_ACCOUNT_UPDATED',
                'USR-ID-' . $user->id,
                "Pembaruan profil / peran akun {$user->name} ({$user->role}) oleh Direktur.",
                Auth::guard('web')->user()->name,
                'DIR-01 [ROOT-ACCESS]',
                'SEALED'
            );

            return redirect()->route('direktur.users.index')
                ->with('success', "Data akun {$user->name} berhasil diperbarui.");
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Gagal memperbarui akun: ' . $exception->getMessage())->withErrors(['error' => 'Gagal memperbarui akun: ' . $exception->getMessage()]);
        }
    }

    // Reset kata sandi pengguna secara cepat menjadi default oleh Direktur Utama
    public function resetPassword(Request $request, User $user)
    {
        $newPassword = $request->input('new_password', 'password123');
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        $this->tataKelolaService->addAuditLog(
            'USER_PASSWORD_RESET',
            'USR-ID-' . $user->id,
            "Reset kata sandi pengguna {$user->name} ({$user->email}) oleh Direktur.",
            Auth::guard('web')->user()->name,
            'DIR-01 [ROOT-ACCESS]',
            'SEALED'
        );

        return back()->with('success', "Kata sandi untuk {$user->name} berhasil direset menjadi '{$newPassword}'.");
    }

    // Prosedur darurat untuk memutus paksa seluruh sesi aktif pengguna lain di semua perangkat
    public function revokeAllSessions(Request $request)
    {
        try {
            $currentUserId = Auth::guard('web')->id();
            DB::table('sessions')->where('user_id', '!=', $currentUserId)->delete();

            $this->tataKelolaService->addAuditLog(
                'EMERGENCY_REVOKE_ALL_SESSIONS',
                'AUTH-SYSTEM-SESSIONS',
                "PROSEDUR DARURAT: Seluruh sesi aktif di seluruh terminal kantor, gudang sentra, dan kendaraan logistik diputus paksa oleh Direktur Utama.",
                Auth::guard('web')->user()->name,
                'DIR-01 [ROOT-ACCESS]',
                'SECURITY_INTERCEPT'
            );

            return back()->with('success', 'Prosedur Darurat Berhasil: Seluruh sesi aktif pengguna lain di semua terminal berhasil diputus seketika.');
        } catch (\Throwable $exception) {
            return back()->with('error', 'Gagal memutus sesi: ' . $exception->getMessage());
        }
    }

    // Ekspor berkas CSV matriks hak akses RBAC untuk kebutuhan audit tata kelola
    public function exportRbacCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="Matriks_RBAC_GPA_' . date('Ymd') . '.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['KOPERASI GREEN PASUNDAN AGRICULTURE - MATRIKS HAK AKSES RBAC']);
            fputcsv($handle, ['WAKTU CETAK', date('d/m/Y H:i') . ' WIB']);
            fputcsv($handle, []);
            fputcsv($handle, ['Modul & Fitur Operasional', 'Klien / Buyer B2B', 'Sekretaris / Finance', 'Koordinator Lapangan', 'Armada / Supir', 'Direktur / Owner']);

            $matrix = [
                ['Katalog & Buat Order B2B', 'Read / Create', 'Form WA / Manual', 'No Access', 'No Access', 'Full Monitoring (Read)'],
                ['Timbangan Aktual & Netto Panen', 'Read-Only', 'Read Riil', 'Verify & Stock Sync', 'No Access', 'Audit Calibration (Read)'],
                ['Terbitkan Surat Jalan', 'No Access', 'Create & Sign', 'Read (Manifest)', 'Read Digital', 'Read & Invalidation'],
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

    // Hapus akun pengguna dari database dan catat riwayat penghapusan ke audit log
    public function destroy(User $user)
    {
        try {
            if ($user->id === Auth::guard('web')->id()) {
                return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            }

            $userName = $user->name;
            $user->delete();

            $this->tataKelolaService->addAuditLog(
                'USER_ACCOUNT_DELETED',
                'USR-ID-' . $user->id,
                "Penghapusan akun {$userName} dari ekosistem GPA oleh Direktur.",
                Auth::guard('web')->user()->name,
                'DIR-01 [ROOT-ACCESS]',
                'SEALED'
            );

            return redirect()->route('direktur.users.index')
                ->with('success', "Akun {$userName} berhasil dihapus dari sistem.");
        } catch (Exception $exception) {
            return back()->with('error', 'Gagal menghapus akun: ' . $exception->getMessage());
        }
    }
}
