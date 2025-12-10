<div class="space-y-8">
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Orders Card -->
        <div class="card bg-base-200 shadow-md">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Total Orders</p>
                        <p class="text-3xl font-bold text-primary">{{ $this->totalOrders }}</p>
                    </div>
                    <div class="text-2xl">📊</div>
                </div>
                <p class="text-xs text-base-content/50">Completed orders</p>
            </div>
        </div>

        <!-- Total Revenue Card -->
        <div class="card bg-base-200 shadow-md">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Total Revenue</p>
                        <p class="text-3xl font-bold text-success">{{ 'Rp ' . number_format($this->totalRevenue, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-2xl">💰</div>
                </div>
                <p class="text-xs text-base-content/50">From all sales</p>
            </div>
        </div>

        <!-- Average Order Value Card -->
        <div class="card bg-base-200 shadow-md">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Avg Order Value</p>
                        <p class="text-3xl font-bold text-warning">{{ 'Rp ' . number_format($this->averageOrderValue, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-2xl">📈</div>
                </div>
                <p class="text-xs text-base-content/50">Average per order</p>
            </div>
        </div>

        <!-- Total Products Card -->
        <div class="card bg-base-200 shadow-md">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Total Products</p>
                        <p class="text-3xl font-bold text-info">{{ $this->totalProducts }}</p>
                    </div>
                    <div class="text-2xl">📦</div>
                </div>
                <p class="text-xs text-base-content/50">In your store</p>
            </div>
        </div>
    </div>

    <!-- Two Column Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products -->
        <div class="card bg-base-200 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">🏆 Top Products</h3>
                @if (count($this->topProducts) > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-sm w-full">
                            <thead>
                                <tr class="border-base-300">
                                    <th class="text-base-content/60">Product</th>
                                    <th class="text-base-content/60 text-right">Qty</th>
                                    <th class="text-base-content/60 text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->topProducts as $product)
                                    <tr class="hover:bg-base-300/30 border-base-300">
                                        <td class="font-medium text-sm">
                                            <div class="truncate" title="{{ $product['name'] }}">
                                                {{ $product['name'] }}
                                            </div>
                                        </td>
                                        <td class="text-right text-sm">{{ $product['quantity'] }}</td>
                                        <td class="text-right text-sm font-semibold text-success">
                                            Rp {{ number_format($product['revenue'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-base-content/50 py-4">No product sales yet</p>
                @endif
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="card bg-base-200 shadow-md">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">⚠️ Low Stock Alert</h3>
                @if (count($this->lowStockProducts) > 0)
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach ($this->lowStockProducts as $product)
                            <div class="p-3 bg-error/10 border border-error/30 rounded-lg flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-sm">{{ $product->name }}</p>
                                    <p class="text-xs text-base-content/60">{{ $product->sku ?? 'No SKU' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-error badge-lg">{{ $product->stock }} left</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-base-content/50 py-4">All products are well stocked! ✅</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card bg-base-200 shadow-md">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">📋 Recent Orders</h3>
            @if (count($this->recentOrders) > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm w-full">
                        <thead>
                            <tr class="border-base-300">
                                <th class="text-base-content/60">Order ID</th>
                                <th class="text-base-content/60">Customer</th>
                                <th class="text-base-content/60 text-center">Items</th>
                                <th class="text-base-content/60 text-right">Total</th>
                                <th class="text-base-content/60 text-center">Date</th>
                                <th class="text-base-content/60 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->recentOrders as $order)
                                <tr class="hover:bg-base-300/30 border-base-300">
                                    <td class="font-mono text-sm">
                                        {{ strtoupper(substr($order->token, 0, 8)) }}
                                    </td>
                                    <td class="text-sm">
                                        <div>
                                            <p class="font-medium">
                                                {{ $order->user ? $order->user->name : $order->buyer_name }}
                                            </p>
                                            <p class="text-xs text-base-content/50">
                                                {{ $order->user ? $order->user->email : 'Guest' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="text-center text-sm">
                                        <span class="badge badge-primary">{{ count($order->items ?? []) }}</span>
                                    </td>
                                    <td class="text-right text-sm font-semibold">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center text-xs text-base-content/60">
                                        {{ $order->created_at->format('d M') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('orders.show', $order->id) }}" 
                                           class="btn btn-xs btn-ghost">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-base-content/50 py-8">No orders yet. Start by creating your first order!</p>
            @endif
        </div>
    </div>
</div>
