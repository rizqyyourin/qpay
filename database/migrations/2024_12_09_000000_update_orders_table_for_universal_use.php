<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update orders table to support both guest and authenticated orders
     */
    public function up(): void
    {
        // First, ensure orders table has the expected columns for modification
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                // Add new columns for guest/multi-tenant support
                if (!Schema::hasColumn('orders', 'token')) {
                    $table->string('token')->unique()->nullable()->after('id');
                }
                if (!Schema::hasColumn('orders', 'seller_id')) {
                    $table->unsignedBigInteger('seller_id')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('orders', 'buyer_name')) {
                    $table->string('buyer_name')->nullable()->after('seller_id');
                }
                if (!Schema::hasColumn('orders', 'buyer_phone')) {
                    $table->string('buyer_phone')->nullable()->after('buyer_name');
                }
                if (!Schema::hasColumn('orders', 'buyer_email')) {
                    $table->string('buyer_email')->nullable()->after('buyer_phone');
                }
                if (!Schema::hasColumn('orders', 'subtotal')) {
                    $table->decimal('subtotal', 15, 2)->default(0)->after('buyer_email');
                }
                if (!Schema::hasColumn('orders', 'items')) {
                    $table->json('items')->nullable()->after('subtotal');
                }
                if (!Schema::hasColumn('orders', 'status')) {
                    $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->after('items');
                }
                if (!Schema::hasColumn('orders', 'transaction_id')) {
                    $table->string('transaction_id')->nullable()->after('status');
                }
            });
            
            // Add foreign key if it doesn't exist
            $table = Schema::connection(null)->getConnection()->getSchemaBuilder();
            try {
                Schema::table('orders', function (Blueprint $table) {
                    // Only add if seller_id exists and foreign key doesn't
                    if (Schema::hasColumn('orders', 'seller_id')) {
                        $table->foreign('seller_id')
                            ->references('id')
                            ->on('users')
                            ->onDelete('cascade');
                    }
                });
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                // Drop foreign key if exists
                try {
                    $table->dropForeign(['seller_id']);
                } catch (\Exception $e) {
                    // Key doesn't exist, continue
                }
                
                // Drop columns if they exist
                $columns = [
                    'token',
                    'seller_id',
                    'buyer_name',
                    'buyer_phone',
                    'buyer_email',
                    'subtotal',
                    'items',
                    'status',
                    'transaction_id',
                ];
                
                foreach ($columns as $column) {
                    if (Schema::hasColumn('orders', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};


