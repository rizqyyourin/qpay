<!DOCTYPE html>
<html lang="en" data-theme="bumblebee">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'QPAY - Minimal POS' }}</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- DaisyUI CDN -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.10/dist/full.min.css" rel="stylesheet" type="text/css" />
    
    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/brand.css') }}">

    @livewireStyles
</head>
<body class="bg-base-100 text-base-content">
    <div class="min-h-screen flex flex-col bg-base-100">
        <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100 shadow-sm">
            <div class="max-w-6xl mx-auto px-5 py-4 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                    <!-- Logo with QR concept -->
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
                    <span class="text-lg font-bold text-base-content tracking-tight">QPAY</span>
                </a>

                <div class="flex items-center gap-3 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-ghost">Logout</button>
                        </form>
                    @else
                        <livewire:nav-auth-buttons />
                    @endauth
                </div>
            </div>
        </header>

        <main class="flex-1 bg-base-100">
            {{ $slot }}
        </main>

        <footer class="border-t border-base-300 bg-base-200 mt-24">
            <div class="max-w-6xl mx-auto px-5 py-16 flex flex-col gap-10 md:flex-row md:items-center md:justify-between">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-primary rounded-md flex items-center justify-center shrink-0">
                            <div class="w-5 h-5 grid grid-cols-3 gap-0.5">
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
                        <span class="font-bold text-base-content">QPAY</span>
                    </div>
                    <p>Made by <a href="https://yourin.my.id" >Yourin</a></p>
                </div>
                <div class="flex flex-col gap-2 md:items-end text-sm text-base-content/70">
                    <a href="mailto:support@qpay.id" class="hover:text-base-content transition font-semibold">support@qpay.id</a>
                    <a href="tel:+6280012345678" class="hover:text-base-content transition font-semibold">+62 800 1234 5678</a>
                </div>
            </div>
        </footer>

        <!-- Auth Modal Component -->
        @livewire('auth-modal')
    </div>

    @livewireScripts
</body>
</html>
