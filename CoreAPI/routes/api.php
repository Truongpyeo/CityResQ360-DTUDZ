<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\VoteController;
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
        // TODO: Route::get('/', [AgencyController::class, 'index']);
        // TODO: Route::get('{id}', [AgencyController::class, 'show']);
        // TODO: Route::get('{id}/reports', [AgencyController::class, 'reports']);
        // TODO: Route::get('{id}/stats', [AgencyController::class, 'stats']);
    });

    // Public user profiles
    Route::prefix('users')->group(function () {
        // TODO: Route::get('{id}', [UserController::class, 'show']);
        // TODO: Route::get('{id}/reports', [UserController::class, 'reports']);
        // TODO: Route::get('{id}/stats', [UserController::class, 'stats']);
    });

    // Public statistics
    // TODO: Route::get('stats/city', [StatsController::class, 'cityStats']);
    // TODO: Route::get('stats/leaderboard', [StatsController::class, 'leaderboard']);

    // ==========================================
    // PROTECTED ROUTES (Authentication Required)
    // ==========================================

    Route::middleware('auth:sanctum')->group(function () {

        // ========== Authentication Management ==========
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
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
            // TODO: Route::get('reports', [MapController::class, 'reports']);
            // TODO: Route::get('heatmap', [MapController::class, 'heatmap']);
            // TODO: Route::get('clusters', [MapController::class, 'clusters']);
            // TODO: Route::get('routes', [MapController::class, 'gtfsRoutes']);
        });

        // ========== Wallet & CityPoints ==========
        Route::prefix('wallet')->group(function () {
            // TODO: Route::get('/', [WalletController::class, 'balance']);
            // TODO: Route::get('transactions', [WalletController::class, 'transactions']);
            // TODO: Route::post('redeem', [WalletController::class, 'redeem']);
            // TODO: Route::get('rewards', [WalletController::class, 'rewards']);
        });

        // ========== Notifications ==========
        Route::prefix('notifications')->group(function () {
            // TODO: Route::get('/', [NotificationController::class, 'index']);
            // TODO: Route::get('unread', [NotificationController::class, 'unread']);
            // TODO: Route::get('unread-count', [NotificationController::class, 'unreadCount']);
            // TODO: Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
            // TODO: Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
            // TODO: Route::delete('{id}', [NotificationController::class, 'destroy']);
            // TODO: Route::put('settings', [NotificationController::class, 'updateSettings']);
        });

        // ========== User Statistics ==========
        Route::prefix('stats')->group(function () {
            // TODO: Route::get('overview', [StatsController::class, 'overview']);
            // TODO: Route::get('categories', [StatsController::class, 'categoriesStats']);
            // TODO: Route::get('timeline', [StatsController::class, 'timeline']);
        });
    });
});
