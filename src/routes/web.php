<?php

use App\Http\Controllers\UrlController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Http\Middleware\VerifyCsrfToken;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Analytics dashboard
Route::get('/dashboard', function () {
    return view('analytics');
});

// API endpoints for URL shortener - explicitly exclude them from web middleware
Route::prefix('api')->group(function () {
    Route::post('/shorten', [UrlController::class, 'shorten'])
        ->withoutMiddleware([VerifyCsrfToken::class]);
    
    Route::get('/analytics/{shortUri}', [UrlController::class, 'analytics'])
        ->withoutMiddleware([VerifyCsrfToken::class]);
        
    Route::get('/analytics', [UrlController::class, 'allAnalytics'])
        ->withoutMiddleware([VerifyCsrfToken::class]);
});

// Optimized redirect route for short URLs
Route::get('/s/{shortUri}', [UrlController::class, 'redirect'])
    ->where('shortUri', '[A-Za-z0-9]{6}')
    ->middleware(['throttle:1000,1']) // Allow high traffic but prevent abuse
    ->withoutMiddleware([VerifyCsrfToken::class]);

