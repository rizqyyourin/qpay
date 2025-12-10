<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
    ) {}

    /**
     * Get all products (paginated).
     */
    public function index(): JsonResponse
    {
        $products = $this->productService->getAllProducts();
        return response()->json($products);
    }

    /**
     * Get products by category.
     */
    public function byCategory(int $categoryId): JsonResponse
    {
        $products = $this->productService->getProductsByCategory($categoryId);
        return response()->json($products);
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        if (empty($query)) {
            return response()->json(['products' => []]);
        }

        $products = $this->productService->searchProducts($query);
        return response()->json($products);
    }

    /**
     * Get product by barcode.
     */
    public function byBarcode(string $barcode): JsonResponse
    {
        $product = $this->productService->getProductByBarcode($barcode);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json($product);
    }

    /**
     * Get product details.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json($product);
    }

    /**
     * Create a new product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json($product, 201);
    }

    /**
     * Update product.
     */
    public function update(int $id, StoreProductRequest $request): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());

        return response()->json($product);
    }

    /**
     * Delete product.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
