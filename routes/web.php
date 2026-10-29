<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Direktur\CommodityVolumeController;
use App\Http\Controllers\Direktur\ContractController;
use App\Http\Controllers\Direktur\DashboardController;
use App\Http\Controllers\Direktur\GovernanceController;
use App\Http\Controllers\Direktur\OrderInspectionController;
use App\Http\Controllers\Direktur\ProductController;
use App\Http\Controllers\Direktur\ReceivableController;
use App\Http\Controllers\Direktur\ReportController;
use App\Http\Controllers\Direktur\SalesMonitoringController;
use App\Http\Controllers\Direktur\UserController;
use App\Http\Controllers\Klien\CartController as KlienCartController;
use App\Http\Controllers\Klien\CatalogController as KlienCatalogController;
use App\Http\Controllers\Klien\DashboardController as KlienDashboardController;
use App\Http\Controllers\Klien\DocumentController as KlienDocumentController;
use App\Http\Controllers\Klien\OrderController as KlienOrderController;
use App\Http\Controllers\Klien\PaymentProofController as KlienPaymentProofController;
use App\Http\Controllers\Klien\ContractProposalController as KlienContractProposalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\AboutController as PublicAboutController;
use App\Http\Controllers\Public\CatalogController as PublicCatalogController;
use App\Http\Controllers\Public\GalleryController as PublicGalleryController;
use App\Http\Controllers\Public\HomeController as PublicHomeController;
use App\Http\Controllers\Public\KontakController as PublicKontakController;
use App\Http\Controllers\Public\MitraKontrakController as PublicMitraKontrakController;
use App\Http\Controllers\Public\SupplyChainController as PublicSupplyChainController;
use App\Http\Controllers\Sekretaris\DashboardController as SekretarisDashboardController;
use App\Http\Controllers\Sekretaris\DispatchController as SekretarisDispatchController;
use App\Http\Controllers\Sekretaris\InventoryController as SekretarisInventoryController;
use App\Http\Controllers\Sekretaris\InvoiceController as SekretarisInvoiceController;
use App\Http\Controllers\Sekretaris\ManualOrderController as SekretarisManualOrderController;
use App\Http\Controllers\Sekretaris\OrderVerificationController as SekretarisOrderVerificationController;
use App\Http\Controllers\Sekretaris\PaymentController as SekretarisPaymentController;
use App\Http\Controllers\Sekretaris\PodMonitoringController as SekretarisPodMonitoringController;
use App\Http\Controllers\Sekretaris\ReportController as SekretarisReportController;
use App\Http\Controllers\Koordinator\DashboardController as KoordinatorDashboardController;
use App\Http\Controllers\Koordinator\ProductController as KoordinatorProductController;
use App\Http\Controllers\Koordinator\StockController as KoordinatorStockController;
use App\Http\Controllers\Armada\TaskController as ArmadaTaskController;
use App\Http\Controllers\Armada\DispatchController as ArmadaDispatchController;
use App\Http\Controllers\Armada\PodController as ArmadaPodController;
use App\Http\Controllers\Armada\StatusController as ArmadaStatusController;
use App\Http\Controllers\Prints\ReturnActController;
use App\Http\Controllers\Prints\SecretaryInvoiceController as PrintsSecretaryInvoiceController;
use App\Http\Controllers\Prints\SuratJalanController;
use App\Http\Controllers\Prints\ReportPrintController;
use Illuminate\Support\Facades\Route;


// rute publik dapat  diakses tanpa login
Route::get('/', PublicHomeController::class)->name('public.home');

Route::get('/beranda', PublicHomeController::class)
    ->name('public.home.beranda');

Route::get('/tentang-gpa', PublicAboutController::class)
    ->name('public.about');

Route::get('/mitra-kontrak', PublicMitraKontrakController::class)
    ->name('public.mitra-kontrak');

Route::get('/rantai-pasok', PublicSupplyChainController::class)
    ->name('public.supply-chain');

Route::get('/katalog-publik', PublicCatalogController::class)
    ->name('public.catalog');

Route::get('/galeri', PublicGalleryController::class)
    ->name('public.gallery');

Route::get('/kontak', PublicKontakController::class)
    ->name('public.kontak');

Route::get('/loading', fn() => view('loading'))
    ->name('loading');

Route::get('/notifikasi', fn() => view('demo-notifikasi'))
    ->name('notifikasi.demo');

// Rute pengujian seluruh halaman error
Route::get('/errors/{code}', function ($code) {
    if (view()->exists("errors.{$code}")) {
        return response()->view("errors.{$code}", [], (int) $code);
    }
    abort(404);
})->where('code', '403|404|419|429|500')->name('errors.preview');

// Pengalihan cerdas /dashboard utama ke dasbor masing-masing peran berbasis Enum
Route::middleware(['auth'])->get('/dashboard', function () {
    $roleEnum = \App\Enums\UserRole::tryFrom((string) auth()->user()?->role);

    return $roleEnum ? redirect()->route($roleEnum->dashboardRoute()) : redirect('/');
})->name('dashboard');

//otentikasi login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');


// Route Peran

// 1. KLIEN
Route::middleware(['auth', 'verified', 'role:KLIEN'])->prefix('klien')->name('klien.')->group(function () {
    // Dasbor Operasional & Ringkasan Klien
    Route::get('/dashboard', [KlienDashboardController::class, 'index'])->name('dashboard');

    // Katalog Komoditas Klien Reguler & Kontrak B2B
    Route::get('/catalog', [KlienCatalogController::class, 'index'])->name('catalog');

    // Keranjang & Pemesanan PO
    Route::get('/cart', [KlienCartController::class, 'index'])->name('cart');

    // Riwayat & Pelacakan Pesanan Live Tracking 5 Tahap
    Route::get('/orders', [KlienOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/print-rekap', [KlienOrderController::class, 'printRekap'])->name('orders.print-rekap');
    Route::get('/orders/export', [KlienOrderController::class, 'exportCsv'])->name('orders.export');
    Route::get('/orders/create', [KlienOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [KlienOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [KlienOrderController::class, 'show'])->name('orders.show');

    // Pusat Dokumen, Faktur Konsolidasi & Unggah Bukti Bayar
    Route::get('/documents', [KlienDocumentController::class, 'index'])->name('documents');
    Route::get('/documents/print-rekap', [KlienDocumentController::class, 'printRekap'])->name('documents.print-rekap');
    Route::get('/documents/export', [KlienDocumentController::class, 'exportCsv'])->name('documents.export');
    Route::get('/payment-proof', [KlienPaymentProofController::class, 'index'])->name('payment-proof');
    Route::post('/payment-proof', [KlienPaymentProofController::class, 'store'])->name('payment-proof.store');

    // Alias Rute Bahasa Indonesia
    Route::get('/bukti-pembayaran', fn() => redirect()->route('klien.payment-proof', request()->query()));
    Route::get('/bukti-bayar', fn() => redirect()->route('klien.payment-proof', request()->query()));
    Route::get('/dokumen', fn() => redirect()->route('klien.documents', request()->query()));
    Route::get('/pesanan', fn() => redirect()->route('klien.orders.index', request()->query()));
    Route::get('/pesanan/{order}', fn($order) => redirect()->route('klien.orders.show', array_merge(['order' => $order], request()->query())));

    // Pengajuan Kontrak Kemitraan B2B (Khusus Klien Reguler ke Direktur)
    Route::post('/contracts/propose', [KlienContractProposalController::class, 'store'])->name('contracts.propose');
});

// 2. SEKRETARIS
Route::middleware(['auth', 'role:SEKRETARIS'])->prefix('sekretaris')->name('sekretaris.')->group(function () {
    // 1. Dasbor Operasional & Administrasi
    Route::get('/dashboard', [SekretarisDashboardController::class, 'index'])->name('dashboard');

    // 2. Verifikasi Pesanan Masuk (PO) & Kunci Kuota Panen
    Route::get('/verifikasi', [SekretarisOrderVerificationController::class, 'index'])->name('verification');
    Route::get('/orders/verify', [SekretarisOrderVerificationController::class, 'index'])->name('orders.verify.index');
    Route::post('/orders/{order}/approve', [SekretarisOrderVerificationController::class, 'approve'])->name('orders.approve');
    Route::post('/orders/{order}/reject', [SekretarisOrderVerificationController::class, 'reject'])->name('orders.reject');

    // 3. Kesiapan Buffer Stock & Monitoring Stok Gudang
    Route::get('/stok', [SekretarisInventoryController::class, 'index'])->name('inventory');

    // 4. Surat Jalan & Dispatch Armada Logistik (SOP 1.2: Hanya pesanan LUNAS)
    Route::get('/surat-jalan', [SekretarisDispatchController::class, 'index'])->name('dispatch');
    Route::post('/surat-jalan/{order}/terbitkan', [SekretarisDispatchController::class, 'issue'])->name('dispatch.issue');
    Route::post('/surat-jalan/{order}/assign-driver', [SekretarisDispatchController::class, 'assignDriver'])->name('dispatch.assign-driver');

    // 5. Faktur Konsolidasi & Penagihan Tempo B2B
    Route::get('/faktur', [SekretarisInvoiceController::class, 'index'])->name('invoices');
    Route::get('/faktur/export', [SekretarisInvoiceController::class, 'exportCsv'])->name('invoices.export');

    // 6. Verifikasi Pembayaran & Pencocokan Rekening Giro
    Route::get('/pembayaran', [SekretarisPaymentController::class, 'index'])->name('payments');
    Route::post('/pembayaran/{payment}/approve', [SekretarisPaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/pembayaran/{payment}/reject', [SekretarisPaymentController::class, 'reject'])->name('payments.reject');

    // 7. Rekap Laporan & Jurnal Harian Operasional
    Route::get('/laporan', [SekretarisReportController::class, 'index'])->name('reports');
    Route::get('/laporan/export', [SekretarisReportController::class, 'exportCsv'])->name('reports.export');
    Route::get('/laporan/print', [SekretarisReportController::class, 'printReport'])->name('reports.print');

    // 8. Pemantauan Bukti Serah Terima (Proof of Delivery / PoD Armada)
    Route::get('/monitoring-pod', [SekretarisPodMonitoringController::class, 'index'])->name('pod.index');

    Route::get('/pesanan-manual', [SekretarisManualOrderController::class, 'index'])->name('manual-order');
    Route::post('/pesanan-manual', [SekretarisManualOrderController::class, 'store'])->name('manual-order.store');
});

// Rute langsung pesanan manual (Khusus staf Sekretaris dan Direktur)
Route::middleware(['auth', 'role:SEKRETARIS,DIREKTUR'])->group(function () {
    Route::get('/pesanan-manual', [SekretarisManualOrderController::class, 'index']);
    Route::post('/pesanan-manual', [SekretarisManualOrderController::class, 'store']);
});

// 3. KOORDINATOR LAPANGAN (Supply & Inventory Management per docs/MODELING_SUMMARY.md)
Route::middleware(['auth', 'role:KOORDINATOR'])->prefix('koordinator')->name('koordinator.')->group(function () {
    // 1. Dashboard Operasional Pasokan, Persediaan & Pantau Antrean Pesanan Masuk
    Route::get('/dashboard', [KoordinatorDashboardController::class, 'index'])->name('dashboard');

    // 2. Manajemen Master Komoditas Hasil Tani (CRUD Products & Terupdate ke Katalog Publik)
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [KoordinatorProductController::class, 'index'])->name('index');
        Route::get('/create', [KoordinatorProductController::class, 'create'])->name('create');
        Route::post('/', [KoordinatorProductController::class, 'store'])->name('store');
        Route::get('/{product}', [KoordinatorProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [KoordinatorProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [KoordinatorProductController::class, 'update'])->name('update');
        Route::post('/{product}/toggle-status', [KoordinatorProductController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{product}', [KoordinatorProductController::class, 'destroy'])->name('destroy');
    });

    // 3. Suplai Pasokan Stok Panen (Quick Intake Ledger) & Neraca Gudang
    Route::get('/stok', [KoordinatorStockController::class, 'index'])->name('stock');
    Route::post('/stok', [KoordinatorStockController::class, 'storeBatch'])->name('stock.store');
    Route::post('/stok/batch', [KoordinatorStockController::class, 'storeBatch'])->name('stock.batch');
});



// 4. ARMADA LOGISTIK / SUPIR
Route::middleware(['auth', 'role:ARMADA'])->prefix('armada')->name('armada.')->group(function () {
    // 1. Daftar Tugas Harian & Antrean Titik Pengantaran
    Route::get('/dashboard', [ArmadaTaskController::class, 'index'])->name('dashboard');
    Route::get('/tugas', [ArmadaTaskController::class, 'index'])->name('tasks');

    // 2. Surat Jalan Digital & Rincian Muatan
    Route::get('/surat-jalan', [ArmadaDispatchController::class, 'index'])->name('dispatch');
    Route::post('/surat-jalan/{order}/mulai', [ArmadaDispatchController::class, 'startRoute'])->name('dispatch.start');

    // 3. Bukti Terima Digital (Proof of Delivery / PoD)
    Route::get('/scan-pod', [ArmadaPodController::class, 'index'])->name('pod');
    Route::post('/scan-pod/{order}/selesai', [ArmadaPodController::class, 'store'])->name('pod.store');

    // 4. Status Kendaraan, Telemetri IoT & Kelaikan Shift
    Route::get('/status', [ArmadaStatusController::class, 'index'])->name('status');
    Route::post('/status/tutup-shift', [ArmadaStatusController::class, 'closeShift'])->name('status.close-shift');
});

// 5. DIREKTUR / OWNER
Route::middleware(['auth', 'role:DIREKTUR'])->prefix('direktur')->name('direktur.')->group(function () {
    // 1. Dasbor Eksekutif & Monitoring Bisnis
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Monitoring Penjualan & Analisis Revenue
    Route::get('/sales', [SalesMonitoringController::class, 'index'])->name('sales.index');
    Route::get('/sales/export', [SalesMonitoringController::class, 'exportCsv'])->name('sales.export');

    // 3. Monitoring Volume Komoditas, Stok Panen & Pasokan
    Route::get('/commodities', [CommodityVolumeController::class, 'index'])->name('commodities.index');
    Route::get('/commodities/export', [CommodityVolumeController::class, 'exportCsv'])->name('commodities.export');

    // 3b. CRUD Katalog Komoditas & Manajemen Produk
    Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::resource('products', ProductController::class);
    Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');

    // 4. Monitoring Piutang, Tagihan Tempo & Risiko Kredit Klien B2B
    Route::get('/receivables', [ReceivableController::class, 'index'])->name('receivables.index');
    Route::post('/receivables/{id}/reminder', [ReceivableController::class, 'sendReminder'])->name('receivables.reminder');
    Route::post('/receivables/{id}/toggle-freeze', [ReceivableController::class, 'toggleFreeze'])->name('receivables.freeze');
    Route::post('/receivables/{id}/restructure', [ReceivableController::class, 'restructure'])->name('receivables.restructure');

    // 5. Otorisasi Kontrak Khusus & Diskon Volume B2B
    Route::get('/contracts/approval', [ContractController::class, 'approval'])->name('contracts.approval');
    Route::post('/contracts/batch-approve', [ContractController::class, 'batchApprove'])->name('contracts.batch-approve');
    Route::post('/contracts/{contract}/reject', [ContractController::class, 'reject'])->name('contracts.reject');
    Route::post('/contracts/{contract}/revision', [ContractController::class, 'requestRevision'])->name('contracts.revision');
    
    // CRUD Penuh Kontrak Kemitraan
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/create', [ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
    Route::get('/contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
    Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
    Route::delete('/contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
    Route::post('/contracts/{contract}/approve', [ContractController::class, 'approve'])->name('contracts.approve');
    Route::post('/contracts/{contract}/terminate', [ContractController::class, 'terminate'])->name('contracts.terminate');

    // 6. Laporan Penjualan Eksekutif - Status Terkunci (Immutable Archive)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/lock', [ReportController::class, 'lockPeriod'])->name('reports.lock');
    Route::post('/reports/unlock', [ReportController::class, 'unlockPeriod'])->name('reports.unlock');
    Route::get('/reports/download', [ReportController::class, 'download'])->name('reports.download');
    Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

    // 7. Pengaturan Tata Kelola, Kebijakan Bisnis & Audit Trail
    Route::get('/governance', [GovernanceController::class, 'index'])->name('governance.index');
    Route::post('/governance/parameters', [GovernanceController::class, 'updateParameters'])->name('governance.parameters');
    Route::post('/governance/freeze', [GovernanceController::class, 'toggleMasterFreeze'])->name('governance.freeze');
    Route::post('/governance/plt', [GovernanceController::class, 'configurePlt'])->name('governance.plt');
    Route::post('/governance/payment-channels', [GovernanceController::class, 'updatePaymentChannels'])->name('governance.payment-channels');
    Route::get('/governance/export-audit', [GovernanceController::class, 'exportAuditLog'])->name('governance.export-audit');

    // Governance route aliases
    Route::post('/governance/update-parameters', [GovernanceController::class, 'updateParameters'])->name('governance.update-parameters');
    Route::post('/governance/master-freeze', [GovernanceController::class, 'toggleMasterFreeze'])->name('governance.master-freeze');
    Route::post('/governance/delegate-plt', [GovernanceController::class, 'configurePlt'])->name('governance.delegate-plt');

    // 8. Manajemen Hak Akses, Akun Pengguna & Matriks RBAC
    Route::get('/users/rbac/export', [UserController::class, 'exportRbacCsv'])->name('users.rbac.export');
    Route::post('/users/revoke-sessions', [UserController::class, 'revokeAllSessions'])->name('users.revoke-sessions');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', UserController::class);

    // 9. Inspeksi Pengawasan Pesanan Terintegrasi
    Route::get('/orders/{order}', [OrderInspectionController::class, 'show'])->name('orders.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Dokumen Cetak Format Resmi A4 (Surat Jalan, Faktur Penagihan, Berita Acara Retur, Laporan Eksekutif)
Route::middleware('auth')->group(function () {
    Route::get('/cetak/surat-jalan', SuratJalanController::class)->name('prints.surat-jalan');
    Route::get('/cetak/faktur/{invoice}', PrintsSecretaryInvoiceController::class)->name('prints.invoice');
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


