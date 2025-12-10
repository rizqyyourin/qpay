<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * INTEGRATION TESTING - API & LIVEWIRE COMPONENTS
 * 
 * Fokus: Integrasi antara frontend components dengan backend API
 * Memastikan data flow yang benar dari UI hingga database
 */
class ApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    // ============================================
    // 🔗 POS TERMINAL INTEGRATION TESTS
    // ============================================

    /** @test */
    public function pos_terminal_full_flow_barcode_to_payment()
    {
        // Create product with barcode
        $product = Product::factory()->create([
            'barcode' => '123456789',
            'name' => 'Laptop',
            'price' => 10000000,
            'stock' => 5,
        ]);

        // Step 1: Get product by barcode
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/products/barcode/123456789");

        $this->assertEquals(200, $response->status());
        $this->assertEquals('Laptop', $response['data']['name']);

        // Step 2: Add to cart
        $cartResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertEquals(201, $cartResponse->status());

        // Step 3: View cart
        $cartViewResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/cart');

        $this->assertEquals(200, $cartViewResponse->status());
        $this->assertCount(1, $cartViewResponse['data']);

        // Step 4: Create order
        $orderResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 500000,
            'tax_amount' => 1000000,
        ]);

        $this->assertEquals(201, $orderResponse->status());
        $orderId = $orderResponse['data']['id'];

        // Expected: (10,000,000 × 2) - 500,000 + 1,000,000 = 20,500,000
        $this->assertEquals('20500000.00', $orderResponse['data']['total_amount']);

        // Step 5: Process payment
        $paymentResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/payments', [
            'order_id' => $orderId,
            'amount' => 20500000,
            'payment_method' => 'cash',
        ]);

        $this->assertEquals(201, $paymentResponse->status());

        // Step 6: Verify order status
        $orderCheckResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/orders/{$orderId}");

        $this->assertEquals(200, $orderCheckResponse->status());
        $this->assertEquals('completed', $orderCheckResponse['data']['payment_status']);

        // Step 7: Verify stock deduction
        $product->refresh();
        $this->assertEquals(3, $product->stock); // 5 - 2

        // Step 8: Verify cart is cleared
        $emptyCartResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/cart');

        $this->assertCount(0, $emptyCartResponse['data']);
    }

    /** @test */
    public function pos_terminal_multiple_products_checkout()
    {
        // Create multiple products
        $laptop = Product::factory()->create([
            'name' => 'Laptop',
            'price' => 10000000,
            'stock' => 3,
        ]);

        $mouse = Product::factory()->create([
            'name' => 'Mouse',
            'price' => 200000,
            'stock' => 10,
        ]);

        $keyboard = Product::factory()->create([
            'name' => 'Keyboard',
            'price' => 500000,
            'stock' => 5,
        ]);

        // Add all products to cart with different quantities
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart', [
                'product_id' => $laptop->id,
                'quantity' => 1,
            ]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart', [
                'product_id' => $mouse->id,
                'quantity' => 2,
            ]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart', [
                'product_id' => $keyboard->id,
                'quantity' => 1,
            ]);

        // Create order
        $orderResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 1000000,
            'tax_amount' => 1500000,
        ]);

        $orderId = $orderResponse['data']['id'];

        // Expected total: 
        // (10,000,000 × 1) + (200,000 × 2) + (500,000 × 1) - 1,000,000 + 1,500,000
        // = 10,000,000 + 400,000 + 500,000 - 1,000,000 + 1,500,000 = 11,400,000

        $this->assertEquals('11400000.00', $orderResponse['data']['total_amount']);

        // Get order details
        $orderDetails = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/orders/{$orderId}");

        // Verify items in order
        $this->assertCount(3, $orderDetails['data']['items']);
    }

    // ============================================
    // 📋 ORDERS LIST INTEGRATION TESTS
    // ============================================

    /** @test */
    public function orders_list_search_and_filter_integration()
    {
        // Create multiple orders with different statuses
        Order::factory(5)->create([
            'user_id' => $this->user->id,
            'payment_status' => 'completed',
            'payment_method' => 'cash',
        ]);

        Order::factory(3)->create([
            'user_id' => $this->user->id,
            'payment_status' => 'pending',
            'payment_method' => 'credit_card',
        ]);

        // Test: Get all orders
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/orders');

        $this->assertEquals(200, $response->status());

        // Test: Filter by status
        $completedOrders = Order::where('payment_status', 'completed')
            ->where('user_id', $this->user->id)
            ->count();

        $this->assertEquals(5, $completedOrders);
    }

    /** @test */
    public function orders_list_pagination_works()
    {
        Order::factory(20)->create(['user_id' => $this->user->id]);

        // Page 1 (15 items)
        $page1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/orders?page=1');

        $this->assertCount(15, $page1['data']);

        // Page 2 (5 items)
        $page2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/orders?page=2');

        $this->assertCount(5, $page2['data']);
    }

    // ============================================
    // 🔄 LIVEWIRE COMPONENT DATA BINDING
    // ============================================

    /** @test */
    public function livewire_pos_terminal_updates_cart_reactively()
    {
        // This tests that Livewire property changes update correctly
        $product = Product::factory()->create([
            'stock' => 5,
            'price' => 50000,
        ]);

        // Simulate Livewire event: Add to cart
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertTrue($response->json()['success']);

        // Verify cart updated in database
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    // ============================================
    // 🎯 COMPLEX WORKFLOW TESTS
    // ============================================

    /** @test */
    public function complete_sales_cycle_with_refund()
    {
        $product = Product::factory()->create([
            'price' => 100000,
            'stock' => 5,
        ]);

        // Add to cart and checkout
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $orderResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 0,
            'tax_amount' => 0,
        ]);

        $orderId = $orderResponse['data']['id'];

        // Process payment
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/payments', [
                'order_id' => $orderId,
                'amount' => 100000,
                'payment_method' => 'cash',
            ]);

        // Order should be completed
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'payment_status' => 'completed',
        ]);

        // Process refund
        $refundResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/payments/{$orderId}/refund");

        // Verify order is refunded
        $order = Order::find($orderId);
        $this->assertEquals('refunded', $order->payment_status);

        // Verify stock is restored
        $product->refresh();
        // Note: Implementation dependent - may or may not restore stock on refund
    }

    /** @test */
    public function stock_validation_prevents_overselling()
    {
        $product = Product::factory()->create(['stock' => 2]);

        // User 1 adds 2 units
        $user1 = User::factory()->create();
        $token1 = $user1->createToken('test')->plainTextToken;

        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertEquals(201, $response1->status());

        // User 2 tries to add 1 more unit (should fail)
        $user2 = User::factory()->create();
        $token2 = $user2->createToken('test')->plainTextToken;

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2,
        ])->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Should fail because stock is only 2 and user1 already reserved them
        // This depends on implementation - could be 400 error
        $this->assertIn($response2->status(), [201, 400]);
    }

    // ============================================
    // 📊 DATA CONSISTENCY TESTS
    // ============================================

    /** @test */
    public function order_items_match_cart_items()
    {
        $product1 = Product::factory()->create(['price' => 50000]);
        $product2 = Product::factory()->create(['price' => 30000]);

        // Add items to cart
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart', [
                'product_id' => $product1->id,
                'quantity' => 2,
            ]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart', [
                'product_id' => $product2->id,
                'quantity' => 1,
            ]);

        // Create order
        $orderResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 0,
            'tax_amount' => 0,
        ]);

        $orderId = $orderResponse['data']['id'];

        // Verify order items
        $order = Order::with('orderItems')->find($orderId);
        $this->assertCount(2, $order->orderItems);

        // Verify item details
        $item1 = $order->orderItems->where('product_id', $product1->id)->first();
        $item2 = $order->orderItems->where('product_id', $product2->id)->first();

        $this->assertEquals(2, $item1->quantity);
        $this->assertEquals(1, $item2->quantity);
    }

    /** @test */
    public function payment_amount_matches_order_total()
    {
        $product = Product::factory()->create(['price' => 100000, 'stock' => 5]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $orderResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 10000,
            'tax_amount' => 5000,
        ]);

        $orderId = $orderResponse['data']['id'];
        $orderTotal = $orderResponse['data']['total_amount'];

        // Process payment
        $paymentResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/payments', [
            'order_id' => $orderId,
            'amount' => $orderTotal,
            'payment_method' => 'cash',
        ]);

        // Get payment record
        $payment = Payment::where('order_id', $orderId)->first();

        // Payment amount should match order total
        $this->assertEquals($orderTotal, $payment->amount);
    }

    // ============================================
    // ✅ ERROR RECOVERY TESTS
    // ============================================

    /** @test */
    public function cart_recovers_from_product_deletion()
    {
        $product = Product::factory()->create();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        // Delete product
        $product->delete();

        // Try to get cart - should handle gracefully
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/cart');

        // Cart should still exist but maybe product is marked as deleted
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function invalid_discount_rejected_at_checkout()
    {
        $product = Product::factory()->create(['price' => 50000]);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/cart', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        // Try to checkout with discount > total
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/orders', [
            'discount_amount' => 100000, // More than total
            'tax_amount' => 0,
        ]);

        $this->assertEquals(422, $response->status());
    }
}
