<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrate all GuestSession data to Orders table
     */
    public function up(): void
    {
        // Only migrate if guest_sessions table exists
        if (Schema::hasTable('guest_sessions')) {
            $guestSessions = DB::table('guest_sessions')->get();
            
            if ($guestSessions->count() > 0) {
                // Get first user as default seller (for existing guest sessions)
                $defaultSeller = DB::table('users')->first();
                $sellerId = $defaultSeller->id ?? 1;
                
                foreach ($guestSessions as $session) {
                    try {
                        DB::table('orders')->insert([
                            'token' => $session->token,
                            'seller_id' => $sellerId,
                            'user_id' => null,  // Guest order
                            'buyer_name' => $session->buyer_name ?? 'Guest',
                            'buyer_phone' => $session->buyer_phone ?? null,
                            'buyer_email' => null,
                            'order_number' => 'ORD-' . date('Y') . '-' . str_pad($session->id, 5, '0', STR_PAD_LEFT),
                            'subtotal' => $session->subtotal ?? 0,
                            'discount_amount' => $session->discount ?? 0,
                            'tax_amount' => $session->tax_amount ?? 0,
                            'total_amount' => $session->total ?? 0,
                            'items' => json_encode($session->items ?? []),
                            'status' => $session->status === 'completed' ? 'completed' : 'pending',
                            'payment_status' => $session->status === 'completed' ? 'paid' : 'unpaid',
                            'payment_method' => $session->payment_method ?? 'cash',
                            'transaction_id' => null,
                            'notes' => null,
                            'created_at' => $session->created_at,
                            'updated_at' => $session->updated_at,
                        ]);
                    } catch (\Exception $e) {
                        // Log but continue on error
                        Log::warning("Failed to migrate guest session {$session->id}: {$e->getMessage()}");
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete migrated records (orders that came from guest_sessions)
        // Only delete orders that don't have an authenticated user
        DB::table('orders')
            ->whereNull('user_id')
            ->delete();
    }
};
