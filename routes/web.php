<?php

use App\Http\Controllers\Auth\BusinessLoginController;
use App\Http\Controllers\Business\ActivityController;
use App\Http\Controllers\Business\CustomerController;
use App\Http\Controllers\Business\DashboardController;
use App\Http\Controllers\Business\ExpenseController;
use App\Http\Controllers\Business\FinancialController;
use App\Http\Controllers\Business\InventoryController;
use App\Http\Controllers\Business\PosController;
use App\Http\Controllers\Business\RepairController;
use App\Http\Controllers\Business\SettingController;
use App\Http\Controllers\Business\SupplierController;
use App\Http\Controllers\Business\UserAccessController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Business\PurchaseController;
use App\Http\Controllers\Business\StockTakeController;
use App\Http\Controllers\Business\ReturnController;
use App\Http\Controllers\Business\ReportController;
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

    // POS
    Route::get('/pos',          [PosController::class, 'index'])->name('pos');
    Route::post('/pos',         [PosController::class, 'store'])->name('pos.store');

    // Inventory
    Route::get('/inventory',    [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory',   [InventoryController::class, 'store'])->name('inventory.store');

    // Customers
    Route::get('/customers',    [CustomerController::class, 'index'])->name('customers');
    Route::post('/customers',   [CustomerController::class, 'store'])->name('customers.store');

    // Suppliers
    Route::get('/suppliers',    [SupplierController::class, 'index'])->name('suppliers');
    Route::post('/suppliers',   [SupplierController::class, 'store'])->name('suppliers.store');

    // Expenses
    Route::get('/expenses',     [ExpenseController::class, 'index'])->name('expenses');
    Route::post('/expenses',    [ExpenseController::class, 'store'])->name('expenses.store');

    // Financial
    Route::get('/financial',    [FinancialController::class, 'index'])->name('financial');

    // Users
    Route::get('/users',        [UserAccessController::class, 'index'])->name('users');
    Route::post('/users',       [UserAccessController::class, 'store'])->name('users.store');

    // Activity History
    Route::get('/activity',     [ActivityController::class, 'index'])->name('activity');

    // Settings
    Route::get('/settings',     [SettingController::class, 'index'])->name('settings');
    Route::put('/settings',     [SettingController::class, 'update'])->name('settings.update');

    // ── Repair, Maintenance & Service ─────────────────────────
    Route::get('/service',                [RepairController::class, 'index'])->name('service');
    Route::post('/service',               [RepairController::class, 'store'])->name('service.store');
    Route::get('/service/{job}',          [RepairController::class, 'show'])->name('service.show');
    Route::post('/service/{job}/status',  [RepairController::class, 'updateStatus'])->name('service.status');
    Route::post('/service/{job}/parts',   [RepairController::class, 'addPart'])->name('service.parts');
    Route::post('/service/{job}/invoice', [RepairController::class, 'updateInvoice'])->name('service.invoice');
    Route::get('/purchases',                 [PurchaseController::class, 'index'])->name('purchases');
    Route::post('/purchases/orders',         [PurchaseController::class, 'storeOrder'])->name('purchases.orders.store');
    Route::post('/purchases/{order}/receive',[PurchaseController::class, 'receive'])->name('purchases.receive');
    Route::get('/stock-taking',                [StockTakeController::class, 'index'])->name('stock-taking');
    Route::post('/stock-taking/start',         [StockTakeController::class, 'startNew'])->name('stock-taking.start');
    Route::post('/stock-taking/{take}/count',  [StockTakeController::class, 'updateCount'])->name('stock-taking.count');
    Route::post('/stock-taking/{take}/post',   [StockTakeController::class, 'post'])->name('stock-taking.post');
    Route::get('/returns',                    [ReturnController::class, 'index'])->name('returns');
    Route::post('/returns',                   [ReturnController::class, 'store'])->name('returns.store');
    Route::post('/returns/{return}/approve',  [ReturnController::class, 'approve'])->name('returns.approve');
    Route::post('/returns/{return}/reject',   [ReturnController::class, 'reject'])->name('returns.reject');
    Route::get('/reports',         [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/{report}',[ReportController::class, 'show'])->name('reports.show');

});
