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
        if (Schema::hasColumn('cart_items', 'variant_info')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropColumn('variant_info');
            });
        }

        if (! Schema::hasColumn('cart_items', 'variant_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreignId('variant_id')->nullable()->after('quantity')->constrained('product_variants')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('orders', 'variant_info')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('variant_info');
            });
        }

        if (! Schema::hasColumn('orders', 'variant_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('variant_id')->nullable()->after('quantity')->constrained('product_variants')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
