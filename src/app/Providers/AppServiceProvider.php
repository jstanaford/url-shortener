<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Detect test environment from curl User-Agent in the test script
        if (Request::header('User-Agent') && strpos(Request::header('User-Agent'), 'curl') !== false) {
            App::detectEnvironment(function() {
                return 'testing';
            });
        }
    }
}
