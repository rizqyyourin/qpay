<div class="min-h-screen bg-base-100">
    <!-- Page Header -->
    <div class="bg-primary text-primary-content p-4 md:p-6">
        <h1 class="text-2xl md:text-3xl font-bold">POS Terminal</h1>
        <p class="text-sm opacity-75">{{ now()->format('l, j F Y') }} | {{ now()->format('H:i') }}</p>
    </div>

    <div class="container mx-auto p-4 md:p-6 grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Left Section: Products -->
        <div class="lg:col-span-3 space-y-4">
            <!-- Search & Barcode Input -->
            <div class="card bg-base-200 shadow-md p-4">
                <h2 class="text-lg font-semibold mb-3">Product Search</h2>

                <!-- Barcode Scanner Input -->
                <div class="mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Barcode Scanner</span>
                    </label>
                    <input
                        type="text"
                        placeholder="Scan barcode here..."
                        wire:model.live="barcode"
                        class="input input-bordered w-full focus:input-primary"
                        autofocus
                    />
                    <p class="text-xs text-gray-500 mt-1">🔍 Automatically searches by barcode</p>
                </div>

                <!-- Text Search -->
                <div>
                    <label class="label">
                        <span class="label-text font-medium">Search by Name</span>
                    </label>
                    <input
                        type="text"
                        placeholder="Type product name..."
                        wire:model.live="search"
                        class="input input-bordered w-full focus:input-primary"
                    />
                </div>
            </div>

            <!-- Products Grid -->
            <div class="card bg-base-200 shadow-md p-4">
                <h2 class="text-lg font-semibold mb-4">Available Products</h2>

                @if($products->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($products as $product)
                            <button
                                wire:click="addToCart({{ $product->id }})"
                                class="card bg-white border-2 border-transparent hover:border-primary hover:shadow-lg transition-all p-3 text-left"
                            >
                                <!-- Product Image Placeholder -->
                                <div class="w-full h-20 md:h-24 bg-gray-200 rounded mb-2 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" />
                                    </svg>
                                </div>

                                <!-- Product Info -->
                                <h3 class="font-semibold text-sm line-clamp-2 mb-1">{{ $product->name }}</h3>

                                <!-- Price -->
                                <div class="text-primary font-bold text-sm mb-2">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </div>

                                <!-- Stock Status -->
                                <div class="flex items-center gap-1 text-xs">
                                    @if($product->stock_quantity > 0)
                                        <span class="badge badge-success badge-sm">Stock: {{ $product->stock_quantity }}</span>
                                    @else
                                        <span class="badge badge-error badge-sm">Out of Stock</span>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <!-- Pagination (if many products) -->
                    <div class="mt-4 flex justify-center">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.5 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM4.5 10a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm11 0a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM4 18a4 4 0 00-4-4h16a4 4 0 00-4 4v2H4v-2z" />
                        </svg>
                        <p class="text-gray-500 text-sm">No products found</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Section: Cart Sidebar -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Cart Header -->
            <div class="card bg-primary text-primary-content shadow-md p-4">
                <h2 class="text-lg font-bold">Shopping Cart</h2>
                <p class="text-sm opacity-75">{{ count($cart) }} item(s)</p>
            </div>

            <!-- Cart Items -->
            <div class="card bg-base-100 shadow-md p-4 min-h-[300px] max-h-[400px] overflow-y-auto border border-gray-200">
                @if(count($cart) > 0)
                    <div class="space-y-3">
                        @foreach($cart as $key => $item)
                            <div class="border-b pb-3">
                                <!-- Product Name & Remove -->
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-semibold text-sm line-clamp-2">{{ $item['name'] }}</h3>
                                    <button
                                        wire:click="removeFromCart({{ $key }})"
                                        class="btn btn-ghost btn-xs text-error"
                                    >
                                        ✕
                                    </button>
                                </div>

                                <!-- Quantity Control -->
                                <div class="flex items-center gap-2 mb-2">
                                    <button
                                        wire:click="updateQuantity({{ $key }}, {{ $item['quantity'] - 1 }})"
                                        class="btn btn-xs btn-outline"
                                    >
                                        −
                                    </button>
                                    <input
                                        type="number"
                                        min="1"
                                        wire:model.change="cart.{{ $key }}.quantity"
                                        class="input input-bordered input-xs w-12 text-center"
                                    />
                                    <button
                                        wire:click="updateQuantity({{ $key }}, {{ $item['quantity'] + 1 }})"
                                        class="btn btn-xs btn-outline"
                                    >
                                        +
                                    </button>
                                </div>

                                <!-- Price & Subtotal -->
                                <div class="flex justify-between text-xs mb-2">
                                    <span class="text-gray-600">{{ number_format($item['quantity'], 0) }} × Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</span>
                                    <span class="font-bold text-primary">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-full">
                        <svg class="w-12 h-12 text-gray-300 mb-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM5 16a2 2 0 110 4 2 2 0 010-4zm8 0a2 2 0 110 4 2 2 0 010-4z" />
                        </svg>
                        <p class="text-gray-500 text-sm font-medium">Cart is empty</p>
                    </div>
                @endif
            </div>

            <!-- Order Summary -->
            @if(count($cart) > 0)
                <div class="card bg-base-100 shadow-md p-4 space-y-3 border border-gray-200">
                    <!-- Subtotal -->
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>

                    <!-- Discount -->
                    <div class="flex justify-between text-sm">
                        <label class="text-gray-600">Discount:</label>
                        <div class="flex items-center gap-1">
                            <span class="text-error">-</span>
                            <input
                                type="number"
                                min="0"
                                wire:model.live="discount"
                                class="input input-bordered input-sm w-24 text-right"
                                placeholder="0"
                            />
                        </div>
                    </div>

                    <!-- Tax -->
                    <div class="flex justify-between text-sm">
                        <label class="text-gray-600">Tax (%):</label>
                        <div class="flex items-center gap-1">
                            <span class="text-success">+</span>
                            <input
                                type="number"
                                min="0"
                                max="100"
                                step="0.1"
                                wire:model.live="tax"
                                class="input input-bordered input-sm w-24 text-right"
                                placeholder="0"
                            />
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="divider my-2"></div>

                    <!-- Total -->
                    <div class="flex justify-between text-lg font-bold text-primary">
                        <span>Total:</span>
                        <span>Rp {{ number_format($cartTotal, 0, ',', '.') }}</span>
                    </div>

                    <!-- Payment Method -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text text-sm">Payment Method</span>
                        </label>
                        <select
                            wire:model="paymentMethod"
                            class="select select-bordered select-sm"
                        >
                            <option value="cash">Cash</option>
                            <option value="card">Debit/Credit Card</option>
                            <option value="transfer">Bank Transfer</option>
                            <option value="ewallet">E-Wallet</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            wire:click="clearCart"
                            class="btn btn-outline btn-error btn-sm"
                        >
                            Clear
                        </button>
                        <button
                            wire:click="checkout"
                            class="btn btn-primary btn-sm"
                        >
                            Checkout
                        </button>
                    </div>
                </div>
            @endif

            <!-- Quick Stats -->
            <div class="card bg-base-100 shadow-md p-4 border border-gray-200 hidden md:block">
                <h3 class="font-semibold text-sm mb-3">Quick Stats</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Items:</span>
                        <span class="font-bold">{{ count($cart) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Avg Item Price:</span>
                        <span class="font-bold">
                            @if(count($cart) > 0)
                                Rp {{ number_format($subtotal / count($cart), 0, ',', '.') }}
                            @else
                                Rp 0
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-focus barcode input on page load
    document.addEventListener('DOMContentLoaded', function() {
        const barcodeInput = document.querySelector('input[placeholder="Scan barcode here..."]');
        if (barcodeInput) barcodeInput.focus();
    });

    // Listen for cart updates from Livewire
    document.addEventListener('livewire:updated', function() {
        console.log('Cart updated');
    });
</script>
@endpush
