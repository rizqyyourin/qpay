<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestCheckoutController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Start a new guest order when QR is scanned
     * Guest (pembeli tanpa login) scan QR → create guest session → add item to cart
     * QR code format: /guest/product/{product_id}/start?seller={seller_id}
     */
    public function startSession(Request $request, Product $product)
    {
        // seller_id dari QR code - ini adalah toko yang di-scan pembeli
        $sellerId = (int) $request->query('seller');
        
        // Verify product belongs to the seller in QR code (bukan Auth::id() karena guest)
        if ($product->user_id !== $sellerId) {
            abort(403, 'Product not found in this store');
        }

        // Check if there's an existing token in query param or session cookie
        $existingToken = $request->query('token') ?? $request->cookie('guest_order_token');
        
        if ($existingToken) {
            // Try to find existing order by token
            $order = Order::where('token', $existingToken)
                ->where('seller_id', $sellerId)
                ->where('status', 'pending')
                ->first();
            
            if ($order) {
                // Try to add to existing order (guest bisa continue shopping)
                try {
                    $this->inventoryService->reserve($order, $product, 1);
                    $order->addItem($product, 1);
                    return redirect()->route('guest.cart', $order->token)
                        ->cookie('guest_order_token', $order->token, 60 * 24); // 24 hour cookie
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', $e->getMessage());
                }
            }
        }
        
        // Create new guest order (tidak perlu login!)
        $order = Order::create([
            'token' => Order::generateToken(),
            'seller_id' => $sellerId, // Dari QR code, bukan dari Auth
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'buyer_name' => null, // Guest bisa isi nanti
            'buyer_phone' => null,
            'buyer_email' => null,
        ]);

        // Try to add initial product to cart with reservation
        try {
            $this->inventoryService->reserve($order, $product, 1);
            $order->addItem($product, 1);
        } catch (\Exception $e) {
            $order->delete(); // Clean up empty order jika fail
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('guest.cart', $order->token)
            ->cookie('guest_order_token', $order->token, 60 * 24); // 24 hour cookie
    }

    /**
     * Show guest cart - GUEST BOLEH AKSES TANPA LOGIN
     * Route: /guest/{token}/cart
     */
    public function showCart($token)
    {
        $order = Order::where('token', $token)->firstOrFail();

        // NO AUTH CHECK! Guest bisa akses cart dengan token mereka
        // Token sudah unique dan unpredictable, jadi secure enough
        
        return view('shop.guest-cart', [
            'order' => $order,
        ]);
    }

    /**
     * Add item to guest cart - GUEST BOLEH AKSES TANPA LOGIN
     * POST /guest/{token}/add-item
     */
    public function addItem(Request $request, $token)
    {
        $order = Order::where('token', $token)->firstOrFail();
        
        // NO AUTH CHECK! Guest token is the security mechanism
        
        // Validate product exists and belongs to same seller
        $product = Product::findOrFail($request->product_id);
        if ($product->user_id !== $order->seller_id) {
            abort(403, 'Product does not belong to this store');
        }
        
        $quantity = $request->quantity ?? 1;
        
        try {
            // Reserve stock before adding to cart
            $this->inventoryService->reserve($order, $product, $quantity);
            
            // Add item to cart
            $order->addItem($product, $quantity);
            
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'items' => $order->items,
                'total' => $order->total_amount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove item from guest cart - GUEST BOLEH AKSES TANPA LOGIN
     * DELETE /guest/{token}/remove/{productId}
     */
    public function removeItem($token, $productId)
    {
        $order = Order::where('token', $token)->firstOrFail();
        
        // NO AUTH CHECK! Token is secure enough
        
        try {
            // Release stock reservation
            \App\Models\InventoryReservation::where('order_id', $order->id)
                ->where('product_id', $productId)
                ->where('status', 'reserved')
                ->each(function ($reservation) {
                    $this->inventoryService->release($reservation);
                });

            // Remove from cart
            $order->removeItem($productId);
            
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'items' => $order->items,
                'total' => $order->total_amount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Update item quantity - GUEST BOLEH AKSES TANPA LOGIN
     * POST /guest/{token}/update-quantity
     */
    public function updateQuantity(Request $request, $token)
    {
        $order = Order::where('token', $token)->firstOrFail();
        
        // NO AUTH CHECK! Token is secure enough

        $product = Product::findOrFail($request->product_id);
        
        // Verify product belongs to same seller
        if ($product->user_id !== $order->seller_id) {
            abort(403, 'Product does not belong to this store');
        }
        
        $items = $order->items ?? [];
        $key = (string) $product->id;
        
        if (!isset($items[$key])) {
            return response()->json(['error' => 'Item not found in cart', 'success' => false], 404);
        }
        
        $quantity = (int) $request->quantity;
        
        // Validate quantity
        if ($quantity < 0) {
            return response()->json(['error' => 'Quantity cannot be negative', 'success' => false], 400);
        }
        
        if ($quantity == 0) {
            // Remove item
            $order->removeItem($product->id);
        } else {
            // Check stock availability with current reservations
            $currentReservation = \App\Models\InventoryReservation::where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->where('status', 'reserved')
                ->sum('quantity') ?? 0;
            
            $availableStock = $product->stock - $currentReservation;
            $newQtyDiff = $quantity - ($items[$key]['quantity'] ?? 0);
            
            if ($newQtyDiff > $availableStock) {
                return response()->json([
                    'error' => "Insufficient stock. Available: {$availableStock} units",
                    'available' => $availableStock,
                    'requested' => $newQtyDiff,
                    'success' => false
                ], 400);
            }
            
            // Update or release/reserve based on quantity change
            if ($newQtyDiff > 0) {
                // Need more items - reserve additional
                $this->inventoryService->reserve($order, $product, $newQtyDiff);
            } elseif ($newQtyDiff < 0) {
                // Need less items - release some
                $releaseQty = abs($newQtyDiff);
                $reservations = \App\Models\InventoryReservation::where('order_id', $order->id)
                    ->where('product_id', $product->id)
                    ->where('status', 'reserved')
                    ->take($releaseQty)
                    ->get();
                
                foreach ($reservations as $reservation) {
                    $this->inventoryService->release($reservation);
                }
            }
            
            $items[$key]['quantity'] = $quantity;
            $order->items = $items;
            $order->calculateTotals();
            $order->save();
        }
        
        return response()->json([
            'success' => true,
            'items' => $order->items,
            'total' => $order->total_amount,
        ]);
    }

    /**
     * Checkout guest cart - GUEST BOLEH AKSES TANPA LOGIN
     * Guest isi nama/no telp → create order → dapetin order ID
     * POST /guest/{token}/checkout
     */
    public function checkout(Request $request, $token)
    {
        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_phone' => 'required|string|max:20',
            'buyer_email' => 'nullable|email|max:255',
        ]);
        
        $order = Order::where('token', $token)->firstOrFail();
        
        // NO AUTH CHECK! Guest bisa checkout dengan token mereka
        
        // Validate order has items
        if (empty($order->items)) {
            return redirect()->back()->with('error', 'Cart is empty');
        }
        
        try {
            // Confirm all reservations (lock them in)
            $this->inventoryService->confirmOrder($order);
            
            // Update buyer info
            $order->update([
                'buyer_name' => $validated['buyer_name'],
                'buyer_phone' => $validated['buyer_phone'],
                'buyer_email' => $validated['buyer_email'] ?? null,
                'status' => 'registered', // Order created, waiting for POS confirmation
            ]);
            
            return redirect()->route('guest.success', $order->token);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Checkout failed: ' . $e->getMessage());
        }
    }

    /**
     * Checkout success page - show Order ID for POS
     * Guest lihat order ID mereka → serahkan ke kasir → kasir masukin ID di POS
     * GET /guest/{token}/success
     */
    public function success($token)
    {
        $order = Order::where('token', $token)->firstOrFail();
        
        // NO AUTH CHECK! Guest bisa akses success page dengan token
        
        return view('shop.guest-success', [
            'order' => $order,
        ]);
    }
}
