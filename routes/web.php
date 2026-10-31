<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;

// 1. Controller Direktur
use App\Http\Controllers\Direktur\DashboardController as DirekturDashboardController;
use App\Http\Controllers\Direktur\InspeksiPesananController as DirekturInspeksiPesananController;
use App\Http\Controllers\Direktur\KomoditasController as DirekturKomoditasController;
use App\Http\Controllers\Direktur\KontrakController as DirekturKontrakController;
use App\Http\Controllers\Direktur\LaporanController as DirekturLaporanController;
use App\Http\Controllers\Direktur\PenjualanController as DirekturPenjualanController;
use App\Http\Controllers\Direktur\PiutangController as DirekturPiutangController;
use App\Http\Controllers\Direktur\ProdukController as DirekturProdukController;
use App\Http\Controllers\Direktur\TataKelolaController as DirekturTataKelolaController;
use App\Http\Controllers\Direktur\UserController as DirekturUserController;

// 2. Controller Klien
use App\Http\Controllers\Klien\BuktiPembayaranController as KlienBuktiPembayaranController;
use App\Http\Controllers\Klien\DashboardController as KlienDashboardController;
use App\Http\Controllers\Klien\DokumenController as KlienDokumenController;
use App\Http\Controllers\Klien\KatalogController as KlienKatalogController;
use App\Http\Controllers\Klien\KeranjangController as KlienKeranjangController;
use App\Http\Controllers\Klien\PengajuanKontrakController as KlienPengajuanKontrakController;
use App\Http\Controllers\Klien\PesananController as KlienPesananController;

// 3. Controller Sekretaris
use App\Http\Controllers\Sekretaris\DashboardController as SekretarisDashboardController;
use App\Http\Controllers\Sekretaris\FakturController as SekretarisFakturController;
use App\Http\Controllers\Sekretaris\LaporanController as SekretarisLaporanController;
use App\Http\Controllers\Sekretaris\MonitoringPodController as SekretarisMonitoringPodController;
use App\Http\Controllers\Sekretaris\PembayaranController as SekretarisPembayaranController;
use App\Http\Controllers\Sekretaris\PesananManualController as SekretarisPesananManualController;
use App\Http\Controllers\Sekretaris\StokGudangController as SekretarisStokGudangController;
use App\Http\Controllers\Sekretaris\SuratJalanController as SekretarisSuratJalanController;
use App\Http\Controllers\Sekretaris\VerifikasiPesananController as SekretarisVerifikasiPesananController;

// 4. Controller Koordinator
use App\Http\Controllers\Koordinator\DashboardController as KoordinatorDashboardController;
use App\Http\Controllers\Koordinator\ProdukController as KoordinatorProdukController;
use App\Http\Controllers\Koordinator\StokController as KoordinatorStokController;

// 5. Controller Armada
use App\Http\Controllers\Armada\BuktiTerimaController as ArmadaBuktiTerimaController;
use App\Http\Controllers\Armada\StatusKendaraanController as ArmadaStatusKendaraanController;
use App\Http\Controllers\Armada\SuratJalanArmadaController as ArmadaSuratJalanController;
use App\Http\Controllers\Armada\TugasController as ArmadaTugasController;

// 6. Controller Cetak Resmi & Profil
use App\Http\Controllers\Prints\ReturnActController;
use App\Http\Controllers\Prints\SecretaryInvoiceController as PrintsSecretaryInvoiceController;
use App\Http\Controllers\Prints\SuratJalanController as PrintsSuratJalanController;
use App\Http\Controllers\Prints\ReportPrintController;
use App\Http\Controllers\Prints\ThermalReceiptController;
use App\Http\Controllers\ProfileController;

// 7. Controller Halaman Publik
use App\Http\Controllers\Public\AboutController as PublicAboutController;
use App\Http\Controllers\Public\CatalogController as PublicCatalogController;
use App\Http\Controllers\Public\GalleryController as PublicGalleryController;
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Public\KontakController as PublicKontakController;
use App\Http\Controllers\Public\MitraKontrakController as PublicMitraKontrakController;
use App\Http\Controllers\Public\SupplyChainController as PublicSupplyChainController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik (Tanpa Otentikasi)
|--------------------------------------------------------------------------
*/
Route::get('/', PublicHomeController::class)->name('public.home');
Route::get('/beranda', PublicHomeController::class)->name('public.home.beranda');
Route::get('/tentang-gpa', PublicAboutController::class)->name('public.about');
Route::get('/mitra-kontrak', PublicMitraKontrakController::class)->name('public.mitra-kontrak');
Route::get('/rantai-pasok', PublicSupplyChainController::class)->name('public.supply-chain');
Route::get('/katalog-publik', PublicCatalogController::class)->name('public.catalog');
Route::get('/galeri', PublicGalleryController::class)->name('public.gallery');
Route::get('/kontak', PublicKontakController::class)->name('public.kontak');
Route::get('/loading', fn() => view('loading'))->name('loading');
Route::get('/notifikasi', fn() => view('demo-notifikasi'))->name('notifikasi.demo');

// Rute pengujian seluruh halaman error HTTP
Route::get('/errors/{code}', function ($code) {
    if (view()->exists("errors.{$code}")) {
        return response()->view("errors.{$code}", [], (int) $code);
    }
    abort(404);
})->where('code', '403|404|419|429|500')->name('errors.preview');

// Pengalihan cerdas /dashboard utama ke dashboard masing-masing peran
Route::middleware(['auth'])->get('/dashboard', function () {
    $roleEnum = \App\Enums\UserRole::tryFrom((string) auth()->user()?->role);

    return $roleEnum ? redirect()->route($roleEnum->dashboardRoute()) : redirect('/');
})->name('dashboard');

// Otentikasi Login Bawaan Laravel
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| 1. ROLE KLIEN (Pelanggan Reguler & Mitra B2B)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:KLIEN'])->prefix('klien')->name('klien.')->group(function () {
    // Dasbor Operasional Klien
    Route::get('/dashboard', [KlienDashboardController::class, 'index'])->name('dashboard');

    // Katalog Komoditas
    Route::get('/katalog', [KlienKatalogController::class, 'index'])->name('katalog');

    // Keranjang Belanja & Checkout PO
    Route::get('/keranjang', [KlienKeranjangController::class, 'index'])->name('keranjang');

    // Riwayat & Pelacakan Pesanan Live Tracking
    Route::get('/pesanan', [KlienPesananController::class, 'index'])->name('pesanan.index');
    Route::get('/pesanan/print-rekap', [KlienPesananController::class, 'printRekap'])->name('pesanan.print-rekap');
    Route::get('/pesanan/export', [KlienPesananController::class, 'exportCsv'])->name('pesanan.export');
    Route::get('/pesanan/create', [KlienPesananController::class, 'create'])->name('pesanan.create');
    Route::post('/pesanan', [KlienPesananController::class, 'store'])->name('pesanan.store');
    Route::get('/pesanan/{order}', [KlienPesananController::class, 'show'])->name('pesanan.show');

    // Pusat Dokumen, Faktur Tagihan Konsolidasi & Rekap
    Route::get('/dokumen', [KlienDokumenController::class, 'index'])->name('dokumen');
    Route::get('/dokumen/print-rekap', [KlienDokumenController::class, 'printRekap'])->name('dokumen.print-rekap');
    Route::get('/dokumen/export', [KlienDokumenController::class, 'exportCsv'])->name('dokumen.export');

    // Unggah Bukti Bayar
    Route::get('/bukti-pembayaran', [KlienBuktiPembayaranController::class, 'index'])->name('bukti-pembayaran');
    Route::post('/bukti-pembayaran', [KlienBuktiPembayaranController::class, 'store'])->name('bukti-pembayaran.store');

    // Pengajuan Kontrak Kemitraan B2B
    Route::post('/pengajuan-kontrak', [KlienPengajuanKontrakController::class, 'store'])->name('pengajuan-kontrak');
});

/*
|--------------------------------------------------------------------------
| 2. ROLE SEKRETARIS (Administrasi Operasional & Keuangan)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:SEKRETARIS'])->prefix('sekretaris')->name('sekretaris.')->group(function () {
    // 1. Dasbor Operasional & Administrasi
    Route::get('/dashboard', [SekretarisDashboardController::class, 'index'])->name('dashboard');

    // 2. Verifikasi Pesanan Masuk (PO) & Kunci Kuota Panen
    Route::get('/verifikasi', [SekretarisVerifikasiPesananController::class, 'index'])->name('verifikasi');
    Route::post('/verifikasi/{order}/setujui', [SekretarisVerifikasiPesananController::class, 'approve'])->name('verifikasi.setujui');
    Route::post('/verifikasi/{order}/tolak', [SekretarisVerifikasiPesananController::class, 'reject'])->name('verifikasi.tolak');

    // 3. Kesiapan Buffer Stock & Monitoring Stok Gudang
    Route::get('/stok', [SekretarisStokGudangController::class, 'index'])->name('stok');

    // 4. Surat Jalan & Dispatch Armada Logistik
    Route::get('/surat-jalan', [SekretarisSuratJalanController::class, 'index'])->name('surat-jalan');
    Route::post('/surat-jalan/{order}/terbitkan', [SekretarisSuratJalanController::class, 'issue'])->name('surat-jalan.terbitkan');
    Route::post('/surat-jalan/{order}/tugaskan-supir', [SekretarisSuratJalanController::class, 'assignDriver'])->name('surat-jalan.tugaskan-supir');

    // 5. Faktur Konsolidasi & Penagihan Tempo B2B
    Route::get('/faktur', [SekretarisFakturController::class, 'index'])->name('faktur');
    Route::get('/faktur/export', [SekretarisFakturController::class, 'exportCsv'])->name('faktur.export');

    // 6. Verifikasi Pembayaran & Pencocokan Rekening Giro
    Route::get('/pembayaran', [SekretarisPembayaranController::class, 'index'])->name('pembayaran');
    Route::post('/pembayaran/{payment}/setujui', [SekretarisPembayaranController::class, 'approve'])->name('pembayaran.setujui');
    Route::post('/pembayaran/{payment}/tolak', [SekretarisPembayaranController::class, 'reject'])->name('pembayaran.tolak');

    // 7. Rekap Laporan & Jurnal Harian Operasional
    Route::get('/laporan', [SekretarisLaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/export', [SekretarisLaporanController::class, 'exportCsv'])->name('laporan.export');
    Route::get('/laporan/print', [SekretarisLaporanController::class, 'printReport'])->name('laporan.print');

    // 8. Pemantauan Bukti Serah Terima / PoD
    Route::get('/monitoring-pod', [SekretarisMonitoringPodController::class, 'index'])->name('monitoring-pod');

    // 9. Input Pesanan Manual
    Route::get('/pesanan-manual', [SekretarisPesananManualController::class, 'index'])->name('pesanan-manual');
    Route::post('/pesanan-manual', [SekretarisPesananManualController::class, 'store'])->name('pesanan-manual.store');
});

// Rute langsung pesanan manual (Khusus staf Sekretaris dan Direktur)
Route::middleware(['auth', 'role:SEKRETARIS,DIREKTUR'])->group(function () {
    Route::get('/pesanan-manual', [SekretarisPesananManualController::class, 'index']);
    Route::post('/pesanan-manual', [SekretarisPesananManualController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| 3. ROLE KOORDINATOR LAPANGAN (Supply & Inventory Management)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:KOORDINATOR'])->prefix('koordinator')->name('koordinator.')->group(function () {
    // 1. Dashboard Operasional Pasokan & Persediaan
    Route::get('/dashboard', [KoordinatorDashboardController::class, 'index'])->name('dashboard');

    // 2. Manajemen Master Komoditas Hasil Tani
    Route::prefix('produk')->name('produk.')->group(function () {
        Route::get('/', [KoordinatorProdukController::class, 'index'])->name('index');
        Route::get('/create', [KoordinatorProdukController::class, 'create'])->name('create');
        Route::post('/', [KoordinatorProdukController::class, 'store'])->name('store');
        Route::get('/{product}', [KoordinatorProdukController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [KoordinatorProdukController::class, 'edit'])->name('edit');
        Route::put('/{product}', [KoordinatorProdukController::class, 'update'])->name('update');
        Route::post('/{product}/toggle-status', [KoordinatorProdukController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{product}', [KoordinatorProdukController::class, 'destroy'])->name('destroy');
    });

    // 3. Suplai Pasokan Stok Panen (Quick Intake Ledger) & Neraca Gudang
    Route::get('/stok', [KoordinatorStokController::class, 'index'])->name('stok');
    Route::post('/stok', [KoordinatorStokController::class, 'storeBatch'])->name('stok.store');
});

/*
|--------------------------------------------------------------------------
| 4. ROLE ARMADA LOGISTIK / SUPIR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ARMADA'])->prefix('armada')->name('armada.')->group(function () {
    // 1. Daftar Tugas Harian & Antrean Titik Pengantaran
    Route::get('/dashboard', [ArmadaTugasController::class, 'index'])->name('dashboard');
    Route::get('/tugas', [ArmadaTugasController::class, 'index'])->name('tugas');

    // 2. Surat Jalan Digital & Rincian Muatan
    Route::get('/surat-jalan', [ArmadaSuratJalanController::class, 'index'])->name('surat-jalan');
    Route::post('/surat-jalan/{order}/mulai', [ArmadaSuratJalanController::class, 'startRoute'])->name('surat-jalan.mulai');

    // 3. Bukti Terima Digital (Proof of Delivery / PoD)
    Route::get('/bukti-terima', [ArmadaBuktiTerimaController::class, 'index'])->name('bukti-terima');
    Route::post('/bukti-terima/{order}/selesai', [ArmadaBuktiTerimaController::class, 'store'])->name('bukti-terima.store');

    // 4. Status Kendaraan, Telemetri IoT & Kelaikan Shift
    Route::get('/status-kendaraan', [ArmadaStatusKendaraanController::class, 'index'])->name('status-kendaraan');
    Route::post('/status-kendaraan/tutup-shift', [ArmadaStatusKendaraanController::class, 'closeShift'])->name('status-kendaraan.tutup-shift');
});

/*
|--------------------------------------------------------------------------
| 5. ROLE DIREKTUR / OWNER (Eksekutif & Tata Kelola)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:DIREKTUR'])->prefix('direktur')->name('direktur.')->group(function () {
    // 1. Dasbor Eksekutif & Monitoring Bisnis
    Route::get('/dashboard', [DirekturDashboardController::class, 'index'])->name('dashboard');

    // 2. Monitoring Penjualan & Analisis Revenue
    Route::get('/penjualan', [DirekturPenjualanController::class, 'index'])->name('penjualan.index');
    Route::get('/penjualan/export', [DirekturPenjualanController::class, 'exportCsv'])->name('penjualan.export');

    // 3. Monitoring Volume Komoditas, Stok Panen & Pasokan
    Route::get('/komoditas', [DirekturKomoditasController::class, 'index'])->name('komoditas.index');
    Route::get('/komoditas/export', [DirekturKomoditasController::class, 'exportCsv'])->name('komoditas.export');

    // 3b. CRUD Katalog Komoditas & Manajemen Produk
    Route::prefix('produk')->name('produk.')->group(function () {
        Route::get('/', [DirekturProdukController::class, 'index'])->name('index');
        Route::get('/create', [DirekturProdukController::class, 'create'])->name('create');
        Route::post('/', [DirekturProdukController::class, 'store'])->name('store');
        Route::get('/{product}', [DirekturProdukController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [DirekturProdukController::class, 'edit'])->name('edit');
        Route::put('/{product}', [DirekturProdukController::class, 'update'])->name('update');
        Route::delete('/{product}', [DirekturProdukController::class, 'destroy'])->name('destroy');
        Route::post('/{product}/toggle-status', [DirekturProdukController::class, 'toggleStatus'])->name('toggle-status');
    });

    // 4. Monitoring Piutang, Tagihan Tempo & Risiko Kredit Klien B2B
    Route::get('/piutang', [DirekturPiutangController::class, 'index'])->name('piutang.index');
    Route::post('/piutang/{id}/reminder', [DirekturPiutangController::class, 'sendReminder'])->name('piutang.reminder');
    Route::post('/piutang/{id}/toggle-freeze', [DirekturPiutangController::class, 'toggleFreeze'])->name('piutang.freeze');
    Route::post('/piutang/{id}/restructure', [DirekturPiutangController::class, 'restructure'])->name('piutang.restructure');

    // 5. Otorisasi Kontrak Khusus & Diskon Volume B2B
    Route::prefix('kontrak')->name('kontrak.')->group(function () {
        Route::get('/', [DirekturKontrakController::class, 'index'])->name('index');
        Route::get('/persetujuan', [DirekturKontrakController::class, 'approval'])->name('persetujuan');
        Route::post('/setujui-serentak', [DirekturKontrakController::class, 'batchApprove'])->name('batch-approve');
        Route::get('/create', [DirekturKontrakController::class, 'create'])->name('create');
        Route::post('/', [DirekturKontrakController::class, 'store'])->name('store');
        Route::get('/{contract}', [DirekturKontrakController::class, 'show'])->name('show');
        Route::get('/{contract}/edit', [DirekturKontrakController::class, 'edit'])->name('edit');
        Route::put('/{contract}', [DirekturKontrakController::class, 'update'])->name('update');
        Route::delete('/{contract}', [DirekturKontrakController::class, 'destroy'])->name('destroy');
        Route::post('/{contract}/setujui', [DirekturKontrakController::class, 'approve'])->name('approve');
        Route::post('/{contract}/tolak', [DirekturKontrakController::class, 'reject'])->name('reject');
        Route::post('/{contract}/revisi', [DirekturKontrakController::class, 'requestRevision'])->name('revision');
        Route::post('/{contract}/terminasi', [DirekturKontrakController::class, 'terminate'])->name('terminate');
    });

    // 6. Laporan Penjualan Eksekutif - Status Terkunci
    Route::get('/laporan', [DirekturLaporanController::class, 'index'])->name('laporan.index');
    Route::post('/laporan/lock', [DirekturLaporanController::class, 'lockPeriod'])->name('laporan.lock');
    Route::post('/laporan/unlock', [DirekturLaporanController::class, 'unlockPeriod'])->name('laporan.unlock');
    Route::get('/laporan/download', [DirekturLaporanController::class, 'download'])->name('laporan.download');
    Route::get('/laporan/export', [DirekturLaporanController::class, 'exportCsv'])->name('laporan.export');

    // 7. Pengaturan Tata Kelola, Kebijakan Bisnis & Audit Trail
    Route::get('/tata-kelola', [DirekturTataKelolaController::class, 'index'])->name('tata-kelola.index');
    Route::post('/tata-kelola/update-parameters', [DirekturTataKelolaController::class, 'updateParameters'])->name('tata-kelola.update-parameters');
    Route::post('/tata-kelola/master-freeze', [DirekturTataKelolaController::class, 'toggleMasterFreeze'])->name('tata-kelola.master-freeze');
    Route::post('/tata-kelola/delegate-plt', [DirekturTataKelolaController::class, 'configurePlt'])->name('tata-kelola.delegate-plt');
    Route::post('/tata-kelola/payment-channels', [DirekturTataKelolaController::class, 'updatePaymentChannels'])->name('tata-kelola.payment-channels');
    Route::get('/tata-kelola/export-audit', [DirekturTataKelolaController::class, 'exportAuditLog'])->name('tata-kelola.export-audit');

    // 8. Manajemen Hak Akses, Akun Pengguna & Matriks RBAC (Pengecualian Khusus: Tetap 'users' sesuai permintaan)
    Route::get('/users/rbac/export', [DirekturUserController::class, 'exportRbacCsv'])->name('users.rbac.export');
    Route::post('/users/revoke-sessions', [DirekturUserController::class, 'revokeAllSessions'])->name('users.revoke-sessions');
    Route::post('/users/{user}/reset-password', [DirekturUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', DirekturUserController::class);

    // 9. Inspeksi Pengawasan Pesanan Terintegrasi
    Route::get('/inspeksi-pesanan/{order}', [DirekturInspeksiPesananController::class, 'show'])->name('inspeksi-pesanan.show');
});

/*
|--------------------------------------------------------------------------
| Profil Pengguna & Dokumen Cetak Format Resmi A4
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dokumen Cetak Format Resmi A4
    Route::get('/cetak/surat-jalan', PrintsSuratJalanController::class)->name('prints.surat-jalan');
    Route::get('/cetak/faktur/{invoice}', PrintsSecretaryInvoiceController::class)->name('prints.invoice');
    Route::get('/cetak/struk/{invoice}', ThermalReceiptController::class)->name('prints.thermal-receipt');
    Route::get('/cetak/berita-acara-retur', ReturnActController::class)->name('prints.return-act');

    // Laporan rekap eksekutif hanya boleh diakses Direktur & Sekretaris
    Route::get('/cetak/laporan-eksekutif', ReportPrintController::class)
        ->middleware('role:DIREKTUR,SEKRETARIS')
        ->name('prints.report');
});

// Fallback rute untuk file bukti serah terima PoD agar tidak terblokir 403
Route::get('/storage/uploads/pod/{filename}', function ($filename) {
    $path = public_path('uploads/pod/' . $filename);
    if (file_exists($path)) {
        return response()->file($path);
    }
    abort(404);
});

require __DIR__.'/auth.php';
