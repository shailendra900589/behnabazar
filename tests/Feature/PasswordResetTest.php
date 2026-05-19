<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_request_accepts_valid_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'member@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.verify.show'))
            ->assertSessionHas('status');

        Mail::assertSent(\App\Mail\OtpMail::class, fn ($mail) => $mail->purpose === 'password_reset');
    }

    public function test_user_can_reset_password_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.test',
            'otp_code' => '123456',
            'otp_expiry' => now()->addMinutes(10),
        ]);

        $this->withSession(['password_reset_email' => $user->email])
            ->post(route('password.verify.submit'), [
                'email' => $user->email,
                'otp' => '123456',
                'password' => 'newpass11',
                'password_confirmation' => 'newpass11',
            ])
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('newpass11', $user->fresh()->password));
        $this->assertNull($user->fresh()->otp_code);
    }

    public function test_reset_password_page_renders(): void
    {
        $this->withSession(['password_reset_email' => 'reset@example.test'])
            ->get(route('password.verify.show'))
            ->assertOk()
            ->assertSee('Enter reset code');
    }
}
