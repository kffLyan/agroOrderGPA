<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @param string ...$roles
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Jika akun tidak aktif, paksa keluar
        if (!$user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda tidak aktif.']);
        }

        // Jika role akun tidak ada di daftar parameter middleware
        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk membuka portal ini.');
        }

        return $next($request);

    }
}
