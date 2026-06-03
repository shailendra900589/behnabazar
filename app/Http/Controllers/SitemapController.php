<?php

namespace App\Http\Controllers;

use App\Support\Seo\SearchEngineIndexer;
use App\Support\Seo\SitemapBuilder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = SitemapBuilder::xml();

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
            'Disallow: /api/',
            '',
            'User-agent: Googlebot',
            'Allow: /',
            '',
            'User-agent: Bingbot',
            'Allow: /',
            '',
            'Sitemap: '.$base.'/sitemap.xml',
        ];

        $key = SearchEngineIndexer::indexNowKey();
        if ($key) {
            $lines[] = '';
            $lines[] = '# IndexNow key: '.$base.'/'.$key.'.txt';
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function indexNowKey(string $key): Response
    {
        $expected = SearchEngineIndexer::indexNowKey();
        abort_unless($expected && hash_equals($expected, $key), 404);

        return response($expected, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
