<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    /**
     * Add product to cart or update quantity if already exists.
     */
    public function addToCart(User $user, Product $product, int $quantity = 1): Cart
    {
        // Check if product already in cart
        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            // Update quantity
            $cartItem->update([
                'quantity' => $cartItem->quantity + $quantity,
                'subtotal' => ($cartItem->quantity + $quantity) * $product->price,
            ]);
            return $cartItem;
        }

        // Create new cart item
        return Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'subtotal' => $quantity * $product->price,
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function updateQuantity(Cart $cartItem, int $quantity): Cart
    {
        if ($quantity <= 0) {
            $cartItem->delete();
            return $cartItem;
        }

        $cartItem->update([
            'quantity' => $quantity,
            'subtotal' => $quantity * $cartItem->unit_price,
        ]);

        return $cartItem;
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(Cart $cartItem): bool
    {
        return $cartItem->delete();
    }

    /**
     * Clear all cart items for user.
     */
    public function clearCart(User $user): int
    {
        return Cart::where('user_id', $user->id)->delete();
    }

    /**
     * Get all cart items for user.
     */
    public function getCartItems(User $user): Collection
    {
        return Cart::with('product')
            ->where('user_id', $user->id)
            ->get();
    }

    /**
     * Get cart total.
     */
    public function getCartTotal(User $user): float
    {
        return (float) Cart::where('user_id', $user->id)->sum('subtotal');
    }

    /**
     * Get cart item count.
     */
    public function getCartCount(User $user): int
    {
        return Cart::where('user_id', $user->id)->count();
    }
}
