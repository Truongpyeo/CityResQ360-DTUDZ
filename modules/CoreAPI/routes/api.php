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



use App\Http\Controllers\Api\V1\AgencyController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\MapController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VoteController;
use App\Http\Controllers\Api\V1\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| RESTful API for CityResQ360 Mobile App (React Native)
| Base URL: /api/v1
| Authentication: Laravel Sanctum (Bearer Token)
|
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'CoreAPI',
        'timestamp' => now()->toIso8601String()
    ]);
});

Route::prefix('v1')->group(function () {

    // ==========================================
    // PUBLIC ROUTES (No Authentication)
    // ==========================================

    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Public agencies (read-only)
    Route::prefix('agencies')->group(function () {
        Route::get('/', [AgencyController::class, 'index']);
        Route::get('{id}', [AgencyController::class, 'show']);
        Route::get('{id}/reports', [AgencyController::class, 'reports']);
        Route::get('{id}/stats', [AgencyController::class, 'stats']);
    });

    // Public user profiles
    Route::prefix('users')->group(function () {
        Route::get('{id}', [UserController::class, 'show']);
        Route::get('{id}/reports', [UserController::class, 'reports']);
        Route::get('{id}/stats', [UserController::class, 'stats']);
    });

    // Public statistics
    Route::get('stats/city', [UserController::class, 'cityStats']);
    Route::get('stats/leaderboard', [UserController::class, 'leaderboard']);

    // Public categories & priorities
    Route::prefix('categories')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
        Route::get('{id}', [\App\Http\Controllers\Api\V1\CategoryController::class, 'show']);
    });
    
    Route::get('priorities', [\App\Http\Controllers\Api\V1\CategoryController::class, 'priorities']);

    // ==========================================
    // PROTECTED ROUTES (Authentication Required)
    // ==========================================

    Route::middleware('auth:sanctum')->group(function () {

        // ========== Authentication Management ==========
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::get('check-login', [AuthController::class, 'checkLogin']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::put('profile', [AuthController::class, 'updateProfile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::post('verify-email', [AuthController::class, 'verifyEmail']);
            Route::post('verify-phone', [AuthController::class, 'verifyPhone']);
            Route::post('update-fcm-token', [AuthController::class, 'updateFcmToken']);
        });

        // ========== Reports Management ==========
        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index']);
            Route::post('/', [ReportController::class, 'store']);
            Route::get('my', [ReportController::class, 'myReports']);
            Route::get('nearby', [ReportController::class, 'nearby']);
            Route::get('trending', [ReportController::class, 'trending']);
            Route::get('{id}', [ReportController::class, 'show']);
            Route::put('{id}', [ReportController::class, 'update']);
            Route::delete('{id}', [ReportController::class, 'destroy']);
            Route::post('{id}/vote', [VoteController::class, 'vote']);
            Route::post('{id}/view', [ReportController::class, 'incrementView']);
            Route::post('{id}/rate', [ReportController::class, 'rate']);

            // Comments on reports
            Route::get('{id}/comments', [CommentController::class, 'index']);
            Route::post('{id}/comments', [CommentController::class, 'store']);
        });

        // ========== Comments Management ==========
        Route::prefix('comments')->group(function () {
            Route::put('{id}', [CommentController::class, 'update']);
            Route::delete('{id}', [CommentController::class, 'destroy']);
            Route::post('{id}/like', [CommentController::class, 'like']);
            Route::delete('{id}/like', [CommentController::class, 'unlike']);
        });

        // ========== Media Management ==========
        Route::prefix('media')->group(function () {
            Route::post('upload', [MediaController::class, 'upload']);
            Route::get('my', [MediaController::class, 'myMedia']);
            Route::get('{id}', [MediaController::class, 'show']);
            Route::delete('{id}', [MediaController::class, 'destroy']);
        });

        // ========== Map & Location Services ==========
        Route::prefix('map')->group(function () {
            Route::get('reports', [MapController::class, 'reports']);
            Route::get('heatmap', [MapController::class, 'heatmap']);
            Route::get('clusters', [MapController::class, 'clusters']);
            Route::get('routes', [MapController::class, 'gtfsRoutes']);
        });

        // ========== Wallet & CityPoints ==========
        Route::prefix('wallet')->group(function () {
            Route::get('/', [WalletController::class, 'balance']);
            Route::get('transactions', [WalletController::class, 'transactions']);
            Route::post('redeem', [WalletController::class, 'redeem']);
            Route::get('rewards', [WalletController::class, 'rewards']);
        });

        // ========== Notifications ==========
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('unread', [NotificationController::class, 'unread']);
            Route::get('unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{id}', [NotificationController::class, 'destroy']);
            Route::put('settings', [NotificationController::class, 'updateSettings']);
        });

        // ========== User Statistics ==========
        Route::prefix('stats')->group(function () {
            Route::get('overview', [UserController::class, 'overview']);
            Route::get('categories', [UserController::class, 'categoriesStats']);
            Route::get('timeline', [UserController::class, 'timeline']);
        });

        // ========== Weather Data ==========
        Route::prefix('weather')->group(function () {
            Route::get('current', [\App\Http\Controllers\Api\V1\WeatherController::class, 'current']);
            Route::get('forecast', [\App\Http\Controllers\Api\V1\WeatherController::class, 'forecast']);
            Route::get('history', [\App\Http\Controllers\Api\V1\WeatherController::class, 'history']);
            Route::post('sync', [\App\Http\Controllers\Api\V1\WeatherController::class, 'sync']);
        });
    });
});
