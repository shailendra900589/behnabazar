<?php

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StructuredData
{
    /** @return list<array<string, mixed>> */
    public static function globalGraph(): array
    {
        $cfg = SiteSeoSettings::config();

        return [
            self::organization($cfg),
            self::webSite($cfg),
        ];
    }

    /** @param  array<string, mixed>  $cfg */
    public static function organization(array $cfg): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $cfg['site_name'],
            'url' => $cfg['url'],
            'logo' => asset('images/brand/bb-mark.jpeg'),
            'description' => $cfg['tagline'],
            'areaServed' => $cfg['area_served'],
        ];

        if (! empty($cfg['contact_email'])) {
            $data['contactPoint'] = [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => $cfg['contact_email'],
                'availableLanguage' => ['en', 'hi'],
            ];
        }

        return $data;
    }

    /** @param  array<string, mixed>  $cfg */
    public static function webSite(array $cfg): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $cfg['site_name'],
            'url' => $cfg['url'],
            'description' => $cfg['tagline'],
            'inLanguage' => $cfg['locale'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $cfg['url'].'/?search={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /** @param  array<string, mixed>  $cfg */
    public static function localBusiness(array $cfg): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $cfg['site_name'],
            'url' => $cfg['url'],
            'image' => asset('images/brand/behna-bazar-wordmark.jpeg'),
            'description' => $cfg['tagline'],
            'address' => [
                '@type' => 'PostalAddress',
                'addressCountry' => $cfg['country'],
                'addressLocality' => $cfg['locality'],
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $cfg['latitude'],
                'longitude' => $cfg['longitude'],
            ],
            'areaServed' => [
                '@type' => 'Country',
                'name' => 'India',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function breadcrumbs(array $items): array
    {
        $list = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        foreach ($items as $i => $item) {
            $list['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return [$list];
    }

    public static function product(Product $product, float $avgRating = 0, int $reviewCount = 0): array
    {
        $cfg = SiteSeoSettings::config();
        $pricing = $product->pricing();
        $url = route('product.show', $product, true);
        $images = array_slice($product->galleryUrls(), 0, 5);

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => Str::limit(strip_tags((string) $product->description), 500),
            'image' => $images,
            'sku' => 'BB-'.$product->id,
            'url' => $url,
            'category' => $product->category?->name,
            'brand' => [
                '@type' => 'Brand',
                'name' => $product->vendor?->shop_name ?? $cfg['site_name'],
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $url,
                'priceCurrency' => $cfg['currency'],
                'price' => number_format($pricing['sale'], 2, '.', ''),
                'availability' => 'https://schema.org/InStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $product->vendor?->shop_name ?? $cfg['site_name'],
                ],
                'areaServed' => $cfg['area_served'],
            ],
        ];

        if ($pricing['mrp']) {
            $data['offers']['priceSpecification'] = [
                '@type' => 'UnitPriceSpecification',
                'price' => number_format($pricing['sale'], 2, '.', ''),
                'priceCurrency' => $cfg['currency'],
                'referenceQuantity' => [
                    '@type' => 'QuantitativeValue',
                    'value' => 1,
                ],
            ];
        }

        if ($reviewCount > 0 && $avgRating > 0) {
            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($avgRating, 1, '.', ''),
                'reviewCount' => $reviewCount,
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        return $data;
    }

    /** AEO: FAQ from product Q&A */
    public static function faqPage(Collection $questions): ?array
    {
        $answered = $questions->filter(fn ($q) => $q->status === 'answered' && filled($q->answer))->take(8);
        if ($answered->isEmpty()) {
            return null;
        }

        $entities = [];
        foreach ($answered as $q) {
            $entities[] = [
                '@type' => 'Question',
                'name' => Str::limit(strip_tags((string) $q->question), 200),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => Str::limit(strip_tags((string) $q->answer), 500),
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    public static function itemList(string $name, string $url, Collection $products): array
    {
        $elements = [];
        foreach ($products->take(12) as $i => $product) {
            if (! $product instanceof Product) {
                continue;
            }
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'url' => route('product.show', $product, true),
                'name' => $product->title,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'url' => $url,
            'numberOfItems' => $products->count(),
            'itemListElement' => $elements,
        ];
    }

    public static function store(User $vendor): array
    {
        $cfg = SiteSeoSettings::config();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Store',
            'name' => $vendor->shop_name ?? $vendor->name,
            'url' => route('vendor.shop', $vendor, true),
            'description' => "Shop {$vendor->shop_name} on {$cfg['site_name']}. Verified vendor with QC-approved products.",
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $vendor->city ?? $cfg['locality'],
                'addressCountry' => $cfg['country'],
            ],
        ];
    }

    public static function category(Category $category): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $category->name,
            'description' => "Browse {$category->name} products on ".SiteSeoSettings::config()['site_name'],
            'url' => route('home', ['cat' => $category->slug], true),
        ];
    }
}
