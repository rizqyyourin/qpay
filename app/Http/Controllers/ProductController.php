<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'image_file'  => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:5120', // 5 MB
            'stock'       => 'required|integer|min:0',
        ]);

        if ($request->hasFile('image_file')) {
            $validated['image'] = Storage::url(
                $request->file('image_file')->store('product-images', 'public')
            );
        }

        unset($validated['image_file']);
        $request->user()->products()->create($validated);

        return redirect()->route('dashboard');
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($product->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($product->user_id === $request->user()->id, 403);

        $product->delete();

        return redirect()->route('dashboard');
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items'          => 'required|array',
            'items.*.id'     => 'required|integer|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1',
        ]);

        foreach ($validated['items'] as $item) {
            Product::where('id', $item['id'])
                ->where('user_id', $request->user()->id)
                ->where('stock', '>', 0)
                ->decrement('stock', $item['qty']);
        }

        return response()->json(['success' => true]);
    }
}
