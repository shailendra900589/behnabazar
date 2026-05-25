<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\VendorNotification;
use App\Observers\ProductSeoObserver;
use App\Support\Seo\SeoResolver;
use App\Services\ReferralProgramService;
use App\Services\VendorNotificationService;
use App\Support\ReferralSettings;
use App\Support\SiteBranding;
use App\Support\SiteMedia;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            $request = $this->app->make('request');
            if ($request) {
                $root = rtrim($request->getSchemeAndHttpHost().$request->getBasePath(), '/');
                if ($root !== '') {
                    URL::forceRootUrl($root);
                }
                if ($request->secure() || $request->header('X-Forwarded-Proto') === 'https') {
                    URL::forceScheme('https');
                }
            }
        }

        Paginator::useBootstrapFive();

        Product::observe(ProductSeoObserver::class);

        View::composer('layouts.app', function ($view): void {
            if (! config('seo.enabled', true)) {
                return;
            }
            $view->with('seo', SeoResolver::resolve(request(), $view->getData()));
        });

        View::composer(['layouts.app', 'layouts.dashboard'], function ($view): void {
            $cartCount = 0;
            $wishlistCount = 0;

            if (Auth::check()) {
                $cartQuery = CartItem::query()->where('user_id', Auth::id());
                $cartCount = (int) $cartQuery->sum('quantity');
                $wishlistCount = Auth::user()->wishlistItems()->count();
            } else {
                $cartQuery = CartItem::query()->where('session_id', request()->session()->getId());
                $cartCount = (int) $cartQuery->sum('quantity');
            }

            $cartItemsPreview = (clone $cartQuery)->with(['product', 'variant'])->latest()->take(3)->get();
            $categories = \App\Models\Category::forNavigation();

            $siteDisplay = SiteMedia::config();
            $siteBranding = SiteBranding::config();
            $referralEnabled = ReferralSettings::enabled();
            $referralCode = '';
            $userCoins = 0;

            if (Auth::check()) {
                $user = Auth::user();
                $userCoins = (int) ($user->coins ?? 0);
                if ($referralEnabled) {
                    $referralCode = app(ReferralProgramService::class)->ensureReferralCode($user);
                }
            }

            $visitorCount = (int) (\Illuminate\Support\Facades\DB::table('site_visits')->where('id', 1)->value('total_count') ?? 0);

            $view->with(compact(
                'cartCount',
                'wishlistCount',
                'categories',
                'cartItemsPreview',
                'siteDisplay',
                'siteBranding',
                'referralEnabled',
                'referralCode',
                'userCoins',
                'visitorCount',
            ));
        });

        View::composer([
            'layouts.dashboard',
            'partials.vendor-notifications',
            'dashboards.vendor',
            'dashboards.resell-catalog',
        ], function ($view): void {
            $vendorNotifications = collect();
            $vendorNotificationUnread = 0;

            if (Auth::check() && Auth::user()->role === 'vendor') {
                $vendorNotifications = VendorNotification::where('vendor_id', Auth::id())
                    ->latest()
                    ->take(15)
                    ->get();
                $vendorNotificationUnread = app(VendorNotificationService::class)->unreadCount((int) Auth::id());
            }

            $view->with(compact('vendorNotifications', 'vendorNotificationUnread'));
        });
    }
}
