<?php

namespace App\Support\Seo;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeoResolver
{
    public static function resolve(Request $request, array $viewData = []): SeoMeta
    {
        if (! config('seo.enabled', true)) {
            return self::fallback();
        }

        if (isset($viewData['seo']) && $viewData['seo'] instanceof SeoMeta) {
            return $viewData['seo'];
        }

        $route = $request->route();
        $name = $route?->getName() ?? '';

        if (self::shouldNoindex($name)) {
            return SeoBuilder::noindex(
                Str::headline(str_replace(['.', '-'], ' ', $name)),
                'Account and checkout pages are not indexed.'
            );
        }

        if (isset($viewData['product']) && $viewData['product'] instanceof Product) {
            return SeoBuilder::forProduct(
                $viewData['product'],
                (float) ($viewData['avgRating'] ?? $viewData['product']->averageRating()),
                (int) ($viewData['totalReviews'] ?? $viewData['product']->reviews()->where('is_approved', true)->count())
            );
        }

        if ($name === 'product.show' && $route?->parameter('product') instanceof Product) {
            $product = $route->parameter('product');

            return SeoBuilder::forProduct($product, $product->averageRating(), (int) $product->reviews()->where('is_approved', true)->count());
        }

        if (isset($viewData['vendor']) && $viewData['vendor'] instanceof User) {
            return SeoBuilder::forVendor($viewData['vendor']);
        }

        if ($name === 'vendor.shop' && $route?->parameter('vendor') instanceof User) {
            return SeoBuilder::forVendor($route->parameter('vendor'));
        }

        return match ($name) {
            'home' => SeoBuilder::forHome($request),
            'contact' => SeoBuilder::forStaticPage('contact'),
            'local-delivery' => SeoBuilder::forStaticPage('local-delivery'),
            'returns-policy' => SeoBuilder::forStaticPage('returns-policy'),
            'cart' => SeoBuilder::forPage('Shopping Cart', 'Review items in your Behna Bazar cart before checkout.', route('cart', [], true), true),
            'checkout' => SeoBuilder::noindex('Checkout', 'Secure checkout'),
            'login' => SeoBuilder::noindex('Login'),
            'register' => SeoBuilder::noindex('Register'),
            'wishlist' => SeoBuilder::noindex('Wishlist'),
            default => self::resolveNamedRoute($name, $request),
        };
    }

    private static function resolveNamedRoute(string $name, Request $request): SeoMeta
    {
        if ($request->filled('cat')) {
            $category = Category::where('slug', $request->cat)->first();
            if ($category) {
                return SeoBuilder::forCategory($category, $request);
            }
        }

        if (str_starts_with($name, 'dashboard') || str_contains($name, 'manage.')) {
            return SeoBuilder::noindex('Dashboard');
        }

        $cfg = SiteSeoSettings::config();

        return new SeoMeta(
            title: $cfg['site_name'],
            description: Str::limit($cfg['tagline'], 160, '…'),
            canonical: url()->current(),
            keywords: ['online shopping', $cfg['site_name']],
            jsonLd: StructuredData::globalGraph(),
        );
    }

    private static function shouldNoindex(string $routeName): bool
    {
        foreach (config('seo.noindex_routes', []) as $pattern) {
            if (str_ends_with($pattern, '.')) {
                if (str_starts_with($routeName, $pattern)) {
                    return true;
                }
            } elseif ($routeName === $pattern) {
                return true;
            }
        }

        return false;
    }

    private static function fallback(): SeoMeta
    {
        $cfg = SiteSeoSettings::config();

        return new SeoMeta(
            title: $cfg['site_name'],
            description: $cfg['tagline'],
            canonical: url()->current(),
        );
    }
}
