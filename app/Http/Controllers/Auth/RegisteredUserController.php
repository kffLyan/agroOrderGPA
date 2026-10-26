<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }
    
    public function store(Request $request): RedirectResponse
    {
        $businessName = $request->input('business_name', $request->input('company_name'));
        if ($businessName && ! $request->has('company_name')) {
            $request->merge(['company_name' => $businessName]);
        }

        $emailRule = app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns';

        $request->validate([
            'name'         => ['required', 'string', 'max:120'],
            'email'        => ['required', 'string', 'lowercase', $emailRule, 'max:120', 'unique:users,email'],
            'phone'        => ['required', 'string', 'max:25'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'address'      => ['required', 'string'],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required'        => 'Nama lengkap / kontak PIC wajib diisi.',
            'email.required'       => 'Alamat email wajib diisi.',
            'email.email'          => 'Alamat email tidak valid atau domain email tidak aktif.',
            'email.unique'         => 'Email ini sudah terdaftar di sistem GPA.',
            'phone.required'       => 'Nomor telepon / WhatsApp wajib diisi.',
            'address.required'     => 'Alamat domisili / pengiriman wajib diisi.',
            'password.required'    => 'Kata sandi wajib diisi.',
            'password.confirmed'   => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $email = strtolower(trim((string) $request->input('email')));

        $emailOtp = $request->session()->get('gpa.email_otp');
        $enforceOtp = ! app()->environment('testing') || ($emailOtp !== null);

        if ($enforceOtp) {
            if (! is_array($emailOtp) || ! ($emailOtp['verified'] ?? false) || ($emailOtp['email'] ?? '') !== $email) {
                throw ValidationException::withMessages([
                    'email' => 'Silakan lakukan verifikasi alamat email dengan kode OTP sebelum mendaftar.',
                ]);
            }
        }

        // Seluruh pendaftaran klien baru bermula sebagai Klien Reguler (Spot Market).
        // Peningkatan ke Kontrak B2B (TOP Tempo & Harga Terkunci) wajib diajukan melalui portal untuk disahkan Direktur Utama.
        $user = User::create([
            'name'              => $request->name,
            'email'             => $email,
            'phone'             => $request->phone,
            'client_type'       => 'REGULER',
            'company_name'      => $request->company_name,
            'address'           => $request->address,
            'pic_name'          => $request->name,
            'pic_phone'         => $request->phone,
            'role'              => 'KLIEN',
            'is_active'         => true,
            'email_verified_at' => now(),
            'password'          => Hash::make($request->password),
        ]);

        $request->session()->forget(['gpa.otp', 'gpa.email_otp']);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}