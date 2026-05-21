<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('to_phone', 20);
            $table->string('recipient_label', 80)->nullable();
            $table->string('template', 50)->nullable();
            $table->text('message');
            $table->string('wa_url', 500);
            $table->string('status', 20)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('to_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_outbox');
    }
};
