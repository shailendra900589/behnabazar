<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::query()->where('qc_status', 'approved')->exists()) {
            return;
        }

        $vendor = User::query()->where('role', 'vendor')->first();
        if (! $vendor) {
            $vendor = User::query()->where('role', 'admin')->first();
        }

        $categories = collect([
            ['Grocery & Essentials', 'bi-basket2'],
            ['Organic Products', 'bi-flower1'],
            ['Electronics', 'bi-cpu'],
            ['Clothing & Fashion', 'bi-bag-heart'],
            ['Home & Kitchen', 'bi-house-heart'],
            ['Beauty & Personal Care', 'bi-stars'],
            ['Footwear & Accessories', 'bi-watch'],
            ['Handmade & Local Goods', 'bi-gem'],
        ])->map(fn ($row) => Category::updateOrCreate(
            ['slug' => Str::slug($row[0])],
            ['name' => $row[0], 'icon' => $row[1]]
        ));

        $demoProducts = [
            ['Daily Grocery Pack', 'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=900&auto=format&fit=crop', 499, 649],
            ['Organic Millet Mix', 'https://images.unsplash.com/photo-1506086670733-894c6f7d3f7b?q=80&w=900&auto=format&fit=crop', 349, 449],
            ['Wireless Earbuds', 'https://images.unsplash.com/photo-1590658268037-6bf3fd8fba49?q=80&w=900&auto=format&fit=crop', 1499, 1999],
            ['Cotton Casual Shirt', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=900&auto=format&fit=crop', 899, 1199],
            ['Steel Kitchen Set', 'https://images.unsplash.com/photo-1556911220-bff31c812dba?q=80&w=900&auto=format&fit=crop', 1199, 1499],
            ['Hydrating Face Cream', 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?q=80&w=900&auto=format&fit=crop', 299, 399],
            ['Classic Sneakers', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop', 1299, 1699],
            ['Handmade Table Runner', 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=900&auto=format&fit=crop', 699, 899],
        ];

        foreach ($demoProducts as $i => [$title, $image, $price, $mrp]) {
            $category = $categories[$i % $categories->count()];
            Product::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'vendor_id' => $vendor?->id,
                    'category_id' => $category->id,
                    'title' => $title,
                    'price' => $price,
                    'compare_at_price' => $mrp,
                    'description' => 'Verified Behna Bazar listing — demo catalog for live storefront.',
                    'image' => $image,
                    'qc_status' => 'approved',
                ]
            );
        }

        if (! Banner::query()->where('status', true)->exists()) {
            Banner::create([
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1400&auto=format&fit=crop',
                'link' => '/',
                'sort_order' => 1,
                'status' => true,
            ]);
        }
    }
}
