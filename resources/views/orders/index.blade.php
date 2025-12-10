<x-app-layout>
    <div class="p-4 md:p-8">
        <!-- Header & Search -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <h1 class="text-3xl font-bold">Orders</h1>
            <form method="GET" class="flex gap-2 w-full md:w-auto">
                <input 
                    type="text" 
                    name="search"
                    placeholder="Search order number..."
                    value="{{ request('search') }}"
                    class="input input-bordered w-full sm:w-auto"
                />
                <button type="submit" class="btn btn-primary">🔍 Search</button>
            </form>
        </div>

        <!-- Filters -->
        <div class="card bg-base-100 shadow mb-6 p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <select name="payment_method" class="select select-bordered" onchange="this.form.submit()">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>💵 Cash</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>💳 Card</option>
                    <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>🏦 Transfer</option>
                    <option value="ewallet" {{ request('payment_method') === 'ewallet' ? 'selected' : '' }}>📱 E-Wallet</option>
                </select>
                
                <input 
                    type="date" 
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="input input-bordered"
                />
                <input 
                    type="date" 
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="input input-bordered"
                />
                <button type="submit" class="btn btn-outline">Filter</button>
                <a href="{{ route('orders.index') }}" class="btn btn-outline">Reset</a>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="card bg-base-100 border border-base-300 shadow">
                <div class="card-body">
                    <h3 class="card-title text-lg text-base-content">Total Orders</h3>
                    <p class="text-3xl font-bold text-primary">{{ $stats['total_orders'] }}</p>
                </div>
            </div>
            <div class="card bg-base-100 border border-base-300 shadow">
                <div class="card-body">
                    <h3 class="card-title text-lg text-base-content">Total Revenue</h3>
                    <p class="text-3xl font-bold text-primary">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="card bg-base-100 border border-base-300 shadow">
                <div class="card-body">
                    <h3 class="card-title text-lg text-base-content">Average Order</h3>
                    <p class="text-3xl font-bold text-primary">Rp {{ number_format($stats['average_order'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card bg-base-100 shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-base-200">
                        <tr>
                            <th class="text-left">Order ID</th>
                            <th class="text-left">Date</th>
                            <th class="text-left">Customer</th>
                            <th class="text-left">Total</th>
                            <th class="text-left">Payment Method</th>
                            <th class="text-left">Status</th>
                            <th class="text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="border-b border-base-200 hover:bg-base-100">
                                <td class="font-semibold text-primary">{{ $order->token }}</td>
                                <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $order->buyer_name ?? '-' }}</td>
                                <td class="font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge">
                                        @switch($order->payment_method ?? '')
                                            @case('cash') 💵 Cash @break
                                            @case('card') 💳 Card @break
                                            @case('transfer') 🏦 Transfer @break
                                            @case('ewallet') 📱 E-Wallet @break
                                            @default - @break
                                        @endswitch
                                    </span>
                                </td>
                                <td><span class="badge badge-success">Completed</span></td>
                                <td>
                                    <a href="{{ route('orders.show', $order) }}" class="btn btn-ghost btn-sm">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-base-content/50">
                                    No matching orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-body flex justify-center">
                {{ $orders->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</x-app-layout>
