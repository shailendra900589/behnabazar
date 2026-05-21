<?php

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoBuilder
{
    public static function forHome(?Request $request = null): SeoMeta
    {
        $cfg = SiteSeoSettings::config();
        $request ??= request();
        $category = null;

        if ($request->filled('cat')) {
            $category = Category::where('slug', $request->cat)->first();
        }

        if ($category) {
            return self::forCategory($category, $request);
        }

        $title = $cfg['site_name'].' — Online Marketplace | Grocery, Fashion & More';
        $description = Str::limit($cfg['tagline'].' Shop verified sellers with MRP deals, local delivery & secure checkout.', 160, '…');
        $canonical = route('home', [], true);

        $jsonLd = array_merge(
            StructuredData::globalGraph(),
            [StructuredData::localBusiness($cfg)]
        );

        return new SeoMeta(
            title: $title,
            description: $description,
            canonical: $canonical,
            keywords: self::baseKeywords(),
            image: asset('images/brand/behna-bazar-wordmark.jpeg'),
            type: 'website',
            jsonLd: $jsonLd,
        );
    }

    public static function forCategory(Category $category, ?Request $request = null): SeoMeta
    {
        $cfg = SiteSeoSettings::config();
        $canonical = route('home', ['cat' => $category->slug], true);

        return new SeoMeta(
            title: "{$category->name} Products — Buy Online".config('seo.title_separator').$cfg['site_name'],
            description: "Shop {$category->name} online at {$cfg['site_name']}. Verified listings, best prices, MRP discounts & delivery in {$cfg['locality']}.",
            canonical: $canonical,
            keywords: array_merge(self::baseKeywords(), [$category->name, $category->slug, 'buy '.$category->name]),
            image: asset('images/brand/bb-mark.jpeg'),
            jsonLd: array_merge(
                StructuredData::globalGraph(),
                [StructuredData::category($category)],
                StructuredData::breadcrumbs([
                    ['name' => 'Home', 'url' => route('home', [], true)],
                    ['name' => $category->name, 'url' => $canonical],
                ])
            ),
        );
    }

    public static function forProduct(Product $product, float $avgRating = 0, int $reviewCount = 0): SeoMeta
    {
        $cfg = SiteSeoSettings::config();
        $product->loadMissing(['category', 'vendor', 'variants', 'questions']);

        $title = $product->seo_title ?: ProductSeoGenerator::title($product);
        $description = $product->seo_description ?: ProductSeoGenerator::description($product);
        $keywords = $product->seo_keywords
            ? array_map('trim', explode(',', $product->seo_keywords))
            : ProductSeoGenerator::keywords($product);

        $canonical = route('product.show', $product, true);
        $image = $product->imageUrl();

        $crumbs = array_values(array_filter([
            ['name' => 'Home', 'url' => route('home', [], true)],
            $product->category
                ? ['name' => $product->category->name, 'url' => route('home', ['cat' => $product->category->slug], true)]
                : null,
            ['name' => $product->title, 'url' => $canonical],
        ]));

        $jsonLd = array_merge(
            StructuredData::globalGraph(),
            StructuredData::breadcrumbs($crumbs),
            [StructuredData::product($product, $avgRating, $reviewCount)]
        );

        $faq = StructuredData::faqPage($product->questions);
        if ($faq) {
            $jsonLd[] = $faq;
        }

        return new SeoMeta(
            title: $title,
            description: $description,
            canonical: $canonical,
            keywords: $keywords,
            image: $image,
            type: 'product',
            jsonLd: array_values(array_filter($jsonLd)),
        );
    }

    public static function forVendor(User $vendor): SeoMeta
    {
        $cfg = SiteSeoSettings::config();
        $shop = $vendor->shop_name ?? $vendor->name;
        $canonical = route('vendor.shop', $vendor, true);

        return new SeoMeta(
            title: "{$shop} — Verified Seller Store".config('seo.title_separator').$cfg['site_name'],
            description: "Browse all products from {$shop} on {$cfg['site_name']}. QC-verified listings, secure checkout & delivery in {$cfg['locality']}.",
            canonical: $canonical,
            keywords: array_merge(self::baseKeywords(), [$shop, $vendor->city, 'vendor shop', 'online store']),
            image: asset('images/brand/bb-mark.jpeg'),
            type: 'website',
            jsonLd: array_merge(
                StructuredData::globalGraph(),
                [StructuredData::store($vendor)],
                StructuredData::breadcrumbs([
                    ['name' => 'Home', 'url' => route('home', [], true)],
                    ['name' => $shop, 'url' => $canonical],
                ])
            ),
        );
    }

    public static function forStaticPage(string $routeName): SeoMeta
    {
        $cfg = SiteSeoSettings::config();
        $pages = config('seo.static_pages', []);
        $page = $pages[$routeName] ?? [
            'title' => Str::headline(str_replace('.', ' ', $routeName)),
            'description' => $cfg['tagline'],
        ];

        $canonical = match ($routeName) {
            'contact' => route('contact', [], true),
            'local-delivery' => route('local-delivery', [], true),
            'returns-policy' => route('returns-policy', [], true),
            default => $cfg['url'],
        };

        $jsonLd = StructuredData::globalGraph();
        if (in_array($routeName, ['contact', 'local-delivery'], true)) {
            $jsonLd[] = StructuredData::localBusiness($cfg);
        }

        return new SeoMeta(
            title: $page['title'].config('seo.title_separator').$cfg['site_name'],
            description: Str::limit($page['description'], 160, '…'),
            canonical: $canonical,
            keywords: self::baseKeywords(),
            jsonLd: $jsonLd,
        );
    }

    public static function forPage(string $title, string $description, ?string $canonical = null, bool $noindex = false): SeoMeta
    {
        $cfg = SiteSeoSettings::config();

        return new SeoMeta(
            title: $title.config('seo.title_separator').$cfg['site_name'],
            description: Str::limit($description, 160, '…'),
            canonical: $canonical ?? url()->current(),
            keywords: self::baseKeywords(),
            index: ! $noindex,
            jsonLd: StructuredData::globalGraph(),
        );
    }

    public static function noindex(string $title, string $description = ''): SeoMeta
    {
        return self::forPage($title, $description ?: 'Private page', url()->current(), true);
    }

    /** @return list<string> */
    private static function baseKeywords(): array
    {
        $cfg = SiteSeoSettings::config();

        return array_filter([
            $cfg['site_name'],
            'online marketplace',
            'buy online',
            $cfg['locality'],
            'grocery online',
            'fashion online',
            'verified sellers',
            'MRP discount',
        ]);
    }
}
