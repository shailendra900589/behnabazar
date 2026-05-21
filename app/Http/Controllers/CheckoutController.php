<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Setting;
use App\Services\CheckoutOrderService;
use App\Support\MarketplaceSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutOrderService $checkout,
    ) {}

    public function createPaymentOrder(Request $request)
    {
        $total = $this->checkout->checkoutTotal($request);
        abort_if($total <= 0, 422, 'Invalid checkout amount.');

        $order = $this->createRazorpayOrder($total, 'order_'.Auth::id().'_'.time());

        session([
            'checkout_razorpay_order_id' => $order['id'],
            'checkout_amount' => $total,
        ]);

        return response()->json([
            'key' => config('services.razorpay.key', env('RAZORPAY_KEY_ID')),
            'order_id' => $order['id'],
            'amount' => (int) round($total * 100),
            'currency' => 'INR',
            'name' => config('app.name', 'Behna Bazar'),
        ]);
    }

    public function show(Request $request): View
    {
        $items = $this->checkout->items();
        $subtotal = $items->sum(fn ($item) => $item->quantity * ($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price));
        $coupon = $this->coupon($request->input('coupon_code'));
        $discount = $this->discount($coupon, $subtotal);
        $coinLimit = (float) Setting::value('coin_redeem_limit', 5);
        $coinDiscount = $request->boolean('use_coins') ? min(Auth::user()->coins, ($subtotal * $coinLimit / 100), max(0, $subtotal - $discount)) : 0;
        $addresses = Auth::user()->addresses()->orderByDesc('is_default')->latest()->get();
        $availableCoupons = Coupon::where('status', true)
            ->where('min_cart_value', '<=', $subtotal)
            ->orderByDesc('discount_value')
            ->get();

        $total = max(0, $subtotal - $discount - $coinDiscount);

        return view('store.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'coinDiscount' => $coinDiscount,
            'addresses' => $addresses,
            'availableCoupons' => $availableCoupons,
            'total' => $total,
            'codEnabled' => MarketplaceSettings::codEnabled(),
            'freeShippingThreshold' => MarketplaceSettings::freeShippingThreshold(),
        ]);
    }

    public function place(Request $request): RedirectResponse
    {
        $codEnabled = MarketplaceSettings::codEnabled();
        $methods = $codEnabled ? 'Prepaid,COD' : 'Prepaid';

        $data = $request->validate([
            'address_id' => ['nullable', 'exists:user_addresses,id'],
            'address' => ['required_without:address_id', 'max:1000'],
            'phone' => ['required_without:address_id', 'max:30'],
            'pincode' => ['nullable', 'digits:6'],
            'city' => ['nullable', 'max:100'],
            'save_address' => ['nullable'],
            'payment_method' => ['required', 'in:'.$methods],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'use_coins' => ['nullable'],
            'razorpay_order_id' => ['required_if:payment_method,Prepaid', 'nullable', 'string'],
            'razorpay_payment_id' => ['required_if:payment_method,Prepaid', 'nullable', 'string'],
            'razorpay_signature' => ['required_if:payment_method,Prepaid', 'nullable', 'string'],
        ]);

        if ($data['payment_method'] === 'Prepaid') {
            abort_unless($data['razorpay_order_id'] === session('checkout_razorpay_order_id'), 403);
            abort_unless($this->verifyRazorpaySignature(
                $data['razorpay_order_id'],
                $data['razorpay_payment_id'],
                $data['razorpay_signature']
            ), 403);
        }

        try {
            $result = $this->checkout->place($request, $data['payment_method']);
        } catch (\RuntimeException $e) {
            return redirect()->route('checkout', $request->only('coupon_code', 'use_coins'))
                ->withErrors(['checkout' => $e->getMessage()]);
        }

        $request->session()->forget(['checkout_razorpay_order_id', 'checkout_amount']);

        $msg = $data['payment_method'] === 'COD'
            ? "Order placed with Cash on Delivery. You earned {$result['coins_earned']} coins."
            : "Payment verified. Order placed. You earned {$result['coins_earned']} coins.";

        return redirect()->route('orders')->with('status', $msg);
    }

    private function coupon(?string $code): ?Coupon
    {
        return $code ? Coupon::where('code', strtoupper($code))->where('status', true)->first() : null;
    }

    private function discount(?Coupon $coupon, float $subtotal): float
    {
        if (! $coupon || $subtotal < $coupon->min_cart_value) {
            return 0;
        }

        return $coupon->discount_type === 'flat'
            ? min($coupon->discount_value, $subtotal)
            : min($subtotal, $subtotal * $coupon->discount_value / 100);
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
