<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoAccountsSeeder::class);
        $vendor = User::where('email', 'vendor@behnabazar.test')->first();
        $admin = User::where('email', 'admin@behnabazar.test')->first();

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
            ['Daily Grocery Pack', 'https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=900&auto=format&fit=crop'],
            ['Organic Millet Mix', 'https://images.unsplash.com/photo-1506086670733-894c6f7d3f7b?q=80&w=900&auto=format&fit=crop'],
            ['Wireless Earbuds', 'https://images.unsplash.com/photo-1590658268037-6bf3fd8fba49?q=80&w=900&auto=format&fit=crop'],
            ['Cotton Casual Shirt', 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=900&auto=format&fit=crop'],
            ['Steel Kitchen Set', 'https://images.unsplash.com/photo-1556911220-bff31c812dba?q=80&w=900&auto=format&fit=crop'],
            ['Hydrating Face Cream', 'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?q=80&w=900&auto=format&fit=crop'],
            ['Classic Sneakers', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop'],
            ['Handmade Table Runner', 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=900&auto=format&fit=crop'],
        ];

        $salePrices = [499, 349, 1499, 899, 1199, 299, 1299, 699];
        $mrpPrices = [649, 449, 1999, 1199, 1499, 399, 1699, 899];

        foreach (range(1, 8) as $i) {
            $category = $categories[($i - 1) % $categories->count()];
            [$title, $image] = $demoProducts[$i - 1];
            Product::create([
                'vendor_id' => $i % 3 === 0 ? null : $vendor->id,
                'category_id' => $category->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'price' => $salePrices[$i - 1],
                'compare_at_price' => $mrpPrices[$i - 1],
                'description' => 'A verified Behna Bazar marketplace product with category review, seller support, and professional fulfillment tracking.',
                'image' => $image,
                'qc_status' => $i === 8 ? 'pending' : 'approved',
            ]);
        }

        Setting::updateOrCreate(['setting_key' => 'coin_earn_rate'], ['setting_value' => '10']);
        Setting::updateOrCreate(['setting_key' => 'coin_redeem_limit'], ['setting_value' => '5']);
        Setting::updateOrCreate(['setting_key' => 'referral_program_enabled'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'referral_require_admin_approval'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'referral_user_reward_coins'], ['setting_value' => '50']);
        Setting::updateOrCreate(['setting_key' => 'referral_vendor_reward_amount'], ['setting_value' => '100']);
        Setting::updateOrCreate(['setting_key' => 'referral_min_order_amount'], ['setting_value' => '0']);
        Setting::updateOrCreate(['setting_key' => 'referral_share_validity_days'], ['setting_value' => '30']);
        Setting::updateOrCreate(['setting_key' => 'referral_user_triggers'], ['setting_value' => 'share_first_purchase']);
        Setting::updateOrCreate(['setting_key' => 'referral_vendor_triggers'], ['setting_value' => 'referee_first_sale,referee_first_product']);
        Setting::updateOrCreate(['setting_key' => 'resell_customize_fee'], ['setting_value' => '99']);
        Setting::updateOrCreate(['setting_key' => 'resell_program_enabled'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'resell_bulk_min_qty'], ['setting_value' => '5']);
        Setting::updateOrCreate(['setting_key' => 'resell_bulk_discount_percent'], ['setting_value' => '5']);
        Setting::updateOrCreate(['setting_key' => 'payout_min_amount'], ['setting_value' => '500']);
        Setting::updateOrCreate(['setting_key' => 'product_edit_requires_qc'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'cod_enabled'], ['setting_value' => '1']);
        Setting::updateOrCreate(['setting_key' => 'free_shipping_threshold'], ['setting_value' => '499']);
        Setting::updateOrCreate(['setting_key' => 'seo_locality'], ['setting_value' => 'India']);
        Setting::updateOrCreate(['setting_key' => 'seo_region'], ['setting_value' => 'IN']);
        Setting::updateOrCreate(['setting_key' => 'seo_latitude'], ['setting_value' => '28.6139']);
        Setting::updateOrCreate(['setting_key' => 'seo_longitude'], ['setting_value' => '77.2090']);
        Coupon::create(['code' => 'WELCOME50', 'discount_type' => 'flat', 'discount_value' => 50, 'min_cart_value' => 500, 'status' => true]);
        // No placeholder ads — real vendor ads are created via dashboard
        Banner::create(['image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1400&auto=format&fit=crop', 'link' => '/', 'sort_order' => 1, 'status' => true]);
    }
}
