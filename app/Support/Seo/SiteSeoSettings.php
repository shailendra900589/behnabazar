<?php

namespace App\Support\Seo;

use App\Models\Setting;
use App\Support\SiteBranding;
use Illuminate\Support\Facades\Cache;

class SiteSeoSettings
{
    public const CACHE_KEY = 'site.seo.settings';

    public static function config(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $geo = config('seo.geo', []);

            return [
                'site_name' => SiteBranding::name(),
                'tagline' => SiteBranding::config()['tagline'],
                'url' => rtrim((string) config('app.url'), '/'),
                'locale' => config('seo.default_locale', 'en-IN'),
                'country' => Setting::value('seo_country', config('seo.default_country', 'IN')),
                'currency' => config('seo.currency', 'INR'),
                'locality' => Setting::value('seo_locality', $geo['locality'] ?? 'India'),
                'region' => Setting::value('seo_region', $geo['region'] ?? 'IN'),
                'latitude' => Setting::value('seo_latitude', $geo['latitude'] ?? '28.6139'),
                'longitude' => Setting::value('seo_longitude', $geo['longitude'] ?? '77.2090'),
                'area_served' => Setting::value('seo_area_served', $geo['area_served'] ?? 'IN'),
                'contact_email' => Setting::value('seo_contact_email', config('mail.from.address', '')),
                'contact_phone' => Setting::value('seo_contact_phone', ''),
            ];
        });
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
