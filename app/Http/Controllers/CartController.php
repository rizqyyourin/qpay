<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        return Inertia::render('Cart');
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items'       => ['required', 'array', 'min:1'],
            'items.*.id'  => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        $items    = collect($validated['items']);
        $products = Product::whereIn('id', $items->pluck('id'))->get()->keyBy('id');

        // Validate all stock before creating the order
        foreach ($items as $item) {
            $product = $products->get($item['id']);
            if (! $product || $product->stock < $item['qty']) {
                return back()->withErrors([
                    'stock' => 'Not enough stock for "' . ($product?->name ?? 'a product') . '". Please adjust quantities.',
                ]);
            }
        }

        $sellerId = $products->first()->user_id;
        $total    = $items->sum(fn ($item) => $products->get($item['id'])->price * $item['qty']);

        $order = Order::create([
            'user_id' => $sellerId,
            'code'    => $this->generateCode(),
            'status'  => 'pending',
            'total'   => $total,
        ]);

        foreach ($items as $item) {
            $product = $products->get($item['id']);
            $order->items()->create([
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'price'        => $product->price,
                'qty'          => $item['qty'],
            ]);
        }

        return redirect()->route('order.status', $order->code);
    }

    private function generateCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        } while (Order::where('code', $code)->exists());

        return $code;
    }
}
