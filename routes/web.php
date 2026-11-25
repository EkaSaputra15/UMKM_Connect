<?php

use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StoreSettingController;
use App\Http\Controllers\TransactionController;
use App\Livewire\CustomerIndex;
use App\Livewire\TransactionCreate;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    // Shared routes: Owner & Cashier
    Route::middleware('role:owner,cashier')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/customers', CustomerIndex::class)->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

        Route::get('/transactions/create', TransactionCreate::class)->name('transactions.create');
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::post('/transactions/{transaction}/resend-receipt', [TransactionController::class, 'resendReceipt'])->name('transactions.resend-receipt');
    });

    // Owner-only routes
    Route::middleware('role:owner')->group(function () {
        Route::get('/broadcasts', [BroadcastController::class, 'index'])->name('broadcasts.index');
        Route::post('/broadcasts', [BroadcastController::class, 'store'])->name('broadcasts.store');
        Route::post('/broadcasts/{broadcast}/resend', [BroadcastController::class, 'resend'])->name('broadcasts.resend');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::patch('/staff/{user}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
        Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');

        Route::get('/settings', [StoreSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [StoreSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-email', [StoreSettingController::class, 'sendTestEmail'])->name('settings.test-email');
    });

    // User personal profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
