<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleLivewireUploads
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow Livewire upload requests if user is authenticated
        if ($request->is('livewire/upload-file*')) {
            // Ensure user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'message' => 'Unauthorized: Please login to upload files'
                ], 401);
            }

            // Allow CORS preflight requests
            if ($request->getMethod() === 'OPTIONS') {
                return response()
                    ->json([])
                    ->header('Access-Control-Allow-Origin', config('app.url'))
                    ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            }
        }

        return $next($request);
    }
}
