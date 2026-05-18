<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VerificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_register_redirects_to_verify_and_otp_verifies_account(): void
    {
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'Shopper',
            'email' => 'shopper@example.test',
            'password' => 'password1',
            'password_confirmation' => 'password1',
        ])->assertRedirect(route('account.verify.show'));

        $user = User::where('email', 'shopper@example.test')->firstOrFail();
        $this->assertFalse($user->is_email_verified);
        $this->assertNotNull($user->otp_code);

        $this->post(route('account.verify.submit'), [
            'otp' => $user->otp_code,
        ])->assertRedirect(route('home'));

        $this->assertTrue($user->fresh()->is_email_verified);
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_vendor_register_verify_and_payment_completes_onboarding(): void
    {
        Mail::fake();

        $this->post(route('vendor.register.store'), [
            'name' => 'Seller',
            'email' => 'seller@example.test',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'shop_name' => 'Bloom Stall',
            'city' => 'Indore',
        ])->assertRedirect(route('vendor.verify.show'));

        $vendor = User::where('email', 'seller@example.test')->firstOrFail();
        $this->assertSame('pending_payment', $vendor->account_status);

        $this->post(route('vendor.verify.submit'), [
            'otp' => $vendor->otp_code,
        ])->assertRedirect(route('vendor.payment.show'));

        $this->post(route('vendor.payment.complete'))
            ->assertRedirect(route('dashboard'));

        $vendor->refresh();
        $this->assertTrue($vendor->reg_fee_paid);
        $this->assertSame('pending_approval', $vendor->account_status);
        $this->assertAuthenticatedAs($vendor);
    }

    public function test_vendor_pending_payment_cannot_open_checkout(): void
    {
        $vendor = User::factory()->vendor()->create([
            'email' => 'pay@example.test',
            'account_status' => 'pending_payment',
            'reg_fee_paid' => false,
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($vendor)
            ->get(route('checkout'))
            ->assertRedirect(route('vendor.payment.show'));
    }
}
