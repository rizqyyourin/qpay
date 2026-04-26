<?php

use App\Http\Controllers\AiGenerationController;
use App\Http\Controllers\OrderStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/order/{code}/status', [OrderStatusController::class, 'poll'])->name('api.order.poll');
Route::post('/order/{code}/cancel', [OrderStatusController::class, 'cancel'])->name('api.order.cancel');
Route::post('/order/{code}/approve', [OrderStatusController::class, 'approve'])->name('api.order.approve');

Route::middleware('throttle:ai-generation')->prefix('ai')->group(function () {
    Route::post('/promo', [AiGenerationController::class, 'promo']);
    Route::post('/product-assets', [AiGenerationController::class, 'productAssets']);
});