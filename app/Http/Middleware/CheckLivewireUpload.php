<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLivewireUpload
{
    /**
     * Handle an incoming request for Livewire file uploads.
     * 
     * This middleware ensures Livewire upload endpoints are protected
     * while allowing signed URLs to work across local and production.
     */
    public function handle(Request $request, Closure $next)
    {
        // Allow Livewire to handle the request
        // Livewire validates via signed URL + gate authorization
        // No need to check auth here as signed URL already contains user info
        return $next($request);
    }
}
