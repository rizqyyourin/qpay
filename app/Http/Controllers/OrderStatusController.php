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
                'code'   => $order->code,
                'status' => $order->status,
                'total'  => $order->total,
                'items'  => $order->items->map(fn ($item) => [
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
}
