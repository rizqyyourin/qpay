<?php

namespace App\Livewire;

use App\Models\Product;
use App\Facades\Api;
use Livewire\Component;
use Livewire\WithPagination;

class PosTerminal extends Component
{
    use WithPagination;

    public $search = '';
    public $barcode = '';
    public $cart = [];
    public $discount = 0;
    public $tax = 0;
    public $paymentMethod = 'cash';
    public $isLoading = false;
    public $error = '';

    protected $listeners = ['addToCart'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedBarcode()
    {
        if ($this->barcode) {
            $this->searchByBarcode();
        }
    }

    public function searchByBarcode()
    {
        $product = Product::where('barcode', $this->barcode)->first();

        if ($product) {
            $this->addToCart($product->id);
            $this->barcode = '';
        } else {
            $this->error = "Product with barcode '{$this->barcode}' not found";
            $this->dispatch('error', $this->error);
            $this->barcode = '';
        }
    }

    public function getProductsProperty()
    {
        $query = Product::query();

        if ($this->search) {
            $query->where('name', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%")
                  ->orWhere('barcode', 'like', "%{$this->search}%");
        }

        return $query->paginate(12);
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            $this->error = 'Product not found';
            $this->dispatch('error', $this->error);
            return;
        }

        if ($product->stock_quantity <= 0) {
            $this->error = "{$product->name} is out of stock";
            $this->dispatch('error', $this->error);
            return;
        }

        $key = 'product_' . $product->id;

        if (isset($this->cart[$key])) {
            if ($this->cart[$key]['quantity'] < $product->stock_quantity) {
                $this->cart[$key]['quantity']++;
                $this->cart[$key]['subtotal'] = $this->cart[$key]['quantity'] * $this->cart[$key]['price'];
                $this->dispatch('success', "{$product->name} quantity increased");
            } else {
                $this->error = "Cannot exceed available stock ({$product->stock_quantity})";
                $this->dispatch('error', $this->error);
            }
        } else {
            $this->cart[$key] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'quantity' => 1,
                'subtotal' => $product->price,
            ];
            $this->dispatch('success', "{$product->name} added to cart");
        }

        $this->search = '';
    }

    public function updateQuantity($key, $quantity)
    {
        if ($quantity <= 0) {
            unset($this->cart[$key]);
            $this->dispatch('success', 'Item removed from cart');
        } else {
            $this->cart[$key]['quantity'] = $quantity;
            $this->cart[$key]['subtotal'] = $quantity * $this->cart[$key]['price'];
        }
    }

    public function removeFromCart($key)
    {
        unset($this->cart[$key]);
        $this->dispatch('success', 'Item removed from cart');
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->discount = 0;
        $this->tax = 0;
        $this->dispatch('success', 'Cart cleared');
    }

    public function getCartTotalProperty()
    {
        $subtotal = collect($this->cart)->sum('subtotal');
        $taxAmount = ($subtotal - $this->discount) * ($this->tax / 100);
        return $subtotal - $this->discount + $taxAmount;
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            $this->error = 'Cart is empty';
            $this->dispatch('error', $this->error);
            return;
        }

        $this->isLoading = true;

        try {
            // Create order via API
            $response = Api::createOrder(
                intval($this->discount),
                intval(($this->subtotal * $this->tax) / 100)
            );

            if (!$response['success']) {
                throw new \Exception('Failed to create order: ' . json_encode($response['errors'] ?? []));
            }

            $order = $response['data'];

            // Process payment
            $paymentResponse = Api::processPayment(
                $order['id'],
                intval($this->cartTotal),
                $this->paymentMethod
            );

            if (!$paymentResponse['success']) {
                throw new \Exception('Payment processing failed');
            }

            // Clear cart on success
            $this->clearCart();
            $this->dispatch('success', "Order {$order['order_number']} completed successfully!");
            $this->dispatch('orderCreated', $order);

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->dispatch('error', $this->error);
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.pos-terminal', [
            'products' => $this->products,
        ]);
    }
}
