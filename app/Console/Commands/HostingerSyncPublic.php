<?php

namespace App\Console\Commands;

use App\Support\HostingerPublicSync;
use Illuminate\Console\Command;

class HostingerSyncPublic extends Command
{
    protected $signature = 'hostinger:sync-public {--path= : Override PUBLIC_HTML_PATH}';

    protected $description = 'Copy Laravel public assets to Hostinger public_html and wire index.php to behnabazar';

    public function handle(): int
    {
        $path = $this->option('path') ?: null;
        $result = HostingerPublicSync::sync($path);

        if (! ($result['ok'] ?? false)) {
            $this->error($result['message'] ?? 'Sync failed.');
            $this->line('Add to .env:');
            $this->line('PUBLIC_HTML_PATH=/home/u991240931/domains/behnabazar.in/public_html');
            $this->line('BB_LARAVEL_ROOT=/home/u991240931/behnabazar');

            return self::FAILURE;
        }

        $this->info($result['message']);
        if (! empty($result['path'])) {
            $this->line('public_html: '.$result['path']);
        }
        if (! empty($result['build'])) {
            $this->line('app.css build: '.$result['build'].' (check ?v= on site)');
        }

        return self::SUCCESS;
    }
}
