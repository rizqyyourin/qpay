<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function confirm(Order $order): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);
        abort_unless($order->status === 'pending', 422, 'Order is no longer pending.');

        // Re-validate stock before decrementing
        $order->load('items.product');
        foreach ($order->items as $item) {
            if ($item->product && $item->product->stock < $item->qty) {
                return back()->withErrors([
                    'stock' => 'Not enough stock for "' . $item->product_name . '".',
                ]);
            }
        }

        foreach ($order->items as $item) {
            $item->product?->decrement('stock', $item->qty);
        }

        $order->update(['status' => 'confirmed']);

        return back();
    }

    public function cancel(Order $order): RedirectResponse
    {
        abort_unless($order->user_id === auth()->id(), 403);
        abort_unless($order->status === 'pending', 422, 'Order is no longer pending.');

        $order->update(['status' => 'cancelled']);

        return back();
    }
}
