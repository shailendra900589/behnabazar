<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('total_count')->default(0);
            $table->timestamp('updated_at')->nullable();
        });

        DB::table('site_visits')->insert(['id' => 1, 'total_count' => 0, 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
