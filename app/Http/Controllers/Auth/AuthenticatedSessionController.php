<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if (!$user->is_active) {
            Auth:guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda dinonaktifkan oleh pengurus Koperasi GPA.',
            ]);
        }

        $request->session()->regenerate();

        return match ($user->role) {
            'KLIEN'       => redirect()->intended(route('klien.dashboard')),
            'SEKRETARIS'  => redirect()->intended(route('sekretaris.dashboard')),
            'KOORDINATOR' => redirect()->intended(route('koordinator.dashboard')),
            'ARMADA'      => redirect()->intended(route('supir.dashboard')),
            'DIREKTUR'    => redirect()->intended(route('direktur.dashboard')),
            default       => redirect()->intended('/'),
        };
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
