<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Form request validasi otentikasi masuk akun (login)
class LoginRequest extends FormRequest
{
    // Otorisasi terbuka untuk publik
    public function authorize(): bool
    {
        return true;
    }

    // Aturan validasi input kredensial masuk
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    // Pesan kustom validasi input
    public function messages(): array
    {
        return [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    // Eksekusi otentikasi kredensial pengguna
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan salah. Silakan periksa kembali.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    // Pastikan percobaan masuk tidak melebihi batas (rate limited)
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $detikTunggu = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$detikTunggu} detik.",
        ]);
    }

    // Kunci unik pelacak frekuensi percobaan masuk (email + IP)
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
