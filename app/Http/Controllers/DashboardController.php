<?php

namespace App\Http\Controllers;

use App\Mail\ProductPromotionMail;
use App\Models\Ad;
use App\Support\SiteMedia;
use App\Models\Newsletter;
use App\Models\AdWalletTransaction;
use App\Models\ReferralReward;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\VendorWalletTransaction;
use App\Services\ReferralProgramService;
use App\Services\VendorWalletService;
use App\Support\AdPlacements;
use App\Support\MarketplaceSettings;
use App\Support\ReferralSettings;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        return match ($user->role) {
            'admin' => $this->admin($request),
            'vendor' => $this->vendor(),
            'qc_manager', 'qc_staff' => $this->qc(),
            default => $this->customer(),
        };
    }

    public function admin(Request $request): View
    {
        $this->requireRole('admin');

        $allowed = ['overview', 'products', 'orders', 'vendors', 'payouts', 'returns', 'catalog', 'storefront', 'marketing', 'referrals', 'program', 'team'];
        $section = $request->query('section', 'overview');
        if (! in_array($section, $allowed, true)) {
            $section = 'overview';
        }

        return view('dashboards.admin', [
            'adminSection' => $section,
            'products' => Product::with(['category', 'vendor', 'qcOfficer', 'images'])->latest()->get(),
            'orders' => Order::with(['user', 'product'])->latest()->get(),
            'recentOrders' => Order::with(['user', 'product'])->latest()->take(10)->get(),
            'topProducts' => Product::with(['category', 'vendor'])
                ->withCount('orders')
                ->where('qc_status', 'approved')
                ->orderByDesc('orders_count')
                ->take(6)
                ->get(),
            'categories' => Category::latest()->get(),
            'vendors' => User::where('role', 'vendor')->latest()->get(),
            'qcUsers' => User::whereIn('role', ['qc_manager', 'qc_staff'])->latest()->get(),
            'coupons' => Coupon::latest()->get(),
            'settings' => Setting::pluck('setting_value', 'setting_key'),
            'ads' => Ad::with(['vendor', 'product'])->orderBy('location')->orderBy('sort_order')->latest()->get(),
            'banners' => Banner::orderBy('sort_order')->latest()->get(),
            'pendingVendors' => User::where('role', 'vendor')->where('account_status', 'pending_approval')->count(),
            'pendingProducts' => Product::where('qc_status', 'pending')->count(),
            'revenue' => (float) Order::query()->sum('total_price'),
            'chartLabels' => collect(range(6, 0))->map(fn($days) => now()->subDays($days)->format('M d'))->toArray(),
            'chartData' => collect(range(6, 0))->map(fn($days) => Order::whereDate('created_at', now()->subDays($days))->sum('total_price'))->toArray(),
            'payoutRequests' => \App\Models\Payout::with('vendor')->latest()->get(),
            'returnRequests' => Order::whereNotNull('return_status')->with(['user', 'product'])->latest()->get(),
            'promotionProducts' => Product::where('qc_status', 'approved')->orderBy('title')->get(['id', 'title', 'price']),
            'newsletterCount' => Newsletter::count(),
            'referralRewards' => ReferralReward::with(['referrer', 'referee', 'order'])->latest()->get(),
        ]);
    }

    public function sendPromotionEmail(Request $request): RedirectResponse
    {
        $this->requireRole('admin');

        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'audience' => ['required', 'in:newsletter,customers,both'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::where('qc_status', 'approved')->findOrFail($data['product_id']);

        $emails = collect();

        if (in_array($data['audience'], ['newsletter', 'both'], true)) {
            $emails = $emails->merge(Newsletter::query()->pluck('email'));
        }

        if (in_array($data['audience'], ['customers', 'both'], true)) {
            $emails = $emails->merge(
                User::query()
                    ->where('role', 'user')
                    ->where('is_email_verified', true)
                    ->pluck('email')
            );
        }

        $emails = $emails->map(fn ($e) => strtolower(trim((string) $e)))->filter()->unique()->values();

        if ($emails->isEmpty()) {
            return back()->withErrors(['product_id' => 'No recipients found for the selected audience.']);
        }

        $sent = 0;
        $failed = 0;

        foreach ($emails as $index => $email) {
            try {
                Mail::to($email)->send(new ProductPromotionMail(
                    $product,
                    $email,
                    $data['message'] ?? null,
                ));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('Promotion email failed', ['email' => $email, 'error' => $e->getMessage()]);
            }

            if ($index < $emails->count() - 1) {
                usleep(300000);
            }
        }

        $status = "Promotion email sent to {$sent} recipient(s).";
        if ($failed > 0) {
            $status .= " {$failed} could not be delivered.";
        }

        return back()->with('status', $status);
    }

    public function exportOrders(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $role = Auth::user()->role;
        abort_unless(in_array($role, ['admin', 'vendor']), 403);
        
        $query = Order::with(['product.vendor', 'user'])->latest();
        
        if ($role === 'vendor') {
            $query->whereHas('product', function($q) {
                $q->where('vendor_id', Auth::id());
            });
        }
        
        $orders = $query->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=orders_export_" . date('Y_m_d_H_i_s') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Order ID', 'Date', 'Customer Name', 'Phone', 'Address', 'Product', 'Vendor', 'Qty', 'Unit Price', 'Total Price', 'Discount', 'Coins Used', 'Status', 'Payment Method'];
        
        $callback = function() use($orders, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->customer_name,
                    $order->phone,
                    $order->address,
                    $order->product_name,
                    $order->product->vendor->shop_name ?? 'Behna Bazar',
                    $order->quantity,
                    $order->unit_price,
                    $order->total_price,
                    $order->discount_amount,
                    $order->coin_discount,
                    $order->status,
                    $order->payment_method
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function vendor(): View
    {
        $this->requireRole('vendor');
        $products = Product::with(['category', 'images'])->where('vendor_id', Auth::id())->latest()->get();
        $vendorProductIds = $products->pluck('id');
        $orders = Order::where(function ($q) use ($vendorProductIds) {
            $q->whereIn('product_id', $vendorProductIds)
                ->orWhere('fulfillment_vendor_id', Auth::id())
                ->orWhere('listing_vendor_id', Auth::id());
        })->with('product', 'user')->latest()->get();
        
        $wallet = app(VendorWalletService::class);
        $availableBalance = $wallet->availableBalance(Auth::id());
        $totalEarnings = (float) Auth::user()->sales_wallet_balance + \App\Models\Payout::where('vendor_id', Auth::id())->where('status', 'paid')->sum('amount');
        $viewsTotal = $products->sum('view_count');
        $conversionRate = $viewsTotal > 0 ? round(($orders->count() / $viewsTotal) * 100, 1) : 0;

        return view('dashboards.vendor', [
            'products' => $products,
            'orders' => $orders,
            'categories' => Category::orderBy('name')->get(),
            'chartLabels' => collect(range(6, 0))->map(fn($days) => now()->subDays($days)->format('M d'))->toArray(),
            'chartData' => collect(range(6, 0))->map(fn($days) => Order::whereIn('product_id', $vendorProductIds)->whereDate('created_at', now()->subDays($days))->sum('total_price'))->toArray(),
            'availableBalance' => $availableBalance,
            'viewsTotal' => $viewsTotal,
            'conversionRate' => $conversionRate,
            'payouts' => \App\Models\Payout::where('vendor_id', Auth::id())->latest()->get(),
            'questions' => \App\Models\ProductQuestion::with(['product', 'user'])->whereIn('product_id', $vendorProductIds)->where('status', 'pending')->latest()->get(),
            'promotions' => Ad::with('product')->where('vendor_id', Auth::id())->latest()->get(),
            'walletTransactions' => AdWalletTransaction::where('vendor_id', Auth::id())->latest()->take(10)->get(),
            'salesWalletTransactions' => VendorWalletTransaction::where('vendor_id', Auth::id())->latest()->take(15)->get(),
            'referralCode' => app(ReferralProgramService::class)->ensureReferralCode(Auth::user()),
            'referralRewards' => ReferralReward::where('referrer_id', Auth::id())->latest()->take(10)->get(),
            'adRates' => $this->adRates(),
            'adWalletMinTopup' => (float) Setting::value('ad_wallet_min_topup', 50),
            'payoutMin' => MarketplaceSettings::payoutMinAmount(),
        ]);
    }

    public function requestPayout(Request $request): RedirectResponse
    {
        $this->requireRole('vendor');
        $minPayout = MarketplaceSettings::payoutMinAmount();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.$minPayout],
            'bank_details' => ['required', 'string', 'max:1000'],
        ]);

        $wallet = app(VendorWalletService::class);
        $availableBalance = $wallet->availableBalance(Auth::id());

        if ($data['amount'] > $availableBalance) {
            return back()->withErrors(['amount' => 'Requested amount exceeds available sales wallet balance.']);
        }

        $payout = \App\Models\Payout::create([
            'vendor_id' => Auth::id(),
            'amount' => $data['amount'],
            'bank_details' => $data['bank_details'],
            'status' => 'pending',
        ]);

        if (! $wallet->reservePayout($payout)) {
            $payout->delete();

            return back()->withErrors(['amount' => 'Could not reserve wallet balance. Please try again.']);
        }

        return back()->with('status', 'Claim request submitted. Admin will approve and transfer to your bank.');
    }

    public function updatePayout(Request $request, \App\Models\Payout $payout): RedirectResponse
    {
        $this->requireRole('admin');
        $request->validate(['status' => 'required|in:paid,rejected']);
        $payout->update(['status' => $request->status]);
        app(VendorWalletService::class)->releasePayout($payout->fresh());

        return back()->with('status', 'Payout status updated.');
    }

    public function updateQuestion(Request $request, \App\Models\ProductQuestion $question): RedirectResponse
    {
        $role = Auth::user()->role;
        abort_unless(in_array($role, ['admin', 'vendor']), 403);
        if ($role === 'vendor') {
            abort_unless($question->product->vendor_id === Auth::id(), 403);
        }

        $request->validate([
            'answer' => 'nullable|string|max:1000',
            'status' => 'required|in:answered,rejected'
        ]);

        $question->update([
            'answer' => $request->answer,
            'status' => $request->status
        ]);

        return back()->with('status', 'Question updated successfully.');
    }

    public function qc(): View
    {
        $this->requireRole(['qc_manager', 'qc_staff']);

        return view('dashboards.qc', [
            'pending' => Product::with(['category', 'vendor', 'images', 'sourceProduct'])->where('qc_status', 'pending')->oldest()->get(),
            'history' => Product::with(['category', 'vendor'])->where('qc_verified_by', Auth::id())->latest('qc_verified_at')->get(),
            'team' => User::whereIn('role', ['qc_manager', 'qc_staff'])->latest()->get(),
        ]);
    }

    public function customer(): View
    {
        $user = Auth::user();

        $referralService = app(ReferralProgramService::class);

        return view('dashboards.customer', [
            'referralCode' => $referralService->ensureReferralCode($user),
            'referralRewards' => ReferralReward::where('referrer_id', $user->id)->latest()->take(10)->get(),
            'referralEnabled' => ReferralSettings::enabled(),
            'orders' => $user->orders()->with('product')->latest()->take(8)->get(),
            'wishlist' => $user->wishlistItems()->with('product')->latest()->take(8)->get(),
            'orderCount' => $user->orders()->count(),
            'wishlistCount' => $user->wishlistItems()->count(),
            'lifetimeSpend' => (float) $user->orders()->sum('total_price'),
            'pendingOrders' => $user->orders()->whereIn('status', ['pending', 'processing', 'shipped', 'out_for_delivery'])->count(),
            'trendingProducts' => \App\Models\Product::with(['category', 'vendor'])->withCount('orders')->where('qc_status', 'approved')->orderByDesc('orders_count')->take(4)->get(),
        ]);
    }

    public function saveCategory(Request $request): RedirectResponse
    {
        $this->requireRole('admin');
        $data = $request->validate(['name' => 'required|max:100', 'icon' => 'nullable|max:50']);
        Category::updateOrCreate(['id' => $request->id], ['name' => $data['name'], 'slug' => Str::slug($data['name']), 'icon' => $data['icon'] ?: 'bi-box']);
        Category::flushNavigationCache();

        return back()->with('status', 'Category saved.');
    }

    public function saveCoupon(Request $request): RedirectResponse
    {
        $this->requireRole('admin');
        $data = $request->validate(['code' => 'required|max:50', 'discount_type' => 'required|in:flat,percent', 'discount_value' => 'required|numeric|min:0', 'min_cart_value' => 'required|numeric|min:0']);
        Coupon::updateOrCreate(['code' => strtoupper($data['code'])], $data + ['code' => strtoupper($data['code']), 'status' => true]);
        return back()->with('status', 'Coupon saved.');
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $this->requireRole('admin');
        foreach ($request->input('settings', []) as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }

        SiteMedia::flushCache();

        return back()->with('status', 'Settings updated.');
    }

    public function saveSiteDisplay(Request $request): RedirectResponse
    {
        $this->requireRole('admin');

        $data = $request->validate([
            'header_marquee_text' => ['nullable', 'string', 'max:500'],
            'header_marquee_link' => ['nullable', 'string', 'max:500'],
            'site_video_type' => ['required', 'in:youtube,iframe'],
            'site_video_url' => ['nullable', 'string', 'max:500'],
            'site_video_embed' => ['nullable', 'string', 'max:8000'],
        ]);

        $settings = [
            'header_marquee_enabled' => $request->boolean('header_marquee_enabled') ? '1' : '0',
            'header_marquee_text' => $data['header_marquee_text'] ?? '',
            'header_marquee_link' => $data['header_marquee_link'] ?? '',
            'site_video_enabled' => $request->boolean('site_video_enabled') ? '1' : '0',
            'site_video_type' => $data['site_video_type'],
            'site_video_url' => $data['site_video_url'] ?? '',
            'site_video_embed' => $data['site_video_embed'] ?? '',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }

        SiteMedia::flushCache();

        return back()->with('status', 'Header marquee and site video updated.');
    }

    public function approveVendor(User $user): RedirectResponse
    {
        $this->requireRole('admin');
        abort_unless($user->role === 'vendor', 404);
        $user->update(['account_status' => 'active']);

        return back()->with('status', 'Vendor approved.');
    }

    public function rejectVendor(User $user): RedirectResponse
    {
        $this->requireRole('admin');
        abort_unless($user->role === 'vendor', 404);
        $user->update(['account_status' => 'rejected']);

        return back()->with('status', 'Vendor application rejected/deactivated.');
    }

    public function deleteVendor(User $user): RedirectResponse
    {
        $this->requireRole('admin');
        abort_unless($user->role === 'vendor', 404);
        $user->delete();

        return back()->with('status', 'Vendor deleted successfully.');
    }

    public function impersonateVendor(User $user): RedirectResponse
    {
        $this->requireRole('admin');
        abort_unless($user->role === 'vendor', 404);
        
        session()->put('impersonated_by', Auth::id());
        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'You are now impersonating '.$user->name);
    }

    public function leaveImpersonation(): RedirectResponse
    {
        $adminId = session()->pull('impersonated_by');
        if ($adminId) {
            Auth::loginUsingId($adminId);
            return redirect()->route('dashboard')->with('status', 'Returned to admin console.');
        }
        return redirect()->route('home');
    }

    public function createQcUser(Request $request): RedirectResponse
    {
        $this->requireRole('admin');
        $data = $request->validate(['name' => 'required|max:100', 'email' => 'required|email|unique:users,email', 'password' => 'required|min:6', 'role' => 'required|in:qc_manager,qc_staff']);
        $data['password'] = Hash::make($data['password']);
        User::create($data + ['account_status' => 'active', 'is_email_verified' => true, 'email_verified_at' => now()]);
        return back()->with('status', 'QC user created.');
    }

    public function saveProduct(Request $request): RedirectResponse
    {
        $role = Auth::user()->role;
        abort_unless(in_array($role, ['admin', 'vendor'], true), 403);

        if ($role === 'vendor' && ! $this->vendorCanManageShop()) {
            return back()->withErrors(['vendor' => 'Your shop is not active yet. Wait for admin approval.']);
        }

        $data = $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:1',
            'description' => 'required|max:3000',
            'image' => 'nullable|image|max:4096',
            'images' => 'nullable|array|max:9',
            'images.*' => 'image|max:4096',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['slug'] = Str::slug($data['title']).'-'.Str::random(5);
        $data['vendor_id'] = $role === 'vendor' ? Auth::id() : null;
        $data['qc_status'] = $role === 'admin' ? 'approved' : 'pending';
        $product = Product::create($data);

        $this->attachUploadedImages($product, $request, $data['image'] ?? null);

        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $variant) {
                if (!empty($variant['color']) || !empty($variant['size'])) {
                    $product->variants()->create([
                        'color' => $variant['color'] ?? null,
                        'size' => $variant['size'] ?? null,
                        'price' => $variant['price'] ?? null,
                        'stock' => $variant['stock'] ?? 0,
                    ]);
                }
            }
        }

        $this->clearStorefrontCaches();

        return back()->with('status', 'Product saved.');
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['admin', 'vendor'], true), 403);
        $this->authorizeProductAccess($product);

        $wasApproved = $product->qc_status === 'approved';

        $rules = [
            'title' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:1',
            'description' => 'required|max:3000',
            'image' => 'nullable|image|max:4096',
            'images' => 'nullable|array|max:9',
            'images.*' => 'image|max:4096',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer|exists:product_images,id',
        ];

        if ($product->isResellListing()) {
            $rules['price'] = 'required|numeric|min:'.max(1, (float) $product->source_base_price);
        }

        $data = $request->validate($rules);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'title' => $data['title'],
            'category_id' => $data['category_id'],
            'price' => $data['price'],
            'description' => $data['description'],
            'image' => $data['image'] ?? $product->image,
        ]);

        if ($request->filled('remove_image_ids')) {
            $toRemove = ProductImage::where('product_id', $product->id)
                ->whereIn('id', $request->input('remove_image_ids'))
                ->get();
            foreach ($toRemove as $img) {
                if ($img->path && ! str_starts_with($img->path, 'http')) {
                    Storage::disk('public')->delete($img->path);
                }
                $img->delete();
            }
            if ($product->image && $toRemove->pluck('path')->contains($product->image)) {
                $first = $product->images()->orderBy('sort_order')->first();
                $product->update(['image' => $first?->path]);
            }
        }

        $this->attachUploadedImages($product, $request, $data['image'] ?? null);

        if ($wasApproved && MarketplaceSettings::editRequiresQc()) {
            $product->update([
                'qc_status' => 'pending',
                'qc_verified_by' => null,
                'qc_verified_at' => null,
                'reject_reason' => null,
            ]);
        }

        $this->clearStorefrontCaches();

        $message = ($wasApproved && MarketplaceSettings::editRequiresQc())
            ? 'Product updated and sent to QC for re-verification.'
            : 'Product updated.';

        return back()->with('status', $message);
    }

    public function updateOrder(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        abort_unless(Auth::user()->isRole(['admin', 'vendor']), 403);
        if (Auth::user()->role === 'vendor') {
            abort_unless($this->vendorCanManageShop(), 403);
            $canManage = (int) $order->fulfillment_vendor_id === (int) Auth::id()
                || ((int) $order->product?->vendor_id === (int) Auth::id() && ! $order->product?->isResellListing());
            abort_unless($canManage, 403);
        }

        $data = $request->validate([
            'status' => 'required|in:pending,processing,shipped,out_for_delivery,delivered,cancelled', 
            'tracking_msg' => 'nullable|max:255',
            'location' => 'nullable|max:255'
        ]);
        
        $order->update([
            'status' => $data['status'],
            'tracking_msg' => $data['tracking_msg'] ?? null
        ]);

        $order->trackings()->create([
            'status' => $data['status'],
            'message' => $data['tracking_msg'] ?? null,
            'location' => $data['location'] ?? null,
        ]);

        if ($data['status'] === 'delivered') {
            $order->load('product.vendor', 'user');
            $referral = app(ReferralProgramService::class);
            $referral->handleOrderDelivered($order);
            if ($order->product) {
                $referral->handleVendorFirstSale($order, $order->product);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Order updated.']);
        }

        return back()->with('status', 'Order updated.');
    }

    public function reviewProduct(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $this->requireRole(['qc_manager', 'qc_staff']);
        $data = $request->validate(['decision' => 'required|in:approved,rejected', 'reject_reason' => 'nullable|required_if:decision,rejected|max:1000']);
        $product->update([
            'qc_status' => $data['decision'],
            'reject_reason' => $data['reject_reason'] ?? null,
            'qc_verified_by' => Auth::id(),
            'qc_verified_at' => now(),
        ]);

        $this->clearStorefrontCaches();

        if ($data['decision'] === 'approved') {
            app(ReferralProgramService::class)->handleProductListed($product->fresh());
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Product reviewed.']);
        }

        return back()->with('status', 'Product reviewed.');
    }

    public function saveReferralSettings(Request $request): RedirectResponse
    {
        $this->requireRole('admin');

        $data = $request->validate([
            'referral_program_enabled' => ['nullable'],
            'referral_require_admin_approval' => ['nullable'],
            'referral_user_reward_coins' => ['required', 'integer', 'min:0'],
            'referral_vendor_reward_amount' => ['required', 'numeric', 'min:0'],
            'referral_min_order_amount' => ['required', 'numeric', 'min:0'],
            'referral_share_validity_days' => ['required', 'integer', 'min:1'],
            'referral_user_triggers' => ['nullable', 'array'],
            'referral_vendor_triggers' => ['nullable', 'array'],
        ]);

        $settings = [
            'referral_program_enabled' => $request->boolean('referral_program_enabled') ? '1' : '0',
            'referral_require_admin_approval' => $request->boolean('referral_require_admin_approval') ? '1' : '0',
            'referral_user_reward_coins' => (string) $data['referral_user_reward_coins'],
            'referral_vendor_reward_amount' => (string) $data['referral_vendor_reward_amount'],
            'referral_min_order_amount' => (string) $data['referral_min_order_amount'],
            'referral_share_validity_days' => (string) $data['referral_share_validity_days'],
            'referral_user_triggers' => implode(',', $data['referral_user_triggers'] ?? ['share_first_purchase']),
            'referral_vendor_triggers' => implode(',', $data['referral_vendor_triggers'] ?? ['referee_first_sale']),
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['setting_key' => $key], ['setting_value' => $value]);
        }

        return redirect()->route('dashboard', ['section' => 'referrals'])->with('status', 'Referral program settings saved.');
    }

    public function approveReferralReward(Request $request, ReferralReward $reward): RedirectResponse
    {
        $this->requireRole('admin');
        app(ReferralProgramService::class)->approveReward($reward, Auth::user(), $request->input('admin_note'));

        return redirect()->route('dashboard', ['section' => 'referrals'])->with('status', 'Referral reward approved and paid.');
    }

    public function rejectReferralReward(Request $request, ReferralReward $reward): RedirectResponse
    {
        $this->requireRole('admin');
        app(ReferralProgramService::class)->rejectReward($reward, Auth::user(), $request->input('admin_note'));

        return redirect()->route('dashboard', ['section' => 'referrals'])->with('status', 'Referral reward rejected.');
    }

    public function saveProgramSettings(Request $request): RedirectResponse
    {
        $this->requireRole('admin');

        $data = $request->validate([
            'resell_program_enabled' => ['nullable'],
            'resell_customize_fee' => ['nullable', 'numeric', 'min:0'],
            'vendor_registration_amount' => ['nullable', 'numeric', 'min:0'],
            'payout_min_amount' => ['nullable', 'numeric', 'min:1'],
            'ad_wallet_min_topup' => ['nullable', 'numeric', 'min:1'],
            'product_edit_requires_qc' => ['nullable'],
        ]);

        if ($request->has('resell_program_enabled')) {
            Setting::updateOrCreate(['setting_key' => 'resell_program_enabled'], [
                'setting_value' => $request->boolean('resell_program_enabled') ? '1' : '0',
            ]);
        }
        if ($request->has('resell_customize_fee')) {
            Setting::updateOrCreate(['setting_key' => 'resell_customize_fee'], [
                'setting_value' => (string) $data['resell_customize_fee'],
            ]);
        }
        if ($request->has('vendor_registration_amount')) {
            Setting::updateOrCreate(['setting_key' => 'vendor_registration_amount'], [
                'setting_value' => (string) $data['vendor_registration_amount'],
            ]);
        }
        if ($request->has('payout_min_amount')) {
            Setting::updateOrCreate(['setting_key' => 'payout_min_amount'], [
                'setting_value' => (string) $data['payout_min_amount'],
            ]);
        }
        if ($request->has('ad_wallet_min_topup')) {
            Setting::updateOrCreate(['setting_key' => 'ad_wallet_min_topup'], [
                'setting_value' => (string) $data['ad_wallet_min_topup'],
            ]);
        }
        if ($request->has('product_edit_requires_qc')) {
            Setting::updateOrCreate(['setting_key' => 'product_edit_requires_qc'], [
                'setting_value' => $request->boolean('product_edit_requires_qc') ? '1' : '0',
            ]);
        }

        return redirect()->route('dashboard', ['section' => 'program'])->with('status', 'Program settings saved.');
    }

    public function deleteCategory(Category $category): RedirectResponse
    {
        $this->requireRole('admin');
        if (Product::where('category_id', $category->id)->exists()) {
            return back()->withErrors(['category' => 'Reassign or delete products in this category first.']);
        }
        $category->delete();
        Category::flushNavigationCache();

        return back()->with('status', 'Category deleted.');
    }

    public function deleteCoupon(Coupon $coupon): RedirectResponse
    {
        $this->requireRole('admin');
        $coupon->delete();

        return back()->with('status', 'Coupon deleted.');
    }

    public function toggleCoupon(Coupon $coupon): RedirectResponse
    {
        $this->requireRole('admin');
        $coupon->update(['status' => ! $coupon->status]);

        return back()->with('status', 'Coupon status updated.');
    }

    public function deleteProduct(Product $product): RedirectResponse
    {
        $role = Auth::user()->role;
        abort_unless(in_array($role, ['admin', 'vendor'], true), 403);
        $this->authorizeProductAccess($product);

        foreach ($product->images as $img) {
            if ($img->path && ! str_starts_with($img->path, 'http')) {
                Storage::disk('public')->delete($img->path);
            }
        }
        foreach (['image', 'image2', 'image3', 'image4'] as $col) {
            if ($product->{$col} && ! str_starts_with($product->{$col}, 'http')) {
                Storage::disk('public')->delete($product->{$col});
            }
        }

        try {
            $product->delete();
        } catch (\Throwable) {
            return back()->withErrors(['product' => 'Cannot delete a product that has order history.']);
        }

        $this->clearStorefrontCaches();

        return back()->with('status', 'Product deleted.');
    }

    public function saveBanner(Request $request): RedirectResponse
    {
        $this->requireRole('admin');
        $data = $request->validate([
            'link' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image' => ['required', 'image', 'max:4096'],
        ]);
        $data['image'] = $request->file('image')->store('banners', 'public');
        $data['status'] = true;
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['link'] = $data['link'] ?? '#';
        Banner::create($data);

        return back()->with('status', 'Banner added.');
    }

    public function deleteBanner(Banner $banner): RedirectResponse
    {
        $this->requireRole('admin');
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return back()->with('status', 'Banner removed.');
    }

    public function saveAd(Request $request): RedirectResponse
    {
        $this->requireRole('admin');
        $data = $request->validate([
            'location' => ['required', Rule::in(AdPlacements::keys())],
            'ad_type' => ['required', 'in:image,code,youtube,iframe,product_card,html'],
            'title' => ['nullable', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'cta_text' => ['nullable', 'string', 'max:80'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'autoplay' => ['nullable', 'boolean'],
            'code' => ['nullable', 'string', 'max:12000'],
            'image' => ['nullable', 'image', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $ad = new Ad();
        $ad->source = 'admin';
        $ad->ad_type = $data['ad_type'];
        $ad->location = $data['location'];
        $ad->title = $data['title'] ?? null;
        $ad->subtitle = $data['subtitle'] ?? null;
        $ad->cta_text = $data['cta_text'] ?? null;
        $ad->link_url = $data['link_url'] ?? null;
        $ad->video_url = $data['video_url'] ?? null;
        $ad->autoplay = $request->boolean('autoplay');
        $ad->code = $data['code'] ?? null;
        $ad->sort_order = $data['sort_order'] ?? 0;
        $ad->starts_at = $data['starts_at'] ?? null;
        $ad->ends_at = $data['ends_at'] ?? null;
        $ad->status = true;

        if ($data['ad_type'] === 'image' && $request->hasFile('image')) {
            if ($ad->image_path) {
                Storage::disk('public')->delete($ad->image_path);
            }
            $ad->image_path = $request->file('image')->store('ads', 'public');
        }

        $ad->save();

        return back()->with('status', 'Ad created.');
    }

    public function savePromotion(Request $request): RedirectResponse
    {
        $this->requireRole('vendor');

        $product = Product::where('vendor_id', Auth::id())
            ->where('qc_status', 'approved')
            ->findOrFail($request->input('product_id'));

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'location' => ['required', 'in:home_top,home_mid,home_bottom'],
            'title' => ['nullable', 'string', 'max:160'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'cta_text' => ['nullable', 'string', 'max:80'],
            'image' => ['nullable', 'image', 'max:4096'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $days = $this->promotionDays($data['starts_at'] ?? null, $data['ends_at'] ?? null);
        $rate = $this->adRate($data['location']);
        $cost = $days * $rate;

        if ((float) Auth::user()->ad_wallet_balance < $cost) {
            return back()->withErrors(['wallet' => 'Insufficient ad wallet balance. Top up your wallet before creating this promotion.']);
        }

        $ad = new Ad([
            'vendor_id' => Auth::id(),
            'product_id' => $product->id,
            'location' => $data['location'],
            'title' => $data['title'] ?: $product->title,
            'subtitle' => $data['subtitle'] ?: 'Promoted by '.Auth::user()->shop_name,
            'cta_text' => $data['cta_text'] ?: 'Shop now',
            'ad_type' => 'image',
            'link_url' => route('product.show', $product),
            'sort_order' => 20,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'source' => 'vendor',
            'status' => true,
        ]);

        $ad->image_path = $request->hasFile('image')
            ? $request->file('image')->store('ads', 'public')
            : $product->image;

        $ad->save();

        Auth::user()->decrement('ad_wallet_balance', $cost);
        AdWalletTransaction::create([
            'vendor_id' => Auth::id(),
            'amount' => $cost,
            'type' => 'debit',
            'purpose' => 'promotion',
            'reference' => 'ad_'.$ad->id,
            'notes' => "{$days} day(s) at Rs. {$rate}/day for {$data['location']}",
        ]);

        return back()->with('status', 'Promotion paid from ad wallet and published.');
    }

    public function createAdWalletOrder(Request $request): JsonResponse
    {
        $this->requireRole('vendor');

        $minTopup = (float) Setting::value('ad_wallet_min_topup', 50);
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:'.$minTopup, 'max:100000']]);
        $order = $this->createRazorpayOrder((float) $data['amount'], 'ad_wallet_'.Auth::id().'_'.time());

        session([
            'ad_wallet_razorpay_order_id' => $order['id'],
            'ad_wallet_amount' => (float) $data['amount'],
        ]);

        return response()->json([
            'key' => config('services.razorpay.key', env('RAZORPAY_KEY_ID')),
            'order_id' => $order['id'],
            'amount' => (int) round($data['amount'] * 100),
            'currency' => 'INR',
            'name' => config('app.name', 'Behna Bazar'),
        ]);
    }

    public function verifyAdWalletPayment(Request $request): RedirectResponse
    {
        $this->requireRole('vendor');

        $data = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        abort_unless($data['razorpay_order_id'] === session('ad_wallet_razorpay_order_id'), 403);
        abort_unless($this->verifyRazorpaySignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']), 403);

        $amount = (float) session('ad_wallet_amount', 0);
        Auth::user()->increment('ad_wallet_balance', $amount);
        AdWalletTransaction::create([
            'vendor_id' => Auth::id(),
            'amount' => $amount,
            'type' => 'credit',
            'purpose' => 'top_up',
            'razorpay_order_id' => $data['razorpay_order_id'],
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'notes' => 'Ad wallet top-up',
        ]);

        $request->session()->forget(['ad_wallet_razorpay_order_id', 'ad_wallet_amount']);

        return back()->with('status', 'Ad wallet topped up successfully.');
    }

    public function deletePromotion(Ad $ad): RedirectResponse
    {
        $this->requireRole('vendor');
        abort_unless($ad->vendor_id === Auth::id(), 403);

        if ($ad->image_path && str_starts_with($ad->image_path, 'ads/')) {
            Storage::disk('public')->delete($ad->image_path);
        }

        $ad->delete();

        return back()->with('status', 'Promotion removed.');
    }

    public function deleteAd(Ad $ad): RedirectResponse
    {
        $this->requireRole('admin');
        if ($ad->image_path && str_starts_with($ad->image_path, 'ads/')) {
            Storage::disk('public')->delete($ad->image_path);
        }
        $ad->delete();

        return back()->with('status', 'Ad removed.');
    }

    private function vendorCanManageShop(): bool
    {
        $user = Auth::user();

        return $user->account_status === 'active' || session()->has('impersonated_by');
    }

    private function authorizeProductAccess(Product $product): void
    {
        $user = Auth::user();

        if ($user->role === 'vendor') {
            abort_unless((int) $product->vendor_id === (int) $user->id, 403);
            abort_unless($this->vendorCanManageShop(), 403);

            return;
        }

        abort_unless($user->role === 'admin', 403);
    }

    private function attachUploadedImages(Product $product, Request $request, ?string $primaryPath = null): void
    {
        $sort = (int) $product->images()->max('sort_order') + 1;

        if ($primaryPath) {
            $exists = $product->images()->where('path', $primaryPath)->exists();
            if (! $exists) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $primaryPath,
                    'sort_order' => 0,
                ]);
            }
            $product->update(['image' => $primaryPath]);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $extraImage) {
                if ($product->images()->count() >= 10) {
                    break;
                }
                $path = $extraImage->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => $sort++,
                ]);
                if (! $product->fresh()->image) {
                    $product->update(['image' => $path]);
                }
            }
        }
    }

    private function adRates(): array
    {
        return [
            'home_top' => $this->adRate('home_top'),
            'home_mid' => $this->adRate('home_mid'),
            'home_bottom' => $this->adRate('home_bottom'),
        ];
    }

    private function adRate(string $location): float
    {
        return (float) Setting::value('ad_rate_'.$location, match ($location) {
            'home_top' => 500,
            'home_bottom' => 200,
            default => 300,
        });
    }

    private function promotionDays(?string $startsAt, ?string $endsAt): int
    {
        if (! $endsAt) {
            return 1;
        }

        $start = $startsAt ? \Carbon\Carbon::parse($startsAt) : now();
        $end = \Carbon\Carbon::parse($endsAt);

        return max(1, (int) ceil($start->diffInHours($end, false) / 24));
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

    public function updateReturn(Request $request, Order $order): RedirectResponse
    {
        $this->requireRole('admin');
        
        $request->validate(['return_status' => 'required|in:approved,rejected,refunded']);
        
        $order->update(['return_status' => $request->return_status]);

        if ($request->return_status === 'refunded') {
            // Give them back the coins they spent, subtract coins they earned
            if ($order->coin_discount > 0) {
                $order->user->increment('coins', $order->coin_discount);
            }
            if ($order->coins_earned > 0) {
                $order->user->decrement('coins', $order->coins_earned);
            }
            $order->update(['status' => 'cancelled']);
        }
        
        return back()->with('status', 'Return status updated.');
    }

    private function clearStorefrontCaches(): void
    {
        Cache::forget('storefront.price_bounds');
        Cache::forget('storefront.flash_deal');
    }

    private function requireRole(string|array $roles): void
    {
        abort_unless(Auth::user()?->isRole($roles), 403);
    }
}
