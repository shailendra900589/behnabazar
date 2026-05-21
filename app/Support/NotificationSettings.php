<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class NotificationSettings
{
    public const CACHE_KEY = 'marketplace.notification_settings';

    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            return [
                'sms_enabled' => Setting::value('notify_sms_enabled', '0') === '1',
                'whatsapp_enabled' => Setting::value('notify_whatsapp_enabled', '0') === '1',
                'order_sms_customer' => Setting::value('notify_order_sms_customer', '1') === '1',
                'order_sms_vendor' => Setting::value('notify_order_sms_vendor', '1') === '1',
                'order_whatsapp_customer' => Setting::value('notify_order_whatsapp_customer', '1') === '1',
                'order_whatsapp_admin' => Setting::value('notify_order_whatsapp_admin', '1') === '1',
                'order_whatsapp_vendor' => Setting::value('notify_order_whatsapp_vendor', '1') === '1',
                'whatsapp_business_phone' => preg_replace('/\D/', '', (string) Setting::value('whatsapp_business_phone', '')),
                'whatsapp_callmebot_api_key' => trim((string) Setting::value('whatsapp_callmebot_api_key', '')),
                'whatsapp_cloud_token' => trim((string) Setting::value('whatsapp_cloud_token', '')),
                'whatsapp_cloud_phone_id' => trim((string) Setting::value('whatsapp_cloud_phone_id', '')),
                'whatsapp_cloud_api_version' => trim((string) Setting::value('whatsapp_cloud_api_version', 'v21.0')),
                'whatsapp_cloud_template' => trim((string) Setting::value('whatsapp_cloud_template', '')),
                'order_alert_phone' => preg_replace('/\D/', '', (string) Setting::value('order_alert_phone', '')),
                'abandoned_cart_enabled' => Setting::value('abandoned_cart_enabled', '1') === '1',
                'abandoned_cart_idle_hours' => max(1, (int) Setting::value('abandoned_cart_idle_hours', 24)),
                'abandoned_cart_cooldown_hours' => max(24, (int) Setting::value('abandoned_cart_cooldown_hours', 72)),
                'stock_alert_enabled' => Setting::value('stock_alert_enabled', '1') === '1',
                'notify_order_status_customer' => Setting::value('notify_order_status_customer', '1') === '1',
                'abandoned_cart_email' => Setting::value('abandoned_cart_email', '1') === '1',
                'abandoned_cart_sms' => Setting::value('abandoned_cart_sms', '1') === '1',
                'abandoned_cart_whatsapp' => Setting::value('abandoned_cart_whatsapp', '1') === '1',
                'vendor_low_stock_threshold' => max(1, (int) Setting::value('vendor_low_stock_threshold', 5)),
            ];
        });
    }

    public static function adminAlertPhone(): string
    {
        $s = self::all();

        return $s['order_alert_phone'] ?: $s['whatsapp_business_phone'];
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
