<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Test route
Route::get('/test', function () {
    return Inertia::render('Test');
});

// CoreAPI chỉ là Admin Panel - redirect root về admin login
Route::get('/', function () {
    return redirect()->route('admin.login');
})->name('home');
