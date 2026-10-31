<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
        Carbon::setLocale(config('app.locale', 'id'));

        View::composer(['errors::*', 'errors.*', 'errors.layout'], function ($view) {
            $user = Auth::user();
            $detectedRole = $user?->role;

            if (! $detectedRole) {
                if (Request::is('direktur*') || Request::is('*/direktur*')) {
                    $detectedRole = 'DIREKTUR';
                } elseif (Request::is('sekretaris*') || Request::is('*/sekretaris*')) {
                    $detectedRole = 'SEKRETARIS';
                } elseif (Request::is('koordinator*') || Request::is('*/koordinator*')) {
                    $detectedRole = 'KOORDINATOR';
                } elseif (Request::is('armada*') || Request::is('*/armada*')) {
                    $detectedRole = 'ARMADA';
                } elseif (Request::is('klien*') || Request::is('*/klien*')) {
                    $detectedRole = 'KLIEN';
                }
            }

            $dashboardUrl = match ($detectedRole) {
                'DIREKTUR'    => route('direktur.dashboard'),
                'SEKRETARIS'  => route('sekretaris.dashboard'),
                'KOORDINATOR' => route('koordinator.dashboard'),
                'ARMADA'      => route('armada.dashboard'),
                'KLIEN'       => route('klien.dashboard'),
                default       => $user ? route('dashboard') : route('public.home'),
            };

            $dashboardLabel = match ($detectedRole) {
                'DIREKTUR'    => 'Kembali ke Dashboard Direktur',
                'SEKRETARIS'  => 'Kembali ke Dashboard Sekretaris',
                'KOORDINATOR' => 'Kembali ke Dashboard Koordinator',
                'ARMADA'      => 'Kembali ke Dashboard Armada',
                'KLIEN'       => 'Kembali ke Dashboard Klien',
                default       => $user ? 'Kembali ke Dashboard' : 'Kembali ke Beranda',
            };

            $view->with([
                'detectedRole'   => $detectedRole,
                'dashboardUrl'   => $dashboardUrl,
                'dashboardLabel' => $dashboardLabel,
            ]);
        });
    }
}
