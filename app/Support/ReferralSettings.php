<?php

namespace App\Support;

use App\Models\Setting;

class ReferralSettings
{
    public static function enabled(): bool
    {
        return Setting::value('referral_program_enabled', '1') === '1';
    }

    public static function requireAdminApproval(): bool
    {
        return Setting::value('referral_require_admin_approval', '1') === '1';
    }

    /** @return list<string> */
    public static function userTriggers(): array
    {
        $raw = Setting::value('referral_user_triggers', 'share_first_purchase');

        return array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
    }

    /** @return list<string> */
    public static function vendorTriggers(): array
    {
        $raw = Setting::value('referral_vendor_triggers', 'referee_first_sale,referee_first_product');

        return array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
    }

    public static function userRewardCoins(): int
    {
        return max(0, (int) Setting::value('referral_user_reward_coins', 50));
    }

    public static function vendorRewardAmount(): float
    {
        return max(0, (float) Setting::value('referral_vendor_reward_amount', 100));
    }

    public static function minOrderAmount(): float
    {
        return max(0, (float) Setting::value('referral_min_order_amount', 0));
    }

    public static function shareValidityDays(): int
    {
        return max(1, (int) Setting::value('referral_share_validity_days', 30));
    }

    public static function hasUserTrigger(string $trigger): bool
    {
        return in_array($trigger, self::userTriggers(), true);
    }

    public static function hasVendorTrigger(string $trigger): bool
    {
        return in_array($trigger, self::vendorTriggers(), true);
    }
}
