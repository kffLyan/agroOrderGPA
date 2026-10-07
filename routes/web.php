<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Direktur\CommodityVolumeController;
use App\Http\Controllers\Direktur\ContractController;
use App\Http\Controllers\Direktur\DashboardController;
use App\Http\Controllers\Direktur\GovernanceController;
use App\Http\Controllers\Direktur\OrderInspectionController;
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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Sekretaris\DashboardController as SekretarisDashboardController;
use App\Http\Controllers\Sekretaris\DispatchController as SekretarisDispatchController;
use App\Http\Controllers\Sekretaris\InventoryController as SekretarisInventoryController;
use App\Http\Controllers\Sekretaris\InvoiceController as SekretarisInvoiceController;
use App\Http\Controllers\Sekretaris\ManualOrderController as SekretarisManualOrderController;
use App\Http\Controllers\Sekretaris\OrderVerificationController as SekretarisOrderVerificationController;
use App\Http\Controllers\Sekretaris\PaymentController as SekretarisPaymentController;
use App\Http\Controllers\Sekretaris\ReportController as SekretarisReportController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;


// rute publik dapat  diakses tanpa login
Route::get('/', function () {
    return view('welcome');
});

// Pengalihan cerdas /dashboard utama ke dasbor masing-masing peran
Route::middleware(['auth'])->get('/dashboard', function () {
    $role = auth()->user()->role;

    return match ($role) {
        'DIREKTUR'    => redirect()->route('direktur.dashboard'),
        'SEKRETARIS'  => redirect()->route('sekretaris.dashboard'),
        'KOORDINATOR' => redirect()->route('koordinator.dashboard'),
        'ARMADA'      => redirect()->route('armada.dashboard'),
        'KLIEN'       => redirect()->route('klien.dashboard'),
        default       => redirect('/'),
    };
})->name('dashboard');

//otentikasi login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');


// Route Peran

// 1. KLIEN
Route::middleware(['auth', 'role:KLIEN'])->prefix('klien')->name('klien.')->group(function () {
    // Dasbor Operasional & Ringkasan Klien
    Route::get('/dashboard', [KlienDashboardController::class, 'index'])->name('dashboard');

    // Katalog Komoditas Klien Reguler & Kontrak B2B
    Route::get('/catalog', [KlienCatalogController::class, 'index'])->name('catalog');

    // Keranjang & Pemesanan PO
    Route::get('/cart', [KlienCartController::class, 'index'])->name('cart');

    // Riwayat & Pelacakan Pesanan Live Tracking 5 Tahap
    Route::get('/orders', [KlienOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/print-rekap', [KlienOrderController::class, 'printRekap'])->name('orders.print-rekap');
    Route::get('/orders/create', [KlienOrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [KlienOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{id}', [KlienOrderController::class, 'show'])->name('orders.show');

    // Pusat Dokumen, Faktur Konsolidasi & Unggah Bukti Bayar
    Route::get('/documents', [KlienDocumentController::class, 'index'])->name('documents');
    Route::get('/documents/print-rekap', [KlienDocumentController::class, 'printRekap'])->name('documents.print-rekap');
    Route::get('/payment-proof', [KlienPaymentProofController::class, 'index'])->name('payment-proof');
    Route::post('/payment-proof', [KlienPaymentProofController::class, 'store'])->name('payment-proof.store');
});

// 2. SEKRETARIS
Route::middleware(['auth', 'role:SEKRETARIS'])->prefix('sekretaris')->name('sekretaris.')->group(function () {
    // 1. Dasbor Operasional & Administrasi
    Route::get('/dashboard', [SekretarisDashboardController::class, 'index'])->name('dashboard');

    // 2. Verifikasi Pesanan Masuk (PO) & Kunci Kuota Panen
    Route::get('/verifikasi', [SekretarisOrderVerificationController::class, 'index'])->name('verification');
    Route::get('/orders/verify', [SekretarisOrderVerificationController::class, 'index'])->name('orders.verify.index');
    Route::post('/orders/{id}/approve', [SekretarisOrderVerificationController::class, 'approve'])->name('orders.approve');
    Route::post('/orders/{id}/reject', [SekretarisOrderVerificationController::class, 'reject'])->name('orders.reject');

    // 3. Kesiapan Buffer Stock & Monitoring Stok Gudang
    Route::get('/stok', [SekretarisInventoryController::class, 'index'])->name('inventory');

    // 4. Surat Jalan & Dispatch Armada Logistik
    Route::get('/surat-jalan', [SekretarisDispatchController::class, 'index'])->name('dispatch');

    // 5. Faktur Konsolidasi & Penagihan Tempo B2B
    Route::get('/faktur', [SekretarisInvoiceController::class, 'index'])->name('invoices');

    // 6. Verifikasi Pembayaran & Pencocokan Rekening Giro
    Route::get('/pembayaran', [SekretarisPaymentController::class, 'index'])->name('payments');

    // 7. Rekap Laporan & Jurnal Harian Operasional
    Route::get('/laporan', [SekretarisReportController::class, 'index'])->name('reports');

    // 8. Input Pesanan Manual / Darurat Telepon & WA
    Route::get('/pesanan-manual', [SekretarisManualOrderController::class, 'index'])->name('manual-order');
});

// 3. KOORDINATOR LAPANGAN
Route::middleware(['auth', 'role:KOORDINATOR'])->prefix('koordinator')->name('koordinator.')->group(function () {
    Route::get('/dashboard', function () {
        return "Halo Koordinator Lapangan: " . auth()->user()->name;
    })->name('dashboard');

    Route::get('/stock', function () {
        return "Kelola Stok Panen (Koordinator)";
    })->name('stock.index');
    Route::post('/stock', function () {
        return back();
    })->name('stock.store');
});

// 4. ARMADA LOGISTIK / SUPIR
Route::middleware(['auth', 'role:ARMADA'])->prefix('armada')->name('armada.')->group(function () {
    Route::get('/dashboard', function () {
        return "Halo Armada Logistik: " . auth()->user()->name;
    })->name('dashboard');
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

    // 4. Monitoring Piutang, Tagihan Tempo & Risiko Kredit Klien B2B
    Route::get('/receivables', [ReceivableController::class, 'index'])->name('receivables.index');
    Route::post('/receivables/{id}/reminder', [ReceivableController::class, 'sendReminder'])->name('receivables.reminder');
    Route::post('/receivables/{id}/toggle-freeze', [ReceivableController::class, 'toggleFreeze'])->name('receivables.freeze');
    Route::post('/receivables/{id}/restructure', [ReceivableController::class, 'restructure'])->name('receivables.restructure');

    // 5. Otorisasi Kontrak Khusus & Diskon Volume B2B
    Route::get('/contracts/approval', [ContractController::class, 'approval'])->name('contracts.approval');
    Route::post('/contracts/batch-approve', [ContractController::class, 'batchApprove'])->name('contracts.batch-approve');
    Route::post('/contracts/{id}/reject', [ContractController::class, 'reject'])->name('contracts.reject');
    Route::post('/contracts/{id}/revision', [ContractController::class, 'requestRevision'])->name('contracts.revision');
    
    // CRUD Penuh Kontrak Kemitraan
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/create', [ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{id}', [ContractController::class, 'show'])->name('contracts.show');
    Route::get('/contracts/{id}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
    Route::put('/contracts/{id}', [ContractController::class, 'update'])->name('contracts.update');
    Route::delete('/contracts/{id}', [ContractController::class, 'destroy'])->name('contracts.destroy');
    Route::post('/contracts/{id}/approve', [ContractController::class, 'approve'])->name('contracts.approve');
    Route::post('/contracts/{id}/terminate', [ContractController::class, 'terminate'])->name('contracts.terminate');

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
    Route::get('/orders/{id}', [OrderInspectionController::class, 'show'])->name('orders.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
