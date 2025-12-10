<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
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

        // Configure Livewire for file uploads
        // Maximum file size for uploads (10MB)
        Livewire::configureFileUploads(
            maxUploadSize: 10 * 1024 * 1024, // 10MB
            disk: 'local',
            directory: 'livewire-tmp'
        );

        // Gate untuk Livewire file uploads
        // Verifikasi signed URL via Gate - Livewire akan call ini sebelum upload
        Gate::define('livewire-upload', function ($user) {
            // Allow upload jika user authenticated
            // Livewire signed URLs sudah handle authorization
            return true;
        });
    }
}
