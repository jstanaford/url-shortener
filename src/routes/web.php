<?php

use App\Http\Controllers\UrlController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyCsrfToken;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// API endpoints for URL shortener - explicitly exclude them from web middleware
Route::prefix('api')->group(function () {
    Route::post('/shorten', [UrlController::class, 'shorten'])
        ->withoutMiddleware([VerifyCsrfToken::class]);
    
    Route::get('/analytics/{shortUri}', [UrlController::class, 'analytics'])
        ->withoutMiddleware([VerifyCsrfToken::class]);
});

// Redirect route for short URLs
Route::get('/s/{shortUri}', [UrlController::class, 'redirect'])->where('shortUri', '[A-Za-z0-9]{6}')->withoutMiddleware([VerifyCsrfToken::class]);

