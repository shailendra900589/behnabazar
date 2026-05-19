<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\MergeGuestCart;
use App\Support\SendsOtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    use SendsOtpMail;

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid login details.'])->onlyInput('email');
        }

        $guestSessionId = $request->session()->getId();
        /** @var User $user */
        $user = Auth::user();
        MergeGuestCart::intoUser($guestSessionId, $user);

        if ($user->role === 'user' && ! $user->is_email_verified) {
            $request->session()->put('customer_register_id', $user->id);
            $request->session()->regenerate();

            return redirect()->route('account.verify.show')
                ->with('warning', 'Verify your email with the code we send you to unlock checkout and orders.');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $otp = (string) random_int(100000, 999999);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'city' => $data['city'] ?? null,
            'account_status' => 'active',
            'is_email_verified' => false,
            'otp_code' => $otp,
            'otp_expiry' => now()->addMinutes(10),
        ]);

        if (! $this->sendOtpMail($user->email, $otp, 'customer')) {
            return back()->withInput()->withErrors(['email' => 'Could not send verification email. Check mail settings and try again.']);
        }

        $request->session()->put('customer_register_id', $user->id);

        return redirect()->route('account.verify.show')
            ->with('status', 'We emailed you a 6-digit code. Enter it below to activate your customer account.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Logged out successfully.');
    }
}
