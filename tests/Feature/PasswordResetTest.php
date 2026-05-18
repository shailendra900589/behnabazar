<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_request_accepts_valid_email(): void
    {
        $user = User::factory()->create(['email' => 'member@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.test']);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpass11',
            'password_confirmation' => 'newpass11',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('newpass11', $user->fresh()->password));
    }

    public function test_reset_password_page_renders(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]))->assertOk()->assertSee('Choose a new password');
    }
}
