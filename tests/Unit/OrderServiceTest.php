<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('OrderService', function () {
    test('create order from cart items', function () {
        $cartService = new CartService();
        $orderService = new OrderService($cartService);
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'subtotal' => 200000,
        ]);

        $order = $orderService->createOrder($user);

        expect($order->user_id)->toBe($user->id);
        expect($order->total_amount)->toEqual(200000);
        expect($order->payment_status)->toBe('pending');
    });

    test('create order generates unique order number', function () {
        $cartService = new CartService();
        $orderService = new OrderService($cartService);
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 100000]);
        $product2 = Product::factory()->create(['price' => 50000]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
        ]);

        $order1 = $orderService->createOrder($user);

        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $order2 = $orderService->createOrder($user);

        expect($order1->order_number)->not->toBe($order2->order_number);
    });

    test('create order creates order items', function () {
        $cartService = new CartService();
        $orderService = new OrderService($cartService);
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 100000]);
        $product2 = Product::factory()->create(['price' => 50000]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'subtotal' => 200000,
        ]);

        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $order = $orderService->createOrder($user);

        expect($order->orderItems)->toHaveCount(2);
        expect($order->orderItems[0]->quantity)->toBe(2);
        expect($order->orderItems[1]->quantity)->toBe(1);
    });

    test('create order applies discount', function () {
        $cartService = new CartService();
        $orderService = new OrderService($cartService);
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
        ]);

        $order = $orderService->createOrder($user, 10000);

        expect($order->discount_amount)->toEqual(10000);
    });

    test('create order applies tax', function () {
        $cartService = new CartService();
        $orderService = new OrderService($cartService);
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
        ]);

        $order = $orderService->createOrder($user, 0, 10000);

        expect($order->tax_amount)->toEqual(10000);
    });

    test('create order clears cart', function () {
        $cartService = new CartService();
        $orderService = new OrderService($cartService);
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000]);
        
        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
        ]);

        $orderService->createOrder($user);

        expect(Cart::where('user_id', $user->id)->count())->toBe(0);
    });

    test('create order from empty cart throws exception', function () {
        $cartService = new CartService();
        $orderService = new OrderService($cartService);
        $user = User::factory()->create();

        expect(fn () => $orderService->createOrder($user))
            ->toThrow(Exception::class, 'Cart is empty');
    });

    test('get order by id', function () {
        $orderService = new OrderService(new CartService());
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $retrieved = $orderService->getOrderById($order->id);

        expect($retrieved->id)->toBe($order->id);
    });

    test('get user orders', function () {
        $orderService = new OrderService(new CartService());
        $user = User::factory()->create();
        
        Order::factory(3)->create(['user_id' => $user->id]);
        Order::factory(2)->create();

        $orders = $orderService->getUserOrders($user);

        expect($orders)->toHaveCount(3);
    });

    test('update order status', function () {
        $orderService = new OrderService(new CartService());
        $order = Order::factory()->create(['payment_status' => 'pending']);

        $updated = $orderService->updateOrderStatus($order, 'paid');

        expect($updated->payment_status)->toBe('paid');
    });

    test('cancel pending order', function () {
        $orderService = new OrderService(new CartService());
        $order = Order::factory()->create(['payment_status' => 'pending']);

        $orderService->cancelOrder($order);

        expect($order->refresh()->payment_status)->toBe('cancelled');
    });

    test('cancel paid order throws exception', function () {
        $orderService = new OrderService(new CartService());
        $order = Order::factory()->create(['payment_status' => 'paid']);

        expect(fn () => $orderService->cancelOrder($order))
            ->toThrow(Exception::class, 'Only pending orders can be cancelled');
    });
});
