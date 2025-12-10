<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Cart Management', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'price' => 100000,
            'stock_quantity' => 10,
        ]);
    });

    test('user can add product to cart', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cart', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Product added to cart')
            ->assertJsonPath('cart_item.quantity', 2)
            ->assertJsonPath('cart_count', 1);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    });

    test('adding same product increases quantity', function () {
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => $this->product->price,
            'subtotal' => $this->product->price,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/cart', [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);
    });

    test('user can view cart items', function () {
        Cart::factory(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'items')
            ->assertJsonPath('count', 3)
            ->assertJsonStructure([
                'items' => [
                    '*' => [
                        'id',
                        'product_id',
                        'quantity',
                        'subtotal',
                    ],
                ],
                'total',
                'count',
            ]);
    });

    test('user can update cart item quantity', function () {
        $cartItem = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price' => $this->product->price,
            'subtotal' => $this->product->price,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/cart/{$cartItem->id}", [
                'quantity' => 5,
            ]);

        $response->assertStatus(200);
        expect($response->json('quantity'))->toBe(5);
    });

    test('user can remove item from cart', function () {
        $cartItem = Cart::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/cart/{$cartItem->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Item removed from cart');

        $this->assertDatabaseMissing('carts', [
            'id' => $cartItem->id,
        ]);
    });

    test('user can clear entire cart', function () {
        Cart::factory(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/cart/clear');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cart cleared');

        $this->assertDatabaseCount('carts', 0);
    });

    test('cart items are isolated per user', function () {
        $user2 = User::factory()->create();

        Cart::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Cart::factory()->create([
            'user_id' => $user2->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cart');

        $response->assertJsonCount(1, 'items');
    });

    test('unauthenticated user cannot access cart', function () {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    });
});
