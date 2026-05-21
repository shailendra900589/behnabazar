<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->json('attributes')->nullable()->after('size');
            $table->decimal('compare_at_price', 10, 2)->nullable()->after('price');
        });

        if (Schema::hasColumn('products', 'compare_at_price')) {
            DB::table('products')
                ->whereNull('compare_at_price')
                ->update(['compare_at_price' => DB::raw('ROUND(price * 1.2, 2)')]);

            DB::table('products')
                ->whereNotNull('compare_at_price')
                ->whereColumn('compare_at_price', '<=', 'price')
                ->update(['compare_at_price' => null]);
        }
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['attributes', 'compare_at_price']);
        });
    }
};
