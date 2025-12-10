<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

/**
 * USER ACCEPTANCE TESTING - QPAY SYSTEM
 * 
 * Test Coverage:
 * ✓ User Authentication & Authorization
 * ✓ Product Management (Browse, Search, Barcode)
 * ✓ Shopping Cart (Add, Update, Remove)
 * ✓ Order Management (Create, View, Cancel)
 * ✓ Payment Processing
 * ✓ Livewire Components (PosTerminal, OrdersList)
 * ✓ Error Handling & Validation
 */
class UserAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    // ============================================
    // 🔐 AUTHENTICATION TESTS
    // ============================================

    /** @test */
    public function user_can_login_successfully()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
    }

    /** @test */
    public function user_cannot_access_api_without_token()
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_access_protected_routes_with_valid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/products');

        $response->assertStatus(200);
    }

    // ============================================
    // 📦 PRODUCT MANAGEMENT TESTS
    // ============================================

    /** @test */
    public function user_can_view_all_products()
    {
        Product::factory(10)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'links']);
    }

    /** @test */
    public function user_can_search_products_by_name()
    {
        Product::factory()->create(['name' => 'Laptop Dell']);
        Product::factory()->create(['name' => 'iPhone 15']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/products/search?q=Laptop');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function user_can_find_product_by_barcode()
    {
        $product = Product::factory()->create([
            'barcode' => '1234567890',
            'name' => 'Test Product',
            'price' => 50000,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/products/barcode/1234567890");

        $response->assertStatus(200);
        $response->assertJsonFragment(['barcode' => '1234567890']);
    }

    /** @test */
    public function user_gets_404_for_nonexistent_barcode()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/products/barcode/nonexistent");

        $response->assertStatus(404);
    }

    /** @test */
    public function user_can_view_product_details()
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $product->id]);
    }

    // ============================================
    // 🛒 SHOPPING CART TESTS
    // ============================================

    /** @test */
    public function user_can_add_product_to_cart()
    {
        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 50000,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201);
        
        // Verify cart was created
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    /** @test */
    public function user_cannot_add_product_out_of_stock()
    {
        $product = Product::factory()->create(['stock' => 0]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'Product out of stock']);
    }

    /** @test */
    public function user_cannot_exceed_available_stock_in_cart()
    {
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function user_can_view_their_cart()
    {
        $product = Product::factory()->create(['stock' => 10]);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/cart');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function user_can_update_cart_quantity()
    {
        $product = Product::factory()->create(['stock' => 10]);
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/cart/{$cart->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'quantity' => 5,
        ]);
    }

    /** @test */
    public function user_can_remove_item_from_cart()
    {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/cart/{$cart->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    /** @test */
    public function user_can_clear_entire_cart()
    {
        Product::factory(3)->create();
        Cart::factory(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cart/clear');

        $response->assertStatus(200);
        $this->assertDatabaseCount('carts', 0);
    }

    // ============================================
    // 📋 ORDER MANAGEMENT TESTS
    // ============================================

    /** @test */
    public function user_can_create_order_from_cart()
    {
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 5000,
            'tax_amount' => 9000,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'order_number', 'total_amount']);
    }

    /** @test */
    public function user_cannot_create_order_with_empty_cart()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 0,
            'tax_amount' => 0,
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function user_can_view_their_orders()
    {
        Order::factory(5)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function user_can_view_order_details()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $order->id]);
    }

    /** @test */
    public function user_can_cancel_pending_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'cancelled',
        ]);
    }

    /** @test */
    public function user_cannot_cancel_completed_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'completed',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(400);
    }

    // ============================================
    // 💳 PAYMENT TESTS
    // ============================================

    /** @test */
    public function user_can_process_payment_for_order()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 100000,
            'payment_status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/payments', [
            'order_id' => $order->id,
            'amount' => 100000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function user_can_calculate_change_for_cash_payment()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 50000,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/payments/{$order->id}/calculate-change?received_amount=100000");

        $response->assertStatus(200);
        $response->assertJsonFragment(['change' => 50000]);
    }

    /** @test */
    public function user_can_refund_payment()
    {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'payment_status' => 'completed',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/payments/{$order->id}/refund");

        $response->assertStatus(200);
    }

    // ============================================
    // 💾 DATA VALIDATION TESTS
    // ============================================

    /** @test */
    public function cart_quantity_must_be_positive_integer()
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => -5,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function discount_cannot_exceed_total_amount()
    {
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 999999,
            'tax_amount' => 0,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function order_total_calculation_is_correct()
    {
        $product1 = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $product2 = Product::factory()->create(['price' => 30000, 'stock' => 10]);
        
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product1->id,
            'quantity' => 2, // 100,000
        ]);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product2->id,
            'quantity' => 1, // 30,000
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 10000,
            'tax_amount' => 12000,
        ]);

        // Expected: 100,000 + 30,000 - 10,000 + 12,000 = 132,000
        $response->assertJsonFragment(['total_amount' => '132000.00']);
    }

    // ============================================
    // 🎯 EDGE CASES & ERROR HANDLING
    // ============================================

    /** @test */
    public function product_stock_decreases_after_order()
    {
        $product = Product::factory()->create(['stock' => 5]);
        Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 0,
            'tax_amount' => 0,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 3,
        ]);
    }

    /** @test */
    public function multiple_users_have_isolated_carts()
    {
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        // Add to cart for user 1
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Check user 2 cart is empty
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $user2->createToken('test')->plainTextToken,
        ])->getJson('/api/cart');

        $response->assertJsonCount(0, 'data');
    }

    /** @test */
    public function concurrent_stock_updates_prevent_overselling()
    {
        $product = Product::factory()->create(['stock' => 1]);
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Both users try to buy the same product
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $user1->createToken('test')->plainTextToken,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $user2->createToken('test')->plainTextToken,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // One should fail due to insufficient stock
        $this->assertTrue(
            ($response1->status() === 201 && $response2->status() === 400) ||
            ($response1->status() === 400 && $response2->status() === 201)
        );
    }

    // ============================================
    // 🧬 LIVEWIRE COMPONENT TESTS
    // ============================================

    /** @test */
    public function pos_terminal_component_renders()
    {
        $response = $this->get('/pos');
        $response->assertStatus(200);
        $response->assertViewIs('pos');
    }

    /** @test */
    public function orders_list_component_renders()
    {
        $response = $this->get('/orders');
        $response->assertStatus(200);
        $response->assertViewIs('orders');
    }
}
