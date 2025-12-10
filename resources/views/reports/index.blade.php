<x-app-layout>
    <div class="p-4 md:p-8">
        <h1 class="text-3xl font-bold mb-8">Sales Report</h1>

        <!-- Report Filters -->
        <div class="card bg-base-100 shadow mb-6 p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="date" class="input input-bordered" />
                <input type="date" class="input input-bordered" />
                <select class="select select-bordered">
                    <option disabled selected>Report Type</option>
                    <option>Daily</option>
                    <option>Weekly</option>
                    <option>Monthly</option>
                </select>
                <button class="btn btn-primary">Generate</button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card bg-gradient-to-br from-primary to-primary-focus text-primary-content shadow">
                <div class="card-body">
                    <p class="text-sm opacity-90">Total Sales</p>
                    <p class="text-3xl font-bold">Rp 12.450.000</p>
                    <p class="text-xs opacity-75">Period January 2025</p>
                </div>
            </div>

            <div class="card bg-gradient-to-br from-secondary to-secondary-focus text-secondary-content shadow">
                <div class="card-body">
                    <p class="text-sm opacity-90">Total Orders</p>
                    <p class="text-3xl font-bold">247</p>
                    <p class="text-xs opacity-75">+15% from last month</p>
                </div>
            </div>

            <div class="card bg-gradient-to-br from-accent to-accent-focus text-accent-content shadow">
                <div class="card-body">
                    <p class="text-sm opacity-90">Average Order</p>
                    <p class="text-3xl font-bold">Rp 50.404</p>
                    <p class="text-xs opacity-75">Per transaction</p>
                </div>
            </div>
        </div>

        <!-- Charts Placeholder -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title">Tren Penjualan</h2>
                    <div class="bg-base-200 rounded h-64 flex items-center justify-center">
                        <p class="text-gray-500">[Grafik Penjualan]</p>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title">Produk Terlaris</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span>Produk A</span>
                            <div class="progress progress-primary w-24 h-2"><div class="progress-value" style="width: 80%"></div></div>
                            <span class="font-bold">156 unit</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Produk B</span>
                            <div class="progress progress-secondary w-24 h-2"><div class="progress-value" style="width: 60%"></div></div>
                            <span class="font-bold">98 unit</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Produk C</span>
                            <div class="progress progress-accent w-24 h-2"><div class="progress-value" style="width: 45%"></div></div>
                            <span class="font-bold">72 unit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
