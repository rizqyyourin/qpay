<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class DashboardStats extends Component
{
    /**
     * Get total completed orders for current seller
     */
    public function getTotalOrdersProperty()
    {
        return Order::where('seller_id', Auth::id())
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Get total revenue from completed orders
     */
    public function getTotalRevenueProperty()
    {
        return Order::where('seller_id', Auth::id())
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValueProperty()
    {
        $totalOrders = $this->totalOrders;
        
        if ($totalOrders === 0) {
            return 0;
        }
        
        return $this->totalRevenue / $totalOrders;
    }

    /**
     * Get top selling products
     */
    public function getTopProductsProperty()
    {
        $userId = Auth::id();

        // Get all completed orders for this seller
        $orders = Order::where('seller_id', $userId)
            ->where('status', 'completed')
            ->get();

        // Aggregate items
        $productSales = [];
        foreach ($orders as $order) {
            foreach ($order->items ?? [] as $item) {
                $productId = $item['id'];
                if (!isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'id' => $productId,
                        'name' => $item['name'],
                        'quantity' => 0,
                        'revenue' => 0,
                    ];
                }
                $productSales[$productId]['quantity'] += $item['quantity'];
                $productSales[$productId]['revenue'] += $item['price'] * $item['quantity'];
            }
        }

        // Sort by quantity desc, take top 5
        usort($productSales, function ($a, $b) {
            return $b['quantity'] <=> $a['quantity'];
        });

        return array_slice($productSales, 0, 5);
    }

    /**
     * Get low stock products (less than 10 units)
     */
    public function getLowStockProductsProperty()
    {
        return Product::where('user_id', Auth::id())
            ->where('stock', '<', 10)
            ->orderBy('stock')
            ->get();
    }

    /**
     * Get recent completed orders
     */
    public function getRecentOrdersProperty()
    {
        return Order::where('seller_id', Auth::id())
            ->where('status', 'completed')
            ->latest('created_at')
            ->take(5)
            ->get();
    }

    /**
     * Get total products
     */
    public function getTotalProductsProperty()
    {
        return Product::where('user_id', Auth::id())->count();
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
}
