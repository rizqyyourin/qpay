<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\InventoryReservation;
use Exception;

class InventoryService
{
    /**
     * Reserve stock for an order item
     * 
     * @throws Exception if stock insufficient
     */
    public function reserve(Order $order, Product $product, int $quantity): InventoryReservation
    {
        // Check available stock (not including existing reservations)
        $reservedQuantity = InventoryReservation::where('product_id', $product->id)
            ->where('status', 'reserved')
            ->where(function ($query) {
                $query->whereNull('released_at')
                      ->orWhere('released_at', '>', now());
            })
            ->sum('quantity');

        $availableStock = $product->stock - $reservedQuantity;

        if ($availableStock < $quantity) {
            throw new Exception(
                "Insufficient stock for {$product->name}. Available: {$availableStock}, Requested: {$quantity}"
            );
        }

        // Create reservation
        return InventoryReservation::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
        ]);
    }

    /**
     * Release stock reservation (e.g., when item removed from cart)
     */
    public function release(InventoryReservation $reservation): void
    {
        $reservation->release();
    }

    /**
     * Release all reservations for an order
     */
    public function releaseOrder(Order $order): void
    {
        InventoryReservation::where('order_id', $order->id)
            ->where('status', 'reserved')
            ->each(function ($reservation) {
                $reservation->release();
            });
    }

    /**
     * Confirm all reservations for an order (move to confirmed)
     */
    public function confirmOrder(Order $order): void
    {
        InventoryReservation::where('order_id', $order->id)
            ->where('status', 'reserved')
            ->each(function ($reservation) {
                $reservation->confirm();
            });
    }

    /**
     * Reduce actual stock based on confirmed reservations
     */
    public function reduceStock(Order $order): void
    {
        $reservations = InventoryReservation::where('order_id', $order->id)
            ->where('status', 'confirmed')
            ->get();

        foreach ($reservations as $reservation) {
            $product = $reservation->product;
            
            // Double-check stock is still available
            if ($product->stock < $reservation->quantity) {
                throw new Exception(
                    "Insufficient stock for {$product->name}. Available: {$product->stock}, Required: {$reservation->quantity}"
                );
            }

            // Reduce stock
            $product->decrement('stock', $reservation->quantity);
        }
    }

    /**
     * Get available stock (including reservations)
     */
    public function getAvailableStock(Product $product): int
    {
        $reservedQuantity = InventoryReservation::where('product_id', $product->id)
            ->where('status', 'reserved')
            ->whereNull('released_at')
            ->sum('quantity');

        return $product->stock - $reservedQuantity;
    }

    /**
     * Clean up expired reservations (older than 30 minutes)
     */
    public static function cleanupExpiredReservations(): int
    {
        $count = 0;
        
        InventoryReservation::where('status', 'reserved')
            ->whereNull('released_at')
            ->where('reserved_at', '<', now()->subMinutes(30))
            ->each(function ($reservation) use (&$count) {
                $reservation->release();
                $count++;
            });

        return $count;
    }
}
