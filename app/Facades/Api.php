<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\ApiClient setToken(string $token)
 * @method static array get(string $endpoint, array $params = [])
 * @method static array post(string $endpoint, array $data = [])
 * @method static array put(string $endpoint, array $data = [])
 * @method static array delete(string $endpoint)
 * @method static array getProducts(int $page = 1, int $limit = 20)
 * @method static array searchProducts(string $query)
 * @method static array getProductByBarcode(string $barcode)
 * @method static array getProduct(int $id)
 * @method static array createProduct(array $data)
 * @method static array updateProduct(int $id, array $data)
 * @method static array deleteProduct(int $id)
 * @method static array getCart()
 * @method static array addToCart(int $productId, int $quantity = 1)
 * @method static array updateCartItem(int $cartId, int $quantity)
 * @method static array removeFromCart(int $cartId)
 * @method static array clearCart()
 * @method static array getOrders(int $page = 1, int $limit = 15)
 * @method static array getOrder(int $id)
 * @method static array createOrder(int $discountAmount = 0, int $taxAmount = 0)
 * @method static array cancelOrder(int $id)
 * @method static array getPayment(int $id)
 * @method static array processPayment(int $orderId, int $amount, string $paymentMethod = 'cash', ?string $transactionId = null)
 * @method static array refundPayment(int $paymentId)
 * @method static array calculateChange(int $paymentId, int $amount)
 * @method static array getDashboardStats()
 * @method static array getRecentOrders(int $limit = 5)
 * @method static array getSalesReport(string $startDate, string $endDate)
 * @method static array getProductReport()
 * @see \App\Services\ApiClient
 */
class Api extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'api';
    }
}
