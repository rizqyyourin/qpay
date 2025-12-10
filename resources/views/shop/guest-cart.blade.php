<!DOCTYPE html>
<html lang="en" data-theme="bumblebee">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shopping Cart - QPAY</title>
    
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
        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100 shadow-sm">
            <div class="px-5 py-4 flex items-center justify-between mx-auto max-w-7xl">
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
                <div class="text-sm font-semibold bg-primary text-primary-content px-4 py-2 rounded-lg">
                    Order: {{ $order->token }}
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 w-full overflow-y-auto">
            <div class="px-5 py-12 max-w-6xl mx-auto">
                <h1 class="text-4xl font-black text-base-content mb-8">Shopping Cart</h1>

                <div class="grid gap-8 lg:grid-cols-3">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2">
                        @if($order->items && count($order->items) > 0)
                            <div class="card bg-base-100 border border-base-300 shadow-sm">
                                <div class="card-body space-y-4 p-0 divide-y divide-base-300">
                                    @foreach($order->items as $productId => $item)
                                        @php
                                            $product = \App\Models\Product::find($productId);
                                            $stock = $product ? $product->stock : 0;
                                        @endphp
                                        <div class="p-4 flex items-center gap-4 hover:bg-base-200 transition">
                                            <!-- Item Info -->
                                            <div class="flex-1">
                                                <h3 class="font-bold text-lg">{{ $item['name'] }}</h3>
                                                <p class="text-primary font-bold text-lg">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                            </div>

                                            <!-- Quantity Controls -->
                                            <div class="flex items-center gap-2 border border-base-300 rounded-lg p-1">
                                                <button onclick="updateQuantity({{ $productId }}, {{ $item['quantity'] - 1 }}, {{ $stock }})" 
                                                        {{ $item['quantity'] <= 1 ? 'disabled title="Set to 0 to remove from cart"' : '' }}
                                                        class="btn btn-xs btn-ghost">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                                    </svg>
                                                </button>
                                                <span class="w-8 text-center font-bold" id="qty-{{ $productId }}">{{ $item['quantity'] }}</span>
                                                <button onclick="updateQuantity({{ $productId }}, {{ $item['quantity'] + 1 }}, {{ $stock }})" 
                                                        {{ $item['quantity'] >= $stock ? 'disabled title="Maximum stock reached"' : '' }}
                                                        class="btn btn-xs btn-ghost">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Subtotal -->
                                            <div class="text-right">
                                                <p class="font-bold text-base">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</p>
                                            </div>

                                            <!-- Remove Button -->
                                            <button onclick="showDeleteConfirmModal('{{ $productId }}', '{{ $item['name'] }}')" class="btn btn-xs btn-error btn-outline">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="card bg-base-100 border border-base-300 shadow-sm text-center py-12">
                                <div class="space-y-4">
                                    <svg class="w-16 h-16 mx-auto text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-lg font-semibold">Cart is Empty</p>
                                    <button onclick="openCameraQRScanner()" class="btn btn-primary btn-sm gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Start Shopping
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Checkout Summary -->
                    <div class="space-y-4">
                        <!-- Order Summary -->
                        <div class="card bg-base-100 border border-base-300 shadow-sm">
                            <div class="card-body space-y-4">
                                <h2 class="text-2xl font-bold">Order Summary</h2>

                                <div class="space-y-2 border-t border-b border-base-300 py-4">
                                    <div class="flex justify-between">
                                        <span>Subtotal</span>
                                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between font-bold text-lg">
                                        <span>Total</span>
                                        <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <!-- Buyer Info Form -->
                                <form action="{{ route('guest.checkout', $order->token) }}" method="POST" class="space-y-3">
                                    @csrf
                                    
                                    <div class="form-control w-full">
                                        <label class="label pb-1">
                                            <span class="label-text font-semibold text-sm">Your Name</span>
                                        </label>
                                        <input type="text" name="buyer_name" placeholder="Enter your name" class="input input-bordered input-sm w-full" required />
                                        @error('buyer_name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-control w-full">
                                        <label class="label pb-1">
                                            <span class="label-text font-semibold text-sm">Phone Number</span>
                                        </label>
                                        <input type="tel" name="buyer_phone" placeholder="Enter your phone" class="input input-bordered input-sm w-full" required />
                                        @error('buyer_phone') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary w-full font-bold gap-2 btn-sm mt-4" {{ !($order->items && count($order->items) > 0) ? 'disabled' : '' }}>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Complete Checkout
                                    </button>
                                </form>

                                <button onclick="openCameraQRScanner()" class="btn btn-outline w-full gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Scan Another Product
                                </button>
                            </div>
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

    <!-- Delete Confirmation Modal -->
    <dialog id="deleteConfirmModal" class="modal modal-middle">
        <div class="modal-box w-full max-w-sm bg-base-100 space-y-4">
            <!-- Alert Content -->
            <div role="alert" class="alert alert-error alert-outline">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2m2-2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <h3 class="font-bold">Remove Item from Cart?</h3>
                    <div class="text-sm">
                        <p id="deleteProductName" class="font-semibold"></p>
                        <p class="mt-2 opacity-90">This action cannot be undone.</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="modal-action gap-3 mt-6">
                <form method="dialog">
                    <button class="btn btn-ghost">Cancel</button>
                </form>
                <button id="confirmDeleteBtn" class="btn btn-error">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Remove
                </button>
            </div>
        </div>
    </dialog>

    <!-- Scan Another Product Modal -->
    <dialog id="scanProductModal" class="modal modal-middle">
        <div class="modal-box w-full max-w-md bg-base-100 space-y-4">
            <!-- Header -->
            <div>
                <h3 class="font-bold text-lg">Scan Another Product</h3>
                <p class="text-sm text-base-content/70 mt-1">Use your camera or enter the product link</p>
            </div>

            <!-- Camera Feed (will be populated by JS) -->
            <div id="cameraContainer" class="bg-base-200 rounded-lg overflow-hidden">
                <video id="cameraFeed" class="w-full aspect-square object-cover" style="display: none;"></video>
                <div id="noCameraMessage" class="w-full aspect-square flex items-center justify-center text-center">
                    <div class="space-y-2">
                        <svg class="w-12 h-12 mx-auto text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m0 0H9a2 2 0 00-2 2v4a2 2 0 002 2h6a2 2 0 002-2v-4a2 2 0 00-2-2zm0 0V9a2 2 0 00-2-2m0 0V5a2 2 0 00-2-2H7a2 2 0 00-2 2v4"></path>
                        </svg>
                        <p class="text-sm">Camera not available</p>
                    </div>
                </div>
            </div>

            <!-- Manual Input -->
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-semibold">Or paste product URL:</span>
                </label>
                <input 
                    type="text" 
                    id="productUrlInput" 
                    placeholder="e.g., http://qpay.test/shop/product/51" 
                    class="input input-bordered focus:input-primary w-full" />
                <label class="label">
                    <span class="label-text-alt text-xs text-base-content/50">Paste full product URL from QR code</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="modal-action gap-2 mt-6">
                <form method="dialog">
                    <button class="btn btn-ghost">Cancel</button>
                </form>
                <button onclick="processProductUrl()" class="btn btn-primary gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Add to Cart
                </button>
            </div>
        </div>
    </dialog>

    <script>
        const token = '{{ $order->token }}';
        let pendingDeleteProductId = null;
        
        // Set cookie for persistent session
        document.cookie = `guest_session_token=${token}; path=/; max-age=${60*60*24}`;

        function showDeleteConfirmModal(productId, productName) {
            pendingDeleteProductId = productId;
            document.getElementById('deleteProductName').textContent = `Remove "${productName}" from cart?`;
            document.getElementById('confirmDeleteBtn').onclick = () => removeItem(productId);
            document.getElementById('deleteConfirmModal').showModal();
        }

        function removeItem(productId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            console.log('CSRF Token:', csrfToken);
            console.log('Fetching:', `/guest/${token}/remove/${productId}`);
            
            fetch(`/guest/${token}/remove/${productId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                }
            })
            .then(res => {
                console.log('Response status:', res.status);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    // Close modal before reload
                    document.getElementById('deleteConfirmModal').close();
                    location.reload();
                } else {
                    alert('⚠️ Failed to remove item');
                }
            })
            .catch(err => {
                console.error('Full error:', err);
                alert('❌ Error removing item: ' + err.message);
            });
        }

        function updateQuantity(productId, quantity, maxStock) {
            // Validate quantity
            if (quantity < 0) {
                alert('❌ Quantity cannot be negative.\n\nTo remove this product, use the Delete button.');
                return;
            }

            if (quantity > maxStock) {
                alert(`❌ Insufficient stock!\n\nAvailable: ${maxStock} unit(s)\nRequested: ${quantity}`);
                return;
            }

            if (quantity === 0) {
                // Ask for confirmation when setting to 0
                if (confirm('This will remove the product from cart. Continue?')) {
                    submitQuantityUpdate(productId, quantity);
                }
                return;
            }

            submitQuantityUpdate(productId, quantity);
        }

        function submitQuantityUpdate(productId, quantity) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            console.log('Updating product', productId, 'to quantity', quantity);

            fetch(`/guest/${token}/update-quantity`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(res => {
                console.log('Response status:', res.status);
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    location.reload();
                } else {
                    alert('⚠️ ' + (data.error || 'Failed to update quantity'));
                }
            })
            .catch(err => {
                console.error('Full error:', err);
                alert('❌ Error updating quantity: ' + err.message);
            });
        }

        function openCameraQRScanner() {
            document.getElementById('scanProductModal').showModal();
            
            // Try to access camera
            initializeCamera();
        }

        function initializeCamera() {
            const video = document.getElementById('cameraFeed');
            const noCameraMsg = document.getElementById('noCameraMessage');
            
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                })
                .then(stream => {
                    video.srcObject = stream;
                    video.style.display = 'block';
                    noCameraMsg.style.display = 'none';
                })
                .catch(err => {
                    console.log('Camera access denied:', err);
                    video.style.display = 'none';
                    noCameraMsg.style.display = 'flex';
                });
            } else {
                noCameraMsg.style.display = 'flex';
            }
        }

        function processProductUrl() {
            const urlInput = document.getElementById('productUrlInput').value.trim();
            
            if (!urlInput) {
                alert('❌ Please enter a product URL');
                return;
            }
            
            // More flexible regex that handles various URL formats
            // Matches: /product/51, product/51, product=51, ?product=51
            const productIdMatch = urlInput.match(/(?:product[\/=]|product_id[\/=])(\d+)/i);
            
            if (productIdMatch && productIdMatch[1]) {
                const productId = productIdMatch[1];
                document.getElementById('scanProductModal').close();
                window.location.href = `/shop/product/${productId}?token=${token}`;
            } else {
                alert('❌ Invalid product URL format.\n\nSupported formats:\n• http://qpay.test/shop/product/51\n• /shop/product/51\n• product/51');
            }
        }

        // Allow Enter key to submit
        document.addEventListener('DOMContentLoaded', function() {
            const urlInput = document.getElementById('productUrlInput');
            if (urlInput) {
                urlInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        processProductUrl();
                    }
                });
            }
        });
    </script>
</body>
</html>
