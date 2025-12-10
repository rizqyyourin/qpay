<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'reserved_at',
        'released_at',
        'status',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    /**
     * Get the order this reservation belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product for this reservation
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if reservation is still active (not released)
     */
    public function isActive(): bool
    {
        return is_null($this->released_at) && $this->status === 'reserved';
    }

    /**
     * Check if reservation has expired (older than 30 minutes)
     */
    public function hasExpired(): bool
    {
        return $this->reserved_at->addMinutes(30) < now();
    }

    /**
     * Release this reservation
     */
    public function release(): void
    {
        $this->update([
            'released_at' => now(),
            'status' => 'released',
        ]);
    }

    /**
     * Confirm (finalize) this reservation
     */
    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
        ]);
    }
}
