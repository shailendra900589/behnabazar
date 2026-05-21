<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
class FixDemoProductImages extends Command
{
    protected $signature = 'marketplace:fix-demo-images';

    protected $description = 'Fix demo product Unsplash images to match product titles (live DB)';

    /** @var array<string, string> slug => image url */
    private const IMAGES = [
        'daily-grocery-pack' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=900&auto=format&fit=crop',
        'organic-millet-mix' => 'https://images.unsplash.com/photo-1506086670733-894c6f7d3f7b?q=80&w=900&auto=format&fit=crop',
        'wireless-earbuds' => 'https://images.unsplash.com/photo-1590658268037-6bf3fd8fba49?q=80&w=900&auto=format&fit=crop',
        'cotton-casual-shirt' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=900&auto=format&fit=crop',
        'steel-kitchen-set' => 'https://images.unsplash.com/photo-1556911220-bff31c812dba?q=80&w=900&auto=format&fit=crop',
        'hydrating-face-cream' => 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?q=80&w=900&auto=format&fit=crop',
        'classic-sneakers' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop',
        'handmade-table-runner' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=900&auto=format&fit=crop',
    ];

    public function handle(): int
    {
        $updated = 0;
        foreach (self::IMAGES as $slug => $url) {
            $product = Product::query()->where('slug', $slug)->first();
            if (! $product) {
                continue;
            }
            if ($product->image !== $url) {
                $product->image = $url;
                $product->saveQuietly();
                $updated++;
                $this->line("Updated: {$product->title}");
            }
        }

        $this->info("Done. {$updated} product image(s) updated.");

        return self::SUCCESS;
    }
}
