<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    /**
     * Get all active products with pagination.
     */
    public function getAllProducts(int $perPage = 15)
    {
        return Product::with('category')
            ->where('is_active', true)
            ->paginate($perPage);
    }

    /**
     * Get products by category.
     */
    public function getProductsByCategory(int $categoryId, int $perPage = 15)
    {
        return Product::with('category')
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->paginate($perPage);
    }

    /**
     * Search products by name, SKU or barcode.
     */
    public function searchProducts(string $query, int $perPage = 15)
    {
        return Product::with('category')
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->paginate($perPage);
    }

    /**
     * Get product by barcode.
     */
    public function getProductByBarcode(string $barcode): ?Product
    {
        return Product::with('category')
            ->where('barcode', $barcode)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get product by ID.
     */
    public function getProductById(int $id): ?Product
    {
        return Product::with('category')->find($id);
    }

    /**
     * Check product availability.
     */
    public function isAvailable(Product $product, int $quantity = 1): bool
    {
        return $product->stock_quantity >= $quantity;
    }

    /**
     * Get available stock.
     */
    public function getAvailableStock(Product $product): int
    {
        return $product->stock_quantity;
    }

    /**
     * Decrease stock after order.
     */
    public function decreaseStock(Product $product, int $quantity): bool
    {
        if (!$this->isAvailable($product, $quantity)) {
            throw new \Exception('Insufficient stock');
        }

        return $product->decrement('stock_quantity', $quantity);
    }

    /**
     * Increase stock (for refund/return).
     */
    public function increaseStock(Product $product, int $quantity): bool
    {
        return (bool) $product->increment('stock_quantity', $quantity);
    }
}
