<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrderStatusController extends Controller
{
    public function show(string $code): Response
    {
        $order = Order::with('items')->where('code', $code)->firstOrFail();

        return Inertia::render('OrderStatus', [
            'order' => [
                'code'       => $order->code,
                'status'     => $order->status,
                'total'      => $order->total,
                'created_at' => $order->created_at->toIso8601String(),
                'items'      => $order->items->map(fn ($item) => [
                    'name'  => $item->product_name,
                    'price' => $item->price,
                    'qty'   => $item->qty,
                ]),
            ],
        ]);
    }

    public function poll(string $code): JsonResponse
    {
        $order = Order::where('code', $code)->firstOrFail();

        return response()->json(['status' => $order->status]);
    }

    public function cancel(string $code): JsonResponse
    {
        $order = Order::where('code', $code)->firstOrFail();

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order is no longer pending.'], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json(['status' => 'cancelled']);
    }

    public function approve(string $code): JsonResponse
    {
        $order = Order::where('code', $code)->firstOrFail();

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order is no longer pending.'], 422);
        }

        // Only allow manual approval after 1 minute has passed
        if ($order->created_at->diffInSeconds(now()) < 60) {
            return response()->json(['error' => 'Manual approval is not available yet.'], 403);
        }

        $order->load('items.product');
        foreach ($order->items as $item) {
            $item->product?->decrement('stock', $item->qty);
        }

        $order->update(['status' => 'confirmed']);

        return response()->json(['status' => 'confirmed']);
    }
}
