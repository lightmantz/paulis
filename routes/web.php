<?php

use App\Http\Controllers\Auth\BusinessLoginController;
use App\Http\Controllers\Business\ActivityController;
use App\Http\Controllers\Business\CustomerController;
use App\Http\Controllers\Business\DashboardController;
use App\Http\Controllers\Business\ExpenseController;
use App\Http\Controllers\Business\FinancialController;
use App\Http\Controllers\Business\InventoryController;
use App\Http\Controllers\Business\PosController;
use App\Http\Controllers\Business\ProfileController;
use App\Http\Controllers\Business\PurchaseController;
use App\Http\Controllers\Business\RepairController;
use App\Http\Controllers\Business\ReportController;
use App\Http\Controllers\Business\ReturnController;
use App\Http\Controllers\Business\SaleController;
use App\Http\Controllers\Business\SettingController;
use App\Http\Controllers\Business\StockTakeController;
use App\Http\Controllers\Business\SupplierController;
use App\Http\Controllers\Business\UserAccessController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// ── Public landing ──────────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');

// ── Business registration (rate-limited) ────────────────────────
Route::get('/register',  [LandingController::class, 'showRegister'])->name('business.register.show');
Route::post('/register', [LandingController::class, 'register'])
    ->middleware('throttle:register')
    ->name('business.register');

// ── Business auth ───────────────────────────────────────────────
Route::get('/login',   [BusinessLoginController::class, 'show'])->name('business.login');
Route::post('/login',  [BusinessLoginController::class, 'login'])->name('business.login.attempt');
Route::post('/logout', [BusinessLoginController::class, 'logout'])->name('business.logout');

// ── Business app ────────────────────────────────────────────────
Route::middleware(['business'])->prefix('app')->name('business.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── POS ─────────────────────────────────────────────────────
    Route::get('/pos',  [PosController::class, 'index'])->name('pos');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');

    // ── Sales list + exports ────────────────────────────────────
    Route::get('/sales',              [SaleController::class, 'index'])->name('sales');
    Route::get('/sales/export/csv',   [SaleController::class, 'exportCsv'])->name('sales.export.csv');
    Route::get('/sales/export/excel', [SaleController::class, 'exportExcel'])->name('sales.export.excel');
    Route::get('/sales/export/pdf',   [SaleController::class, 'exportPdf'])->name('sales.export.pdf');

    // ── Inventory ───────────────────────────────────────────────
    Route::get('/inventory',              [InventoryController::class, 'index'])->name('inventory');
    Route::post('/inventory',             [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{product}',    [InventoryController::class, 'show'])->name('inventory.show');
    Route::put('/inventory/{product}',    [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{product}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    // ── Customers ───────────────────────────────────────────────
    Route::get('/customers',              [CustomerController::class, 'index'])->name('customers');
    Route::post('/customers',             [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}',   [CustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}',   [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}',[CustomerController::class, 'destroy'])->name('customers.destroy');

    // ── Suppliers ───────────────────────────────────────────────
    Route::get('/suppliers',              [SupplierController::class, 'index'])->name('suppliers');
    Route::post('/suppliers',             [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}',   [SupplierController::class, 'show'])->name('suppliers.show');
    Route::put('/suppliers/{supplier}',   [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}',[SupplierController::class, 'destroy'])->name('suppliers.destroy');

    // ── Expenses ────────────────────────────────────────────────
    Route::get('/expenses',  [ExpenseController::class, 'index'])->name('expenses');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('/expenses/{expense}',              [ExpenseController::class, 'show'])->name('expenses.show');
    Route::post('/expenses/{expense}/approve',    [ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::put('/expenses/{expense}',              [ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('/expenses/{expense}',           [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // ── Financial Information ───────────────────────────────────
    Route::get('/financial', [FinancialController::class, 'index'])->name('financial');

    // ── Profile ─────────────────────────────────────────────────
    Route::get('/profile',           [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile',           [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

    // ── Users & Access ──────────────────────────────────────────
    Route::get('/users',  [UserAccessController::class, 'index'])->name('users');
    Route::post('/users', [UserAccessController::class, 'store'])->name('users.store');

    // ── Activity History ────────────────────────────────────────
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity');

    // ── Settings ────────────────────────────────────────────────
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // ── Repair, Maintenance & Service ───────────────────────────
    Route::get('/service',                [RepairController::class, 'index'])->name('service');
    Route::post('/service',               [RepairController::class, 'store'])->name('service.store');
    Route::get('/service/{job}',          [RepairController::class, 'show'])->name('service.show');
    Route::post('/service/{job}/status',  [RepairController::class, 'updateStatus'])->name('service.status');
    Route::post('/service/{job}/parts',   [RepairController::class, 'addPart'])->name('service.parts');
    Route::post('/service/{job}/invoice', [RepairController::class, 'updateInvoice'])->name('service.invoice');

    // ── Purchases ───────────────────────────────────────────────
    Route::get('/purchases',                 [PurchaseController::class, 'index'])->name('purchases');
    Route::post('/purchases/orders',         [PurchaseController::class, 'storeOrder'])->name('purchases.orders.store');
    Route::post('/purchases/{order}/receive',[PurchaseController::class, 'receive'])->name('purchases.receive');
    Route::get('/purchases/{purchase}',            [PurchaseController::class, 'show'])->name('purchases.show');
    Route::put('/purchases/{purchase}',            [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/purchases/{purchase}',         [PurchaseController::class, 'destroy'])->name('purchases.destroy');

    // ── Stock Taking ────────────────────────────────────────────
    Route::get('/stock-taking',                [StockTakeController::class, 'index'])->name('stock-taking');
    Route::post('/stock-taking/start',         [StockTakeController::class, 'startNew'])->name('stock-taking.start');
    Route::post('/stock-taking/{take}/count',  [StockTakeController::class, 'updateCount'])->name('stock-taking.count');
    Route::post('/stock-taking/{take}/post',   [StockTakeController::class, 'post'])->name('stock-taking.post');
    Route::delete('/stock-taking/{take}',      [StockTakeController::class, 'destroy'])->name('stock-taking.destroy');

    // ── Returns ─────────────────────────────────────────────────
    Route::get('/returns',                    [ReturnController::class, 'index'])->name('returns');
    Route::post('/returns',                   [ReturnController::class, 'store'])->name('returns.store');
    Route::post('/returns/{return}/approve',  [ReturnController::class, 'approve'])->name('returns.approve');
    Route::post('/returns/{return}/reject',   [ReturnController::class, 'reject'])->name('returns.reject');

    // ── Reports ─────────────────────────────────────────────────
    Route::get('/reports',          [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
});
