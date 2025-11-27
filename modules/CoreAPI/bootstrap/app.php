<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            
            // NGSI-LD API routes (for OLP 2025 - Linked Open Data requirement)
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/ngsi-ld.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (for HTTPS behind Nginx reverse proxy)
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Register admin middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminAuthenticate::class,
            'admin.guest' => \App\Http\Middleware\AdminAuthenticate::class.':guest',
            'admin.track' => \App\Http\Middleware\AdminAuthenticate::class.':track',
            'admin.role' => \App\Http\Middleware\CheckAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
