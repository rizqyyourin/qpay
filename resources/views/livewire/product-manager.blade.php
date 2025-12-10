<div class="max-w-7xl mx-auto px-5 space-y-6"
    @redirect.window="window.location.href = $event.detail.url">
    <!-- Search & Add Button -->
    <div class="flex items-center justify-between gap-4">
        <div class="form-control flex-1">
            <input 
                type="text" 
                wire:model.live="search"
                placeholder="Search products..." 
                class="input input-bordered focus:input-primary w-full" />
        </div>
        <button 
            wire:click="openForm" 
            class="btn btn-primary font-bold gap-2 whitespace-nowrap">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Product
        </button>
    </div>

        <!-- Products Grid -->
        @if(count($products) > 0)
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                @foreach($products as $product)
                    <div class="card bg-base-100 border border-base-300 hover:border-primary/30 transition-colors shadow-sm overflow-hidden">
                        <!-- Product Image -->
                        <div class="w-full h-20 bg-base-200 overflow-hidden">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-base-content/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <div class="card-body space-y-2 p-3">
                            <!-- Product Header -->
                            <div class="space-y-2">
                                <h3 class="text-sm font-bold text-base-content line-clamp-1">{{ $product->name }}</h3>
                                <p class="text-xs text-base-content/70 line-clamp-1">{{ $product->description }}</p>
                            </div>

                            <div class="divider my-1"></div>

                            <!-- Product Details -->
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-base-content/70">Price:</span>
                                    <span class="font-bold text-primary text-base">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-base-content/70">Stock:</span>
                                    <span class="badge {{ $product->stock > 0 ? 'badge-success' : 'badge-warning' }} badge-sm">{{ $product->stock }}</span>
                                </div>
                            </div>

                            <div class="divider my-1"></div>

                            <!-- Actions -->
                            <div class="flex gap-1">
                                <button 
                                    wire:click="openForm({{ $product->id }})"
                                    class="btn btn-xs btn-outline flex-1">
                                    Edit
                                </button>
                                <button 
                                    onclick="openQRModal('qr-modal-{{ $product->id }}', '{{ $product->id }}')"
                                    class="btn btn-xs btn-outline flex-1">
                                    QR
                                </button>
                                <button 
                                    wire:click="confirmDelete({{ $product->id }})"
                                    class="btn btn-xs btn-error btn-outline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- QR Modal -->
                            <dialog id="qr-modal-{{ $product->id }}" class="modal modal-middle">
                                <div class="modal-box w-full max-w-md bg-base-100 space-y-4 relative">
                                    <!-- Close Button (Top Right) -->
                                    <form method="dialog" class="absolute top-4 right-4">
                                        <button class="btn btn-sm btn-circle btn-error">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Header -->
                                    <div class="pr-12">
                                        <h3 class="font-bold text-lg">QR Code - {{ $product->name }}</h3>
                                        <p class="text-sm text-base-content/70 mt-1">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                    </div>

                                    <!-- QR Code Display -->
                                    <div class="bg-white p-6 rounded-lg flex items-center justify-center border-2 border-base-300">
                                        <div id="qrcode-{{ $product->id }}"></div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="flex gap-2">
                                        <button onclick="downloadQR('qrcode-{{ $product->id }}', '{{ $product->id }}')" class="btn btn-primary flex-1 gap-2 btn-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Download
                                        </button>
                                        <button onclick="printQR('qrcode-{{ $product->id }}', '{{ $product->name }}', 'Rp {{ number_format($product->price, 0, ",", ".") }}', '{{ $product->stock }}')" class="btn btn-outline flex-1 gap-2 btn-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4H9a2 2 0 00-2 2v2a2 2 0 002 2h6a2 2 0 002-2v-2a2 2 0 00-2-2zm0 0h6"></path>
                                            </svg>
                                            Print
                                        </button>
                                    </div>
                                </div>
                                <form method="dialog" class="modal-backdrop">
                                    <button></button>
                                </form>
                            </dialog>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $products->links() }}
            </div>
        @else
            <div class="card bg-base-100 border border-base-300 text-center py-12">
                <div class="card-body space-y-4">
                    <svg class="w-16 h-16 mx-auto text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-lg font-semibold text-base-content">No products yet</p>
                    <p class="text-base-content/70">Create your first product to get started</p>
                    <button 
                        wire:click="openForm"
                        class="btn btn-primary btn-sm mx-auto">
                        Create Product
                    </button>
                </div>
            </div>
        @endif

        <!-- Form Modal -->
        @if($showForm)
            <dialog class="modal modal-middle" open @click.self="$wire.closeForm()">
                <div class="modal-box w-full max-w-md" @click.stop>
                    <div class="space-y-6">
                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <h2 class="text-3xl font-black text-base-content">
                                {{ $editingProduct ? 'Edit Product' : 'New Product' }}
                            </h2>
                            <form method="dialog">
                                <button class="btn btn-ghost btn-sm btn-circle">✕</button>
                            </form>
                        </div>

                        <!-- Form -->
                        <form wire:submit.prevent="save" class="space-y-4">
                            <!-- Name -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Product Name <span class="text-error">*</span></span>
                                </label>
                                <input 
                                    type="text"
                                    wire:model="name"
                                    placeholder="Product name"
                                    class="input input-bordered focus:input-primary w-full" />
                                @error('name') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <!-- Image Upload -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Product Image</span>
                                </label>
                                <div class="text-xs text-base-content/70 mb-2">📁 Max: 5MB | Format: JPG, PNG, GIF, WebP</div>
                                <div class="flex flex-col gap-3">
                                    <!-- Fixed Container untuk Preview -->
                                    <div class="w-full h-64 bg-base-200 rounded-lg overflow-hidden shrink-0 flex items-center justify-center border-2 border-base-300">
                                        @if($currentImage)
                                            <img src="{{ asset('storage/' . $currentImage) }}" alt="Current product image" class="max-w-full max-h-full object-contain">
                                        @elseif($image)
                                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="max-w-full max-h-full object-contain">
                                        @else
                                            <div class="text-center">
                                                <svg class="w-12 h-12 mx-auto text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <p class="text-sm text-base-content/50 mt-2">Select image</p>
                                            </div>
                                        @endif
                                    </div>
                                    <input 
                                        type="file"
                                        wire:model="image"
                                        accept="image/*"
                                        class="file-input file-input-bordered focus:file-input-primary w-full"
                                        @change="validateImageFile($event)" />
                                </div>
                                @error('image') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <!-- Description -->
                            <div class="form-control w-full">
                                <label class="label pb-2">
                                    <span class="label-text font-semibold">Description</span>
                                </label>
                                <textarea 
                                    wire:model="description"
                                    placeholder="Product description"
                                    class="textarea textarea-bordered focus:textarea-primary h-20 w-full"></textarea>
                                @error('description') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <!-- Price -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Price (Rp) <span class="text-error">*</span></span>
                                </label>
                                <input 
                                    type="number"
                                    wire:model="price"
                                    step="0.01"
                                    min="0"
                                    placeholder="0"
                                    class="input input-bordered focus:input-primary w-full" />
                                @error('price') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <!-- Stock -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-semibold">Stock <span class="text-error">*</span></span>
                                </label>
                                <input 
                                    type="number"
                                    wire:model="stock"
                                    min="0"
                                    placeholder="0"
                                    class="input input-bordered focus:input-primary w-full" />
                                @error('stock') <span class="label-text-alt text-error">{{ $message }}</span> @enderror
                            </div>

                            <!-- Buttons -->
                            <div class="flex gap-2 pt-4 modal-action">
                                <form method="dialog" class="flex gap-2 w-full">
                                    <button 
                                        type="button"
                                        wire:click="closeForm"
                                        class="btn btn-outline flex-1">
                                        Batal
                                    </button>
                                </form>
                                <button 
                                    type="submit"
                                    class="btn btn-primary flex-1 font-bold"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="loading">
                                    {{ $editingProduct ? 'Update' : 'Create' }} Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </dialog>
        @endif

        <!-- Delete Confirmation Modal -->
        @if($productToDelete)
            <dialog class="modal modal-middle" open @click.self="$wire.cancelDelete()">
                <div class="modal-box w-full max-w-sm" @click.stop>
                    <!-- Alert Content -->
                    <div role="alert" class="alert alert-error alert-outline">
                        <div>
                            <h3 class="font-bold">Delete Product?</h3>
                            <div class="text-sm">Product "<span class="font-semibold">{{ $productToDelete->name }}</span>" will be permanently deleted and cannot be recovered.</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="modal-action gap-3 mt-6">
                        <form method="dialog">
                            <button class="btn btn-ghost">Cancel</button>
                        </form>
                        <button 
                            wire:click="proceedDelete"
                            class="btn btn-error">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </dialog>
        @endif
        @if(session('message'))
            <div class="alert alert-success fixed bottom-4 right-4 max-w-sm shadow-lg" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function showAlert(type, title, message) {
            // Create modal container
            const modalId = 'alert-modal-' + Date.now();
            
            const modalHTML = `
                <dialog id="${modalId}" class="modal modal-open">
                    <div class="modal-box w-full max-w-md">
                        <h3 class="font-bold text-lg">${title}</h3>
                        <p class="py-4 text-sm text-base-content/80">${message}</p>
                        <div class="modal-action">
                            <button onclick="document.getElementById('${modalId}').close(); document.getElementById('${modalId}').remove();" class="btn btn-primary btn-sm">OK</button>
                        </div>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button onclick="document.getElementById('${modalId}').remove();">close</button>
                    </form>
                </dialog>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            document.getElementById(modalId).showModal();
        }

        function validateImageFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            const maxSize = 5 * 1024 * 1024;
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (file.size > maxSize) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                showAlert('error', 'Image too large!', `File size: ${sizeMB}MB<br>Max size: 5MB<br><br>Please use a smaller image.`);
                event.target.value = '';
            } else if (!allowedTypes.includes(file.type)) {
                showAlert('error', 'Image format not supported!', `Supported formats: JPG, PNG, GIF, WebP<br>Your file format: ${file.type || 'Unknown'}`);
                event.target.value = '';
            }
        }

        function openQRModal(modalId, productId) {
            const modal = document.getElementById(modalId);
            const containerId = `qrcode-${productId}`;
            
            modal.showModal();
            
            // Generate QR after modal opens
            setTimeout(() => {
                const container = document.getElementById(containerId);
                if (container && !container.querySelector('canvas')) {
                    const baseUrl = `{{ route('shop.product', 999999) }}`.replace('999999', productId);
                    new QRCode(container, {
                        text: baseUrl,
                        width: 250,
                        height: 250,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            }, 100);
        }

        function downloadQR(containerId, productId) {
            const container = document.getElementById(containerId);
            const canvas = container.querySelector('canvas');
            
            if (!canvas) {
                alert('QR Code belum siap. Tunggu sebentar dan coba lagi.');
                return;
            }
            
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = `qr-${productId}.png`;
            link.click();
        }

        function printQR(containerId, productName, price, stock) {
            const container = document.getElementById(containerId);
            const canvas = container.querySelector('canvas');
            
            if (!canvas) {
                alert('QR Code belum siap. Tunggu sebentar dan coba lagi.');
                return;
            }
            
            const qrImage = canvas.toDataURL('image/png');
            const printWindow = window.open('', 'printQR', 'height=600,width=800');
            
            const htmlContent = `<html><head><title>QR Code - ${productName}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 30px; text-align: center; background: white; }
                    h1 { font-size: 24px; margin-bottom: 20px; }
                    .qr-container { margin: 30px 0; }
                    img { max-width: 300px; height: auto; border: 2px solid #333; padding: 10px; }
                    .details { margin-top: 30px; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto; }
                    .detail-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #ddd; }
                    .label { font-weight: bold; }
                    .value { text-align: right; }
                    .price { font-size: 18px; font-weight: bold; color: #f59e0b; }
                    @media print { body { padding: 0; } }
                </style>
            </head><body>
                <h1>${productName}</h1>
                <div class="qr-container">
                    <img src="${qrImage}" alt="QR Code"/>
                </div>
                <div class="details">
                    <div class="detail-row"><span class="label">Harga:</span><span class="value price">${price}</span></div>
                    <div class="detail-row"><span class="label">Stok:</span><span class="value">${stock} Unit</span></div>
                </div>
            </body></html>`;
            
            printWindow.document.write(htmlContent);
            printWindow.document.close();
            
            setTimeout(() => {
                printWindow.print();
            }, 250);
        }
    </script>
