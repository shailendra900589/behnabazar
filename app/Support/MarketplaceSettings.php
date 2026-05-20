<?php

namespace App\Support;

use App\Models\Setting;

class MarketplaceSettings
{
    public static function resellEnabled(): bool
    {
        return Setting::value('resell_program_enabled', '1') === '1';
    }

    public static function resellCustomizeFee(): float
    {
        return max(0, (float) Setting::value('resell_customize_fee', 99));
    }

    public static function payoutMinAmount(): float
    {
        return max(1, (float) Setting::value('payout_min_amount', 500));
    }

    public static function vendorRegistrationAmount(): float
    {
        return max(0, (float) Setting::value('vendor_registration_amount', 150));
    }

    public static function editRequiresQc(): bool
    {
        return Setting::value('product_edit_requires_qc', '1') === '1';
    }
}
