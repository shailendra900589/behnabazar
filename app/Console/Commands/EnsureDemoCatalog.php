<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class EnsureDemoCatalog extends Command
{
    protected $signature = 'marketplace:ensure-catalog';

    protected $description = 'Seed demo products on live if no approved products exist';

    public function handle(): int
    {
        $approved = Product::query()->where('qc_status', 'approved')->count();

        if ($approved > 0) {
            $this->info("Catalog OK: {$approved} approved product(s).");

            return self::SUCCESS;
        }

        $this->warn('No approved products — seeding demo catalog...');
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\DemoCatalogSeeder', '--force' => true]);
        Cache::forget('storefront.price_bounds');
        Cache::forget('storefront.flash_deal');

        $count = Product::query()->where('qc_status', 'approved')->count();
        $this->info("Done. {$count} approved product(s) now in catalog.");

        return self::SUCCESS;
    }
}
