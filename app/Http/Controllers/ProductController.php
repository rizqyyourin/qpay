<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

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

        $debugId = (string) str()->uuid();

        Log::info('Product delete requested.', [
            'debug_id' => $debugId,
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'product_exists_before_delete' => $product->exists,
            'expects_json' => $request->expectsJson(),
            'inertia' => $request->header('X-Inertia'),
        ]);

        try {
            $product->delete();

            Log::info('Product delete completed.', [
                'debug_id' => $debugId,
                'product_id' => $product->id,
                'deleted_at' => $product->fresh()?->deleted_at,
            ]);
        } catch (Throwable $exception) {
            Log::error('Product delete failed.', [
                'debug_id' => $debugId,
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'Failed to delete product.',
                    'debug_id' => $debugId,
                ], 500);
            }

            return back()->with('error', 'Failed to delete product. Debug ID: '.$debugId);
        }

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
