<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\Seo\ProductSeoGenerator;
use App\Support\Seo\SearchEngineIndexer;
use App\Support\Seo\SitemapBuilder;

class ProductSeoObserver
{
    public function saving(Product $product): void
    {
        if (! $product->title) {
            return;
        }

        ProductSeoGenerator::apply($product);
    }

    public function saved(Product $product): void
    {
        SitemapBuilder::flush();

        if ($product->qc_status !== 'approved') {
            return;
        }

        if ($product->wasRecentlyCreated
            || $product->wasChanged(['qc_status', 'title', 'slug', 'description', 'price', 'category_id'])) {
            SearchEngineIndexer::notifyProduct($product);
        }
    }

    public function deleted(Product $product): void
    {
        SitemapBuilder::flush();
    }
}
