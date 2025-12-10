<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    /**
     * Get all orders for authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $orders = $this->orderService->getUserOrders($user);

        return response()->json([
            'orders' => $orders,
            'count' => $orders->count(),
        ]);
    }

    /**
     * Create new order from cart.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $order = $this->orderService->createOrder(
                $user,
                $request->discount_amount ?? 0,
                $request->tax_amount ?? 0,
                $request->notes
            );

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('orderItems.product'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get order details.
     */
    public function show(int $orderId): JsonResponse
    {
        $user = Auth::user();
        $order = $this->orderService->getOrderById($orderId);

        if (!$order || $order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json($order);
    }

    /**
     * Cancel order.
     */
    public function cancel(int $orderId): JsonResponse
    {
        try {
            $user = Auth::user();
            $order = Order::where('user_id', $user->id)->findOrFail($orderId);

            $this->orderService->cancelOrder($order);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
