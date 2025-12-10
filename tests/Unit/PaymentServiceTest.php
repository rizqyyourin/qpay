<?php

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('PaymentService', function () {
    test('process payment creates payment record', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'payment_status' => 'pending',
        ]);

        $payment = $paymentService->processPayment($order, 100000, 'cash');

        expect($payment->order_id)->toBe($order->id);
        expect($payment->amount)->toEqual(100000);
        expect($payment->payment_method)->toBe('cash');
        expect($payment->status)->toBe('completed');
    });

    test('process payment updates order status to paid', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'payment_status' => 'pending',
        ]);

        $paymentService->processPayment($order, 100000, 'cash');

        expect($order->refresh()->payment_status)->toBe('paid');
        expect($order->refresh()->payment_method)->toBe('cash');
    });

    test('process payment with transaction id', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'payment_status' => 'pending',
        ]);

        $payment = $paymentService->processPayment(
            $order,
            100000,
            'card',
            'TXN-12345'
        );

        expect($payment->transaction_id)->toBe('TXN-12345');
    });

    test('process payment throws on already paid order', function () {
        $paymentService = new PaymentService();
        $paidOrder = Order::factory()->create(['payment_status' => 'paid']);

        expect(fn () => $paymentService->processPayment($paidOrder, 100000, 'cash'))
            ->toThrow(Exception::class, 'Order already paid');
    });

    test('process payment throws on insufficient amount', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'payment_status' => 'pending',
        ]);

        expect(fn () => $paymentService->processPayment($order, 50000, 'cash'))
            ->toThrow(Exception::class, 'Payment amount is less than order total');
    });

    test('get payment by order', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'payment_status' => 'pending',
        ]);
        $payment = Payment::factory()->create(['order_id' => $order->id]);

        $retrieved = $paymentService->getPaymentByOrder($order);

        expect($retrieved->id)->toBe($payment->id);
    });

    test('refund payment sets status to refunded', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'payment_status' => 'pending',
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'completed',
        ]);

        $paymentService->refundPayment($payment);

        expect($payment->refresh()->status)->toBe('refunded');
    });

    test('refund payment updates order status to pending', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'payment_status' => 'paid',
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'completed',
        ]);

        $paymentService->refundPayment($payment);

        expect($order->refresh()->payment_status)->toBe('pending');
    });

    test('refund non-completed payment throws exception', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        expect(fn () => $paymentService->refundPayment($payment))
            ->toThrow(Exception::class, 'Only completed payments can be refunded');
    });

    test('calculate change correctly', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
        ]);

        $change = $paymentService->calculateChange($order, 150000);

        expect($change)->toEqual(50000);
    });

    test('calculate change with tax', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 10000,
            'discount_amount' => 0,
        ]);

        $change = $paymentService->calculateChange($order, 120000);

        expect($change)->toEqual(10000);
    });

    test('calculate change with discount', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 10000,
        ]);

        $change = $paymentService->calculateChange($order, 95000);

        expect($change)->toEqual(5000);
    });

    test('validate payment amount sufficient', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
        ]);

        $isValid = $paymentService->validatePaymentAmount($order, 100000);

        expect($isValid)->toBeTrue();
    });

    test('validate payment amount with excess', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
        ]);

        $isValid = $paymentService->validatePaymentAmount($order, 150000);

        expect($isValid)->toBeTrue();
    });

    test('validate payment amount insufficient', function () {
        $paymentService = new PaymentService();
        $order = Order::factory()->create([
            'total_amount' => 100000,
            'tax_amount' => 0,
            'discount_amount' => 0,
        ]);

        $isValid = $paymentService->validatePaymentAmount($order, 50000);

        expect($isValid)->toBeFalse();
    });
});
