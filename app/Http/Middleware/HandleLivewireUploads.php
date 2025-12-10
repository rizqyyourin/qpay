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
        // Handle Livewire upload requests
        if ($request->is('livewire/upload-file') || $request->is('livewire/upload-file*')) {
            // Check if user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'message' => 'Unauthorized: Please login to upload files'
                ], 401);
            }

            // Handle OPTIONS/preflight requests
            if ($request->getMethod() === 'OPTIONS') {
                return response()
                    ->json([])
                    ->header('Access-Control-Allow-Origin', $request->header('Origin') ?? config('app.url'))
                    ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                    ->header('Access-Control-Allow-Credentials', 'true');
            }

            // Add CORS headers to response
            return $next($request)
                ->header('Access-Control-Allow-Origin', $request->header('Origin') ?? config('app.url'))
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        return $next($request);
    }
}
