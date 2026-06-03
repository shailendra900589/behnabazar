<?php

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SitemapBuilder
{
    public const CACHE_KEY = 'seo.sitemap.xml';

    public static function xml(): string
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes((int) config('seo.sitemap_cache_minutes', 60)), function () {
            return self::build();
        });
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function build(): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $urls = [];

        $urls[] = self::urlEntry($base.'/', now(), 'daily', '1.0');
        $urls[] = self::urlEntry(route('shops', [], true), now(), 'weekly', '0.75');
        $urls[] = self::urlEntry(route('contact', [], true), now(), 'monthly', '0.6');
        $urls[] = self::urlEntry(route('local-delivery', [], true), now(), 'monthly', '0.6');
        $urls[] = self::urlEntry(route('returns-policy', [], true), now(), 'monthly', '0.5');

        foreach (Category::orderBy('name')->get() as $category) {
            $urls[] = self::urlEntry(
                route('home', ['cat' => $category->slug], true),
                $category->updated_at ?? now(),
                'daily',
                '0.8'
            );
        }

        Product::query()
            ->where('qc_status', 'approved')
            ->with(['category'])
            ->orderByDesc('updated_at')
            ->chunk(200, function ($products) use (&$urls) {
                foreach ($products as $product) {
                    $urls[] = self::productEntry($product);
                }
            });

        User::query()
            ->where('role', 'vendor')
            ->where('account_status', 'active')
            ->orderByDesc('updated_at')
            ->chunk(100, function ($vendors) use (&$urls) {
                foreach ($vendors as $vendor) {
                    $urls[] = self::urlEntry(
                        route('vendor.shop', $vendor, true),
                        $vendor->updated_at ?? now(),
                        'weekly',
                        '0.7'
                    );
                }
            });

        $body = implode("\n", $urls);

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            .'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n"
            .$body."\n</urlset>";
    }

    /** @return list<string> */
    public static function collectIndexableUrls(): array
    {
        $base = rtrim((string) config('app.url'), '/');
        $list = [$base.'/', route('shops', [], true)];

        foreach (Category::pluck('slug') as $slug) {
            $list[] = route('home', ['cat' => $slug], true);
        }

        Product::query()
            ->where('qc_status', 'approved')
            ->orderByDesc('updated_at')
            ->chunk(500, function ($products) use (&$list) {
                foreach ($products as $product) {
                    $list[] = route('product.show', $product, true);
                }
            });

        User::query()
            ->where('role', 'vendor')
            ->where('account_status', 'active')
            ->chunk(200, function ($vendors) use (&$list) {
                foreach ($vendors as $vendor) {
                    $list[] = route('vendor.shop', $vendor, true);
                }
            });

        return array_values(array_unique($list));
    }

    private static function productEntry(Product $product): string
    {
        $loc = route('product.show', $product, true);
        $lm = ($product->updated_at ?? now())->format('Y-m-d');
        $image = htmlspecialchars($product->imageUrl(), ENT_XML1);
        $title = htmlspecialchars($product->title, ENT_XML1);

        return '  <url>'
            .'<loc>'.htmlspecialchars($loc, ENT_XML1).'</loc>'
            .'<lastmod>'.$lm.'</lastmod>'
            .'<changefreq>weekly</changefreq>'
            .'<priority>0.9</priority>'
            .'<image:image><image:loc>'.$image.'</image:loc><image:title>'.$title.'</image:title></image:image>'
            .'</url>';
    }

    private static function urlEntry(string $loc, $lastmod, string $changefreq, string $priority): string
    {
        $lm = $lastmod instanceof \DateTimeInterface ? $lastmod->format('Y-m-d') : date('Y-m-d');

        return '  <url>'
            .'<loc>'.htmlspecialchars($loc, ENT_XML1).'</loc>'
            .'<lastmod>'.$lm.'</lastmod>'
            .'<changefreq>'.$changefreq.'</changefreq>'
            .'<priority>'.$priority.'</priority>'
            .'</url>';
    }
}
