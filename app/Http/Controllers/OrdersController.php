<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    /**
     * Display list of completed orders for current seller
     */
    public function index(Request $request)
    {
        // Multi-tenant: Only show orders for current seller
        $query = Order::where('seller_id', Auth::id())
            ->where('status', 'completed');

        // Search by token/order ID
        if ($request->filled('search')) {
            $query->where('token', 'like', '%' . strtoupper($request->search) . '%')
                  ->orWhere('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('buyer_name', 'like', '%' . $request->search . '%');
        }

        // Filter by payment method
        if ($request->filled('payment_method') && $request->payment_method !== 'semua') {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Paginate results
        $orders = $query->latest('created_at')->paginate(15);

        // Calculate summary stats (only for current seller)
        $stats = [
            'total_orders' => Order::where('seller_id', Auth::id())
                ->where('status', 'completed')
                ->count(),
            'total_revenue' => Order::where('seller_id', Auth::id())
                ->where('status', 'completed')
                ->sum('total_amount'),
            'average_order' => Order::where('seller_id', Auth::id())
                ->where('status', 'completed')
                ->avg('total_amount'),
        ];

        return view('orders.index', [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    /**
     * Show order details (with authorization check)
     */
    public function show(Order $order)
    {
        // Verify order belongs to current seller
        if ($order->seller_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('orders.show', [
            'order' => $order,
        ]);
    }
}
