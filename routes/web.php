<?php

use App\Http\Controllers\BuyController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/buy/{product}', [BuyController::class, 'show'])->name('buy.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::get('/order/{code}', [OrderStatusController::class, 'show'])->name('order.status');

Route::get('/dashboard', function () {
    $user = auth()->user();

    try {
        return Inertia::render('Dashboard', [
            'products'         => $user->products()->latest()->get(),
            'ai_enabled'       => (bool) $user->ai_enabled,
            'pending_orders'   => $user->orders()
                ->with('items')
                ->where('status', 'pending')
                ->latest()
                ->get()
                ->map(fn ($order) => [
                    'id'         => $order->id,
                    'code'       => $order->code,
                    'status'     => $order->status,
                    'total'      => $order->total,
                    'created_at' => $order->created_at,
                    'items'      => $order->items->map(fn ($item) => [
                        'name'  => $item->product_name,
                        'price' => $item->price,
                        'qty'   => $item->qty,
                    ]),
                ]),
            'monthly_revenue'  => $user->orders()
                ->where('status', 'confirmed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'monthly_orders'   => $user->orders()
                ->where('status', 'confirmed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'confirmed_orders' => $user->orders()
                ->with('items')
                ->where('status', 'confirmed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->get()
                ->map(fn ($order) => [
                    'code'       => $order->code,
                    'total'      => $order->total,
                    'created_at' => $order->created_at->toIso8601String(),
                    'items'      => $order->items->map(fn ($item) => [
                        'name'  => $item->product_name,
                        'price' => $item->price,
                        'qty'   => $item->qty,
                    ]),
                ]),
        ]);
    } catch (\Throwable $exception) {
        Log::error('Dashboard render failed.', [
            'user_id' => $user?->id,
            'exception' => $exception::class,
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        throw $exception;
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::patch('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/{product}/manual-sale', [ProductController::class, 'manualSale'])->name('products.manual-sale');
    Route::post('/checkout', [ProductController::class, 'checkout'])->name('checkout');
    Route::patch('/settings/ai-toggle', function (\Illuminate\Http\Request $request) {
        $request->user()->update(['ai_enabled' => (bool) $request->input('ai_enabled')]);
        return back();
    })->name('settings.ai-toggle');

    Route::patch('/settings/store-name', function (\Illuminate\Http\Request $request) {
        $request->validate(['name' => ['required', 'string', 'max:255']]);
        $request->user()->update(['name' => $request->input('name')]);
        return back();
    })->name('settings.store-name');

    Route::get('/orders/search/{code}', function (string $code) {
        $order = auth()->user()->orders()
            ->with('items')
            ->where('code', strtoupper($code))
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found for your store.'], 404);
        }

        return response()->json([
            'id'         => $order->id,
            'code'       => $order->code,
            'status'     => $order->status,
            'total'      => $order->total,
            'created_at' => $order->created_at->toIso8601String(),
            'items'      => $order->items->map(fn ($item) => [
                'name'  => $item->product_name,
                'price' => $item->price,
                'qty'   => $item->qty,
            ]),
        ]);
    })->name('orders.search');

    Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
