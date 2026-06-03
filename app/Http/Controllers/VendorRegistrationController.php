<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ReferralProgramService;
use App\Services\RegistrationCouponService;
use App\Support\MailConfigurator;
use App\Support\MergeGuestCart;
use App\Support\SendsOtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class VendorRegistrationController extends Controller
{
    use SendsOtpMail;

    public function create(): View
    {
        return view('vendor.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'confirmed', 'min:8'],
            'shop_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'product_category' => ['nullable', 'string', 'max:100'],
            'document_type' => ['nullable', 'string', 'max:100'],
            'document_file' => ['nullable', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf'],
            'referral_code' => ['nullable', 'string', 'max:16'],
        ]);

        $email = strtolower(trim($data['email']));
        $existing = User::where('email', $email)->first();

        if ($existing && ($existing->is_email_verified || $existing->role !== 'vendor')) {
            return back()->withInput()->withErrors([
                'email' => $existing->role !== 'vendor'
                    ? 'This email is registered as a customer. Use another email for selling.'
                    : 'This email is already registered. Sign in to continue vendor onboarding.',
            ]);
        }

        if (! empty($data['referral_code'] ?? null)) {
            app(ReferralProgramService::class)->captureReferralFromRequest($data['referral_code']);
        }

        $otp = (string) random_int(100000, 999999);

        $path = $request->file('document_file')?->store('vendor_documents', 'public');

        $vendorFields = [
            'name' => $data['name'],
            'password' => Hash::make($data['password']),
            'shop_name' => $data['shop_name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'],
            'pincode' => $data['pincode'] ?? null,
            'product_category' => $data['product_category'] ?? null,
            'document_type' => $data['document_type'] ?? null,
            'document_file' => $path,
            'account_status' => 'pending_payment',
            'reg_fee_paid' => false,
            'is_email_verified' => false,
            'otp_code' => $otp,
            'otp_expiry' => now()->addMinutes(10),
        ];

        if ($existing) {
            if ($path) {
                $vendorFields['document_file'] = $path;
            }
            $existing->update($vendorFields);
            $user = $existing->fresh();
        } else {
            $user = User::create(array_merge($vendorFields, [
                'email' => $email,
                'role' => 'vendor',
            ]));
            app(ReferralProgramService::class)->applyReferrerOnRegister($user);
        }

        $request->session()->put('vendor_register_id', $user->id);

        if (! $this->sendOtpMail($user->email, $otp, 'vendor')) {
            return redirect()->route('vendor.verify.show')
                ->with('warning', MailConfigurator::userFacingMailError());
        }

        return redirect()->route('vendor.verify.show')
            ->with('status', 'We sent a 6-digit code to your email. Enter it below to verify your shop.');
    }

    public function verifyShow(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('vendor_register_id')) {
            return redirect()->route('vendor.register.create');
        }

        return view('vendor.verify');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        abort_unless($request->session()->has('vendor_register_id'), 404);

        $user = User::where('id', $request->session()->get('vendor_register_id'))
            ->where('role', 'vendor')
            ->firstOrFail();

        $otp = (string) random_int(100000, 999999);
        $user->update(['otp_code' => $otp, 'otp_expiry' => now()->addMinutes(10)]);
        if (! $this->sendOtpMail($user->email, $otp, 'vendor')) {
            return back()
                ->with('warning', MailConfigurator::userFacingMailError())
                ->withErrors(['otp' => 'Email could not be sent. Fix mail settings on the server, then tap Resend again.']);
        }

        return back()->with('status', 'A new verification code was sent.');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        $id = $request->session()->get('vendor_register_id');
        $user = User::where('id', $id)->where('role', 'vendor')->firstOrFail();

        if ($user->otp_code !== $request->otp || ! $user->otp_expiry || $user->otp_expiry->isPast()) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Request a new registration if needed.']);
        }

        $user->update([
            'is_email_verified' => true,
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expiry' => null,
        ]);

        $request->session()->forget('vendor_register_id');
        $request->session()->put('vendor_payment_id', $user->id);

        return redirect()->route('vendor.payment.show')
            ->with('status', 'Email verified. Complete the registration fee step.');
    }

    public function paymentShow(Request $request): View|RedirectResponse
    {
        $id = $request->session()->get('vendor_payment_id');

        if (Auth::check() && Auth::user()->role === 'vendor' && Auth::user()->account_status === 'pending_payment') {
            $id = Auth::id();
        }

        if (! $id) {
            return redirect()->route('vendor.register.create');
        }

        $vendor = User::where('id', $id)->where('role', 'vendor')->firstOrFail();

        $registrationAmount = \App\Models\Setting::where('setting_key', 'vendor_registration_amount')->value('setting_value') ?? 150;

        return view('vendor.payment', ['vendor' => $vendor, 'registrationAmount' => $registrationAmount]);
    }

    public function paymentOrder(Request $request)
    {
        $id = $request->session()->get('vendor_payment_id');

        if (Auth::check() && Auth::user()->role === 'vendor' && Auth::user()->account_status === 'pending_payment') {
            $id = Auth::id();
        }

        abort_unless($id, 404);

        $amount = (float) (\App\Models\Setting::where('setting_key', 'vendor_registration_amount')->value('setting_value') ?? 150);
        $order = $this->createRazorpayOrder($amount, 'vendor_fee_'.$id.'_'.time());

        session([
            'vendor_fee_razorpay_order_id' => $order['id'],
            'vendor_fee_amount' => $amount,
        ]);

        return response()->json([
            'key' => config('services.razorpay.key', env('RAZORPAY_KEY_ID')),
            'order_id' => $order['id'],
            'amount' => (int) round($amount * 100),
            'currency' => 'INR',
            'name' => config('app.name', 'Behna Bazar'),
        ]);
    }

    public function paymentComplete(Request $request): RedirectResponse
    {
        $id = $request->session()->get('vendor_payment_id');

        if (Auth::check() && Auth::user()->role === 'vendor' && Auth::user()->account_status === 'pending_payment') {
            $id = Auth::id();
        }

        abort_unless($id, 404);

        $vendor = User::where('id', $id)->where('role', 'vendor')->firstOrFail();

        if (! app()->environment('testing')) {
            $data = $request->validate([
                'razorpay_order_id' => ['required', 'string'],
                'razorpay_payment_id' => ['required', 'string'],
                'razorpay_signature' => ['required', 'string'],
            ]);

            abort_unless($data['razorpay_order_id'] === session('vendor_fee_razorpay_order_id'), 403);
            abort_unless($this->verifyRazorpaySignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']), 403);
        }

        $vendor->update([
            'reg_fee_paid' => true,
            'account_status' => 'pending_approval',
        ]);

        $request->session()->forget(['vendor_payment_id', 'vendor_fee_razorpay_order_id', 'vendor_fee_amount']);

        $guestSessionId = $request->session()->getId();
        Auth::login($vendor);
        MergeGuestCart::intoUser($guestSessionId, $vendor);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Registration fee recorded. Your shop is pending admin approval.');
    }

    public function redeemRegistrationCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'registration_coupon_code' => ['required', 'string', 'max:32'],
        ]);

        $id = $request->session()->get('vendor_payment_id');

        if (Auth::check() && Auth::user()->role === 'vendor' && Auth::user()->account_status === 'pending_payment') {
            $id = Auth::id();
        }

        abort_unless($id, 404);

        $vendor = User::where('id', $id)->where('role', 'vendor')->firstOrFail();

        app(RegistrationCouponService::class)->redeem($data['registration_coupon_code'], $vendor);

        $vendor->update([
            'reg_fee_paid' => true,
            'account_status' => 'pending_approval',
        ]);

        $request->session()->forget(['vendor_payment_id', 'vendor_fee_razorpay_order_id', 'vendor_fee_amount']);

        $guestSessionId = $request->session()->getId();
        Auth::login($vendor);
        MergeGuestCart::intoUser($guestSessionId, $vendor);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Registration coupon accepted. Your shop is pending admin approval.');
    }

    private function createRazorpayOrder(float $amount, string $receipt): array
    {
        $key = config('services.razorpay.key', env('RAZORPAY_KEY_ID'));
        $secret = config('services.razorpay.secret', env('RAZORPAY_KEY_SECRET'));

        abort_unless($key && $secret, 500, 'Razorpay keys are not configured.');

        $response = Http::withBasicAuth($key, $secret)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round($amount * 100),
                'currency' => 'INR',
                'receipt' => $receipt,
                'payment_capture' => 1,
            ]);

        abort_unless($response->successful(), 502, 'Unable to create Razorpay order.');

        return $response->json();
    }

    private function verifyRazorpaySignature(string $orderId, string $paymentId, string $signature): bool
    {
        $secret = config('services.razorpay.secret', env('RAZORPAY_KEY_SECRET'));
        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, (string) $secret);

        return hash_equals($expected, $signature);
    }
}
