<x-app-layout>
    <div class="p-4 md:p-8">
        <h1 class="text-3xl font-bold mb-6">Complete Order</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Order Search & Items -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order ID Input Section -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Enter Order ID
                        </h2>
                        <div class="form-control">
                            <input 
                                type="text" 
                                id="order-id-input"
                                placeholder="e.g., 48B8C6FD"
                                class="input input-bordered w-full focus:input-primary uppercase mb-3"
                                autofocus
                            />
                            <button onclick="searchOrder()" class="btn btn-primary w-full sm:w-auto">Search</button>
                        </div>
                    </div>
                </div>

                <!-- Order Items Section -->
                <div id="order-items-section" class="hidden space-y-6">
                    <!-- Order Info Card -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title mb-4">Customer Information</h2>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-base-content/70">Order ID</p>
                                        <p class="font-bold text-lg" id="order-id-display">-</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-base-content/70">Status</p>
                                        <p class="font-bold badge badge-lg" id="order-status-badge">-</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-base-content/70">Customer Name</p>
                                        <p class="font-bold text-lg" id="order-customer-name">-</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-base-content/70">Phone</p>
                                        <p class="font-bold" id="order-customer-phone">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Management Card -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title mb-4">Order Items (Fixed)</h2>
                            <div class="overflow-x-auto">
                                <table class="table table-sm w-full">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Qty</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="order-items-table">
                                        <tr>
                                            <td colspan="4" class="text-center py-8 text-base-content/50">No items loaded</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="empty-state" class="card bg-base-200 shadow">
                    <div class="card-body text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-base-content/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-lg text-base-content/70">Enter an Order ID to get started</p>
                    </div>
                </div>
            </div>

            <!-- Right: Payment Summary -->
            <div class="space-y-6">
                <!-- Order Summary Card -->
                <div id="summary-section" class="hidden">
                    <div class="card bg-primary text-primary-content shadow-lg sticky top-20">
                        <div class="card-body">
                            <h2 class="card-title mb-4 text-xl">Order Summary</h2>

                            <!-- Totals -->
                            <div class="space-y-2 mb-4 pb-4 border-b border-primary-focus">
                                <div class="flex justify-between text-sm">
                                    <span>Subtotal:</span>
                                    <span id="summary-subtotal">Rp 0</span>
                                </div>
                                <div class="form-control">
                                    <label class="label pb-1">
                                        <span class="label-text text-sm">Discount (Rp):</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        id="discount-input"
                                        placeholder="Rp 0"
                                        class="input input-sm input-bordered text-base-content"
                                        min="0"
                                        onchange="calculateTotal()"
                                    />
                                </div>
                                <div class="form-control">
                                    <label class="label pb-1">
                                        <span class="label-text text-sm">Tax (%):</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        id="tax-input"
                                        placeholder="0"
                                        class="input input-sm input-bordered text-base-content"
                                        min="0"
                                        max="100"
                                        onchange="calculateTotal()"
                                    />
                                </div>
                                <div class="flex justify-between text-lg font-bold mt-3 pt-2 border-t border-primary-focus">
                                    <span>Total:</span>
                                    <span id="summary-total">Rp 0</span>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="form-control mb-4">
                                <label class="label pb-1">
                                    <span class="label-text text-sm">Payment Method:</span>
                                </label>
                                <select id="payment-method" class="select select-bordered select-sm text-base-content">
                                    <option value="cash">💵 Cash</option>
                                    <option value="card">💳 Card</option>
                                    <option value="transfer">🏦 Transfer</option>
                                    <option value="ewallet">📱 E-Wallet</option>
                                </select>
                            </div>

                            <!-- Complete Button -->
                            <button onclick="completeOrder()" class="btn btn-accent w-full font-bold gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Finish Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <dialog id="confirmModal" class="modal modal-middle">
        <div class="modal-box w-full max-w-sm bg-white">
            <h3 class="font-bold text-lg mb-4">Confirm Order Completion</h3>
            <div class="space-y-3 mb-6">
                <div class="flex justify-between">
                    <span class="text-base-content/70">Order ID:</span>
                    <span class="font-semibold" id="modal-order-id">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/70">Payment Method:</span>
                    <span class="font-semibold" id="modal-payment-method">-</span>
                </div>
                <div class="flex justify-between text-lg">
                    <span class="text-base-content/70">Total:</span>
                    <span class="font-bold" id="modal-total">Rp 0</span>
                </div>
            </div>
            <div class="modal-action">
                <form method="dialog" class="flex gap-2 w-full">
                    <button class="btn flex-1">Cancel</button>
                </form>
                <button onclick="confirmComplete()" class="btn btn-primary flex-1">Confirm</button>
            </div>
        </div>
    </dialog>

    <script>
        let currentOrder = null;

        function showAlert(title, message) {
            const alertModal = document.createElement('dialog');
            alertModal.className = 'modal modal-middle';
            alertModal.innerHTML = `
                <div class="modal-box w-full max-w-sm text-center">
                    <h3 class="font-bold text-lg mb-4">${title}</h3>
                    <p class="text-base-content/70 mb-6">${message}</p>
                    <form method="dialog">
                        <button class="btn btn-primary w-full">OK</button>
                    </form>
                </div>
            </dialog>`;
            document.body.appendChild(alertModal);
            alertModal.showModal();
            alertModal.addEventListener('close', () => {
                alertModal.remove();
            });
        }

        async function searchOrder() {
            const orderId = document.getElementById('order-id-input').value.trim().toUpperCase();
            
            if (!orderId) {
                showAlert('Empty Order ID', 'Please enter an Order ID');
                return;
            }

            try {
                const response = await fetch(`/api/guest-orders/${orderId}`);
                
                if (!response.ok) {
                    if (response.status === 404) {
                        showAlert('Order Not Found', 'No order found with ID: ' + orderId);
                    } else {
                        showAlert('Error', 'Failed to load order. Please try again.');
                    }
                    document.getElementById('order-items-section').classList.add('hidden');
                    document.getElementById('summary-section').classList.add('hidden');
                    document.getElementById('empty-state').classList.remove('hidden');
                    return;
                }

                const data = await response.json();
                loadOrder(data);

            } catch (error) {
                console.error('Error:', error);
                showAlert('Error', error.message);
            }
        }

        function loadOrder(order) {
            currentOrder = order;

            // Show sections
            document.getElementById('order-items-section').classList.remove('hidden');
            document.getElementById('summary-section').classList.remove('hidden');
            document.getElementById('empty-state').classList.add('hidden');

            // Populate order info
            document.getElementById('order-id-display').textContent = order.token;
            document.getElementById('order-customer-name').textContent = order.buyer_name;
            document.getElementById('order-customer-phone').textContent = order.buyer_phone;
            
            const statusBadge = document.getElementById('order-status-badge');
            statusBadge.textContent = order.status.toUpperCase();
            statusBadge.className = `font-bold badge badge-lg ${
                order.status === 'completed' ? 'badge-warning' : 
                order.status === 'finished' ? 'badge-success' : 
                'badge-info'
            }`;

            // Populate items and fetch product stock info
            loadItemsWithStock(order.items);

            // Update totals
            calculateTotal();
        }

        async function loadItemsWithStock(items) {
            renderItems(items);
        }

        function renderItems(items) {
            const table = document.getElementById('order-items-table');
            
            if (!items || Object.keys(items).length === 0) {
                table.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-base-content/50">No items</td></tr>';
                return;
            }

            table.innerHTML = Object.entries(items).map(([productId, item]) => `
                <tr>
                    <td class="font-semibold">${item.name}</td>
                    <td>Rp ${parseInt(item.price).toLocaleString('id-ID')}</td>
                    <td class="font-bold">${item.quantity}</td>
                    <td>Rp ${(item.price * item.quantity).toLocaleString('id-ID')}</td>
                </tr>
            `).join('');
        }

        function updateItemQty(productId, newQty) {
            // Not used - items are fixed from customer order
        }

        function removeItem(productId) {
            // Not used - items are fixed from customer order
        }

        function calculateTotal() {
            if (!currentOrder) return;

            const items = currentOrder.items || {};
            const subtotal = Object.values(items).reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const discount = parseFloat(document.getElementById('discount-input').value) || 0;
            const taxPercent = parseFloat(document.getElementById('tax-input').value) || 0;
            const taxAmount = ((subtotal - discount) * taxPercent) / 100;
            const total = subtotal - discount + taxAmount;

            document.getElementById('summary-subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('summary-total').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');

            // Update order totals for completion
            currentOrder.subtotal = subtotal;
            currentOrder.total = Math.round(total);
        }

        async function completeOrder() {
            if (!currentOrder) {
                showAlert('No Order Loaded', 'Please search and load an order first');
                return;
            }

            if (currentOrder.status === 'completed') {
                showAlert('Order Already Completed', 'This order has already been completed and cannot be processed again');
                return;
            }

            if (Object.keys(currentOrder.items || {}).length === 0) {
                showAlert('No Items', 'Order has no items');
                return;
            }

            const total = currentOrder.total;
            const paymentMethod = document.getElementById('payment-method').value;

            // Show modal with confirmation details
            document.getElementById('modal-order-id').textContent = currentOrder.token;
            document.getElementById('modal-payment-method').textContent = paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1);
            document.getElementById('modal-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
            
            document.getElementById('confirmModal').showModal();
        }

        async function confirmComplete() {
            if (!currentOrder) return;

            const total = currentOrder.total;
            const paymentMethod = document.getElementById('payment-method').value;
            const discount = parseFloat(document.getElementById('discount-input').value) || 0;
            const taxPercent = parseFloat(document.getElementById('tax-input').value) || 0;
            const items = currentOrder.items || {};
            const subtotal = Object.values(items).reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const taxAmount = ((subtotal - discount) * taxPercent) / 100;
            const modal = document.getElementById('confirmModal');

            modal.close();

            try {
                const response = await fetch(`/api/guest-orders/${currentOrder.token}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        payment_method: paymentMethod,
                        discount: discount,
                        tax_percent: taxPercent,
                        tax_amount: Math.round(taxAmount)
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Failed to complete order');
                }

                // Success modal
                const successModal = document.createElement('dialog');
                successModal.className = 'modal modal-middle';
                successModal.innerHTML = `
                    <div class="modal-box w-full max-w-sm text-center">
                        <div class="mb-4 text-5xl">✅</div>
                        <h3 class="font-bold text-xl mb-2">Order Completed!</h3>
                        <p class="text-base-content/70 mb-6">Order ${currentOrder.token} has been successfully processed.</p>
                        <form method="dialog">
                            <button class="btn btn-primary w-full">Done</button>
                        </form>
                    </div>
                </dialog>`;
                document.body.appendChild(successModal);
                successModal.showModal();
                successModal.addEventListener('close', () => {
                    successModal.remove();
                    // Reset form
                    document.getElementById('order-id-input').value = '';
                    document.getElementById('order-items-section').classList.add('hidden');
                    document.getElementById('summary-section').classList.add('hidden');
                    document.getElementById('empty-state').classList.remove('hidden');
                    document.getElementById('discount-input').value = '';
                    document.getElementById('tax-input').value = '';
                    currentOrder = null;
                });

            } catch (error) {
                console.error('Error:', error);
                const errorModal = document.createElement('dialog');
                errorModal.className = 'modal modal-middle';
                errorModal.innerHTML = `
                    <div class="modal-box w-full max-w-sm text-center">
                        <div class="mb-4 text-5xl">❌</div>
                        <h3 class="font-bold text-xl mb-2">Error</h3>
                        <p class="text-base-content/70 mb-6">${error.message}</p>
                        <form method="dialog">
                            <button class="btn btn-primary w-full">Close</button>
                        </form>
                    </div>
                </dialog>`;
                document.body.appendChild(errorModal);
                errorModal.showModal();
                errorModal.addEventListener('close', () => {
                    errorModal.remove();
                });
            }
        }

        // Allow Enter key to search
        document.getElementById('order-id-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchOrder();
            }
        });
    </script>
</x-app-layout>
