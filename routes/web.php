<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Klien\CartController as KlienCartController;
use App\Http\Controllers\Klien\CatalogController as KlienCatalogController;
use App\Http\Controllers\Klien\DashboardController as KlienDashboardController;
use App\Http\Controllers\Klien\DocumentController as KlienDocumentController;
use App\Http\Controllers\Klien\OrderController as KlienOrderController;
use App\Http\Controllers\Klien\PaymentProofController as KlienPaymentProofController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Rute publik selamat datang
Route::get('/', function () {
    return view('welcome');
});

// Pengalihan cerdas /dashboard utama
Route::middleware(['auth'])->get('/dashboard', function () {
    $role = auth()->user()->role;

    return match ($role) {
        'KLIEN'       => redirect()->route('klien.dashboard'),
        default       => redirect('/'),
    };
})->name('dashboard');

// Otentikasi login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');

// ==========================================
// PORTAL KLIEN (REGULER & B2B KONTRAK)
// ==========================================
Route::middleware(['auth', 'role:KLIEN'])->prefix('klien')->name('klien.')->group(function () {
    // 1. Dasbor Operasional & Ringkasan Klien
    Route::get('/dashboard', [KlienDashboardController::class, 'index'])->name('dashboard');

    // 2. Katalog Komoditas Klien Reguler & Kontrak B2B
    Route::get('/catalog', [KlienCatalogController::class, 'index'])->name('catalog');

    // 3. Keranjang & Pemesanan PO
    Route::get('/cart', [KlienCartController::class, 'index'])->name('cart');

    // 4. Riwayat & Pelacakan Pesanan Live Tracking 5 Tahap
    Route::get('/orders', [KlienOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/print-rekap', [KlienOrderController::class, 'printRekap'])->name('orders.print-rekap');
    Route::get('/orders/create', [KlienOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [KlienOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{id}', [KlienOrderController::class, 'show'])->name('orders.show');

    // 5. Pusat Dokumen, Faktur Konsolidasi & Unggah Bukti Bayar
    Route::get('/documents', [KlienDocumentController::class, 'index'])->name('documents');
    Route::get('/documents/print-rekap', [KlienDocumentController::class, 'printRekap'])->name('documents.print-rekap');
    Route::get('/payment-proof', [KlienPaymentProofController::class, 'index'])->name('payment-proof');
    Route::post('/payment-proof', [KlienPaymentProofController::class, 'store'])->name('payment-proof.store');
});

// Manajemen Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
