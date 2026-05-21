<?php

namespace App\Support\Seo;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeoGenerator
{
    public static function apply(Product $product): void
    {
        $product->seo_title = self::title($product);
        $product->seo_description = self::description($product);
        $product->seo_keywords = self::keywordsString($product);
    }

    public static function title(Product $product): string
    {
        $site = SiteSeoSettings::config()['site_name'];
        $category = $product->category?->name;
        $pricing = $product->pricing();
        $price = '₹'.number_format($pricing['sale'], 0);

        $parts = array_filter([
            $product->title,
            $category ? "Buy {$category} Online" : 'Buy Online',
            $price,
        ]);

        $title = implode(' — ', $parts);

        return Str::limit($title, 58, '').config('seo.title_separator', ' | ').$site;
    }

    public static function description(Product $product): string
    {
        $site = SiteSeoSettings::config()['site_name'];
        $pricing = $product->pricing();
        $category = $product->category?->name ?? 'marketplace';
        $vendor = $product->vendor?->shop_name;
        $locality = SiteSeoSettings::config()['locality'];

        $priceLine = 'Now '.$pricing['sale'];
        if ($pricing['mrp']) {
            $priceLine .= ' (MRP ₹'.number_format($pricing['mrp'], 0).', '.$pricing['percent_off'].'% off)';
        }

        $base = trim((string) ($product->description ?: ''));
        if ($base === '') {
            $base = "Shop {$product->title} on {$site}. Verified {$category} listing with secure checkout.";
        }

        $extras = array_filter([
            $priceLine,
            $vendor ? "Sold by {$vendor}" : null,
            "Delivery across {$locality}",
        ]);

        $text = Str::limit(strip_tags($base), 90, '…').' '.implode('. ', $extras).'.';

        return Str::limit($text, (int) config('seo.max_description_length', 160), '…');
    }

    /** @return list<string> */
    public static function keywords(Product $product): array
    {
        $pricing = $product->pricing();
        $words = array_filter([
            $product->title,
            $product->category?->name,
            $product->category?->slug,
            $product->vendor?->shop_name,
            $product->vendor?->city,
            SiteSeoSettings::config()['site_name'],
            SiteSeoSettings::config()['locality'],
            'buy online',
            'online shopping',
            $pricing['percent_off'] ? $pricing['percent_off'].'% off' : null,
            'QC verified',
            'Behna Bazar',
        ]);

        foreach ($product->variants ?? [] as $variant) {
            foreach ($variant->attributeMap() as $value) {
                $words[] = $value;
            }
        }

        return array_values(array_unique(array_map(
            fn ($w) => Str::lower(trim((string) $w)),
            array_filter($words, fn ($w) => is_string($w) && strlen($w) > 2)
        )));
    }

    public static function keywordsString(Product $product): string
    {
        return implode(', ', array_slice(self::keywords($product), 0, (int) config('seo.max_keywords', 12)));
    }
}
