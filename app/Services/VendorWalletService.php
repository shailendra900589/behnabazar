<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payout;
use App\Models\Product;
use App\Models\ReferralReward;
use App\Models\User;
use App\Models\VendorWalletTransaction;
use Illuminate\Support\Facades\DB;

class VendorWalletService
{
    public function availableBalance(int $vendorId): float
    {
        $user = User::find($vendorId);
        if (! $user) {
            return 0;
        }

        return max(0, (float) $user->sales_wallet_balance);
    }

    public function creditSale(Order $order): void
    {
        $product = Product::find($order->product_id);
        if (! $product?->vendor_id) {
            return;
        }

        if (VendorWalletTransaction::where('order_id', $order->id)->where('type', 'credit_sale')->exists()) {
            return;
        }

        DB::transaction(function () use ($order, $product) {
            VendorWalletTransaction::create([
                'vendor_id' => $product->vendor_id,
                'amount' => $order->total_price,
                'type' => 'credit_sale',
                'status' => 'completed',
                'order_id' => $order->id,
                'description' => 'Sale delivered — order #'.$order->id,
            ]);

            User::whereKey($product->vendor_id)->increment('sales_wallet_balance', $order->total_price);
        });
    }

    public function creditReferral(ReferralReward $reward): void
    {
        if ($reward->beneficiary_type !== 'vendor' || $reward->reward_amount <= 0) {
            return;
        }

        if (VendorWalletTransaction::where('referral_reward_id', $reward->id)->exists()) {
            return;
        }

        DB::transaction(function () use ($reward) {
            VendorWalletTransaction::create([
                'vendor_id' => $reward->referrer_id,
                'amount' => $reward->reward_amount,
                'type' => 'credit_referral',
                'status' => 'completed',
                'referral_reward_id' => $reward->id,
                'description' => 'Referral reward — '.$reward->trigger_type,
            ]);

            User::whereKey($reward->referrer_id)->increment('sales_wallet_balance', $reward->reward_amount);
        });
    }

    public function reservePayout(Payout $payout): bool
    {
        return DB::transaction(function () use ($payout) {
            $vendor = User::lockForUpdate()->find($payout->vendor_id);
            if (! $vendor || (float) $vendor->sales_wallet_balance < (float) $payout->amount) {
                return false;
            }

            $vendor->decrement('sales_wallet_balance', $payout->amount);

            VendorWalletTransaction::create([
                'vendor_id' => $payout->vendor_id,
                'amount' => -$payout->amount,
                'type' => 'debit_payout',
                'status' => 'pending',
                'payout_id' => $payout->id,
                'description' => 'Payout claim requested',
            ]);

            return true;
        });
    }

    public function releasePayout(Payout $payout): void
    {
        DB::transaction(function () use ($payout) {
            VendorWalletTransaction::where('payout_id', $payout->id)
                ->where('type', 'debit_payout')
                ->update(['status' => 'completed', 'description' => 'Payout paid to bank']);

            if ($payout->status === 'rejected') {
                User::whereKey($payout->vendor_id)->increment('sales_wallet_balance', $payout->amount);
                VendorWalletTransaction::create([
                    'vendor_id' => $payout->vendor_id,
                    'amount' => $payout->amount,
                    'type' => 'adjustment',
                    'status' => 'completed',
                    'payout_id' => $payout->id,
                    'description' => 'Payout request rejected — balance restored',
                ]);
            }
        });
    }
}
