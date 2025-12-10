<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private CartService $cartService,
    ) {}

    /**
     * Create order from cart items.
     */
    public function createOrder(
        User $user,
        ?float $discountAmount = 0,
        ?float $taxAmount = 0,
        ?string $notes = null
    ): Order {
        return DB::transaction(function () use ($user, $discountAmount, $taxAmount, $notes) {
            $cartItems = $this->cartService->getCartItems($user);

            if ($cartItems->isEmpty()) {
                throw new \Exception('Cart is empty');
            }

            $totalAmount = $this->cartService->getCartTotal($user);

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $this->generateOrderNumber(),
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount ?? 0,
                'tax_amount' => $taxAmount ?? 0,
                'payment_status' => 'pending',
                'notes' => $notes,
            ]);

            // Create order items from cart
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'subtotal' => $cartItem->subtotal,
                ]);
            }

            // Clear cart after order created
            $this->cartService->clearCart($user);

            return $order->load('orderItems.product');
        });
    }

    /**
     * Get order by ID with relationships.
     */
    public function getOrderById(int $orderId): ?Order
    {
        return Order::with(['user', 'orderItems.product', 'payment'])
            ->find($orderId);
    }

    /**
     * Get all orders for user.
     */
    public function getUserOrders(User $user): Collection
    {
        return Order::with(['orderItems.product', 'payment'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Order $order, string $status): Order
    {
        $order->update(['payment_status' => $status]);
        return $order;
    }

    /**
     * Cancel order (only if pending).
     */
    public function cancelOrder(Order $order): bool
    {
        if ($order->payment_status !== 'pending') {
            throw new \Exception('Only pending orders can be cancelled');
        }

        return $order->update(['payment_status' => 'cancelled']);
    }

    /**
     * Generate unique order number.
     */
    private function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd');
        $count = Order::whereDate('created_at', now())->count() + 1;
        return $prefix . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
