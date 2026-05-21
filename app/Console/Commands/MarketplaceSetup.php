<?php

namespace App\Console\Commands;

use App\Support\StoragePublicLink;
use Illuminate\Console\Command;

class MarketplaceSetup extends Command
{
    protected $signature = 'marketplace:setup {--fresh : Run migrations fresh (destructive)}';

    protected $description = 'Apply migrations, default marketplace/SEO/COD settings, and clear caches';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            if (! $this->confirm('migrate:fresh will DELETE all data. Continue?')) {
                return self::FAILURE;
            }
            $this->call('migrate:fresh', ['--force' => true]);
            $this->call('db:seed', ['--force' => true]);
        } else {
            $this->call('migrate', ['--force' => true]);
            $this->call('db:seed', ['--class' => 'Database\\Seeders\\MarketplaceDefaultsSeeder', '--force' => true]);
        }

        $this->call('optimize:clear');
        foreach (glob(base_path('bootstrap/cache/*.php')) ?: [] as $file) {
            if (basename($file) !== '.gitignore') {
                @unlink($file);
            }
        }
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        $storageOk = StoragePublicLink::ensure();

        $this->newLine();
        $this->info('Behna Bazar marketplace setup complete.');
        $this->line('  • COD, free shipping, SEO/GEO defaults saved');
        $this->line('  • Product MRP + SEO fields refreshed');
        if ($storageOk) {
            $this->line('  • public/storage ready (symlink or copy)');
        } else {
            $this->warn('  • '.StoragePublicLink::helpMessage());
        }
        $this->newLine();
        $this->comment('Set in .env: APP_URL, RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET');
        $this->comment('Live server: run php artisan marketplace:deploy (full cache + verify)');
        $this->comment('Production: APP_DEBUG=false');

        return self::SUCCESS;
    }
}
