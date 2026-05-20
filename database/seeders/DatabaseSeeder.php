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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@behnabazar.test', 'password' => Hash::make('password'), 'role' => 'admin', 'account_status' => 'active', 'is_email_verified' => true, 'email_verified_at' => now()]);
        $vendor = User::create(['name' => 'Demo Vendor', 'email' => 'vendor@behnabazar.test', 'password' => Hash::make('password'), 'role' => 'vendor', 'shop_name' => 'Bloom Local Studio', 'city' => 'Indore', 'account_status' => 'active', 'reg_fee_paid' => true, 'is_email_verified' => true, 'email_verified_at' => now()]);
        User::create(['name' => 'QC Manager', 'email' => 'qc@behnabazar.test', 'password' => Hash::make('password'), 'role' => 'qc_manager', 'account_status' => 'active', 'is_email_verified' => true, 'email_verified_at' => now()]);
        User::create(['name' => 'Customer', 'email' => 'user@behnabazar.test', 'password' => Hash::make('password'), 'role' => 'user', 'coins' => 120, 'phone' => '9999999999', 'address' => 'Demo Street, Local Market', 'city' => 'Indore', 'pincode' => '452001', 'account_status' => 'active', 'is_email_verified' => true, 'email_verified_at' => now()]);

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

        $images = [
            'https://images.unsplash.com/photo-1612336307429-8a898d10e223?q=80&w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1595777457583-95e059d581b8?q=80&w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?q=80&w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?q=80&w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1562157873-818bc0726f68?q=80&w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?q=80&w=900&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=900&auto=format&fit=crop',
        ];

        foreach (range(1, 8) as $i) {
            $category = $categories[($i - 1) % $categories->count()];
            Product::create([
                'vendor_id' => $i % 3 === 0 ? null : $vendor->id,
                'category_id' => $category->id,
                'title' => ['Daily Grocery Pack', 'Organic Millet Mix', 'Wireless Earbuds', 'Cotton Casual Shirt', 'Steel Kitchen Set', 'Hydrating Face Cream', 'Classic Sneakers', 'Handmade Table Runner'][$i - 1],
                'slug' => Str::slug(['Daily Grocery Pack', 'Organic Millet Mix', 'Wireless Earbuds', 'Cotton Casual Shirt', 'Steel Kitchen Set', 'Hydrating Face Cream', 'Classic Sneakers', 'Handmade Table Runner'][$i - 1]),
                'price' => [499, 349, 1499, 899, 1199, 299, 1299, 699][$i - 1],
                'description' => 'A verified Behna Bazar marketplace product with category review, seller support, and professional fulfillment tracking.',
                'image' => $images[$i - 1],
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
        Setting::updateOrCreate(['setting_key' => 'payout_min_amount'], ['setting_value' => '500']);
        Setting::updateOrCreate(['setting_key' => 'product_edit_requires_qc'], ['setting_value' => '1']);
        Coupon::create(['code' => 'WELCOME50', 'discount_type' => 'flat', 'discount_value' => 50, 'min_cart_value' => 500, 'status' => true]);
        Ad::create(['location' => 'home_top', 'ad_type' => 'code', 'code' => '<div class="p-4 text-center">Promote your local brand on Behna Bazar</div>', 'status' => true]);
        Banner::create(['image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1400&auto=format&fit=crop', 'link' => '/', 'sort_order' => 1, 'status' => true]);
    }
}
