<?php

use App\Http\Controllers\Client\CartController as ClientCartController;
use App\Http\Controllers\Client\CatalogController as ClientCatalogController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DocumentsController as ClientDocumentsController;
use App\Http\Controllers\Client\OrdersController as ClientOrdersController;
use App\Http\Controllers\Client\PaymentProofController as ClientPaymentProofController;
use App\Http\Controllers\Coordinator\CoordinatorDashboardController;
use App\Http\Controllers\Coordinator\CoordinatorDispatchController;
use App\Http\Controllers\Coordinator\CoordinatorHarvestPlanController;
use App\Http\Controllers\Coordinator\CoordinatorMonitoringController;
use App\Http\Controllers\Coordinator\CoordinatorStockController;
use App\Http\Controllers\Coordinator\CoordinatorWeighingController;
use App\Http\Controllers\Director\DirectorApprovalController;
use App\Http\Controllers\Director\DirectorDashboardController;
use App\Http\Controllers\Director\DirectorGovernanceController;
use App\Http\Controllers\Director\DirectorReceivablesController;
use App\Http\Controllers\Director\DirectorReportController;
use App\Http\Controllers\Director\DirectorSalesController;
use App\Http\Controllers\Director\DirectorVolumeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\SecretaryDashboardController;
use App\Http\Controllers\Staff\SecretaryDispatchController;
use App\Http\Controllers\Staff\SecretaryInventoryController;
use App\Http\Controllers\Staff\SecretaryInvoiceController;
use App\Http\Controllers\Staff\SecretaryManualOrderController;
use App\Http\Controllers\Staff\SecretaryOrderVerificationController;
use App\Http\Controllers\Staff\SecretaryPaymentController;
use App\Http\Controllers\Staff\SecretaryReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', ClientDashboardController::class)
    ->name('dashboard');

Route::get('/konsol-koordinator', CoordinatorDashboardController::class)
    ->name('coordinator.dashboard');

Route::get('/konsol-koordinator/penimbangan', CoordinatorWeighingController::class)
    ->name('coordinator.weighing');

Route::get('/konsol-koordinator/stok', CoordinatorStockController::class)
    ->name('coordinator.stock');

Route::get('/konsol-koordinator/rencana-panen', CoordinatorHarvestPlanController::class)
    ->name('coordinator.harvest');

Route::get('/konsol-koordinator/monitoring', CoordinatorMonitoringController::class)
    ->name('coordinator.monitoring');

Route::get('/konsol-koordinator/surat-jalan', CoordinatorDispatchController::class)
    ->name('coordinator.dispatch');

Route::get('/direktur', DirectorDashboardController::class)
    ->name('director.dashboard');

Route::get('/direktur/penjualan', DirectorSalesController::class)
    ->name('director.sales');

Route::get('/direktur/volume-komoditas', DirectorVolumeController::class)
    ->name('director.volume');

Route::get('/direktor/laporan', DirectorReportController::class)
    ->name('director.report');

Route::get('/direktor/piutang-tagihan', DirectorReceivablesController::class)
    ->name('director.receivables');

Route::get('/direktor/persetujuan-kontrak', DirectorApprovalController::class)
    ->name('director.approval');

Route::get('/direktor/pengaturan-tata-kelola', DirectorGovernanceController::class)
    ->name('director.governance');

Route::get('/konsol-sekretaris', SecretaryDashboardController::class)
    ->name('secretary.dashboard');

Route::get('/konsol-sekretaris/verifikasi-pesanan', SecretaryOrderVerificationController::class)
    ->name('secretary.verification');

Route::get('/konsol-sekretaris/input-order-manual', SecretaryManualOrderController::class)
    ->name('secretary.verification.manual');

Route::get('/konsol-sekretaris/stok', SecretaryInventoryController::class)
    ->name('secretary.inventory');

Route::get('/konsol-sekretaris/surat-jalan', SecretaryDispatchController::class)
    ->name('secretary.dispatch');

Route::get('/konsol-sekretaris/faktur-tagihan', SecretaryInvoiceController::class)
    ->name('secretary.invoicing');

Route::get('/konsol-sekretaris/verifikasi-pembayaran', SecretaryPaymentController::class)
    ->name('secretary.payments');

Route::get('/konsol-sekretaris/rekap-laporan', SecretaryReportController::class)
    ->name('secretary.reports');

Route::get('/katalog', ClientCatalogController::class)
    ->name('catalog');

Route::get('/keranjang', ClientCartController::class)
    ->name('cart');

Route::get('/pesanan', ClientOrdersController::class)
    ->name('orders');

Route::get('/dokumen-faktur', ClientDocumentsController::class)
    ->name('documents');

Route::get('/dokumen-faktur/unggah-bukti', ClientPaymentProofController::class)
    ->name('documents.payment-proof');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
