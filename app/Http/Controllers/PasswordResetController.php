<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\SendsOtpMail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    use SendsOtpMail;

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $otp = (string) random_int(100000, 999999);
            $user->update(['otp_code' => $otp, 'otp_expiry' => now()->addMinutes(10)]);

            if (! $this->sendOtpMail($user->email, $otp, 'password_reset')) {
                return back()
                    ->withInput()
                    ->withErrors(['email' => 'Could not send email right now. Check mail settings and try again.']);
            }
        }

        $request->session()->put('password_reset_email', $request->email);

        return redirect()->route('password.verify.show')
            ->with('status', 'If that email is registered, we sent a 6-digit code. Enter it below with your new password.');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password-otp', [
            'email' => $request->session()->get('password_reset_email', old('email')),
        ]);
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');
        abort_unless($email, 404);

        $user = User::where('email', $email)->first();
        if ($user) {
            $otp = (string) random_int(100000, 999999);
            $user->update(['otp_code' => $otp, 'otp_expiry' => now()->addMinutes(10)]);

            if (! $this->sendOtpMail($user->email, $otp, 'password_reset')) {
                return back()->withErrors(['otp' => 'Could not resend email. Try again shortly.']);
            }
        }

        return back()->with('status', 'A new code was sent to your email.');
    }

    public function resetWithOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || $user->otp_code !== $request->otp || ! $user->otp_expiry || $user->otp_expiry->isPast()) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Request a new one.'])->withInput();
        }

        $user->forceFill([
            'password' => $request->password,
            'otp_code' => null,
            'otp_expiry' => null,
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        $request->session()->forget('password_reset_email');

        return redirect()->route('login')->with('status', 'Password updated. You can sign in with your new password.');
    }
}
