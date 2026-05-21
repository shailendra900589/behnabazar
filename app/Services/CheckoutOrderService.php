<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserAddress;
use App\Support\CartStockValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutOrderService
{
    public function __construct(
        private readonly OrderNotificationService $notifications,
    ) {}

    /** @return array{orders: Collection<int, Order>, coins_earned: int, final_total: float} */
    public function place(Request $request, string $paymentMethod): array
    {
        $data = $this->validatedAddress($request);
        $items = $this->items();

        if ($items->isEmpty()) {
            throw new \RuntimeException('Your cart is empty.');
        }

        if ($error = CartStockValidator::validateCart($items)) {
            throw new \RuntimeException($error);
        }

        $subtotal = $this->subtotal($items);
        $coupon = $this->coupon($request->input('coupon_code'));
        $discount = $this->discount($coupon, $subtotal);
        $coinLimit = (float) Setting::value('coin_redeem_limit', 5);
        $coinDiscount = $request->boolean('use_coins')
            ? min(Auth::user()->coins, ($subtotal * $coinLimit / 100), max(0, $subtotal - $discount))
            : 0;
        $finalTotal = max(0, $subtotal - $discount - $coinDiscount);
        $coinsEarned = (int) floor($finalTotal / (float) Setting::value('coin_earn_rate', 10));
        $createdOrders = collect();

        DB::transaction(function () use ($items, $data, $coupon, $subtotal, $discount, $coinDiscount, $coinsEarned, $paymentMethod, &$createdOrders) {
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

                $product = $item->product->loadMissing('sourceProduct');
                $sourceAmount = null;
                $listingAmount = null;
                $fulfillmentVendorId = $product->fulfillmentVendorId();

                if ($product->isResellListing()) {
                    $unitSource = (float) ($product->source_base_price ?? $product->sourceProduct?->price ?? 0);
                    $sourceAmount = round($unitSource * $item->quantity, 2);
                    $listingAmount = max(0, round($lineTotal - $sourceAmount, 2));
                }

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'fulfillment_vendor_id' => $fulfillmentVendorId,
                    'listing_vendor_id' => $product->isResellListing() ? $product->vendor_id : null,
                    'source_vendor_amount' => $sourceAmount,
                    'listing_vendor_amount' => $listingAmount,
                    'product_name' => $item->product->title.($item->variant ? ' ('.$item->variant->displayLabel().')' : ''),
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                    'customer_name' => Auth::user()->name,
                    'address' => $data['address'],
                    'phone' => $data['phone'],
                    'payment_method' => $paymentMethod,
                    'coupon_code' => $coupon?->code,
                    'discount_amount' => $lineDiscount,
                    'coin_discount' => $lineCoin,
                    'coins_earned' => $idx === 0 ? $coinsEarned : 0,
                    'status' => $paymentMethod === 'COD' ? 'pending' : 'processing',
                ]);

                $createdOrders->push($order);
            }

            Auth::user()->update(['coins' => max(0, Auth::user()->coins - $coinDiscount + $coinsEarned)]);
            $items->each->delete();
        });

        $this->notifications->orderPlaced(Auth::user(), $createdOrders, $finalTotal, $paymentMethod);

        return [
            'orders' => $createdOrders,
            'coins_earned' => $coinsEarned,
            'final_total' => $finalTotal,
        ];
    }

    public function checkoutTotal(Request $request): float
    {
        $items = $this->items();
        $subtotal = $this->subtotal($items);
        $coupon = $this->coupon($request->input('coupon_code'));
        $discount = $this->discount($coupon, $subtotal);
        $coinLimit = (float) Setting::value('coin_redeem_limit', 5);
        $coinDiscount = $request->boolean('use_coins')
            ? min(Auth::user()->coins, ($subtotal * $coinLimit / 100), max(0, $subtotal - $discount))
            : 0;

        return max(0, $subtotal - $discount - $coinDiscount);
    }

    /** @return Collection<int, CartItem> */
    public function items(): Collection
    {
        return CartItem::with(['product', 'variant'])->where('user_id', Auth::id())->latest()->get();
    }

    /** @return array{address: string, phone: string} */
    private function validatedAddress(Request $request): array
    {
        $request->validate([
            'address_id' => ['nullable', 'exists:user_addresses,id'],
            'address' => ['required_without:address_id', 'max:1000'],
            'phone' => ['required_without:address_id', 'max:30'],
            'pincode' => ['nullable', 'digits:6'],
            'save_address' => ['nullable'],
        ]);

        if ($request->filled('address_id')) {
            $addr = UserAddress::where('id', $request->address_id)->where('user_id', Auth::id())->firstOrFail();

            return [
                'address' => $addr->address.($addr->city ? ', '.$addr->city : '').($addr->pincode ? ' - '.$addr->pincode : ''),
                'phone' => $addr->phone,
            ];
        }

        $pincode = preg_replace('/\D/', '', (string) $request->input('pincode', ''));
        if ($pincode !== '' && ! \App\Support\MarketplaceSettings::isPincodeServiceable($pincode)) {
            throw new \RuntimeException('Sorry, we do not deliver to this PIN code yet.');
        }

        if ($request->boolean('save_address')) {
            UserAddress::create([
                'user_id' => Auth::id(),
                'name' => Auth::user()->name,
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'city' => $request->input('city'),
                'pincode' => $pincode ?: null,
                'is_default' => ! Auth::user()->addresses()->exists(),
            ]);
        }

        return [
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
        ];
    }

    private function subtotal(Collection $items): float
    {
        return $items->sum(fn ($item) => $item->quantity * ($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price));
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
}
