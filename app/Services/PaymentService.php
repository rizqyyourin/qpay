<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;

class PaymentService
{
    /**
     * Process payment for order.
     */
    public function processPayment(
        Order $order,
        float $amount,
        string $method = 'cash',
        ?string $transactionId = null,
        ?array $gatewayResponse = null
    ): Payment {
        if ($order->payment_status === 'paid') {
            throw new \Exception('Order already paid');
        }

        // Calculate grand total: base amount + tax - discount
        $grandTotal = $order->total_amount + $order->tax_amount - $order->discount_amount;

        if ($amount < $grandTotal) {
            throw new \Exception('Payment amount is less than order total');
        }

        // Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'payment_method' => $method,
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'gateway_response' => $gatewayResponse,
        ]);

        // Update order payment status
        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $method,
        ]);

        return $payment;
    }

    /**
     * Get payment by order.
     */
    public function getPaymentByOrder(Order $order): ?Payment
    {
        return Payment::where('order_id', $order->id)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Refund payment.
     */
    public function refundPayment(Payment $payment): Payment
    {
        if ($payment->status !== 'completed') {
            throw new \Exception('Only completed payments can be refunded');
        }

        $payment->update(['status' => 'refunded']);

        // Update order status back to pending
        $payment->order->update(['payment_status' => 'pending']);

        return $payment;
    }

    /**
     * Calculate change amount.
     */
    public function calculateChange(Order $order, float $paidAmount): float
    {
        $totalDue = $order->total_amount + $order->tax_amount - $order->discount_amount;
        return $paidAmount - $totalDue;
    }

    /**
     * Validate payment amount.
     */
    public function validatePaymentAmount(Order $order, float $amount): bool
    {
        $totalDue = $order->total_amount + $order->tax_amount - $order->discount_amount;
        return $amount >= $totalDue;
    }
}
