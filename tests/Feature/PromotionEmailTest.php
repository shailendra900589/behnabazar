<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PromotionEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_promotion_email(): void
    {
        Mail::fake();
        $this->seed();

        $admin = User::where('role', 'admin')->firstOrFail();
        $product = Product::where('qc_status', 'approved')->firstOrFail();
        Newsletter::create(['email' => 'promo-test@example.test']);

        $this->actingAs($admin)
            ->post(route('manage.promotions.email'), [
                'product_id' => $product->id,
                'audience' => 'newsletter',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        Mail::assertSent(\App\Mail\ProductPromotionMail::class);
    }

    public function test_vendor_cannot_send_promotion_email(): void
    {
        Mail::fake();
        $this->seed();

        $vendor = User::where('role', 'vendor')->firstOrFail();
        $product = Product::where('qc_status', 'approved')->firstOrFail();

        $this->actingAs($vendor)
            ->post(route('manage.promotions.email'), [
                'product_id' => $product->id,
                'audience' => 'newsletter',
            ])
            ->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_customer_cannot_send_promotion_email(): void
    {
        Mail::fake();
        $this->seed();

        $customer = User::where('role', 'user')->firstOrFail();
        $product = Product::where('qc_status', 'approved')->firstOrFail();

        $this->actingAs($customer)
            ->post(route('manage.promotions.email'), [
                'product_id' => $product->id,
                'audience' => 'newsletter',
            ])
            ->assertForbidden();

        Mail::assertNothingSent();
    }
}
