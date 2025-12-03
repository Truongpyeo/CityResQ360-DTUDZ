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



use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DocsController;

// Test route
Route::get('/test', function () {
    return Inertia::render('Test');
});

// Custom route to serve Swagger JSON directly (Bypasses SwaggerController generation logic)
Route::get('api-docs-json', function () {
    // We serve from public path because storage might be a separate docker volume
    $path = public_path('api-docs.json');

    if (!file_exists($path)) {
        return response()->json(['error' => 'API Docs not found at ' . $path], 404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/json',
        'Access-Control-Allow-Origin' => '*',
    ]);
})->name('l5-swagger.default.docs');

// ==========================================
// CLIENT AUTHENTICATION
// ==========================================
Route::get('/login', [\App\Http\Controllers\Auth\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::get('/register', [\App\Http\Controllers\Auth\AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\AuthController::class, 'register']);

Route::post('/logout', [\App\Http\Controllers\Auth\AuthController::class, 'logout'])->middleware('auth:web')->name('logout');

// ==========================================
// PUBLIC API DOCUMENTATION
// ==========================================
Route::prefix('documents')->name('docs.')->group(function () {
    Route::get('/', [DocsController::class, 'index'])->name('index');
    Route::get('/{service}', [DocsController::class, 'show'])->name('service');
});

// ==========================================
// CLIENT PORTAL
// ==========================================
Route::middleware(['auth:web'])->prefix('client')->name('client.')->group(function () {
    // Dashboard / Homepage
    Route::get('/', [\App\Http\Controllers\Client\ClientPortalController::class, 'dashboard'])->name('dashboard');

    // Module Integration
    Route::get('/modules', [\App\Http\Controllers\Client\ClientPortalController::class, 'modules'])->name('modules');
    Route::get('/modules/{moduleKey}/register', [\App\Http\Controllers\Client\ClientPortalController::class, 'registerForm'])->name('modules.register');
    Route::post('/modules/{moduleKey}/register', [\App\Http\Controllers\Client\ClientPortalController::class, 'registerModule'])->name('modules.register.submit');

    // API Keys & Usage
    Route::get('/api-keys', [\App\Http\Controllers\Client\ClientPortalController::class, 'apiKeys'])->name('api-keys');
    Route::post('/credentials/{id}/refresh-secret', [\App\Http\Controllers\Client\ClientPortalController::class, 'refreshSecret'])->name('credentials.refresh-secret');
    Route::get('/usage', [\App\Http\Controllers\Client\ClientPortalController::class, 'usage'])->name('usage');
});

// Public Landing Page
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');
