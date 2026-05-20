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
        $product = Product::with('sourceProduct')->find($order->product_id);
        if (! $product) {
            return;
        }

        if (VendorWalletTransaction::where('order_id', $order->id)->exists()) {
            return;
        }

        if ($product->isResellListing() && $product->sourceProduct) {
            $this->creditResellSale($order, $product);

            return;
        }

        if (! $product->vendor_id) {
            return;
        }

        DB::transaction(function () use ($order, $product) {
            $this->addWalletCredit(
                $product->vendor_id,
                (float) $order->total_price,
                'credit_sale',
                $order->id,
                'Sale delivered — order #'.$order->id,
            );
        });
    }

    protected function creditResellSale(Order $order, Product $product): void
    {
        $sourceVendorId = $product->sourceProduct->vendor_id;
        $listingVendorId = $product->vendor_id;

        if (! $sourceVendorId) {
            return;
        }

        $sourceAmount = (float) ($order->source_vendor_amount ?? $product->source_base_price ?? $product->sourceProduct->price);
        $listingAmount = (float) ($order->listing_vendor_amount ?? max(0, $order->total_price - $sourceAmount));

        DB::transaction(function () use ($order, $sourceVendorId, $listingVendorId, $sourceAmount, $listingAmount) {
            if ($sourceAmount > 0) {
                $this->addWalletCredit(
                    $sourceVendorId,
                    $sourceAmount,
                    'credit_sale',
                    $order->id,
                    'Resell source payout — order #'.$order->id,
                );
            }

            if ($listingVendorId && $listingAmount > 0) {
                $this->addWalletCredit(
                    $listingVendorId,
                    $listingAmount,
                    'credit_resell_margin',
                    $order->id,
                    'Resell margin — order #'.$order->id,
                );
            }
        });
    }

    protected function addWalletCredit(int $vendorId, float $amount, string $type, int $orderId, string $description): void
    {
        if ($amount <= 0) {
            return;
        }

        VendorWalletTransaction::create([
            'vendor_id' => $vendorId,
            'amount' => $amount,
            'type' => $type,
            'status' => 'completed',
            'order_id' => $orderId,
            'description' => $description,
        ]);

        User::whereKey($vendorId)->increment('sales_wallet_balance', $amount);
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
