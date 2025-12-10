<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ApiClient;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('api', function ($app) {
            return new ApiClient();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load aliases (only if not already declared)
        if (!class_exists('Api', false)) {
            class_alias('App\Facades\Api', 'Api');
        }
    }
}
