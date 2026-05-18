<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropUnique(['location']);
            $table->foreignId('vendor_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->after('vendor_id')->constrained()->nullOnDelete();
            $table->string('title')->nullable()->after('location');
            $table->string('subtitle')->nullable()->after('title');
            $table->string('cta_text', 80)->nullable()->after('subtitle');
            $table->unsignedInteger('sort_order')->default(0)->after('clicks');
            $table->dateTime('starts_at')->nullable()->after('sort_order');
            $table->dateTime('ends_at')->nullable()->after('starts_at');
            $table->enum('source', ['admin', 'vendor'])->default('admin')->after('ends_at');
            $table->index(['location', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropIndex(['location', 'status', 'sort_order']);
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn(['title', 'subtitle', 'cta_text', 'sort_order', 'starts_at', 'ends_at', 'source']);
            $table->unique('location');
        });
    }
};
