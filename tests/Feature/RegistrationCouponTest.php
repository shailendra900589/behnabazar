<?php

namespace Tests\Feature;

use App\Models\RegistrationCoupon;
use App\Models\RegistrationCouponHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationCouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_registration_coupon_with_history(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('manage.registration-coupons.save'), [
                'code' => 'SELLER100',
                'issued_to_name' => 'Priya Verma',
                'issued_to_email' => 'priya@example.test',
                'notes' => 'Partner onboarding',
            ])
            ->assertRedirect(route('dashboard', ['section' => 'marketing']));

        $coupon = RegistrationCoupon::where('code', 'SELLER100')->firstOrFail();
        $this->assertSame('Priya Verma', $coupon->issued_to_name);
        $this->assertSame($admin->id, $coupon->created_by);

        $this->assertDatabaseHas('registration_coupon_histories', [
            'registration_coupon_id' => $coupon->id,
            'action' => 'created',
            'subject_name' => 'Priya Verma',
        ]);
    }

    public function test_vendor_can_complete_registration_with_one_time_coupon(): void
    {
        Mail::fake();
        $this->seed();

        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)
            ->post(route('manage.registration-coupons.save'), [
                'code' => 'FREESELL01',
                'issued_to_name' => 'Seller One',
            ]);

        auth()->logout();
        $this->flushSession();

        $this->post(route('vendor.register.store'), [
            'name' => 'Seller One',
            'email' => 'seller-coupon@example.test',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'shop_name' => 'Coupon Shop',
            'city' => 'Indore',
        ])->assertRedirect(route('vendor.verify.show'));

        $vendor = User::where('email', 'seller-coupon@example.test')->firstOrFail();

        $this->post(route('vendor.verify.submit'), [
            'otp' => $vendor->otp_code,
        ])->assertRedirect(route('vendor.payment.show'));

        $this->post(route('vendor.payment.coupon'), [
            'registration_coupon_code' => 'FREESELL01',
        ])->assertRedirect(route('dashboard'));

        $vendor->refresh();
        $this->assertTrue($vendor->reg_fee_paid);
        $this->assertSame('pending_approval', $vendor->account_status);

        $coupon = RegistrationCoupon::where('code', 'FREESELL01')->firstOrFail();
        $this->assertSame($vendor->id, $coupon->used_by_user_id);
        $this->assertNotNull($coupon->used_at);

        $this->assertDatabaseHas('registration_coupon_histories', [
            'registration_coupon_id' => $coupon->id,
            'action' => 'used',
            'subject_email' => 'seller-coupon@example.test',
        ]);
    }

    public function test_registration_coupon_cannot_be_reused(): void
    {
        Mail::fake();
        $this->seed();

        RegistrationCoupon::create([
            'code' => 'ONCEONLY',
            'issued_to_name' => 'First Seller',
            'used_by_user_id' => User::factory()->vendor()->create()->id,
            'used_at' => now(),
        ]);

        $this->post(route('vendor.register.store'), [
            'name' => 'Second Seller',
            'email' => 'second@example.test',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'shop_name' => 'Another Shop',
            'city' => 'Indore',
        ]);

        $vendor = User::where('email', 'second@example.test')->firstOrFail();
        $this->post(route('vendor.verify.submit'), ['otp' => $vendor->otp_code]);

        $this->post(route('vendor.payment.coupon'), [
            'registration_coupon_code' => 'ONCEONLY',
        ])->assertSessionHasErrors('registration_coupon_code');
    }
}
