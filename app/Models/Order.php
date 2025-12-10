<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',           // NULL for guest orders, user_id for authenticated
        'seller_id',         // The seller (always required for multi-tenant)
        'token',             // Unique identifier (guest orders + reference)
        'order_number',      // Auto-generated order number (e.g., ORD-2024-001)
        'buyer_name',        // Guest name or authenticated user's name
        'buyer_phone',       // Guest phone
        'buyer_email',       // Guest email (optional)
        'subtotal',          // Sum of items before tax/discount
        'discount_amount',   // Discount value
        'tax_amount',        // Tax value
        'total_amount',      // Final amount (subtotal - discount + tax)
        'items',             // JSON array of ordered items
        'status',            // pending, completed, cancelled
        'payment_status',    // unpaid, paid, failed, refunded
        'payment_method',    // cash, card, transfer, ewallet
        'transaction_id',    // Payment gateway transaction ID
        'notes',             // Order notes/remarks
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'items' => 'array',  // JSON array cast
        ];
    }

    /**
     * Get the seller (owner) of this order.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the user that placed the order (NULL for guests).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all order items in this order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get payment information for this order.
     */
    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if this is a guest order (no authenticated user).
     */
    public function isGuestOrder(): bool
    {
        return is_null($this->user_id);
    }

    /**
     * Check if this is an authenticated user order.
     */
    public function isAuthenticatedOrder(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Generate unique token for order tracking.
     */
    public static function generateToken(): string
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    }

    /**
     * Add item to order.
     */
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

    /**
     * Remove item from order.
     */
    public function removeItem(int $productId): void
    {
        $items = $this->items ?? [];
        unset($items[(string) $productId]);
        
        $this->items = $items;
        $this->calculateTotals();
        $this->save();
    }

    /**
     * Calculate and update order totals.
     */
    public function calculateTotals(): void
    {
        $subtotal = 0;
        $items = $this->items ?? [];
        
        foreach ($items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        $this->subtotal = $subtotal;
        $this->total_amount = $subtotal + $this->tax_amount - $this->discount_amount;
    }

    /**
     * Get the grand total (including tax and discount).
     */
    public function getGrandTotalAttribute(): float
    {
        return (float) $this->total_amount;
    }
}
