<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    /**
     * Get all cart items for authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $cartItems = $this->cartService->getCartItems($user);
        $total = $this->cartService->getCartTotal($user);
        $count = $this->cartService->getCartCount($user);

        return response()->json([
            'items' => $cartItems,
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * Add product to cart.
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $user = Auth::user();
        $product = \App\Models\Product::findOrFail($request->product_id);
        
        $cartItem = $this->cartService->addToCart(
            $user,
            $product,
            $request->quantity
        );

        return response()->json([
            'message' => 'Product added to cart',
            'cart_item' => $cartItem,
            'cart_count' => $this->cartService->getCartCount($user),
        ], 201);
    }

    /**
     * Update cart item quantity.
     */
    public function update(int $cartId, \App\Http\Requests\UpdateCartRequest $request): JsonResponse
    {
        $user = Auth::user();
        $cartItem = Cart::where('user_id', $user->id)->findOrFail($cartId);

        $this->cartService->updateQuantity($cartItem, $request->quantity);

        return response()->json([
            'message' => 'Cart item updated',
            'cart_item' => $cartItem->fresh(),
            'cart_total' => $this->cartService->getCartTotal($user),
            'quantity' => $cartItem->fresh()->quantity,
        ], 200);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(int $cartId): JsonResponse
    {
        $user = Auth::user();
        $cartItem = Cart::where('user_id', $user->id)->findOrFail($cartId);

        $this->cartService->removeFromCart($cartItem);

        return response()->json([
            'message' => 'Item removed from cart',
            'cart_count' => $this->cartService->getCartCount($user),
            'cart_total' => $this->cartService->getCartTotal($user),
        ]);
    }

    /**
     * Clear all cart items.
     */
    public function clear(): JsonResponse
    {
        $user = Auth::user();
        $this->cartService->clearCart($user);

        return response()->json([
            'message' => 'Cart cleared',
        ]);
    }
}
