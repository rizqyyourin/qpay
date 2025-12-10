<?php

use App\Http\Controllers\Api\GuestOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Guest Order API (Public - for POS cashier)
Route::get('/guest-orders/{token}', [GuestOrderController::class, 'show']);
Route::post('/guest-orders/{token}/complete', [GuestOrderController::class, 'complete']);

// Product API
Route::get('/products', function () {
    $ids = request()->query('ids', '');
    if (empty($ids)) {
        return response()->json([]);
    }
    
    $productIds = explode(',', $ids);
    $products = \App\Models\Product::whereIn('id', $productIds)->get(['id', 'stock']);
    
    return response()->json($products);
});

Route::middleware('auth:sanctum')->group(function () {
    // Product routes (browsing)
    Route::prefix('products')->name('api.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        Route::get('/barcode/{barcode}', [ProductController::class, 'byBarcode'])->name('barcode');
        Route::get('/category/{categoryId}', [ProductController::class, 'byCategory'])->name('category');
        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
    });

    // Cart routes
    Route::prefix('cart')->name('api.cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/', [CartController::class, 'store'])->name('store');
        Route::put('/{cartId}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cartId}', [CartController::class, 'destroy'])->name('destroy');
        Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    });

    // Order routes
    Route::prefix('orders')->name('api.orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{orderId}', [OrderController::class, 'show'])->name('show');
        Route::post('/{orderId}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    });

    // Payment routes
    Route::prefix('payments')->name('api.payments.')->group(function () {
        Route::get('/order/{orderId}', [PaymentController::class, 'show'])->name('show');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::post('/{orderId}/refund', [PaymentController::class, 'refund'])->name('refund');
        Route::get('/{orderId}/calculate-change', [PaymentController::class, 'calculateChange'])->name('calculate-change');
    });
});
