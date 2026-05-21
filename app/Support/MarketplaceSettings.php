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

    public static function resellBulkMinQty(): int
    {
        return max(1, (int) Setting::value('resell_bulk_min_qty', 5));
    }

    public static function resellBulkDiscountPercent(): float
    {
        return max(0, min(50, (float) Setting::value('resell_bulk_discount_percent', 5)));
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

    public static function codEnabled(): bool
    {
        return Setting::value('cod_enabled', '1') === '1';
    }

    public static function freeShippingThreshold(): float
    {
        return max(0, (float) Setting::value('free_shipping_threshold', 499));
    }

    /** @return list<string> */
    public static function serviceablePincodes(): array
    {
        $raw = (string) Setting::value('delivery_pincodes', '');

        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($p) => preg_replace('/\D/', '', trim($p)),
            preg_split('/[\s,]+/', $raw) ?: []
        ), fn ($p) => strlen($p) === 6));
    }

    public static function isPincodeServiceable(string $pincode): bool
    {
        $pincode = preg_replace('/\D/', '', $pincode);
        if (strlen($pincode) !== 6) {
            return false;
        }

        $list = self::serviceablePincodes();

        return $list === [] || in_array($pincode, $list, true);
    }
}
