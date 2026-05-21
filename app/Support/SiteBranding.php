<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SiteBranding
{
    public const CACHE_KEY = 'site.branding';

    public static function config(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            $name = trim((string) Setting::value('site_display_name', ''));
            if ($name === '') {
                $name = (string) config('app.name', 'Behna Bazar');
            }

            $tagline = trim((string) Setting::value('site_tagline', ''));
            if ($tagline === '') {
                $tagline = 'Your trusted multipurpose marketplace for grocery, fashion, electronics, home, beauty, and local sellers.';
            }

            $navHome = trim((string) Setting::value('nav_home_label', ''));
            if ($navHome === '') {
                $navHome = 'Home';
            }

            return [
                'name' => $name,
                'tagline' => $tagline,
                'nav_home_label' => $navHome,
            ];
        });
    }

    public static function name(): string
    {
        return self::config()['name'];
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
