<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function adClick(Ad $ad): RedirectResponse
    {
        abort_unless($ad->isActiveNow(), 404);

        $ad->increment('clicks');

        $target = $ad->link_url ?: ($ad->product ? route('product.show', $ad->product) : route('home'));

        return redirect()->away($target);
    }

    public function home(Request $request): View
    {
        $query = Product::query()->with(['category', 'vendor'])->where('qc_status', 'approved')->withCount('orders');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('cat')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->cat));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', max(0, (float) $request->min_price));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', max(0, (float) $request->max_price));
        }

        $sort = $request->input('sort', 'new');
        match ($sort) {
            'price_low' => $query->orderBy('price')->orderByDesc('id'),
            'price_high' => $query->orderByDesc('price')->orderByDesc('id'),
            'popular' => $query->orderByDesc('orders_count')->orderByDesc('id'),
            default => $query->latest(),
        };

        [$priceCatalogMin, $priceCatalogMax] = Cache::remember('storefront.price_bounds', 600, function () {
            $priceBounds = Product::query()
                ->where('qc_status', 'approved')
                ->selectRaw('COALESCE(MIN(price), 0) as min_p, COALESCE(MAX(price), 0) as max_p')
                ->first();

            return [
                $priceBounds ? (float) $priceBounds->min_p : 0.0,
                $priceBounds ? (float) $priceBounds->max_p : 0.0,
            ];
        });

        $recentIds = session()->get('recently_viewed', []);
        $recentlyViewed = !empty($recentIds) 
            ? Product::whereIn('id', $recentIds)->where('qc_status', 'approved')->get()->sortBy(fn($p) => array_search($p->id, $recentIds))
            : collect();

        return view('store.home', [
            'products' => $query->paginate(12)->withQueryString(),
            'newArrivals' => Product::where('qc_status', 'approved')->latest()->take(4)->get(),
            'hotProducts' => Product::withCount('orders')->where('qc_status', 'approved')->orderByDesc('orders_count')->take(4)->get(),
            'flashDeal' => Cache::remember('storefront.flash_deal', 300, fn () => Product::query()
                ->where('qc_status', 'approved')
                ->latest('id')
                ->first()),
            'recentlyViewed' => $recentlyViewed,
            'categories' => Category::forNavigation(),
            'banners' => Banner::where('status', true)->orderBy('sort_order')->get(),
            'ads' => Ad::with(['product', 'vendor'])
                ->where('status', true)
                ->where(function ($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                })
                ->orderBy('sort_order')
                ->latest()
                ->get()
                ->groupBy('location'),
            'sort' => $sort,
            'priceCatalogMin' => $priceCatalogMin,
            'priceCatalogMax' => $priceCatalogMax,
        ]);
    }

    public function liveSearch(Request $request)
    {
        if (!$request->filled('q')) return response()->json([]);

        $products = Product::where('qc_status', 'approved')
            ->where('title', 'like', '%'.$request->q.'%')
            ->select('id', 'title', 'price', 'slug')
            ->take(5)
            ->get();
            
        $products->map(function($product) {
            $product->url = route('product.show', $product);
            $product->formatted_price = '₹' . number_format($product->price, 2);
            $product->image = $product->imageUrl();
            return $product;
        });

        return response()->json($products);
    }

    public function product(Product $product): View
    {
        abort_unless($product->qc_status === 'approved' || Auth::user()?->isRole(['admin', 'vendor', 'qc_manager', 'qc_staff']), 404);

        // Increment view count
        $product->increment('view_count');

        // Track recently viewed
        $recent = session()->get('recently_viewed', []);
        if (!in_array($product->id, $recent)) {
            array_unshift($recent, $product->id);
            session()->put('recently_viewed', array_slice($recent, 0, 8)); // Keep last 8
        }

        return view('store.product', [
            'product' => $product->load(['category', 'vendor', 'reviews.user']),
            'related' => Product::query()
                ->where('qc_status', 'approved')
                ->where('category_id', $product->category_id)
                ->whereKeyNot($product->id)
                ->withCount('orders')
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }

    public function profile(): View
    {
        return view('account.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:100'],
            'phone' => ['nullable', 'max:30'],
            'address' => ['nullable', 'max:1000'],
            'city' => ['nullable', 'max:100'],
            'pincode' => ['nullable', 'max:20'],
        ]);

        $request->user()->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function orders(): View
    {
        return view('account.orders', [
            'orders' => Auth::user()->orders()->with('product')->latest()->get(),
        ]);
    }

    public function track(Order $order): View
    {
        abort_unless($order->user_id === Auth::id() || Auth::user()?->isRole(['admin', 'vendor']), 403);

        return view('account.track', ['order' => $order->load('product')]);
    }

    public function cancelOrder(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->withErrors(['error' => 'You cannot cancel an order that has already been shipped.']);
        }

        $order->update(['status' => 'cancelled', 'tracking_msg' => 'Order cancelled by customer.']);

        // Refund coins if they were used
        if ($order->coin_discount > 0) {
            Auth::user()->increment('coins', $order->coin_discount);
        }

        // Deduct earned coins
        if ($order->coins_earned > 0) {
            Auth::user()->decrement('coins', $order->coins_earned);
        }

        return back()->with('status', 'Your order has been successfully cancelled.');
    }

    public function returnOrder(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if ($order->status !== 'delivered' || $order->return_status !== null) {
            return back()->withErrors(['error' => 'This order is not eligible for a return.']);
        }

        $request->validate([
            'return_reason' => 'required|string|max:1000'
        ]);

        $order->update([
            'return_status' => 'requested',
            'return_reason' => $request->return_reason
        ]);

        return back()->with('status', 'Your return request has been submitted and is pending approval.');
    }

    public function shops(Request $request): View
    {
        $query = \App\Models\User::where('role', 'vendor')->where('account_status', 'active');

        if ($request->filled('search')) {
            $query->where('shop_name', 'like', '%' . $request->search . '%')
                  ->orWhere('city', 'like', '%' . $request->search . '%');
        }

        $vendors = $query->withCount(['products' => function($q) {
                $q->where('qc_status', 'approved');
            }])
            ->orderByDesc('products_count')
            ->paginate(12)
            ->withQueryString();

        return view('store.shops', compact('vendors'));
    }

    public function vendorShop(Request $request, \App\Models\User $vendor): View
    {
        abort_unless($vendor->role === 'vendor' && $vendor->account_status === 'active', 404);

        $query = Product::where('vendor_id', $vendor->id)
            ->where('qc_status', 'approved')
            ->withCount('orders');

        $sort = $request->input('sort', 'new');
        match ($sort) {
            'price_low' => $query->orderBy('price')->orderByDesc('id'),
            'price_high' => $query->orderByDesc('price')->orderByDesc('id'),
            'popular' => $query->orderByDesc('orders_count')->orderByDesc('id'),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('store.vendor_shop', [
            'vendor' => $vendor,
            'products' => $products,
        ]);
    }

    public function postReview(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000']
        ]);

        // Must have bought the product
        $hasOrdered = Order::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->where('status', 'delivered')
            ->exists();

        if (!$hasOrdered) {
            return back()->withErrors(['review' => 'You can only review products after they have been delivered.']);
        }

        \App\Models\Review::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $product->id],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        return back()->with('status', 'Thank you for your review!');
    }

    public function postQuestion(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'question' => ['required', 'string', 'max:1000']
        ]);

        $product->questions()->create([
            'user_id' => Auth::id(),
            'question' => $request->question,
            'status' => 'pending'
        ]);

        return back()->with('status', 'Your question has been submitted successfully and is pending an answer.');
    }

    public function unsubscribeNewsletter(Request $request): View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This unsubscribe link is invalid or expired.');
        }

        $email = strtolower(trim((string) $request->query('email', '')));
        abort_unless(filter_var($email, FILTER_VALIDATE_EMAIL), 404);

        \App\Models\Newsletter::where('email', $email)->delete();

        return view('store.newsletter-unsubscribed', ['email' => $email]);
    }

    public function subscribeNewsletter(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);
        \App\Models\Newsletter::firstOrCreate(['email' => $request->email]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Thank you for subscribing to our newsletter!']);
        }

        return back()->with('status', 'Thank you for subscribing to our newsletter!');
    }
}
