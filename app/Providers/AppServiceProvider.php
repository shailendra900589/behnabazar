<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Support\SiteMedia;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
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
        Paginator::useBootstrapFive();

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

            $view->with(compact('cartCount', 'wishlistCount', 'categories', 'cartItemsPreview', 'siteDisplay'));
        });
    }
}
