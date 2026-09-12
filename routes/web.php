<?php

use App\Http\Controllers\Auth\BusinessLoginController;
use App\Http\Controllers\Business\CustomerController;
use App\Http\Controllers\Business\DashboardController;
use App\Http\Controllers\Business\ExpenseController;
use App\Http\Controllers\Business\FinancialController;
use App\Http\Controllers\Business\InventoryController;
use App\Http\Controllers\Business\SupplierController;
use App\Http\Controllers\Business\UserAccessController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/register',  [LandingController::class, 'showRegister'])->name('business.register.show');
Route::post('/register', [LandingController::class, 'register'])->name('business.register');

// Business auth
Route::get('/login',   [BusinessLoginController::class, 'show'])->name('business.login');
Route::post('/login',  [BusinessLoginController::class, 'login'])->name('business.login.attempt');
Route::post('/logout', [BusinessLoginController::class, 'logout'])->name('business.logout');

// Business app
Route::middleware(['business'])->prefix('app')->name('business.')->group(function () {
    Route::get('/dashboard',    [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/inventory',    [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory',   [InventoryController::class, 'store'])->name('inventory.store');

    Route::get('/customers',    [CustomerController::class, 'index'])->name('customers');
    Route::post('/customers',   [CustomerController::class, 'store'])->name('customers.store');

    Route::get('/suppliers',    [SupplierController::class, 'index'])->name('suppliers');
    Route::post('/suppliers',   [SupplierController::class, 'store'])->name('suppliers.store');

    Route::get('/expenses',     [ExpenseController::class, 'index'])->name('expenses');
    Route::post('/expenses',    [ExpenseController::class, 'store'])->name('expenses.store');

    Route::get('/financial',    [FinancialController::class, 'index'])->name('financial');

    Route::get('/users',        [UserAccessController::class, 'index'])->name('users');
    Route::post('/users',       [UserAccessController::class, 'store'])->name('users.store');

    // Placeholders for later phases
    Route::view('/pos',          'business.coming-soon', ['page' => 'Point of Sale', 'phase' => 6])->name('pos');
    Route::view('/service',      'business.coming-soon', ['page' => 'Repair, Maintenance & Service', 'phase' => 7])->name('service');
    Route::view('/purchases',    'business.coming-soon', ['page' => 'Purchases', 'phase' => 7])->name('purchases');
    Route::view('/stock-taking', 'business.coming-soon', ['page' => 'Stock Taking', 'phase' => 8])->name('stock-taking');
    Route::view('/returns',      'business.coming-soon', ['page' => 'Returns', 'phase' => 8])->name('returns');
    Route::view('/reports',      'business.coming-soon', ['page' => 'Reports', 'phase' => 8])->name('reports');
    Route::view('/activity',     'business.coming-soon', ['page' => 'Activity History', 'phase' => 8])->name('activity');
    Route::view('/settings',     'business.coming-soon', ['page' => 'Settings', 'phase' => 8])->name('settings');
});
