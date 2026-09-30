<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return view('auth.login');
    }

    // Memproses otentikasi login
    public function login(Request $request)
    {
        // Validasi Input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Percobaan login dengan pengecekan is_active = true
        $remember = $request->boolean('remember');

        if (Auth::attempt(array_merge($credentials, ['is_active' => true]), $remember)) {
            // Regenerasi session ID untuk keamanan
            $request->session()->regenerate();

            $user = Auth::user();

            return $this->redirectBasedOnRole($user->role)
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        // Jika login gagal otentikasi
        return back()->withErrors([
            'email' => 'Kombinasi email atau kata sandi tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    // Mengarahkan Pengguna ke rute dasbor sesuai dengan peranna
    private function redirectBasedOnRole(string $role)
    {
        return match ($role) {
            'KLIEN'       => redirect()->intended('/klien/dashboard'),
            'SEKRETARIS'  => redirect()->intended('/admin/dashboard'),
            'KOORDINATOR' => redirect()->intended('/koordinator/dashboard'),
            'ARMADA'      => redirect()->intended('/supir/dashboard'),
            'DIREKTUR'    => redirect()->intended('/direktur/dashboard'),
            default       => redirect()->to('/'),
        };
    }

}
