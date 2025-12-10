<x-app-layout>
    <div class="max-w-4xl mx-auto px-5 py-12 space-y-8">
        <!-- Header -->
        <div class="space-y-4">
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm bg-base-200 text-base-content hover:bg-base-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Products
            </a>
            <div>
                <h1 class="text-5xl font-black text-base-content">Product QR Code</h1>
                <p class="text-lg text-base-content/70 font-medium mt-2">{{ $product->name }}</p>
            </div>
        </div>

        <div class="grid gap-8 md:grid-cols-2">
            <!-- QR Code Preview -->
            <div class="space-y-6">
                <div class="card bg-base-100 border border-base-300 shadow-sm">
                    <div class="card-body space-y-6">
                        <h2 class="text-2xl font-bold text-base-content">QR Code Preview</h2>
                        
                        <!-- QR Code Display -->
                        <div class="bg-white p-8 rounded-lg flex items-center justify-center">
                            <div id="qrcode"></div>
                        </div>

                        <!-- Download Button -->
                        <div class="flex gap-2">
                            <a href="#" onclick="downloadQR()" class="btn btn-primary flex-1 gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download
                            </a>
                            <a href="#" onclick="printQR()" class="btn btn-outline flex-1 gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4H9a2 2 0 00-2 2v2a2 2 0 002 2h6a2 2 0 002-2v-2a2 2 0 00-2-2zm0 0h6"></path>
                                </svg>
                                Print
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="space-y-6">
                <div class="card bg-base-100 border border-base-300 shadow-sm">
                    <div class="card-body space-y-6">
                        <h2 class="text-2xl font-bold text-base-content">Product Details</h2>

                        <!-- Product Image -->
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

                        <div class="space-y-4">
                            <!-- Name -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold uppercase tracking-widest text-base-content/70">Product Name</label>
                                <p class="text-xl font-bold text-base-content">{{ $product->name }}</p>
                            </div>

                            <!-- Description -->
                            @if($product->description)
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold uppercase tracking-widest text-base-content/70">Description</label>
                                    <p class="text-base-content/80">{{ $product->description }}</p>
                                </div>
                            @endif

                            <!-- Price -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold uppercase tracking-widest text-base-content/70">Price</label>
                                <p class="text-4xl font-black text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            </div>

                            <!-- Stock -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold uppercase tracking-widest text-base-content/70">Stock</label>
                                <div class="flex items-center gap-3">
                                    <span class="badge {{ $product->stock > 0 ? 'badge-success' : 'badge-warning' }} badge-lg font-bold text-base">{{ $product->stock }}</span>
                                    <span class="text-base-content/70">{{ $product->stock > 0 ? 'Available' : 'Out of Stock' }}</span>
                                </div>
                            </div>

                            <!-- Barcode -->
                                    <p class="font-mono text-sm bg-base-200 p-2 rounded">{{ $product->barcode }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Edit Button -->
                        <div class="pt-4">
                            <a href="{{ route('products.index') }}" class="btn btn-primary w-full font-bold gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Product
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Generate QR Code with delay to ensure library loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const qrContainer = document.getElementById("qrcode");
                if (qrContainer && window.QRCode) {
                    // QR Code URL: guest scan → start guest checkout → add product
                    // Includes seller_id so guest can add items from this seller's store
                    const qrUrl = "{{ route('guest.start', $product->id) }}" + "?seller={{ Auth::id() }}";
                    
                    new QRCode(qrContainer, {
                        text: qrUrl,
                        width: 300,
                        height: 300,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            }, 100);
        });

        function downloadQR() {
            const canvas = document.querySelector('#qrcode canvas');
            if (!canvas) {
                alert('QR Code belum siap. Tunggu sebentar dan coba lagi.');
                return;
            }
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = 'qr-{{ $product->id }}.png';
            link.click();
        }

        function printQR() {
            const canvas = document.querySelector('#qrcode canvas');
            if (!canvas) {
                alert('QR Code belum siap. Tunggu sebentar dan coba lagi.');
                return;
            }
            
            const qrImage = canvas.toDataURL('image/png');
            const printWindow = window.open('', 'printQR', 'height=600,width=800');
            
            const htmlContent = '<html><head><title>QR Code - {{ $product->name }}</title>' +
                '<style>' +
                'body { font-family: Arial, sans-serif; padding: 30px; text-align: center; background: white; } ' +
                'h1 { font-size: 28px; margin-bottom: 20px; } ' +
                '.qr-container { margin: 30px 0; } ' +
                'img { max-width: 300px; height: auto; border: 2px solid #333; padding: 10px; } ' +
                '.details { margin-top: 30px; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto; } ' +
                '.detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd; } ' +
                '.label { font-weight: bold; } ' +
                '.value { text-align: right; } ' +
                '.price { font-size: 20px; font-weight: bold; color: #f59e0b; } ' +
                '@media print { body { padding: 0; } } ' +
                '</style>' +
                '</head><body>' +
                '<h1>{{ $product->name }}</h1>' +
                '<div class="qr-container">' +
                '<img src="' + qrImage + '" alt="QR Code"/>' +
                '</div>' +
                '<div class="details">' +
                '<div class="detail-row"><span class="label">Harga:</span><span class="value price">Rp {{ number_format($product->price, 0, ",", ".") }}</span></div>' +
                '<div class="detail-row"><span class="label">Stok:</span><span class="value">{{ $product->stock }} Unit</span></div>' +
                '</div>' +
                '</body></html>';
            
            printWindow.document.write(htmlContent);
            printWindow.document.close();
            
            setTimeout(function() {
                printWindow.print();
            }, 250);
        }
    </script>
</x-app-layout>
