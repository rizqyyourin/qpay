<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;

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
        // Register layout components
        Blade::component('layouts.app', 'app-layout');
        Blade::component('layouts.auth', 'auth-layout');
        Blade::component('layouts.public', 'public-layout');

        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            // Force URL generation to use HTTPS
            URL::forceRootUrl(config('app.url'));
        }

        // Gate untuk Livewire file uploads
        // Livewire calls this gate to authorize file uploads
        // The signed URL ensures the request comes from an authenticated user
        Gate::define('upload-files', function ($user) {
            // User must be authenticated (checked via signed URL)
            // If $user is null, the signed URL was invalid or expired
            return $user !== null;
        });
    }
}
