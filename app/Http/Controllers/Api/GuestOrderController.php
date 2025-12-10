<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestOrderController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get order details by token
     * Multi-tenant: Only return if seller owns it
     */
    public function show($token)
    {
        $order = Order::where('token', strtoupper($token))->first();
        
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Verify order belongs to authenticated seller (if logged in)
        if (Auth::check() && $order->seller_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($order);
    }

    /**
     * Complete order and reduce stock
     * Multi-tenant: Only allow if seller owns it
     */
    public function complete($token, Request $request)
    {
        $order = Order::where('token', strtoupper($token))->first();
        
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Verify order belongs to authenticated seller
        if (Auth::check() && $order->seller_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Only pending or registered orders can be completed
        if (!in_array($order->status, ['pending', 'registered'])) {
            return response()->json(['error' => 'Order must be in pending or registered status to complete'], 422);
        }

        try {
            // Confirm reservations if not already confirmed
            $pendingReservations = \App\Models\InventoryReservation::where('order_id', $order->id)
                ->where('status', 'reserved')
                ->count();
            
            if ($pendingReservations > 0) {
                $this->inventoryService->confirmOrder($order);
            }

            // Reduce actual stock based on confirmed reservations
            $this->inventoryService->reduceStock($order);

            // Mark order as completed with payment details
            $order->update([
                'status' => 'completed',
                'payment_method' => $request->input('payment_method', 'cash'),
                'payment_status' => 'paid',
                'discount_amount' => $request->input('discount_amount', 0),
                'tax_amount' => $request->input('tax_amount', 0),
                'transaction_id' => $request->input('transaction_id'),
            ]);

            // Recalculate totals
            $order->calculateTotals();
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order completed successfully',
                'order' => $order,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
