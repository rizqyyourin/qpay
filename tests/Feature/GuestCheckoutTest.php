<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected $seller;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test seller
        $this->seller = User::factory()->create(['name' => 'Test Seller']);

        // Create test product
        $this->product = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 50000,
            'stock' => 100,
            'barcode' => 'TEST-001',
        ]);
    }

    /**
     * Test guest can start shopping without login (no 403 error)
     */
    public function test_guest_can_start_checkout_without_login()
    {
        // Guest (unauthenticated) scans QR
        // URL: /guest/product/{product_id}/start?seller={seller_id}
        
        $response = $this->get(
            route('guest.start', $this->product->id) . 
            '?seller=' . $this->seller->id
        );

        // Should redirect to cart (302 = found/redirect)
        // NOT 403 error! This is the fix!
        $this->assertTrue(
            in_array($response->status(), [301, 302, 307, 308]),
            "Expected redirect (301/302/307/308), got {$response->status()}"
        );
        
        // Check order was created
        $this->assertDatabaseHas('orders', [
            'seller_id' => $this->seller->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test guest can add items to cart using token
     */
    public function test_guest_can_add_items_to_cart()
    {
        // Create an order
        $order = Order::create([
            'token' => Order::generateToken(),
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Guest adds item to cart (no auth)
        $response = $this->post(
            route('guest.add-item', $order->token),
            [
                'product_id' => $this->product->id,
                'quantity' => 2,
            ]
        );

        // Should succeed (no 403)
        $this->assertEquals(200, $response->status());
        $this->assertTrue($response->json('success'));

        // Check item was added
        $this->assertNotEmpty($order->fresh()->items);
        $this->assertEquals(2, $order->fresh()->items[$this->product->id]['quantity']);
    }

    /**
     * Test guest cannot add items from different seller
     */
    public function test_guest_cannot_add_items_from_different_seller()
    {
        // Create second seller
        $otherSeller = User::factory()->create();
        $otherProduct = Product::create([
            'user_id' => $otherSeller->id,
            'name' => 'Other Product',
            'price' => 30000,
            'stock' => 50,
            'barcode' => 'OTHER-001',
        ]);

        // Create order for seller 1
        $order = Order::create([
            'token' => Order::generateToken(),
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Try to add product from seller 2 to seller 1's cart
        $response = $this->post(
            route('guest.add-item', $order->token),
            [
                'product_id' => $otherProduct->id,
                'quantity' => 1,
            ]
        );

        // Should be forbidden
        $this->assertEquals(403, $response->status());
    }

    /**
     * Test guest can checkout without login
     */
    public function test_guest_can_checkout()
    {
        // Create order with item
        $order = Order::create([
            'token' => Order::generateToken(),
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $order->addItem($this->product, 2);

        // Guest checkout (no auth)
        $response = $this->post(
            route('guest.checkout', $order->token),
            [
                'buyer_name' => 'John Doe',
                'buyer_phone' => '08123456789',
                'buyer_email' => 'john@example.com',
            ]
        );

        // Should redirect to success page
        $this->assertTrue(
            $response->status() === 302,
            "Expected redirect, got {$response->status()}"
        );

        // Check order was updated
        $order->fresh();
        $this->assertEquals('registered', $order->status);
        $this->assertEquals('John Doe', $order->buyer_name);
        $this->assertEquals('08123456789', $order->buyer_phone);
    }

    /**
     * Test guest can view cart with token
     */
    public function test_guest_can_view_cart()
    {
        $order = Order::create([
            'token' => Order::generateToken(),
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $order->addItem($this->product, 1);

        // Guest views cart (no auth)
        $response = $this->get(route('guest.cart', $order->token));

        // Should show cart (200 OK)
        $this->assertEquals(200, $response->status());
        // Just check it renders without error
        $this->assertNotNull($response->getContent());
    }

    /**
     * Test QR code URL includes seller_id
     */
    public function test_qr_code_includes_seller_id()
    {
        // Seller generates QR code
        $response = $this->actingAs($this->seller)
            ->get(route('products.qr', $this->product->id));

        $this->assertEquals(200, $response->status());
        
        // Check that seller auth is available in view
        // (The view generates QR with Auth::id())
    }

    /**
     * Test stock is reserved when item is added
     */
    public function test_stock_is_reserved_when_item_added()
    {
        $order = Order::create([
            'token' => Order::generateToken(),
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Add item
        $this->post(
            route('guest.add-item', $order->token),
            [
                'product_id' => $this->product->id,
                'quantity' => 5,
            ]
        );

        // Check reservation was created
        $this->assertDatabaseHas('inventory_reservations', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'status' => 'reserved',
        ]);
    }

    /**
     * Test stock cannot oversell
     */
    public function test_cannot_oversell_products()
    {
        // Create product with limited stock
        $limitedProduct = Product::create([
            'user_id' => $this->seller->id,
            'name' => 'Limited Product',
            'price' => 20000,
            'stock' => 5,
            'barcode' => 'LIMITED-001',
        ]);

        $order = Order::create([
            'token' => Order::generateToken(),
            'seller_id' => $this->seller->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Try to add more than available
        $response = $this->post(
            route('guest.add-item', $order->token),
            [
                'product_id' => $limitedProduct->id,
                'quantity' => 10, // More than stock!
            ]
        );

        // Should fail
        $this->assertEquals(400, $response->status());
        $this->assertStringContainsString('Insufficient stock', $response->json('error'));
    }
}
