<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function createPaymentOrder(Request $request)
    {
        $total = $this->checkoutTotal($request);
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
        $items = $this->items();
        $subtotal = $items->sum(fn ($item) => $item->quantity * ($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price));
        $coupon = $this->coupon($request->input('coupon_code'));
        $discount = $this->discount($coupon, $subtotal);
        $coinLimit = (float) Setting::value('coin_redeem_limit', 5);
        $coinDiscount = $request->boolean('use_coins') ? min(Auth::user()->coins, ($subtotal * $coinLimit / 100), max(0, $subtotal - $discount)) : 0;
        $addresses = Auth::user()->addresses()->latest()->get();
        $availableCoupons = Coupon::where('status', true)
            ->where('min_cart_value', '<=', $subtotal)
            ->orderByDesc('discount_value')
            ->get();

        return view('store.checkout', compact('items', 'subtotal', 'coupon', 'discount', 'coinDiscount', 'addresses', 'availableCoupons'));
    }

    public function place(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address_id' => ['nullable', 'exists:user_addresses,id'],
            'address' => ['required_without:address_id', 'max:1000'],
            'phone' => ['required_without:address_id', 'max:30'],
            'payment_method' => ['required', 'in:Prepaid'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'use_coins' => ['nullable'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        abort_unless($data['razorpay_order_id'] === session('checkout_razorpay_order_id'), 403);
        abort_unless($this->verifyRazorpaySignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']), 403);

        if (!empty($data['address_id'])) {
            $addr = \App\Models\UserAddress::where('id', $data['address_id'])->where('user_id', Auth::id())->firstOrFail();
            $data['address'] = $addr->address . ($addr->city ? ', ' . $addr->city : '') . ($addr->pincode ? ' - ' . $addr->pincode : '');
            $data['phone'] = $addr->phone;
        } else {
            // Save new address
            \App\Models\UserAddress::create([
                'user_id' => Auth::id(),
                'name' => Auth::user()->name,
                'phone' => $data['phone'],
                'address' => $data['address'],
            ]);
        }

        $items = $this->items();
        if ($items->isEmpty()) {
            return redirect()->route('cart')->withErrors(['cart' => 'Your cart is empty.']);
        }

        $subtotal = $items->sum(fn ($item) => $item->quantity * ($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price));
        $coupon = $this->coupon($data['coupon_code'] ?? null);
        $discount = $this->discount($coupon, $subtotal);
        $coinLimit = (float) Setting::value('coin_redeem_limit', 5);
        $coinDiscount = $request->boolean('use_coins') ? min(Auth::user()->coins, ($subtotal * $coinLimit / 100), max(0, $subtotal - $discount)) : 0;
        $finalTotal = max(0, $subtotal - $discount - $coinDiscount);
        $coinsEarned = (int) floor($finalTotal / (float) Setting::value('coin_earn_rate', 10));

        DB::transaction(function () use ($items, $data, $coupon, $subtotal, $discount, $coinDiscount, $coinsEarned) {
            $list = $items->values();
            $allocatedDisc = 0.0;
            $allocatedCoin = 0.0;

            foreach ($list as $idx => $item) {
                $unitPrice = $item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price;
                $lineTotal = $item->quantity * $unitPrice;
                $isLast = $idx === $list->count() - 1;
                $lineDiscount = $isLast
                    ? max(0, $discount - $allocatedDisc)
                    : round($discount * ($subtotal > 0 ? $lineTotal / $subtotal : 0), 2);
                $lineCoin = $isLast
                    ? max(0, $coinDiscount - $allocatedCoin)
                    : round($coinDiscount * ($subtotal > 0 ? $lineTotal / $subtotal : 0), 2);
                $allocatedDisc += $lineDiscount;
                $allocatedCoin += $lineCoin;

                Order::create([
                    'user_id' => Auth::id(),
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'product_name' => $item->product->title . ($item->variant ? ' (' . trim($item->variant->color . ' ' . $item->variant->size) . ')' : ''),
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'customer_name' => Auth::user()->name,
                    'address' => $data['address'],
                    'phone' => $data['phone'],
                    'payment_method' => $data['payment_method'],
                    'coupon_code' => $coupon?->code,
                    'discount_amount' => $lineDiscount,
                    'coin_discount' => $lineCoin,
                    'coins_earned' => $idx === 0 ? $coinsEarned : 0,
                ]);
            }

            Auth::user()->update(['coins' => max(0, Auth::user()->coins - $coinDiscount + $coinsEarned)]);
            $items->each->delete();
        });

        $request->session()->forget(['checkout_razorpay_order_id', 'checkout_amount']);

        return redirect()->route('orders')->with('status', "Payment verified. Order placed. You earned {$coinsEarned} coins.");
    }

    private function checkoutTotal(Request $request): float
    {
        $items = $this->items();
        $subtotal = $items->sum(fn ($item) => $item->quantity * ($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price));
        $coupon = $this->coupon($request->input('coupon_code'));
        $discount = $this->discount($coupon, $subtotal);
        $coinLimit = (float) Setting::value('coin_redeem_limit', 5);
        $coinDiscount = $request->boolean('use_coins') ? min(Auth::user()->coins, ($subtotal * $coinLimit / 100), max(0, $subtotal - $discount)) : 0;

        return max(0, $subtotal - $discount - $coinDiscount);
    }

    private function items()
    {
        return CartItem::with(['product', 'variant'])->where('user_id', Auth::id())->latest()->get();
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

        return $coupon->discount_type === 'flat' ? min($coupon->discount_value, $subtotal) : min($subtotal, $subtotal * $coupon->discount_value / 100);
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
