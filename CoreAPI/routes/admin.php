<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AgencyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
| All routes use the 'admin' prefix and authentication middleware.
|
*/

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Login/Logout (Guest only)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware(['admin'])->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index']);

        // Reports Management
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('{id}', [ReportController::class, 'show'])->name('show');
            Route::patch('{id}/status', [ReportController::class, 'updateStatus'])->name('update-status');
            Route::patch('{id}/priority', [ReportController::class, 'updatePriority'])->name('update-priority');
            Route::delete('{id}', [ReportController::class, 'destroy'])->name('destroy');
        });

        // Users Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('{id}', [UserController::class, 'show'])->name('show');
            Route::patch('{id}/status', [UserController::class, 'updateStatus'])->name('update-status');
            Route::post('{id}/verify', [UserController::class, 'verify'])->name('verify');
            Route::post('{id}/points', [UserController::class, 'addPoints'])->name('add-points');
            Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
        });

        // Agencies Management (SuperAdmin & Data Admin only)
        Route::middleware(['admin.role:0,1'])->prefix('agencies')->name('agencies.')->group(function () {
            Route::get('/', [AgencyController::class, 'index'])->name('index');
            Route::get('create', [AgencyController::class, 'create'])->name('create');
            Route::post('/', [AgencyController::class, 'store'])->name('store');
            Route::get('{id}', [AgencyController::class, 'show'])->name('show');
            Route::get('{id}/edit', [AgencyController::class, 'edit'])->name('edit');
            Route::patch('{id}', [AgencyController::class, 'update'])->name('update');
            Route::delete('{id}', [AgencyController::class, 'destroy'])->name('destroy');
        });
    });
});
