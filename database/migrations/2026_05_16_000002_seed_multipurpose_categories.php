<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            ['name' => 'Grocery & Essentials', 'icon' => 'bi-basket2'],
            ['name' => 'Organic Products', 'icon' => 'bi-flower1'],
            ['name' => 'Electronics', 'icon' => 'bi-cpu'],
            ['name' => 'Clothing & Fashion', 'icon' => 'bi-bag-heart'],
            ['name' => 'Home & Kitchen', 'icon' => 'bi-house-heart'],
            ['name' => 'Beauty & Personal Care', 'icon' => 'bi-stars'],
            ['name' => 'Footwear & Accessories', 'icon' => 'bi-watch'],
            ['name' => 'Handmade & Local Goods', 'icon' => 'bi-gem'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('categories')
            ->whereIn('slug', [
                'grocery-essentials',
                'organic-products',
                'electronics',
                'clothing-fashion',
                'home-kitchen',
                'beauty-personal-care',
                'footwear-accessories',
                'handmade-local-goods',
            ])
            ->delete();
    }
};
