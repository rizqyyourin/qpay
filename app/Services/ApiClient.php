<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class ApiClient
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('app.api_url', 'http://localhost:8000/api');
        $this->token = auth()->user()->api_token ?? null;
    }

    /**
     * Set the API token
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * GET request
     */
    public function get($endpoint, $params = [])
    {
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}{$endpoint}", $params);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * POST request
     */
    public function post($endpoint, $data = [])
    {
        try {
            $response = Http::withToken($this->token)
                ->post("{$this->baseUrl}{$endpoint}", $data);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * PUT request
     */
    public function put($endpoint, $data = [])
    {
        try {
            $response = Http::withToken($this->token)
                ->put("{$this->baseUrl}{$endpoint}", $data);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * DELETE request
     */
    public function delete($endpoint)
    {
        try {
            $response = Http::withToken($this->token)
                ->delete("{$this->baseUrl}{$endpoint}");

            return $this->handleResponse($response);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    /**
     * Handle response
     */
    protected function handleResponse($response)
    {
        if ($response->successful()) {
            return [
                'success' => true,
                'status' => $response->status(),
                'data' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'status' => $response->status(),
            'errors' => $response->json(),
        ];
    }

    /**
     * Handle errors
     */
    protected function handleError($exception)
    {
        return [
            'success' => false,
            'status' => 0,
            'error' => $exception->getMessage(),
        ];
    }

    /**
     * Product endpoints
     */

    public function getProducts($page = 1, $limit = 20)
    {
        return $this->get('/products', ['page' => $page, 'per_page' => $limit]);
    }

    public function searchProducts($query)
    {
        return $this->get('/products/search', ['q' => $query]);
    }

    public function getProductByBarcode($barcode)
    {
        return $this->get("/products/barcode/{$barcode}");
    }

    public function getProduct($id)
    {
        return $this->get("/products/{$id}");
    }

    public function createProduct($data)
    {
        return $this->post('/products', $data);
    }

    public function updateProduct($id, $data)
    {
        return $this->put("/products/{$id}", $data);
    }

    public function deleteProduct($id)
    {
        return $this->delete("/products/{$id}");
    }

    /**
     * Cart endpoints
     */

    public function getCart()
    {
        return $this->get('/cart');
    }

    public function addToCart($productId, $quantity = 1)
    {
        return $this->post('/cart', [
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    public function updateCartItem($cartId, $quantity)
    {
        return $this->put("/cart/{$cartId}", ['quantity' => $quantity]);
    }

    public function removeFromCart($cartId)
    {
        return $this->delete("/cart/{$cartId}");
    }

    public function clearCart()
    {
        return $this->post('/cart/clear');
    }

    /**
     * Order endpoints
     */

    public function getOrders($page = 1, $limit = 15)
    {
        return $this->get('/orders', ['page' => $page, 'per_page' => $limit]);
    }

    public function getOrder($id)
    {
        return $this->get("/orders/{$id}");
    }

    public function createOrder($discountAmount = 0, $taxAmount = 0)
    {
        return $this->post('/orders', [
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
        ]);
    }

    public function cancelOrder($id)
    {
        return $this->post("/orders/{$id}/cancel");
    }

    /**
     * Payment endpoints
     */

    public function getPayment($id)
    {
        return $this->get("/payments/{$id}");
    }

    public function processPayment($orderId, $amount, $paymentMethod = 'cash', $transactionId = null)
    {
        return $this->post('/payments', [
            'order_id' => $orderId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
        ]);
    }

    public function refundPayment($paymentId)
    {
        return $this->post("/payments/{$paymentId}/refund");
    }

    public function calculateChange($paymentId, $amount)
    {
        return $this->get("/payments/{$paymentId}/calculate-change", ['amount' => $amount]);
    }

    /**
     * Dashboard endpoints
     */

    public function getDashboardStats()
    {
        return $this->get('/dashboard/stats');
    }

    public function getRecentOrders($limit = 5)
    {
        return $this->get('/dashboard/orders', ['limit' => $limit]);
    }

    /**
     * Report endpoints
     */

    public function getSalesReport($startDate, $endDate)
    {
        return $this->get('/reports/sales', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function getProductReport()
    {
        return $this->get('/reports/products');
    }
}
