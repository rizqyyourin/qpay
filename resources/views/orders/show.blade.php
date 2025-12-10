<x-app-layout>
    <div class="p-4 md:p-8">
        <h1 class="text-3xl font-bold mb-6">Detail Pesanan: {{ $order->token }}</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Details -->
            <div class="lg:col-span-2 card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title mb-6">{{ $order->token }}</h2>

                    <!-- Customer Info -->
                    <div class="bg-base-200 p-4 rounded-lg mb-6">
                        <h3 class="font-bold mb-3">Customer Information</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Name:</span>
                                <span class="font-semibold">{{ $order->buyer_name ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Phone:</span>
                                <span class="font-semibold">{{ $order->buyer_phone ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="space-y-4 mb-6">
                        <h3 class="font-bold">Order Items</h3>
                        <div class="overflow-x-auto">
                            <table class="table w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left">Product</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Price</th>
                                        <th class="text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($order->items ?? [] as $item)
                                        <tr class="border-b">
                                            <td>{{ $item['name'] }}</td>
                                            <td class="text-center">{{ $item['quantity'] }}</td>
                                            <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                            <td class="text-right">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-base-content/50">No items</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="bg-base-200 p-4 rounded-lg space-y-2">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="font-semibold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($order->discount_amount && $order->discount_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span>Discount:</span>
                                <span class="font-semibold text-red-600">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($order->tax_amount && $order->tax_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span>Tax:</span>
                                <span class="font-semibold text-blue-600">+Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold border-t pt-2">
                            <span>Total:</span>
                            <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Status -->
            <div class="space-y-6">
                <!-- Order Info Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Order Information</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Date:</span>
                                <span>{{ $order->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Status:</span>
                                <span class="badge badge-success">Completed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h3 class="card-title text-lg mb-4">Payment Information</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Method:</span>
                                <span class="font-semibold">
                                    @switch($order->payment_method ?? '')
                                        @case('cash') 💵 Cash @break
                                        @case('card') 💳 Card @break
                                        @case('transfer') 🏦 Transfer @break
                                        @case('ewallet') 📱 E-Wallet @break
                                        @default - @break
                                    @endswitch
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/70">Total:</span>
                                <span class="font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card bg-base-100 shadow no-print">
                    <div class="card-body">
                        <div class="space-y-2">
                            <button class="btn btn-primary w-full" onclick="window.print()">Print Receipt</button>
                            <a href="{{ route('orders.index') }}" class="btn btn-outline w-full">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style media="print">
        @page {
            size: 80mm auto;
            margin: 0;
            padding: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 5mm;
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            width: 80mm;
        }

        .no-print {
            display: none !important;
        }

        x-app-layout {
            display: block !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .p-4,
        .md\:p-8,
        .p-4\.md\:p-8 {
            padding: 0 !important;
            margin: 0 !important;
        }

        .grid,
        .lg\:col-span-2,
        .gap-6 {
            display: block !important;
            width: 100% !important;
            gap: 0 !important;
        }

        .card {
            border: 1px solid #000;
            margin-bottom: 0;
            page-break-inside: avoid;
            box-shadow: none !important;
            border-radius: 0;
        }

        .card-body {
            padding: 3mm !important;
        }

        .card-title {
            font-size: 13px;
            font-weight: bold;
            margin: 2mm 0;
            text-align: center;
        }

        h1 {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            margin: 2mm 0;
            padding-bottom: 2mm;
            border-bottom: 1px solid #000;
        }

        h2, h3 {
            font-size: 11px;
            font-weight: bold;
            margin: 2mm 0 1mm 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2mm 0;
            font-size: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 2mm;
            text-align: left;
        }

        th {
            background: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
        }

        .bg-base-200 {
            background: #f5f5f5 !important;
            padding: 3mm !important;
            margin: 2mm 0;
        }

        .flex {
            display: flex !important;
        }

        .justify-between {
            justify-content: space-between !important;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .space-y-2 > div {
            margin-bottom: 2mm;
        }

        .border-b {
            border-bottom: 1px solid #ddd;
        }

        .border-t {
            border-top: 1px solid #000;
            padding-top: 1mm;
        }

        .font-semibold,
        .font-bold {
            font-weight: bold;
        }

        .text-lg {
            font-size: 12px;
        }

        .text-sm {
            font-size: 10px;
        }

        .badge {
            padding: 1mm 3mm;
            border-radius: 2px;
            font-weight: bold;
            font-size: 9px;
        }

        .text-red-600 {
            color: #dc2626;
        }

        .text-blue-600 {
            color: #2563eb;
        }

        /* Print receipt layout */
        .space-y-4 {
            margin: 2mm 0;
        }

        .overflow-x-auto {
            overflow: visible !important;
        }

        .lg\:col-span-2 {
            grid-column: unset !important;
        }

        /* Hide elements that shouldn't print */
        .shadow {
            box-shadow: none !important;
        }

        /* Footer separator */
        .card + .card {
            margin-top: 0;
        }

        /* Make summary clearer */
        .bg-base-200.p-4 {
            border: 1px dashed #999;
            margin: 2mm 0;
        }
    </style>
</x-app-layout>
