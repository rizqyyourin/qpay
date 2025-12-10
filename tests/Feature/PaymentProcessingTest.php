<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Payment Processing', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'payment_status' => 'pending',
        ]);
    });

    test('user can process cash payment', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'order_id' => $this->order->id,
                'amount' => 100000,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Payment processed successfully')
            ->assertJsonStructure([
                'payment' => [
                    'id',
                    'order_id',
                    'amount',
                    'payment_method',
                    'status',
                ],
                'order' => [
                    'id',
                    'payment_status',
                ],
                'change',
            ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'amount' => 100000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'payment_status' => 'paid',
        ]);
    });

    test('payment calculates change correctly', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'order_id' => $this->order->id,
                'amount' => 150000,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('change', 50000);
    });

    test('payment fails if amount is insufficient', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'order_id' => $this->order->id,
                'amount' => 50000,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Payment amount is insufficient');
    });

    test('user can process card payment', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'order_id' => $this->order->id,
                'amount' => 100000,
                'payment_method' => 'card',
                'transaction_id' => 'TXN-123456',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('payment.payment_method', 'card')
            ->assertJsonPath('payment.transaction_id', 'TXN-123456');
    });

    test('user can process transfer payment', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'order_id' => $this->order->id,
                'amount' => 100000,
                'payment_method' => 'transfer',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('payment.payment_method', 'transfer');
    });

    test('user can process ewallet payment', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'order_id' => $this->order->id,
                'amount' => 100000,
                'payment_method' => 'ewallet',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('payment.payment_method', 'ewallet');
    });

    test('order with tax is calculated correctly', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 100000,
            'tax_amount' => 10000,
            'discount_amount' => 0,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'order_id' => $order->id,
                'amount' => 110000,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('change', 0);
    });

    test('order with discount is calculated correctly', function () {
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 10000,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'order_id' => $order->id,
                'amount' => 90000,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('change', 0);
    });

    test('payment fails on already paid order', function () {
        $paidOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/payments', [
                'order_id' => $paidOrder->id,
                'amount' => 100000,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Order already paid');
    });

    test('user can refund completed payment', function () {
        Payment::factory()->create([
            'order_id' => $this->order->id,
            'amount' => 100000,
            'status' => 'completed',
        ]);

        $this->order->update(['payment_status' => 'paid']);

        $response = $this->actingAs($this->user)
            ->postJson("/api/payments/{$this->order->id}/refund");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Payment refunded successfully')
            ->assertJsonPath('payment.status', 'refunded')
            ->assertJsonPath('order.payment_status', 'pending');
    });

    test('user can get payment by order', function () {
        $payment = Payment::factory()->create([
            'order_id' => $this->order->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/payments/order/{$this->order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $payment->id);
    });

    test('payment not found returns 404', function () {
        $response = $this->actingAs($this->user)
            ->getJson("/api/payments/order/{$this->order->id}");

        $response->assertStatus(404);
    });

    test('unauthenticated user cannot process payment', function () {
        $response = $this->postJson('/api/payments', [
            'order_id' => $this->order->id,
            'amount' => 100000,
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(401);
    });
});
