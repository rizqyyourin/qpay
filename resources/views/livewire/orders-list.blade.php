<div class="min-h-screen bg-base-100">
    <!-- Page Header -->
    <div class="bg-primary text-primary-content p-4 md:p-6">
        <h1 class="text-2xl md:text-3xl font-bold">Orders Management</h1>
        <p class="text-sm opacity-75">View and manage all customer orders</p>
    </div>

    <div class="container mx-auto p-4 md:p-6 space-y-4">
        <!-- Filters Section -->
        <div class="card bg-base-200 shadow-md p-4 md:p-6">
            <h2 class="text-lg font-semibold mb-4">Filters & Search</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search by Order Number -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Search Order or Customer</span>
                    </label>
                    <input
                        type="text"
                        placeholder="Order number or customer name..."
                        wire:model.live="search"
                        class="input input-bordered focus:input-primary"
                    />
                </div>

                <!-- Status Filter -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Payment Status</span>
                    </label>
                    <select wire:model.live="status" class="select select-bordered focus:select-primary">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>

                <!-- Payment Method Filter -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Payment Method</span>
                    </label>
                    <select wire:model.live="paymentMethod" class="select select-bordered focus:select-primary">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="card">Debit/Credit Card</option>
                        <option value="transfer">Bank Transfer</option>
                        <option value="ewallet">E-Wallet</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Sort By</span>
                    </label>
                    <select class="select select-bordered focus:select-primary">
                        <option value="">Newest First</option>
                        <option value="total_amount">Highest Amount</option>
                        <option value="created_at">Oldest First</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card bg-base-100 shadow-md overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                @if($orders->count() > 0)
                    <table class="table w-full">
                        <thead class="bg-base-200">
                            <tr>
                                <th class="cursor-pointer hover:bg-base-300" wire:click="sortBy('order_number')">
                                    <div class="flex items-center gap-2">
                                        Order Number
                                        @if($sortBy === 'order_number')
                                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th>Customer</th>
                                <th class="cursor-pointer hover:bg-base-300" wire:click="sortBy('created_at')">
                                    <div class="flex items-center gap-2">
                                        Date
                                        @if($sortBy === 'created_at')
                                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th>Items</th>
                                <th class="cursor-pointer hover:bg-base-300" wire:click="sortBy('total_amount')">
                                    <div class="flex items-center gap-2">
                                        Total
                                        @if($sortBy === 'total_amount')
                                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50 border-t">
                                    <!-- Order Number -->
                                    <td>
                                        <span class="font-semibold text-primary">{{ $order->order_number }}</span>
                                    </td>

                                    <!-- Customer Name -->
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="avatar placeholder">
                                                <div class="bg-primary text-primary-content rounded-full w-8 text-xs font-bold">
                                                    {{ substr($order->user->name, 0, 1) }}
                                                </div>
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ $order->user->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $order->user->email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Order Date -->
                                    <td>
                                        <div>
                                            <p class="text-sm">{{ $order->created_at->format('d/m/Y') }}</p>
                                            <p class="text-xs text-gray-500">{{ $order->created_at->format('H:i') }}</p>
                                        </div>
                                    </td>

                                    <!-- Number of Items -->
                                    <td>
                                        <span class="badge badge-outline">{{ $order->items()->count() }} item(s)</span>
                                    </td>

                                    <!-- Total Amount -->
                                    <td>
                                        <span class="font-bold text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                    </td>

                                    <!-- Payment Status Badge -->
                                    <td>
                                        @switch($order->payment_status)
                                            @case('completed')
                                                <span class="badge badge-success">Completed</span>
                                                @break
                                            @case('pending')
                                                <span class="badge badge-warning">Pending</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-error">Cancelled</span>
                                                @break
                                            @case('refunded')
                                                <span class="badge badge-info">Refunded</span>
                                                @break
                                            @default
                                                <span class="badge">{{ ucfirst($order->payment_status) }}</span>
                                        @endswitch
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="flex gap-2">
                                            <a
                                                href="{{ route('orders.show', $order->id) }}"
                                                class="btn btn-primary btn-xs"
                                            >
                                                View
                                            </a>
                                            @if($order->payment_status === 'pending')
                                                <button
                                                    wire:click="cancelOrder({{ $order->id }})"
                                                    class="btn btn-error btn-xs"
                                                    onclick="return confirm('Are you sure?')"
                                                >
                                                    Cancel
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="px-4 md:px-6 py-4 bg-base-100 border-t">
                        {{ $orders->links() }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 2a2 2 0 012-2h6a2 2 0 012 2v2h2a2 2 0 110 4h-.081l1.122 5.753a2 2 0 01-1.939 2.386H4.878a2 2 0 01-1.938-2.386L4.081 8H2a2 2 0 110-4h2V2zm10 12H6v-2h9v2z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-gray-500 text-lg font-medium">No orders found</p>
                        <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filters</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Summary Stats (Optional) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Orders -->
            <div class="stat bg-base-100 shadow-md border border-gray-200">
                <div class="stat-title">Total Orders</div>
                <div class="stat-value text-primary">{{ $orders->total() }}</div>
                <div class="stat-desc">in results</div>
            </div>

            <!-- Total Amount -->
            <div class="stat bg-base-100 shadow-md border border-gray-200">
                <div class="stat-title">Total Revenue</div>
                <div class="stat-value text-success">Rp {{ number_format($orders->sum('total_amount'), 0, ',', '.') }}</div>
                <div class="stat-desc">for filtered orders</div>
            </div>

            <!-- Average Order Value -->
            <div class="stat bg-base-100 shadow-md border border-gray-200">
                <div class="stat-title">Average Order</div>
                <div class="stat-value text-warning">
                    Rp {{ number_format($orders->count() > 0 ? $orders->sum('total_amount') / $orders->count() : 0, 0, ',', '.') }}
                </div>
                <div class="stat-desc">per order</div>
            </div>
        </div>
    </div>
</div>
