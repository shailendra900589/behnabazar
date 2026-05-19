<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\ReferralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReferralProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_share_payload_is_public_and_includes_links(): void
    {
        Setting::updateOrCreate(['setting_key' => 'referral_program_enabled'], ['setting_value' => '1']);

        $vendor = User::create([
            'name' => 'Vendor',
            'email' => 'vendor-share@test.test',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'account_status' => 'active',
            'is_email_verified' => true,
        ]);
        $category = Category::create(['name' => 'Test', 'slug' => 'test', 'icon' => 'bi-box']);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Share Test Product',
            'slug' => Str::slug('Share Test Product'),
            'price' => 99,
            'description' => 'Test',
            'image' => 'https://example.com/p.jpg',
            'qc_status' => 'approved',
        ]);

        $response = $this->getJson(route('product.share.payload', $product));

        $response->assertOk()
            ->assertJsonStructure(['url', 'title', 'text', 'links' => ['whatsapp', 'facebook']]);
    }

    public function test_admin_referrals_section_loads(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-ref@test.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'account_status' => 'active',
            'is_email_verified' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard', ['section' => 'referrals']))
            ->assertOk()
            ->assertSee('Referral program');
    }

    public function test_referral_settings_helper_reads_triggers(): void
    {
        Setting::updateOrCreate(['setting_key' => 'referral_user_triggers'], ['setting_value' => 'share_first_purchase,first_purchase']);

        $this->assertTrue(ReferralSettings::hasUserTrigger('share_first_purchase'));
        $this->assertTrue(ReferralSettings::hasUserTrigger('first_purchase'));
        $this->assertFalse(ReferralSettings::hasUserTrigger('signup_with_code'));
    }
}
