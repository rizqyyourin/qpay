<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestSession extends Model
{
    protected $fillable = [
        'token',
        'buyer_name',
        'buyer_phone',
        'subtotal',
        'discount',
        'tax_percent',
        'tax_amount',
        'total',
        'status',
        'items',
        'payment_method',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public static function generateToken(): string
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }

    public function addItem(Product $product, int $quantity): void
    {
        $items = $this->items ?? [];
        
        $key = (string) $product->id;
        if (isset($items[$key])) {
            $items[$key]['quantity'] += $quantity;
        } else {
            $items[$key] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
            ];
        }
        
        $this->items = $items;
        $this->calculateTotals();
        $this->save();
    }

    public function removeItem(int $productId): void
    {
        $items = $this->items ?? [];
        unset($items[(string) $productId]);
        
        $this->items = $items;
        $this->calculateTotals();
        $this->save();
    }

    public function calculateTotals(): void
    {
        $subtotal = 0;
        $items = $this->items ?? [];
        
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $this->subtotal = $subtotal;
        $this->total = $subtotal;
    }
}
