<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugLivewireUploadController extends Controller
{
    /**
     * Debug Livewire upload issues
     * Access: GET /debug/livewire-upload
     */
    public function index(Request $request)
    {
        $debug = [
            'APP_KEY' => substr(env('APP_KEY', 'NOT_SET'), 0, 20) . '...',
            'APP_ENV' => env('APP_ENV'),
            'APP_URL' => env('APP_URL'),
            'APP_DEBUG' => env('APP_DEBUG'),
            'FILESYSTEM_DISK' => env('FILESYSTEM_DISK'),
            'TIMEZONE' => config('app.timezone'),
            'SERVER_TIME' => now()->format('Y-m-d H:i:s'),
            'SERVER_TIMESTAMP' => time(),
            'REQUEST_URL' => $request->url(),
            'REQUEST_METHOD' => $request->method(),
            'USER_ID' => auth()->id(),
            'IS_AUTHENTICATED' => auth()->check(),
            'SESSION_ID' => session()->getId(),
            'LIVEWIRE_CONFIG' => [
                'middleware' => config('livewire.temporary_file_upload.middleware'),
                'directory' => config('livewire.temporary_file_upload.directory'),
                'disk' => config('livewire.temporary_file_upload.disk'),
            ],
            'STORAGE_PATHS' => [
                'temp_upload_path' => storage_path('app/livewire-tmp'),
                'temp_upload_exists' => is_dir(storage_path('app/livewire-tmp')),
                'temp_upload_writable' => is_writable(storage_path('app/livewire-tmp')),
                'public_path' => storage_path('app/public'),
                'public_exists' => is_dir(storage_path('app/public')),
                'public_writable' => is_writable(storage_path('app/public')),
            ],
            'GATE_DEFINED' => Gate::has('upload-files'),
        ];

        // Log for debugging
        Log::info('Livewire Upload Debug', $debug);

        return response()->json($debug, 200);
    }
}
