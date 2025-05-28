<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
       // api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Configure the API middleware group to not include web middleware
        // which will exclude VerifyCsrfToken and authentication middleware
        
        // Exclude specific routes from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'shorten',
            '/shorten',
            'analytics/*',
            '/analytics/*',
            'analytics',
            '/analytics',
            's/*',
            '/s/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
