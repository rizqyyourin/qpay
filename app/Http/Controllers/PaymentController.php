<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    /**
     * Get payment details for an order.
     */
    public function show(int $orderId): JsonResponse
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)->findOrFail($orderId);
        $payment = $this->paymentService->getPaymentByOrder($order);

        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json($payment);
    }

    /**
     * Process payment for order.
     */
    public function store(ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $order = Order::where('user_id', $user->id)->findOrFail($request->order_id);

            // Validate payment amount
            if (!$this->paymentService->validatePaymentAmount($order, $request->amount)) {
                return response()->json([
                    'message' => 'Payment amount is insufficient',
                    'required_amount' => $order->total_amount + $order->tax_amount - $order->discount_amount,
                ], 422);
            }

            $payment = $this->paymentService->processPayment(
                $order,
                $request->amount,
                $request->payment_method,
                $request->transaction_id,
            );

            $change = $this->paymentService->calculateChange($order, $request->amount);

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment,
                'order' => $order->fresh(),
                'change' => $change,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Refund payment.
     */
    public function refund(int $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            $order = Order::where('user_id', $user->id)->findOrFail($orderId);
            $payment = $this->paymentService->getPaymentByOrder($order);

            if (!$payment) {
                return response()->json([
                    'message' => 'Payment not found',
                ], 404);
            }

            $refundedPayment = $this->paymentService->refundPayment($payment);

            return response()->json([
                'message' => 'Payment refunded successfully',
                'payment' => $refundedPayment,
                'order' => $order->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Calculate change amount.
     */
    public function calculateChange(int $orderId, float $paidAmount): JsonResponse
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)->findOrFail($orderId);
        $change = $this->paymentService->calculateChange($order, $paidAmount);

        return response()->json([
            'total_due' => $order->total_amount + $order->tax_amount - $order->discount_amount,
            'paid_amount' => $paidAmount,
            'change' => $change,
        ]);
    }
}
