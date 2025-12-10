<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('CartService', function () {
    test('add to cart creates new cart item', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Test Cat', 'description' => 'Test']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Product', 'sku' => 'SKU001', 'barcode' => '1234567890128', 'price' => 100000, 'cost' => 50000, 'stock_quantity' => 100]);

        $cartItem = $cartService->addToCart($user, $product, 2);

        expect($cartItem->user_id)->toBe($user->id);
        expect($cartItem->product_id)->toBe($product->id);
        expect($cartItem->quantity)->toBe(2);
        expect($cartItem->subtotal)->toEqual(200000);
    });

    test('add same product to cart increases quantity', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test2@test.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Test Cat', 'description' => 'Test']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Product', 'sku' => 'SKU002', 'barcode' => '1234567890129', 'price' => 100000, 'cost' => 50000, 'stock_quantity' => 100]);

        $cartService->addToCart($user, $product, 1);
        $cartItem = $cartService->addToCart($user, $product, 2);

        expect($cartItem->quantity)->toBe(3);
        expect($cartItem->subtotal)->toEqual(300000);
    });

    test('update quantity recalculates subtotal', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test3@test.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Test Cat', 'description' => 'Test']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Product', 'sku' => 'SKU003', 'barcode' => '1234567890130', 'price' => 100000, 'cost' => 50000, 'stock_quantity' => 100]);
        $cartItem = $cartService->addToCart($user, $product, 1);
        
        $updated = $cartService->updateQuantity($cartItem, 5);

        expect($updated->quantity)->toBe(5);
        expect($updated->subtotal)->toEqual(500000);
    });

    test('update quantity to 0 or less deletes item', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test4@test.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Test Cat', 'description' => 'Test']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Product', 'sku' => 'SKU004', 'barcode' => '1234567890131', 'price' => 100000, 'cost' => 50000, 'stock_quantity' => 100]);
        $cartItem = $cartService->addToCart($user, $product, 1);
        
        $cartService->updateQuantity($cartItem, 0);

        expect(Cart::where('id', $cartItem->id)->exists())->toBeFalse();
    });

    test('remove from cart deletes item', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test5@test.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Test Cat', 'description' => 'Test']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Product', 'sku' => 'SKU005', 'barcode' => '1234567890132', 'price' => 100000, 'cost' => 50000, 'stock_quantity' => 100]);
        $cartItem = $cartService->addToCart($user, $product, 1);
        
        $cartService->removeFromCart($cartItem);

        expect(Cart::where('id', $cartItem->id)->exists())->toBeFalse();
    });

    test('clear cart removes all items for user', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test6@test.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Test Cat', 'description' => 'Test']);
        $product1 = Product::create(['category_id' => $category->id, 'name' => 'Product1', 'sku' => 'SKU006', 'barcode' => '1234567890133', 'price' => 100000, 'cost' => 50000, 'stock_quantity' => 100]);
        $product2 = Product::create(['category_id' => $category->id, 'name' => 'Product2', 'sku' => 'SKU007', 'barcode' => '1234567890134', 'price' => 50000, 'cost' => 25000, 'stock_quantity' => 100]);
        
        $cartService->addToCart($user, $product1, 1);
        $cartService->addToCart($user, $product2, 1);

        $cartService->clearCart($user);

        expect(Cart::where('user_id', $user->id)->count())->toBe(0);
    });

    test('get cart items returns user items', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test7@test.com', 'password' => 'password']);
        $user2 = User::create(['name' => 'Test2', 'email' => 'test8@test.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Test Cat', 'description' => 'Test']);
        $product1 = Product::create(['category_id' => $category->id, 'name' => 'Product', 'sku' => 'SKU008', 'barcode' => '1234567890135', 'price' => 100000, 'cost' => 50000, 'stock_quantity' => 100]);
        $product2 = Product::create(['category_id' => $category->id, 'name' => 'Product2', 'sku' => 'SKU009', 'barcode' => '1234567890136', 'price' => 50000, 'cost' => 25000, 'stock_quantity' => 100]);
        
        $cartService->addToCart($user, $product1, 1);
        $cartService->addToCart($user, $product2, 2);
        $cartService->addToCart($user2, $product1, 1);

        $items = $cartService->getCartItems($user);

        expect($items)->toHaveCount(2);
        expect($items->pluck('user_id')->unique()->values()->toArray())->toBe([$user->id]);
    });

    test('get cart total sums subtotal', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test9@test.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Test Cat', 'description' => 'Test']);
        $product1 = Product::create(['category_id' => $category->id, 'name' => 'Product1', 'sku' => 'SKU010', 'barcode' => '1234567890137', 'price' => 100000, 'cost' => 50000, 'stock_quantity' => 100]);
        $product2 = Product::create(['category_id' => $category->id, 'name' => 'Product2', 'sku' => 'SKU011', 'barcode' => '1234567890138', 'price' => 50000, 'cost' => 25000, 'stock_quantity' => 100]);
        
        $cartService->addToCart($user, $product1, 2);
        $cartService->addToCart($user, $product2, 1);

        $total = $cartService->getCartTotal($user);

        expect($total)->toEqual(250000);
    });

    test('get cart count returns item count', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test10@test.com', 'password' => 'password']);
        $category = Category::create(['name' => 'Test Cat', 'description' => 'Test']);
        $product1 = Product::create(['category_id' => $category->id, 'name' => 'Product1', 'sku' => 'SKU012', 'barcode' => '1234567890139', 'price' => 100000, 'cost' => 50000, 'stock_quantity' => 100]);
        $product2 = Product::create(['category_id' => $category->id, 'name' => 'Product2', 'sku' => 'SKU013', 'barcode' => '1234567890140', 'price' => 50000, 'cost' => 25000, 'stock_quantity' => 100]);
        
        $cartService->addToCart($user, $product1, 1);
        $cartService->addToCart($user, $product2, 1);

        $count = $cartService->getCartCount($user);

        expect($count)->toBe(2);
    });

    test('get cart count for empty cart returns 0', function () {
        $cartService = new CartService();
        $user = User::create(['name' => 'Test', 'email' => 'test11@test.com', 'password' => 'password']);
        
        $count = $cartService->getCartCount($user);

        expect($count)->toBe(0);
    });
});
