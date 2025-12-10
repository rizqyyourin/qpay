<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;

class OrdersList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $paymentMethod = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    protected $listeners = ['orderCreated' => 'resetPage'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingPaymentMethod()
    {
        $this->resetPage();
    }

    public function getOrdersProperty()
    {
        $query = Order::query();

        // Search by order number or customer name
        if ($this->search) {
            $query->where('order_number', 'like', "%{$this->search}%")
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
        }

        // Filter by payment status
        if ($this->status) {
            $query->where('payment_status', $this->status);
        }

        // Filter by payment method
        if ($this->paymentMethod) {
            $query->whereHas('payment', function ($q) {
                $q->where('payment_method', $this->paymentMethod);
            });
        }

        return $query->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function cancelOrder($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $order->update(['payment_status' => 'cancelled']);
            $this->dispatch('success', 'Order cancelled successfully');
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to cancel order');
        }
    }

    public function render()
    {
        return view('livewire.orders-list', [
            'orders' => $this->orders,
        ]);
    }
}
