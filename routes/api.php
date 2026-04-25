<?php

use App\Http\Controllers\AiGenerationController;
use App\Http\Controllers\OrderStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/order/{code}/status', [OrderStatusController::class, 'poll'])->name('api.order.poll');

Route::middleware('throttle:ai-generation')->prefix('ai')->group(function () {
    Route::post('/promo', [AiGenerationController::class, 'promo']);
    Route::post('/product-assets', [AiGenerationController::class, 'productAssets']);
});