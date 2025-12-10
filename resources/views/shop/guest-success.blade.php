<!DOCTYPE html>
<html lang="en" data-theme="bumblebee">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Completed - QPAY</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            padding-bottom: 0;
        }

        @media print {
            header, footer, .no-print {
                display: none !important;
            }

            main {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }

            .card {
                break-inside: avoid;
            }

            body {
                background: white !important;
            }

            * {
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-base-100 text-base-content">
    <div class="min-h-screen flex flex-col bg-base-100">
        <!-- Header -->
        <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100 shadow-sm no-print">
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
                <div></div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 w-full overflow-y-auto flex items-center justify-center">
            <div class="px-5 py-12 max-w-md mx-auto text-center space-y-8">
                <!-- Success Icon -->
                <div class="flex justify-center">
                    <div class="w-24 h-24 bg-success/20 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Success Message -->
                <div class="space-y-2">
                    <h1 class="text-4xl font-black text-base-content">Order Registered!</h1>
                </div>

                <!-- Order ID Highlight -->
                <div class="bg-base-100 border-2 border-primary rounded-lg p-8 space-y-4">
                    <p class="text-sm font-semibold uppercase tracking-widest text-base-content/70">Your Order ID</p>
                    <p class="text-5xl font-black text-primary tracking-wider font-mono">{{ $order->token }}</p>
                    <p class="text-base text-base-content/70">Show this ID to the cashier to complete your order</p>
                </div>

                <!-- Order Details Card -->
                <div class="card bg-base-100 border border-base-300 shadow-sm">
                    <div class="card-body space-y-4">
                        <div class="space-y-2 text-left">
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Order ID</span>
                                <span class="font-bold">{{ $order->token }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Buyer Name</span>
                                <span class="font-bold">{{ $order->buyer_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Phone</span>
                                <span class="font-bold">{{ $order->buyer_phone }}</span>
                            </div>
                        </div>

                        <div class="divider my-2"></div>

                        <div class="space-y-2 text-left">
                            <p class="text-sm font-semibold uppercase tracking-widest text-base-content/70">Items</p>
                            <div class="space-y-2">
                                @foreach($order->items ?? [] as $item)
                                    <div class="flex justify-between text-sm">
                                        <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                                        <span>Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="divider my-2"></div>

                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3 no-print">
                    <button onclick="captureScreenshot()" class="btn btn-primary w-full gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Print / Download Receipt
                    </button>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-base-300 bg-base-200 mt-12 no-print">
            <div class="max-w-7xl mx-auto px-5 py-8 text-center text-sm text-base-content/70">
                <p>&copy; 2025 QPAY. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script>
        function captureScreenshot() {
            // Open print dialog - user can save as PDF or image
            window.print();
        }
    </script>
</body>
</html>
