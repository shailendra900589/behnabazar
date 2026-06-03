<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Setting;
use App\Support\Seo\ProductSeoGenerator;
use App\Support\SiteBranding;
use App\Support\Seo\SiteSeoSettings;
use Illuminate\Database\Seeder;
use App\Support\StoragePublicLink;
use Illuminate\Support\Facades\Cache;

class MarketplaceDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_display_name' => 'Behna Bazar',
            'site_tagline' => 'Your trusted multipurpose marketplace for grocery, fashion, electronics, home, beauty, and local sellers.',
            'nav_home_label' => 'Home',
            'coin_earn_rate' => '10',
            'coin_redeem_limit' => '5',
            'referral_program_enabled' => '1',
            'referral_require_admin_approval' => '1',
            'referral_user_reward_coins' => '50',
            'referral_vendor_reward_amount' => '100',
            'referral_min_order_amount' => '0',
            'referral_share_validity_days' => '30',
            'referral_user_triggers' => 'share_first_purchase',
            'referral_vendor_triggers' => 'referee_first_sale,referee_first_product',
            'resell_program_enabled' => '1',
            'resell_customize_fee' => '99',
            'resell_bulk_min_qty' => '5',
            'resell_bulk_discount_percent' => '5',
            'vendor_registration_amount' => '150',
            'payout_min_amount' => '500',
            'ad_wallet_min_topup' => '50',
            'product_edit_requires_qc' => '1',
            'cod_enabled' => '1',
            'free_shipping_threshold' => '499',
            'delivery_pincodes' => '',
            'seo_locality' => 'India',
            'seo_region' => 'IN',
            'seo_latitude' => '28.6139',
            'seo_longitude' => '77.2090',
            'seo_country' => 'IN',
            'seo_area_served' => 'IN',
            'seo_contact_email' => config('mail.from.address', ''),
            'seo_contact_phone' => '',
            'notify_sms_enabled' => '1',
            'notify_whatsapp_enabled' => '1',
            'notify_order_sms_customer' => '1',
            'notify_order_sms_vendor' => '1',
            'notify_order_whatsapp_customer' => '1',
            'notify_order_whatsapp_admin' => '1',
            'notify_order_whatsapp_vendor' => '1',
            'whatsapp_business_phone' => '',
            'whatsapp_callmebot_api_key' => '',
            'whatsapp_cloud_token' => '',
            'whatsapp_cloud_phone_id' => '',
            'whatsapp_cloud_api_version' => 'v21.0',
            'whatsapp_cloud_template' => '',
            'order_alert_phone' => '',
            'abandoned_cart_enabled' => '1',
            'abandoned_cart_idle_hours' => '24',
            'abandoned_cart_cooldown_hours' => '72',
            'stock_alert_enabled' => '1',
            'notify_order_status_customer' => '1',
            'abandoned_cart_email' => '1',
            'abandoned_cart_sms' => '1',
            'abandoned_cart_whatsapp' => '1',
            'vendor_low_stock_threshold' => '5',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => (string) $value]
            );
        }

        Product::query()->chunkById(50, function ($products) {
            foreach ($products as $product) {
                if (empty($product->compare_at_price) || (float) $product->compare_at_price <= (float) $product->price) {
                    $product->compare_at_price = round((float) $product->price * 1.2, 2);
                }
                ProductSeoGenerator::apply($product);
                $product->saveQuietly();
            }
        });

        SiteBranding::flushCache();
        SiteSeoSettings::flushCache();
        \App\Support\NotificationSettings::flushCache();
        \App\Support\Seo\SitemapBuilder::flush();
        Cache::forget('storefront.price_bounds');
        Cache::forget('storefront.flash_deal');

        StoragePublicLink::ensure();
    }
}
