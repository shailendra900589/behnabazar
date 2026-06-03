<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\Seo\SearchEngineIndexer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoIndexingTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_and_robots_are_public(): void
    {
        $this->seed();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false);

        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap:');
    }

    public function test_indexnow_key_file_is_served_when_configured(): void
    {
        $key = str_repeat('a', 32);
        Setting::updateOrCreate(['setting_key' => 'indexnow_api_key'], ['setting_value' => $key]);

        $this->get('/'.$key.'.txt')
            ->assertOk()
            ->assertSee($key, false);
    }

    public function test_seo_index_command_pings_search_engines(): void
    {
        $this->seed();
        Http::fake([
            'www.google.com/*' => Http::response('', 200),
            'www.bing.com/*' => Http::response('', 200),
            'api.indexnow.org/*' => Http::response('', 200),
        ]);

        $this->artisan('marketplace:seo-index')
            ->assertSuccessful();

        Http::assertSent(fn ($request) => str_contains($request->url(), 'google.com/ping'));
    }

    public function test_homepage_includes_seo_verification_meta_when_set(): void
    {
        $this->seed();
        Setting::updateOrCreate(['setting_key' => 'seo_google_verification'], ['setting_value' => 'test-google-code']);
        Setting::updateOrCreate(['setting_key' => 'seo_bing_verification'], ['setting_value' => 'test-bing-code']);

        $this->get('/')
            ->assertOk()
            ->assertSee('name="google-site-verification" content="test-google-code"', false)
            ->assertSee('name="msvalidate.01" content="test-bing-code"', false);
    }
}
