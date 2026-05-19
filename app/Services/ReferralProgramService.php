<?php

namespace App\Services;

use App\Models\CoinTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReferralReward;
use App\Models\ReferralShare;
use App\Models\User;
use App\Support\ReferralSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralProgramService
{
    public function __construct(
        protected VendorWalletService $vendorWallet
    ) {}

    public function ensureReferralCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        $user->update(['referral_code' => $code]);

        return $code;
    }

    public function captureReferralFromRequest(?string $code): void
    {
        if (! ReferralSettings::enabled() || ! $code) {
            return;
        }

        $referrer = User::where('referral_code', strtoupper(trim($code)))->first();
        if (! $referrer) {
            return;
        }

        session([
            'referral_code' => $referrer->referral_code,
            'referral_referrer_id' => $referrer->id,
        ]);
    }

    public function recordShare(User $user, ?int $productId, ?string $channel = null): ReferralShare
    {
        $this->ensureReferralCode($user);

        $share = ReferralShare::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'share_token' => Str::random(40),
            'channel' => $channel,
        ]);

        session([
            'referral_last_share_id' => $share->id,
            'referral_last_share_user_id' => $user->id,
        ]);

        return $share;
    }

    public function applyReferrerOnRegister(User $newUser): void
    {
        if (! ReferralSettings::enabled()) {
            $this->ensureReferralCode($newUser);

            return;
        }

        $referrerId = session('referral_referrer_id');
        if ($referrerId && (int) $referrerId !== (int) $newUser->id) {
            $newUser->update(['referred_by_id' => $referrerId]);
        }

        $this->ensureReferralCode($newUser);

        if (ReferralSettings::hasUserTrigger('signup_with_code') && $newUser->referred_by_id) {
            $this->queueUserReward(
                referrer: User::find($newUser->referred_by_id),
                referee: $newUser,
                trigger: 'signup_with_code',
            );
        }
    }

    public function handleOrderDelivered(Order $order): void
    {
        if (! ReferralSettings::enabled()) {
            app(VendorWalletService::class)->creditSale($order);

            return;
        }

        app(VendorWalletService::class)->creditSale($order);

        $buyer = $order->user;
        if (! $buyer?->referred_by_id) {
            return;
        }

        if ($order->total_price < ReferralSettings::minOrderAmount()) {
            return;
        }

        $isFirstOrder = Order::where('user_id', $buyer->id)
            ->where('status', 'delivered')
            ->count() === 1;

        if (! $isFirstOrder) {
            return;
        }

        $referrer = User::find($buyer->referred_by_id);
        if (! $referrer) {
            return;
        }

        if (ReferralSettings::hasUserTrigger('first_purchase')) {
            $this->queueUserReward($referrer, $buyer, 'first_purchase', $order);
        }

        if (ReferralSettings::hasUserTrigger('share_first_purchase')) {
            $share = $this->findValidShare($referrer->id, $order->product_id);
            if ($share) {
                $this->queueUserReward($referrer, $buyer, 'share_first_purchase', $order, $share);
            }
        }

        if ($buyer->role === 'vendor' && ReferralSettings::hasVendorTrigger('referee_first_sale')) {
            $this->queueVendorReward($referrer, $buyer, 'referee_first_sale', $order);
        }
    }

    public function handleProductListed(Product $product): void
    {
        if (! ReferralSettings::enabled() || ! ReferralSettings::hasVendorTrigger('referee_first_product')) {
            return;
        }

        $vendor = $product->vendor;
        if (! $vendor?->referred_by_id) {
            return;
        }

        $count = Product::where('vendor_id', $vendor->id)->where('qc_status', 'approved')->count();
        if ($count !== 1) {
            return;
        }

        $referrer = User::find($vendor->referred_by_id);
        if ($referrer) {
            $this->queueVendorReward($referrer, $vendor, 'referee_first_product', null, $product);
        }
    }

    public function handleVendorFirstSale(Order $order, Product $product): void
    {
        if (! ReferralSettings::enabled() || ! ReferralSettings::hasVendorTrigger('share_first_sale')) {
            return;
        }

        $vendor = $product->vendor;
        if (! $vendor?->referred_by_id) {
            return;
        }

        $saleCount = Order::whereIn('product_id', Product::where('vendor_id', $vendor->id)->pluck('id'))
            ->where('status', 'delivered')
            ->count();

        if ($saleCount !== 1) {
            return;
        }

        $referrer = User::find($vendor->referred_by_id);
        $share = $referrer ? $this->findValidShare($referrer->id, $order->product_id) : null;

        if ($referrer && $share) {
            $this->queueVendorReward($referrer, $vendor, 'share_first_sale', $order, $product, $share);
        }
    }

    public function approveReward(ReferralReward $reward, User $admin, ?string $note = null): void
    {
        if ($reward->status !== 'pending') {
            return;
        }

        DB::transaction(function () use ($reward, $admin, $note) {
            $reward->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'admin_note' => $note,
            ]);

            $this->payReward($reward->fresh());
        });
    }

    public function rejectReward(ReferralReward $reward, User $admin, ?string $note = null): void
    {
        $reward->update([
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'admin_note' => $note,
        ]);
    }

    protected function queueUserReward(
        ?User $referrer,
        User $referee,
        string $trigger,
        ?Order $order = null,
        ?ReferralShare $share = null,
    ): void {
        if (! $referrer) {
            return;
        }

        if ($this->rewardExists($referrer->id, $referee->id, $trigger)) {
            return;
        }

        $reward = ReferralReward::create([
            'referrer_id' => $referrer->id,
            'referee_id' => $referee->id,
            'beneficiary_type' => 'user',
            'trigger_type' => $trigger,
            'status' => ReferralSettings::requireAdminApproval() ? 'pending' : 'approved',
            'reward_coins' => ReferralSettings::userRewardCoins(),
            'order_id' => $order?->id,
            'product_id' => $order?->product_id ?? $share?->product_id,
            'referral_share_id' => $share?->id,
            'qualified_at' => now(),
            'approved_at' => ReferralSettings::requireAdminApproval() ? null : now(),
        ]);

        if (! ReferralSettings::requireAdminApproval()) {
            $this->payReward($reward);
        }
    }

    protected function queueVendorReward(
        User $referrer,
        User $referee,
        string $trigger,
        ?Order $order = null,
        ?Product $product = null,
        ?ReferralShare $share = null,
    ): void {
        if ($this->rewardExists($referrer->id, $referee->id, $trigger)) {
            return;
        }

        $reward = ReferralReward::create([
            'referrer_id' => $referrer->id,
            'referee_id' => $referee->id,
            'beneficiary_type' => 'vendor',
            'trigger_type' => $trigger,
            'status' => ReferralSettings::requireAdminApproval() ? 'pending' : 'approved',
            'reward_amount' => ReferralSettings::vendorRewardAmount(),
            'order_id' => $order?->id,
            'product_id' => $product?->id ?? $order?->product_id,
            'referral_share_id' => $share?->id,
            'qualified_at' => now(),
            'approved_at' => ReferralSettings::requireAdminApproval() ? null : now(),
        ]);

        if (! ReferralSettings::requireAdminApproval()) {
            $this->payReward($reward);
        }
    }

    protected function payReward(ReferralReward $reward): void
    {
        if ($reward->status !== 'approved') {
            return;
        }

        if ($reward->beneficiary_type === 'user' && $reward->reward_coins > 0) {
            User::whereKey($reward->referrer_id)->increment('coins', $reward->reward_coins);
            CoinTransaction::create([
                'user_id' => $reward->referrer_id,
                'amount' => $reward->reward_coins,
                'type' => 'referral_'.$reward->trigger_type,
                'description' => 'Referral reward from '.$reward->referee?->name,
                'referral_reward_id' => $reward->id,
            ]);
        }

        if ($reward->beneficiary_type === 'vendor' && $reward->reward_amount > 0) {
            $this->vendorWallet->creditReferral($reward);
        }

        $reward->update(['status' => 'paid']);
    }

    protected function findValidShare(int $referrerId, ?int $productId): ?ReferralShare
    {
        $query = ReferralShare::where('user_id', $referrerId)
            ->where('created_at', '>=', now()->subDays(ReferralSettings::shareValidityDays()));

        if ($productId) {
            $specific = (clone $query)->where('product_id', $productId)->latest()->first();
            if ($specific) {
                return $specific;
            }
        }

        return $query->latest()->first();
    }

    protected function rewardExists(int $referrerId, int $refereeId, string $trigger): bool
    {
        return ReferralReward::where('referrer_id', $referrerId)
            ->where('referee_id', $refereeId)
            ->where('trigger_type', $trigger)
            ->whereNotIn('status', ['rejected'])
            ->exists();
    }
}
