<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerbaruiProfilRequest;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    // Menampilkan formulir profil pengguna, status kemitraan B2B, dan ringkasan limit plafon kredit.
    public function edit(Request $request): View
    {
        $user = $request->user();

        $contract = null;
        if (method_exists($user, 'contracts')) {
            $contract = $user->contracts()
                ->where('status', 'ACTIVE')
                ->latest()
                ->first()
                ?? $user->contracts()->latest()->first();
        }

        $isB2b = ($user->client_type === 'B2B_KONTRAK' || $contract !== null);

        $creditLimit = 0.0;
        if ($isB2b) {
            if ($contract && $contract->fixed_price_per_kg && $contract->committed_volume_per_cycle) {
                $creditLimit = (float) ($contract->fixed_price_per_kg * $contract->committed_volume_per_cycle * 2);
            }
            if ($creditLimit <= 0) {
                $creditLimit = 500000000.0;
            }
        }

        $unpaidInvoicesSum = (float) Invoice::where('user_id', $user->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('grand_total');

        $availableCredit = max(0, $creditLimit - $unpaidInvoicesSum);
        $creditPercent = $creditLimit > 0 ? min(100, max(0, ($availableCredit / $creditLimit) * 100)) : 0.0;

        $clientId = 'BYR-KPN-' . str_pad($user->id, 3, '0', STR_PAD_LEFT);
        $contractNumber = $contract?->contract_number ?? ($isB2b ? ('PKS-GPA-KPN/2026/' . str_pad($user->id, 3, '0', STR_PAD_LEFT)) : '-');
        $topDays = $contract?->top_days ?? ($isB2b ? 30 : 0);
        $accountType = $isB2b
            ? "Klien Kontrak B2B (TOP {$topDays} Hari)"
            : "Klien Reguler (Spot Market)";

        return view('profile.edit', compact(
            'user',
            'contract',
            'isB2b',
            'creditLimit',
            'unpaidInvoicesSum',
            'availableCredit',
            'creditPercent',
            'clientId',
            'contractNumber',
            'topDays',
            'accountType'
        ));
    }

    // Memperbarui data identitas profil pengguna, email, dan kata sandi akun.
    public function update(PerbaruiProfilRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->filled('password')) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    // Menghapus akun pengguna setelah verifikasi kata sandi dan mengakhiri sesi autentikasi.
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
