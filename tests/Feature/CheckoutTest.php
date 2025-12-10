<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Order Checkout', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->product1 = Product::factory()->create([
            'price' => 100000,
        ]);
        $this->product2 = Product::factory()->create([
            'price' => 50000,
        ]);
    });

    test('user can create order from cart', function () {
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'subtotal' => 200000,
        ]);

        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product2->id,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/orders', [
                'discount_amount' => 0,
                'tax_amount' => 0,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Order created successfully')
            ->assertJsonStructure([
                'order' => [
                    'id',
                    'order_number',
                    'total_amount',
                    'discount_amount',
                    'tax_amount',
                    'payment_status',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => 250000,
            'payment_status' => 'pending',
        ]);

        // Cart should be cleared
        $this->assertDatabaseCount('carts', 0);
    });

    test('order number is generated uniquely', function () {
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
        ]);

        $response1 = $this->actingAs($this->user)
            ->postJson('/api/orders', []);

        $order1 = Order::first();
        $orderNumber1 = $order1->order_number;

        // Create new cart for second order
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product2->id,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $response2 = $this->actingAs($this->user)
            ->postJson('/api/orders', []);

        $order2 = Order::orderByDesc('id')->first();
        $orderNumber2 = $order2->order_number;

        $this->assertNotEquals($orderNumber1, $orderNumber2);
    });

    test('order items are created from cart items', function () {
        $cartItem1 = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'unit_price' => 100000,
            'subtotal' => 200000,
        ]);

        $cartItem2 = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product2->id,
            'quantity' => 1,
            'unit_price' => 50000,
            'subtotal' => 50000,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/orders', []);

        $order = Order::first();

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product2->id,
            'quantity' => 1,
        ]);
    });

    test('order cannot be created from empty cart', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/orders', []);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Cart is empty');
    });

    test('user can view order details', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
        ]);

        OrderItem::factory(2)->create([
            'order_id' => $order->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $order->id)
            ->assertJsonPath('order_number', $order->order_number);
    });

    test('user can view their orders', function () {
        $user2 = User::factory()->create();

        Order::factory(3)->create(['user_id' => $this->user->id]);
        Order::factory(2)->create(['user_id' => $user2->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'orders')
            ->assertJsonPath('count', 3);
    });

    test('user can apply discount to order', function () {
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 1,
            'unit_price' => 100000,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/orders', [
                'discount_amount' => 10000,
                'tax_amount' => 0,
            ]);

        $response->assertStatus(201);

        $order = Order::first();
        $this->assertEquals(10000, $order->discount_amount);
    });

    test('user can cancel pending order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Order cancelled successfully');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'cancelled',
        ]);
    });

    test('user cannot cancel paid order', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(400);
    });

    test('unauthenticated user cannot checkout', function () {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(401);
    });
});
