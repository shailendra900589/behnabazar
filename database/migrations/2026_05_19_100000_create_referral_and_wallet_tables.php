<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 16)->nullable()->unique()->after('coins');
            $table->foreignId('referred_by_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->decimal('sales_wallet_balance', 12, 2)->default(0)->after('ad_wallet_balance');
        });

        Schema::create('referral_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('share_token', 64)->unique();
            $table->string('channel', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('beneficiary_type', 20); // user | vendor
            $table->string('trigger_type', 60);
            $table->string('status', 20)->default('pending'); // pending | approved | rejected | paid
            $table->unsignedInteger('reward_coins')->default(0);
            $table->decimal('reward_amount', 12, 2)->default(0);
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('referral_share_id')->nullable()->constrained('referral_shares')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('type', 30); // credit_sale | credit_referral | debit_payout | adjustment
            $table->string('status', 20)->default('completed');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payout_id')->nullable()->constrained('payouts')->nullOnDelete();
            $table->foreignId('referral_reward_id')->nullable()->constrained('referral_rewards')->nullOnDelete();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount'); // positive credit, negative debit
            $table->string('type', 40);
            $table->string('description')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('referral_reward_id')->nullable()->constrained('referral_rewards')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
        Schema::dropIfExists('vendor_wallet_transactions');
        Schema::dropIfExists('referral_rewards');
        Schema::dropIfExists('referral_shares');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_id');
            $table->dropColumn(['referral_code', 'sales_wallet_balance']);
        });
    }
};
