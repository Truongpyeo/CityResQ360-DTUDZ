<?php
/*
 * CityResQ360-DTUDZ - Smart City Emergency Response System
 * Copyright (C) 2025 DTU-DZ Team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */



use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AgencyController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AnalyticsController;
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
    Route::middleware('admin.guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
    });

    // Logout (can be accessed by authenticated users)
    Route::middleware('admin')->post('logout', [AuthController::class, 'logout'])->name('logout');

    // Protected Admin Routes
    Route::middleware(['admin:track'])->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', function() {
            return redirect()->route('admin.dashboard');
        });

        // Analytics
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('comparison', [AnalyticsController::class, 'comparison'])->name('comparison');
        });

        // Profile
        Route::get('profile', [AuthController::class, 'profile'])->name('profile');
        Route::put('profile', [AuthController::class, 'updateProfile'])->name('profile.update');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('change-password');

        // Reports Management
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::patch('status/{id}', [ReportController::class, 'updateStatus'])->name('update-status');
            Route::patch('priority/{id}', [ReportController::class, 'updatePriority'])->name('update-priority');
            Route::get('export', [ReportController::class, 'export'])->name('export');
            Route::get('{id}', [ReportController::class, 'show'])->name('show');
            Route::delete('{id}', [ReportController::class, 'destroy'])->name('destroy');
        });

        // Users Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('update/{id}', [UserController::class, 'update'])->name('update');
            Route::patch('status/{id}', [UserController::class, 'updateStatus'])->name('update-status');
            Route::post('verify/{id}', [UserController::class, 'verify'])->name('verify');
            Route::post('points/{id}', [UserController::class, 'addPoints'])->name('add-points');
            Route::get('export', [UserController::class, 'export'])->name('export');
            Route::get('{id}', [UserController::class, 'show'])->name('show');
            Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
        });

        // Agencies Management (SuperAdmin & Data Admin only)
        Route::prefix('agencies')->name('agencies.')->group(function () {
            Route::get('/', [AgencyController::class, 'index'])->name('index');
            Route::get('create', [AgencyController::class, 'create'])->name('create');
            Route::post('/', [AgencyController::class, 'store'])->name('store');
            Route::get('export', [AgencyController::class, 'export'])->name('export');
            Route::get('{id}', [AgencyController::class, 'show'])->name('show');
            Route::get('{id}/edit', [AgencyController::class, 'edit'])->name('edit');
            Route::patch('{id}', [AgencyController::class, 'update'])->name('update');
            Route::delete('{id}', [AgencyController::class, 'destroy'])->name('destroy');
        });

        // Admins Management (SuperAdmin only)
        Route::prefix('admins')->name('admins.')->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('index');
            Route::get('create', [AdminController::class, 'create'])->name('create');
            Route::post('/', [AdminController::class, 'store'])->name('store');
            Route::get('{id}', [AdminController::class, 'show'])->name('show');
            Route::get('edit/{id}', [AdminController::class, 'edit'])->name('edit');
            Route::patch('update/{id}', [AdminController::class, 'update'])->name('update');
            Route::patch('status/{id}', [AdminController::class, 'updateStatus'])->name('update-status');
            Route::post('role/{id}', [AdminController::class, 'updateRole'])->name('update-role');
            Route::post('password/{id}', [AdminController::class, 'changePassword'])->name('change-password');
            Route::delete('delete/{id}', [AdminController::class, 'destroy'])->name('destroy');
        });

        // Permissions Management (SuperAdmin only)
        Route::prefix('permissions')->name('permissions.')->group(function () {
            // Roles
            Route::get('roles', [PermissionController::class, 'roles'])->name('roles');
            Route::get('roles/create', [PermissionController::class, 'createRole'])->name('roles.create');
            Route::post('roles', [PermissionController::class, 'storeRole'])->name('roles.store');
            Route::get('roles/edit/{id}', [PermissionController::class, 'editRole'])->name('roles.edit');
            Route::patch('roles/update/{id}', [PermissionController::class, 'updateRole'])->name('roles.update');
            Route::delete('roles/delete/{id}', [PermissionController::class, 'destroyRole'])->name('roles.destroy');
            Route::get('roles/assign/{id}', [PermissionController::class, 'assignPermissions'])->name('roles.assign');
            Route::post('roles/assign/{id}', [PermissionController::class, 'updatePermissions'])->name('roles.update-permissions');

            // Functions
            Route::get('functions', [PermissionController::class, 'functions'])->name('functions');
            Route::get('functions/create', [PermissionController::class, 'createFunction'])->name('functions.create');
            Route::post('functions', [PermissionController::class, 'storeFunction'])->name('functions.store');
            Route::get('functions/edit/{id}', [PermissionController::class, 'editFunction'])->name('functions.edit');
            Route::patch('functions/update/{id}', [PermissionController::class, 'updateFunction'])->name('functions.update');
            Route::delete('functions/delete/{id}', [PermissionController::class, 'destroyFunction'])->name('functions.destroy');
        });
    });
});
