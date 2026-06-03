<?php

namespace App\Console\Commands;

use App\Support\Seo\SearchEngineIndexer;
use Illuminate\Console\Command;

class SeoAutoIndexCommand extends Command
{
    protected $signature = 'marketplace:seo-index';

    protected $description = 'Rebuild sitemap and ping Google, Bing, and IndexNow for automatic search indexing';

    public function handle(): int
    {
        if (! config('seo.enabled', true)) {
            $this->warn('SEO is disabled (SEO_ENABLED=false).');

            return self::FAILURE;
        }

        $this->info('Running automatic SEO indexing…');

        $result = SearchEngineIndexer::runFullIndex();

        foreach ($result['messages'] as $line) {
            $this->line('  '.$line);
        }

        $key = SearchEngineIndexer::indexNowKey();
        if ($key) {
            $this->line('  IndexNow key file: '.SearchEngineIndexer::indexNowKeyLocation());
        }

        $this->info('SEO indexing finished.');

        return self::SUCCESS;
    }
}
