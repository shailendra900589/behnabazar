<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('compare_at_price', 10, 2)->nullable()->after('price');
            $table->decimal('reseller_dp_price', 10, 2)->nullable()->after('compare_at_price');
        });

        Schema::create('vendor_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('link', 500)->nullable();
            $table->foreignId('related_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('actor_vendor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['vendor_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_notifications');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['compare_at_price', 'reseller_dp_price']);
        });
    }
};
