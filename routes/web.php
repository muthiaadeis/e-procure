<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RlpController;
use App\Http\Controllers\JobCodeController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;

Route::get('/', function () {
    return redirect()->route('material-requests.index');
});


Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'force.password.change'])->name('dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'force.password.change', 'restrict.admin'])->name('dashboard');

Route::get('/', function () {
    if (auth()->check() && auth()->user()->is_admin) {
        return redirect()->route('admin.users.index');
    }

    return redirect()->route('material-requests.index');
});

Route::middleware(['auth', 'force.password.change'])->group(function () {
    Route::resource('material-requests', MaterialRequestController::class);
    Route::get('material-requests/{materialRequest}/print', [MaterialRequestController::class, 'printPdf'])
    ->name('material-requests.print');
    Route::patch('material-requests/{materialRequest}/mark-paid', [MaterialRequestController::class, 'markPaid'])
        ->name('material-requests.mark-paid');
    Route::patch('material-requests/{materialRequest}/approve', [MaterialRequestController::class, 'approve'])
        ->name('material-requests.approve');
    Route::patch('material-requests/{materialRequest}/reject', [MaterialRequestController::class, 'reject'])
    ->name('material-requests.reject');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('rlps', RlpController::class)->except(['show']);
    Route::get('rlps/{rlp}/print', [RlpController::class, 'printPdf'])->name('rlps.print');
    Route::patch('rlps/{rlp}/review', [RlpController::class, 'review'])->name('rlps.review');
    Route::patch('rlps/{rlp}/acknowledge', [RlpController::class, 'acknowledge'])->name('rlps.acknowledge');
    Route::patch('rlps/{rlp}/approve', [RlpController::class, 'approve'])->name('rlps.approve');

    Route::resource('job-codes', JobCodeController::class)->except(['show']);
    Route::resource('vendors', VendorController::class)->except(['show']);

    // Modul PR & PO belum dibangun, sementara cuma halaman placeholder
    // biar sidebar & menunya udah kelihatan lengkap.
        Route::resource('purchase-requests', PurchaseRequestController::class);
    Route::get('purchase-requests/{purchaseRequest}/print', [PurchaseRequestController::class, 'printPdf'])
        ->name('purchase-requests.print');
    Route::post('purchase-requests/{purchaseRequest}/approvals/{approval}/sign', [PurchaseRequestController::class, 'sign'])
        ->name('purchase-requests.sign');
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::get('purchase-orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'printPdf'])
        ->name('purchase-orders.print');
    Route::post('purchase-orders/{purchaseOrder}/approvals/{approval}/sign', [PurchaseOrderController::class, 'sign'])
        ->name('purchase-orders.sign');

    // Global Search & Topbar Notifications
    Route::get('/search/quick', [GlobalSearchController::class, 'search'])->name('search.quick');
    Route::get('/notifications/feed', [GlobalSearchController::class, 'notifications'])->name('notifications.feed');
});

// Halaman ganti password wajib — HARUS di luar grup 'force.password.change'
// di atas, supaya user yang lagi wajib ganti password tetap bisa membukanya.
Route::middleware('auth')->group(function () {
    Route::get('/force-password-change', [ForcePasswordChangeController::class, 'show'])
        ->name('password.force-change');
    Route::put('/force-password-change', [ForcePasswordChangeController::class, 'update'])
        ->name('password.force-change.update');
});

// Halaman admin: kelola user & reset password
Route::middleware(['auth', 'admin', 'force.password.change'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
        ->name('users.reset-password');
});

require __DIR__.'/auth.php';
