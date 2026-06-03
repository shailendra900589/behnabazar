<?php

namespace App\Support\Seo;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class SearchEngineIndexer
{
    /** @return array{ok: bool, messages: list<string>} */
    public static function runFullIndex(): array
    {
        if (! config('seo.enabled', true)) {
            return ['ok' => false, 'messages' => ['SEO disabled (SEO_ENABLED=false).']];
        }

        SitemapBuilder::flush();
        $sitemapUrl = route('sitemap', [], true);
        $messages = ['Sitemap rebuilt: '.$sitemapUrl];

        foreach (self::pingSitemap($sitemapUrl) as $line) {
            $messages[] = $line;
        }

        $urls = array_slice(SitemapBuilder::collectIndexableUrls(), 0, (int) config('seo.indexnow_max_urls', 200));
        foreach (self::submitIndexNow($urls) as $line) {
            $messages[] = $line;
        }

        Setting::updateOrCreate(['setting_key' => 'seo_last_indexed_at'], [
            'setting_value' => now()->toIso8601String(),
        ]);

        return ['ok' => true, 'messages' => $messages];
    }

    public static function notifyUrl(string $url): void
    {
        if (! config('seo.enabled', true) || ! config('seo.auto_index', true)) {
            return;
        }

        SitemapBuilder::flush();
        self::submitIndexNow([$url]);
    }

    public static function notifyProduct(Product $product): void
    {
        if ($product->qc_status !== 'approved') {
            return;
        }

        self::notifyUrl(route('product.show', $product, true));
    }

    public static function indexNowKey(): ?string
    {
        $key = trim((string) (Setting::value('indexnow_api_key') ?: env('INDEXNOW_KEY', '')));

        if ($key === '' && config('seo.auto_index', true)) {
            $key = bin2hex(random_bytes(16));
            Setting::updateOrCreate(['setting_key' => 'indexnow_api_key'], ['setting_value' => $key]);
        }

        return $key !== '' ? $key : null;
    }

    public static function indexNowKeyLocation(): ?string
    {
        $key = self::indexNowKey();

        return $key ? rtrim((string) config('app.url'), '/').'/'.$key.'.txt' : null;
    }

    /** @return list<string> */
    public static function pingSitemap(string $sitemapUrl): array
    {
        $encoded = urlencode($sitemapUrl);
        $endpoints = [
            'Google' => 'https://www.google.com/ping?sitemap='.$encoded,
            'Bing' => 'https://www.bing.com/ping?sitemap='.$encoded,
        ];

        $lines = [];

        foreach ($endpoints as $name => $url) {
            try {
                $response = Http::timeout(15)->get($url);
                $lines[] = $name.' sitemap ping: HTTP '.$response->status();
            } catch (\Throwable $e) {
                Log::warning('Sitemap ping failed', ['engine' => $name, 'error' => $e->getMessage()]);
                $lines[] = $name.' sitemap ping failed';
            }
        }

        return $lines;
    }

    /** @return list<string> */
    public static function submitIndexNow(array $urls): array
    {
        $key = self::indexNowKey();
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! $key || ! $host || $urls === []) {
            return ['IndexNow skipped (no key or URLs)'];
        }

        $urls = array_values(array_unique(array_filter($urls)));
        $chunks = array_chunk($urls, 100);
        $lines = [];

        foreach ($chunks as $chunk) {
            try {
                $response = Http::timeout(20)
                    ->withHeaders(['Content-Type' => 'application/json; charset=utf-8'])
                    ->post('https://api.indexnow.org/indexnow', [
                        'host' => $host,
                        'key' => $key,
                        'keyLocation' => self::indexNowKeyLocation(),
                        'urlList' => $chunk,
                    ]);

                $lines[] = 'IndexNow ('.count($chunk).' URLs): HTTP '.$response->status();
            } catch (\Throwable $e) {
                Log::warning('IndexNow failed', ['error' => $e->getMessage()]);
                $lines[] = 'IndexNow batch failed';
            }
        }

        return $lines;
    }

    public static function googleVerification(): string
    {
        return trim((string) (Setting::value('seo_google_verification') ?: env('GOOGLE_SITE_VERIFICATION', '')));
    }

    public static function bingVerification(): string
    {
        return trim((string) (Setting::value('seo_bing_verification') ?: env('BING_SITE_VERIFICATION', '')));
    }
}
