<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('seo.sitemap.xml', 3600, function () {
            return $this->buildXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /dashboard',
            'Disallow: /manage/',
            'Disallow: /checkout',
            'Disallow: /cart',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /account/',
            'Disallow: /sell/',
            '',
            'Sitemap: '.$base.'/sitemap.xml',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    private function buildXml(): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $urls = [];

        $urls[] = $this->urlEntry($base.'/', now(), 'daily', '1.0');
        $urls[] = $this->urlEntry(route('contact', [], true), now(), 'monthly', '0.6');
        $urls[] = $this->urlEntry(route('local-delivery', [], true), now(), 'monthly', '0.6');
        $urls[] = $this->urlEntry(route('returns-policy', [], true), now(), 'monthly', '0.5');

        foreach (Category::orderBy('name')->get() as $category) {
            $urls[] = $this->urlEntry(
                route('home', ['cat' => $category->slug], true),
                $category->updated_at ?? now(),
                'daily',
                '0.8'
            );
        }

        Product::query()
            ->where('qc_status', 'approved')
            ->orderByDesc('updated_at')
            ->chunk(200, function ($products) use (&$urls) {
                foreach ($products as $product) {
                    $urls[] = $this->urlEntry(
                        route('product.show', $product, true),
                        $product->updated_at ?? now(),
                        'weekly',
                        '0.9'
                    );
                }
            });

        User::query()
            ->where('role', 'vendor')
            ->where('account_status', 'active')
            ->orderByDesc('updated_at')
            ->chunk(100, function ($vendors) use (&$urls) {
                foreach ($vendors as $vendor) {
                    $urls[] = $this->urlEntry(
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

    private function urlEntry(string $loc, $lastmod, string $changefreq, string $priority): string
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
