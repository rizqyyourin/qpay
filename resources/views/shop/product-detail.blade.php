<!DOCTYPE html>
<html lang="en" data-theme="bumblebee">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - QPAY</title>
    
    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            padding-bottom: 0;
        }
    </style>
</head>
<body class="bg-base-100 text-base-content">
    <div class="min-h-screen flex flex-col bg-base-100">
        <!-- Simple Header -->
        <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100 shadow-sm">
            <div class="px-5 py-4 flex items-center justify-between mx-auto max-w-7xl">
                <!-- Left: Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center transition-transform group-hover:scale-110">
                        <div class="w-6 h-6 grid grid-cols-3 gap-0.5">
                            <div class="bg-primary-content rounded-sm"></div>
                            <div class="bg-primary-content rounded-sm"></div>
                            <div></div>
                            <div class="bg-primary-content rounded-sm"></div>
                            <div></div>
                            <div class="bg-primary-content rounded-sm"></div>
                            <div></div>
                            <div class="bg-primary-content rounded-sm"></div>
                            <div class="bg-primary-content rounded-sm"></div>
                        </div>
                    </div>
                    <span class="text-xl font-black text-base-content tracking-tight">QPAY</span>
                </a>

                <!-- Right: Empty for now -->
                <div></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 w-full overflow-y-auto flex items-center justify-center">
            <div class="px-5 py-12 max-w-2xl w-full">
                <div class="grid gap-8 md:grid-cols-2">
                    <!-- Product Image/QR Section -->
                    <div class="space-y-6">
                        <div class="card bg-base-100 border border-base-300 shadow-sm">
                            <div class="card-body space-y-6">
                                <div class="aspect-square bg-base-200 rounded-lg flex items-center justify-center overflow-hidden">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-center">
                                            <svg class="w-16 h-16 mx-auto text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <p class="text-sm text-base-content/50 mt-2">No product image</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-6">
                        <!-- Name & Price -->
                        <div class="space-y-4">
                            <h1 class="text-4xl font-black text-base-content">{{ $product->name }}</h1>
                            <div class="text-5xl font-black text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                        </div>

                        <!-- Description -->
                        @if($product->description)
                            <div class="space-y-2">
                                <h3 class="text-sm font-semibold uppercase tracking-widest text-base-content/70">Description</h3>
                                <p class="text-base-content/80 leading-relaxed">{{ $product->description }}</p>
                            </div>
                        @endif

                        <!-- Stock Status -->
                        <div class="card bg-base-200 border border-base-300">
                            <div class="card-body">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold uppercase tracking-widest text-base-content/70">Available Stock</span>
                                    <span class="badge {{ $product->stock > 0 ? 'badge-success' : 'badge-warning' }} whitespace-nowrap">
                                        {{ $product->stock > 0 ? $product->stock . ' units' : 'Out of Stock' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Add to Cart Button -->
                        @if($product->stock > 0)
                            @php
                                $cartUrl = route('guest.start', $product->id) . '?seller=' . $product->user_id;
                                $token = request()->query('token') ?? request()->cookie('guest_order_token');
                                if ($token) {
                                    $cartUrl .= '&token=' . $token;
                                }
                            @endphp
                            <a href="{{ $cartUrl }}" class="btn btn-primary btn-lg w-full gap-2 font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Add to Cart
                            </a>
                        @else
                            <button class="btn btn-disabled btn-lg w-full gap-2 font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Out of Stock
                            </button>
                        @endif

                        <!-- Product Info -->
                        <div class="divider"></div>
                        <div class="space-y-3 text-sm">
                            @if($product->barcode)
                                <div class="flex justify-between items-center">
                                    <span class="text-base-content/70">Barcode:</span>
                                    <code class="bg-base-200 px-3 py-1 rounded">{{ $product->barcode }}</code>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-base-300 bg-base-200 mt-12">
            <div class="max-w-7xl mx-auto px-5 py-8 text-center text-sm text-base-content/70">
                <p>&copy; 2025 QPAY. All rights reserved.</p>
            </div>
        </footer>
    </div>
</body>
</html>
