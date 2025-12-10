<x-public-layout>
    <div class="max-w-4xl mx-auto px-4 py-24 space-y-24">
        <!-- Hero Section -->
        <section class="fade-up text-center space-y-8">
            <div class="space-y-6">
                <div class="inline-block">
                </div>
                <h1 class="text-5xl md:text-6xl font-black text-base-content leading-tight">
                    Modernize your checkout with QR code shopping
                </h1>
                <p class="text-lg text-base-content/80 max-w-2xl mx-auto leading-relaxed font-medium">
                    Let customers scan product QR codes to build their cart, then let cashiers confirm and settle orders in seconds.
                </p>
            </div>
            <div class="flex justify-center gap-3 pt-4">
                <button onclick="document.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { mode: 'register' } }))" class="btn btn-primary btn-lg font-bold">Create your own QR now!</button>
            </div>
        </section>

        <!-- Divider -->
        <div class="divider divider-primary"></div>

        <!-- Flow Line -->
        <section class="fade-up delay-100 text-center">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-base-content/70">Make QR → Checkout → Cashier</p>
        </section>

        <!-- Metrics -->
        <section class="fade-up delay-150">
            <div class="stats stats-horizontal shadow-none w-full bg-base-100 rounded-lg border border-base-300">
                @foreach ([
                    ['value' => '2.1K', 'label' => 'Items catalogued daily'],
                    ['value' => '145K', 'label' => 'Scans per month'],
                    ['value' => '2m 32s', 'label' => 'Avg checkout time'],
                ] as $stat)
                    <div class="stat">
                        <div class="stat-value text-primary text-4xl font-black">{{ $stat['value'] }}</div>
                        <div class="stat-title text-xs uppercase tracking-widest font-bold text-base-content/70">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Flow Story -->
        <section class="space-y-12 fade-up delay-200">
            <div class="text-center space-y-3">
                <span class="badge badge-primary badge-outline font-bold text-xs uppercase tracking-widest">Quick Guide</span>
                <h2 class="text-4xl font-black text-base-content">Centered steps that guide every visitor</h2>
            </div>
            <div class="grid gap-8 md:grid-cols-3">
                @foreach ([
                    ['num' => '1', 'title' => 'Configure catalog', 'body' => 'Upload product data once, print QR tags automatically, and publish availability.'],
                    ['num' => '2', 'title' => 'Shopper scans', 'body' => 'Customers see price, notes, and variants in a centered card, then tap "add to cart."'],
                    ['num' => '3', 'title' => 'Cashier confirms', 'body' => 'Cashiers pick up the same cart, adjust, and settle in seconds.']
                ] as $card)
                    <div class="card bg-base-100 shadow-none border border-base-300 hover:border-primary/30 transition-colors">
                        <div class="card-body text-center space-y-3">
                            <div class="flex justify-center">
                                <span class="badge badge-primary badge-lg text-lg font-black">{{ $card['num'] }}</span>
                            </div>
                            <h3 class="text-lg font-bold text-base-content">{{ $card['title'] }}</h3>
                            <p class="text-sm text-base-content/70 leading-relaxed font-medium">{{ $card['body'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Testimonial -->
        <section class="space-y-8 fade-up delay-300">
            <div class="card bg-base-100 shadow-none border border-base-300">
                <div class="card-body text-center space-y-6 py-12">
                    <p class="text-2xl md:text-3xl font-bold text-base-content italic leading-relaxed">
                        "Everything is guided in the center of the screen—the admin adds a product, shoppers scan the QR, and the cashier closes it without bouncing tabs."
                    </p>
                    <div class="divider my-2"></div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <div class="text-4xl font-black text-primary">86%</div>
                            <div class="text-xs text-base-content/70 uppercase tracking-widest font-bold">QR Scan Rate</div>
                        </div>
                        <div>
                            <div class="text-4xl font-black text-primary">4.1/5</div>
                            <div class="text-xs text-base-content/70 uppercase tracking-widest font-bold">Happiness Score</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="fade-up delay-300 text-center space-y-8">
            <div class="space-y-4">
                <span class="badge badge-primary badge-outline font-bold text-xs uppercase tracking-widest">Ready to launch?</span>
                <h3 class="text-3xl font-black text-base-content">Let shoppers scan calmly and cashiers close quickly.</h3>
            </div>
            <div class="flex justify-center gap-3">
                <button onclick="document.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { mode: 'register' } }))" class="btn btn-primary btn-lg font-bold">Start free</button>
            </div>
        </section>
    </div>
</x-public-layout>
