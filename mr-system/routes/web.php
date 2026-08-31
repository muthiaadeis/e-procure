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

Route::get('/', function () {
    return redirect()->route('material-requests.index');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
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
    Route::resource('job-codes', JobCodeController::class)->except(['show']);
    Route::resource('vendors', VendorController::class)->except(['show']);

    // Modul PR & PO belum dibangun, sementara cuma halaman placeholder
    // biar sidebar & menunya udah kelihatan lengkap.
    Route::get('purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
});

require __DIR__.'/auth.php';
