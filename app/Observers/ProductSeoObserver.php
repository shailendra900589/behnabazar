<?php

namespace App\Observers;

use App\Models\Product;
use App\Support\Seo\ProductSeoGenerator;

class ProductSeoObserver
{
    public function saving(Product $product): void
    {
        if (! $product->title) {
            return;
        }

        ProductSeoGenerator::apply($product);
    }
}
