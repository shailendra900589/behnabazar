<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('source_product_id')->nullable()->after('vendor_id')->constrained('products')->nullOnDelete();
            $table->string('resell_mode', 20)->nullable()->after('source_product_id');
            $table->decimal('source_base_price', 10, 2)->nullable()->after('resell_mode');
            $table->decimal('resell_listing_fee', 10, 2)->default(0)->after('source_base_price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('fulfillment_vendor_id')->nullable()->after('product_id')->constrained('users')->nullOnDelete();
            $table->foreignId('listing_vendor_id')->nullable()->after('fulfillment_vendor_id')->constrained('users')->nullOnDelete();
            $table->decimal('source_vendor_amount', 10, 2)->nullable()->after('total_price');
            $table->decimal('listing_vendor_amount', 10, 2)->nullable()->after('source_vendor_amount');
        });

        Schema::table('ads', function (Blueprint $table) {
            $table->string('video_url', 500)->nullable()->after('link_url');
            $table->boolean('autoplay')->default(false)->after('video_url');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE ads MODIFY ad_type VARCHAR(30) NOT NULL DEFAULT 'image'");
        }

        $products = DB::table('products')->get();
        foreach ($products as $product) {
            $sort = 0;
            foreach (['image', 'image2', 'image3', 'image4'] as $col) {
                $path = $product->{$col} ?? null;
                if ($path) {
                    DB::table('product_images')->insert([
                        'product_id' => $product->id,
                        'path' => $path,
                        'sort_order' => $sort++,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'autoplay']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['fulfillment_vendor_id']);
            $table->dropForeign(['listing_vendor_id']);
            $table->dropColumn(['fulfillment_vendor_id', 'listing_vendor_id', 'source_vendor_amount', 'listing_vendor_amount']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['source_product_id']);
            $table->dropColumn(['source_product_id', 'resell_mode', 'source_base_price', 'resell_listing_fee']);
        });

        Schema::dropIfExists('product_images');
    }
};
