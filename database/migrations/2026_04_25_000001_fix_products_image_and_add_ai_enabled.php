<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix: base64 images are too large for VARCHAR(255)
        Schema::table('products', function (Blueprint $table) {
            $table->text('image')->nullable()->change();
            $table->text('description')->nullable()->change();
        });

        // Add per-user AI feature toggle
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ai_enabled')->default(false)->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
            $table->string('description')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ai_enabled');
        });
    }
};
