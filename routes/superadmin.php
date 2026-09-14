<?php

use App\Http\Controllers\Auth\SuperAdminLoginController;
use App\Http\Controllers\SuperAdmin\AuditController;
use App\Http\Controllers\SuperAdmin\BusinessController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\FinanceController;
use App\Http\Controllers\SuperAdmin\RoleController;
use App\Http\Controllers\SuperAdmin\SettingController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('superadmin')->name('superadmin.')->group(function () {

    Route::get('/login',   [SuperAdminLoginController::class, 'show'])->name('login');
    Route::post('/login',  [SuperAdminLoginController::class, 'login'])->name('login.attempt');
    Route::post('/logout', [SuperAdminLoginController::class, 'logout'])->name('logout');

    Route::middleware(['superadmin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/businesses',                     [BusinessController::class, 'index'])->name('businesses');
        Route::post('/businesses',                    [BusinessController::class, 'store'])->name('businesses.store');
        Route::put('/businesses/{business}',          [BusinessController::class, 'update'])->name('businesses.update');
        Route::post('/businesses/{business}/toggle',  [BusinessController::class, 'toggle'])->name('businesses.toggle');

        Route::get('/users',                            [UserController::class, 'index'])->name('users');
        Route::post('/users/{user}/toggle',             [UserController::class, 'toggle'])->name('users.toggle');
        Route::post('/users/{user}/reset-password',     [UserController::class, 'resetPassword'])->name('users.reset-password');

        Route::get('/subscriptions',                              [SubscriptionController::class, 'index'])->name('subscriptions');
        Route::post('/subscriptions/{business}/pay',              [SubscriptionController::class, 'recordPayment'])->name('subscriptions.pay');

        Route::get('/finance',  [FinanceController::class, 'index'])->name('finance');
        Route::get('/roles',    [RoleController::class, 'index'])->name('roles');
        Route::get('/audit',    [AuditController::class, 'index'])->name('audit');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    });
});
