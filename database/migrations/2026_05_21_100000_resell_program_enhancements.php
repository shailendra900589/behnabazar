<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('resell_allowed')->default(true)->after('resell_listing_fee');
        });

        Schema::create('vendor_resell_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('source_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('qty_purchased')->default(0);
            $table->unsignedInteger('qty_remaining')->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['vendor_id', 'source_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_resell_inventory');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('resell_allowed');
        });
    }
};
