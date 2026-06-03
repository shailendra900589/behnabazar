<?php

namespace App\Console\Commands;

use App\Support\PublicStorage;
use App\Support\StoragePublicLink;
use Illuminate\Console\Command;

class PublishPublicStorage extends Command
{
    protected $signature = 'marketplace:publish-storage';

    protected $description = 'Publish uploaded files from storage/app/public to public/storage and public_html';

    public function handle(): int
    {
        StoragePublicLink::ensure();
        PublicStorage::republishAll();
        $this->info('All public uploads published (banners, product images, etc.).');

        return self::SUCCESS;
    }
}
