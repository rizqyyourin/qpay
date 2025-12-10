<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProductQrController;
use App\Http\Controllers\GuestCheckoutController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Livewire File Upload Routes - Protected
Route::middleware(['web', 'auth'])->group(function () {
    // Livewire auto-registers upload-file route, but we ensure auth here
    Route::post('/livewire/upload-file', function () {
        // This route is handled by Livewire, but middleware ensures auth
    })->name('livewire.upload-file');
});

// Public Routes
Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Public Product Detail (accessible without login for customers to view and add to cart)
Route::get('/shop/product/{product}', function (App\Models\Product $product) {
    return view('shop.product-detail', ['product' => $product]);
})->name('shop.product');

// Guest Checkout Routes (for customers without accounts)
Route::prefix('guest')->name('guest.')->middleware('web')->group(function () {
    Route::get('/product/{product}/start', [GuestCheckoutController::class, 'startSession'])->name('start');
    Route::get('/{token}/cart', [GuestCheckoutController::class, 'showCart'])->name('cart');
    Route::post('/{token}/add-item', [GuestCheckoutController::class, 'addItem'])->name('add-item');
    Route::delete('/{token}/remove/{productId}', [GuestCheckoutController::class, 'removeItem'])->name('remove-item');
    Route::post('/{token}/update-quantity', [GuestCheckoutController::class, 'updateQuantity'])->name('update-quantity');
    Route::post('/{token}/checkout', [GuestCheckoutController::class, 'checkout'])->name('checkout');
    Route::get('/{token}/success', [GuestCheckoutController::class, 'success'])->name('success');
});

Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    // Register Routes
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    // POS Terminal (Cashier)
    Route::get('/pos', function () {
        return view('pos.index');
    })->name('pos');

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrdersController::class, 'index'])->name('index');
        Route::get('/{order}', [OrdersController::class, 'show'])->name('show');
    });

    // Products (All authenticated users - sellers)
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', function () {
            return view('products.index');
        })->name('index');

        Route::get('/{product}/qr', [ProductQrController::class, 'show'])->name('qr');
        Route::get('/{product}', function () {
            return view('products.show');
        })->name('show');
    });

    // Reports (Admin)
    Route::get('/reports', function () {
        return view('reports.index');
    })->middleware('role:admin')->name('reports');

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::post('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
        Route::post('/delete', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
