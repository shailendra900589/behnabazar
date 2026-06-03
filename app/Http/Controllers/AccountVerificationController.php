<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\MailConfigurator;
use App\Support\MergeGuestCart;
use App\Support\SendsOtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountVerificationController extends Controller
{
    use SendsOtpMail;

    public function show(Request $request): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'user' && ! Auth::user()->is_email_verified) {
            $request->session()->put('customer_register_id', Auth::id());

            return view('auth.verify-account');
        }

        if ($request->session()->has('customer_register_id')) {
            return view('auth.verify-account');
        }

        return redirect()->route('register');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        $id = $request->session()->get('customer_register_id');
        if (! $id && Auth::check() && Auth::user()->role === 'user') {
            $id = Auth::id();
        }

        abort_unless($id, 404);

        $user = User::where('id', $id)->where('role', 'user')->firstOrFail();

        if ($user->otp_code !== $request->otp || ! $user->otp_expiry || $user->otp_expiry->isPast()) {
            return back()->withErrors(['otp' => 'Invalid or expired code.']);
        }

        $user->update([
            'is_email_verified' => true,
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expiry' => null,
        ]);

        $guestSessionId = $request->session()->getId();
        MergeGuestCart::intoUser($guestSessionId, $user);

        Auth::login($user);
        $request->session()->forget('customer_register_id');
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Welcome! Your email is verified.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $id = $request->session()->get('customer_register_id');
        if (! $id && Auth::check() && Auth::user()->role === 'user' && ! Auth::user()->is_email_verified) {
            $id = Auth::id();
            $request->session()->put('customer_register_id', $id);
        }

        abort_unless($id, 404);

        $user = User::where('id', $id)->where('role', 'user')->firstOrFail();
        $otp = (string) random_int(100000, 999999);
        $user->update(['otp_code' => $otp, 'otp_expiry' => now()->addMinutes(10)]);
        if (! $this->sendOtpMail($user->email, $otp, 'customer')) {
            return back()
                ->with('warning', MailConfigurator::userFacingMailError())
                ->withErrors(['otp' => 'Email could not be sent. Fix mail settings on the server, then tap Resend again.']);
        }

        return back()->with('status', 'A new code was sent to your email.');
    }
}
