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

    public function test_admin_can_create_registration_coupon_without_user_details(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('manage.registration-coupons.save'), [
                'code' => 'SELLER100',
            ])
            ->assertRedirect(route('dashboard', ['section' => 'marketing']));

        $coupon = RegistrationCoupon::where('code', 'SELLER100')->firstOrFail();
        $this->assertNull($coupon->issued_to_name);
        $this->assertNull($coupon->issued_to_email);
        $this->assertSame($admin->id, $coupon->created_by);

        $this->assertDatabaseHas('registration_coupon_histories', [
            'registration_coupon_id' => $coupon->id,
            'action' => 'created',
        ]);
    }

    public function test_vendor_registration_details_are_saved_when_coupon_is_used(): void
    {
        Mail::fake();
        $this->seed();

        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin)
            ->post(route('manage.registration-coupons.save'), [
                'code' => 'FREESELL01',
            ]);

        auth()->logout();
        $this->flushSession();

        $this->post(route('vendor.register.store'), [
            'name' => 'Seller One',
            'email' => 'seller-coupon@example.test',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'shop_name' => 'Coupon Shop',
            'phone' => '9876543210',
            'city' => 'Indore',
        ])->assertRedirect(route('vendor.verify.show'));

        $vendor = User::where('email', 'seller-coupon@example.test')->firstOrFail();

        $this->post(route('vendor.verify.submit'), [
            'otp' => $vendor->otp_code,
        ])->assertRedirect(route('vendor.payment.show'));

        $this->post(route('vendor.payment.coupon'), [
            'registration_coupon_code' => 'FREESELL01',
        ])->assertRedirect(route('dashboard'));

        $coupon = RegistrationCoupon::where('code', 'FREESELL01')->firstOrFail();
        $this->assertSame('Seller One', $coupon->issued_to_name);
        $this->assertSame('seller-coupon@example.test', $coupon->issued_to_email);
        $this->assertSame('9876543210', $coupon->issued_to_phone);
        $this->assertStringContainsString('Coupon Shop', $coupon->notes);
        $this->assertStringContainsString('Indore', $coupon->notes);

        $this->assertDatabaseHas('registration_coupon_histories', [
            'registration_coupon_id' => $coupon->id,
            'action' => 'used',
            'subject_name' => 'Seller One',
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
