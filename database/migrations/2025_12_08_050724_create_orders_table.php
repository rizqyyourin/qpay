<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip if table already exists (created by earlier migration)
        if (Schema::hasTable('orders')) {
            return;
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique()->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_email')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->json('items')->nullable();
            $table->enum('status', ['pending', 'registered', 'completed', 'cancelled'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->string('order_number')->unique()->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'paid', 'cancelled'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('seller_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
