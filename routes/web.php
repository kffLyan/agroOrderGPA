<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


// rute publik dapat  diakses tanpa login
Route::get('/', function () {
    return view('welcome');
});

//otentikasi login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');


// Route Peran

// 1. KLIEN
Route::middleware(['auth', 'role:KLIEN'])->prefix('klien')->name('klien.')->group(function () {
    Route::get('/dashboard', function () {
        return "Halo Klien: " . auth()->user()->name;
    })->name('dashboard');
});

// 2. SEKRETARIS / ADMIN
Route::middleware(['auth', 'role:SEKRETARIS'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return "Halo Sekretaris GPA: " . auth()->user()->name;
    })->name('dashboard');
});

// 3. KOORDINATOR LAPANGAN
Route::middleware(['auth', 'role:KOORDINATOR'])->prefix('koordinator')->name('koordinator.')->group(function () {
    Route::get('/dashboard', function () {
        return "Halo Koordinator Lapangan: " . auth()->user()->name;
    })->name('dashboard');
});

// 4. ARMADA LOGISTIK / SUPIR
Route::middleware(['auth', 'role:ARMADA'])->prefix('supir')->name('supir.')->group(function () {
    Route::get('/dashboard', function () {
        return "Halo Armada Logistik: " . auth()->user()->name;
    })->name('dashboard');
});

// 5. DIREKTUR / OWNER
Route::middleware(['auth', 'role:DIREKTUR'])->prefix('direktur')->name('direktur.')->group(function () {
    Route::get('/dashboard', function () {
        return "Halo Direktur GPA: " . auth()->user()->name;
    })->name('dashboard');
});

require __DIR__.'/auth.php';
