<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_coupons', function (Blueprint $table) {
            $table->string('issued_to_name', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('registration_coupons', function (Blueprint $table) {
            $table->string('issued_to_name', 120)->nullable(false)->change();
        });
    }
};
