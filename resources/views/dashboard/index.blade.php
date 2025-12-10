<x-app-layout>
    <div class="px-5 py-12 space-y-8 max-w-7xl mx-auto">
        <!-- Welcome Section -->
        <section class="space-y-4">
            <h1 class="text-5xl font-black text-base-content">Welcome, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-lg text-base-content/60">Manage your store with ease. Choose an action below to get started.</p>
        </section>

        <!-- Dashboard Statistics -->
        <section>
            <livewire:dashboard-stats />
        </section>

        <!-- Quick Actions -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- View Orders -->
            <a href="{{ route('orders.index') }}" class="card bg-base-200 hover:bg-primary hover:text-primary-content transition-all cursor-pointer shadow-md">
                <div class="card-body space-y-4">
                    <div class="text-4xl">📋</div>
                    <h3 class="card-title text-xl">Orders</h3>
                    <p class="text-sm opacity-75">View and manage all customer orders</p>
                </div>
            </a>

            <!-- View Products -->
            <a href="{{ route('products.index') }}" class="card bg-base-200 hover:bg-primary hover:text-primary-content transition-all cursor-pointer shadow-md">
                <div class="card-body space-y-4">
                    <div class="text-4xl">🛍️</div>
                    <h3 class="card-title text-xl">Products</h3>
                    <p class="text-sm opacity-75">Manage your product inventory</p>
                </div>
            </a>

            <!-- POS Terminal -->
            <a href="{{ route('pos') }}" class="card bg-base-200 hover:bg-primary hover:text-primary-content transition-all cursor-pointer shadow-md">
                <div class="card-body space-y-4">
                    <div class="text-4xl">💳</div>
                    <h3 class="card-title text-xl">POS Terminal</h3>
                    <p class="text-sm opacity-75">Create new orders and process payments</p>
                </div>
            </a>
        </section>
    </div>
</x-app-layout>
