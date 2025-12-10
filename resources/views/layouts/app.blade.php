<!DOCTYPE html>
<html lang="id" data-theme="bumblebee">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'QPAY - POS System' }}</title>
    
    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    <style>
        body {
            padding-bottom: 64px;
        }

        @media (min-width: 1024px) {
            body {
                padding-bottom: 0;
            }
        }
    </style>
</head>
<body class="bg-base-100 text-base-content">
    <div class="min-h-screen flex flex-col bg-base-100">
        <!-- Top Navbar (Desktop & Mobile) -->
        <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100 shadow-sm">
            <div class="px-5 py-4 flex items-center justify-between mx-auto max-w-7xl">
                <!-- Left: Logo with Text -->
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

                <!-- Center: Navigation (Desktop Only) -->
                <nav class="hidden lg:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-base-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('products.index') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-base-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                        <span>Products</span>
                    </a>

                    <a href="{{ route('orders.index') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-base-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14a1 1 0 011 1v10a1 1 0 01-1 1H5a1 1 0 01-1-1V10a1 1 0 011-1z"></path>
                        </svg>
                        <span>Orders</span>
                    </a>

                    <a href="{{ route('pos') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm hover:bg-base-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>POS Terminal</span>
                    </a>
                </nav>

                <!-- Right: Profile Dropdown -->
                <div class="dropdown dropdown-end">
                    <button tabindex="0" class="btn btn-ghost btn-sm btn-circle avatar focus:outline-none">
                        <div class="w-9 h-9 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </button>
                    <div tabindex="0" class="dropdown-content z-10 card card-compact bg-base-100 text-base-content rounded-lg w-64 shadow-lg border border-base-300">
                        <div class="card-body gap-3">
                            <!-- User Info -->
                            <div class="space-y-1 pb-3 border-b border-base-300">
                                <p class="font-bold text-sm">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-base-content/60">{{ auth()->user()->email }}</p>
                            </div>

                            <!-- Menu Items -->
                            <div class="space-y-2">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-base font-semibold text-base-content hover:bg-base-200 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Profile</span>
                                </a>

                                <!-- Logout -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-base font-semibold text-error hover:bg-error/10 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto w-full">
            <div class="mx-auto max-w-7xl">
                {{ $slot }}
            </div>
        </main>

        <!-- Bottom Navigation (Mobile Only) -->
        <nav class="fixed bottom-0 left-0 right-0 z-40 border-t border-base-300 bg-base-100 shadow-lg lg:hidden">
            <div class="flex items-center justify-around">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 px-4 py-3 text-xs font-semibold hover:bg-base-200 transition flex-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 11l4-4"></path>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('products.index') }}" class="flex flex-col items-center gap-1 px-4 py-3 text-xs font-semibold hover:bg-base-200 transition flex-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <span>Products</span>
                </a>

                <a href="{{ route('orders.index') }}" class="flex flex-col items-center gap-1 px-4 py-3 text-xs font-semibold hover:bg-base-200 transition flex-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14a1 1 0 011 1v10a1 1 0 01-1 1H5a1 1 0 01-1-1V10a1 1 0 011-1z"></path>
                    </svg>
                    <span>Orders</span>
                </a>

                <a href="{{ route('pos') }}" class="flex flex-col items-center gap-1 px-4 py-3 text-xs font-semibold hover:bg-base-200 transition flex-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>POS</span>
                </a>
            </div>
        </nav>

        <footer class="border-t border-base-300 bg-base-200 mt-24 hidden lg:block">
            <div class="max-w-7xl mx-auto px-5 py-8 text-center text-sm text-base-content/70">
                <p>&copy; 2025 QPAY. All rights reserved.</p>
            </div>
        </footer>
    </div>

    @livewireScripts
</body>
</html>