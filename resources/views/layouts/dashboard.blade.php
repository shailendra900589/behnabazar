@extends('layouts.app')
@section('content')
@php
    $u = auth()->user();
@endphp
<div class="dashboard-shell">
    <div class="container-fluid px-3 px-md-4 py-3">
        <div class="row g-0 g-lg-4">
            <div class="col-lg-3">
                <button class="btn btn-bloom rounded-pill w-100 d-lg-none dashboard-mobile-toggle mb-3 mt-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashboardSidebar" aria-controls="dashboardSidebar">
                    <i class="bi bi-list me-2"></i>Dashboard menu
                </button>
                <div class="offcanvas offcanvas-lg offcanvas-start dashboard-offcanvas" tabindex="-1" id="dashboardSidebar" aria-labelledby="dashboardSidebarLabel">
                    <div class="offcanvas-header d-lg-none border-bottom">
                        <h5 class="offcanvas-title fw-bold" id="dashboardSidebarLabel">Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#dashboardSidebar" aria-label="Close"></button>
                    </div>
                    <aside class="offcanvas-body p-2 p-lg-3 dashboard-sidebar">
                <nav class="nav flex-column gap-1">
                    @auth
                        @if ($u->role === 'admin')
                            @php
                                $admSec = request('section', 'overview');
                                $whatsappPendingCount = \App\Models\WhatsappOutbox::pendingCount();
                                $stockAlertCount = \App\Models\StockAlert::pending()->count();
                            @endphp
                            <p class="sidebar-section-label text-uppercase small fw-semibold mb-2">Navigation</p>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'overview' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'overview']) }}">
                                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'products' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'products']) }}">
                                <i class="bi bi-grid-1x2"></i><span>Products</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'reviews' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'reviews']) }}">
                                <i class="bi bi-chat-square-text"></i><span>Reviews</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'orders' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'orders']) }}">
                                <i class="bi bi-bag-check"></i><span>Orders</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'vendors' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'vendors']) }}">
                                <i class="bi bi-shop-window"></i><span>Vendors</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'payouts' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'payouts']) }}">
                                <i class="bi bi-wallet2"></i><span>Payouts</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'returns' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'returns']) }}">
                                <i class="bi bi-arrow-return-left"></i><span>Returns</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'catalog' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'catalog']) }}">
                                <i class="bi bi-folder2-open"></i><span>Catalog</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'storefront' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'storefront']) }}">
                                <i class="bi bi-window-sidebar"></i><span>Storefront</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'marketing' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'marketing']) }}">
                                <i class="bi bi-gift"></i><span>Rewards &amp; coupons</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'referrals' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'referrals']) }}">
                                <i class="bi bi-share"></i><span>Referrals</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'program' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'program']) }}">
                                <i class="bi bi-sliders"></i><span>Program settings</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'whatsapp' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'whatsapp']) }}">
                                <i class="bi bi-whatsapp text-success"></i><span>WhatsApp outbox</span>
                                @if(($whatsappPendingCount ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill ms-auto" id="bbWaPendingBadge">{{ $whatsappPendingCount }}</span>
                                @endif
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'notifications' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'notifications']) }}">
                                <i class="bi bi-bell"></i><span>Message log</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'alerts' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'alerts']) }}">
                                <i class="bi bi-box-seam"></i><span>Stock alerts</span>
                                @if(($stockAlertCount ?? 0) > 0)
                                    <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $stockAlertCount }}</span>
                                @endif
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'team' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'team']) }}">
                                <i class="bi bi-people"></i><span>QC team</span>
                            </a>
                            <hr class="border-secondary opacity-25 my-3">
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('home') ? 'active fw-semibold' : '' }}" href="{{ route('home') }}">
                                <i class="bi bi-shop"></i><span>View store</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('profile') ? 'active fw-semibold' : '' }}" href="{{ route('profile') }}">
                                <i class="bi bi-person"></i><span>Profile</span>
                            </a>
                        @elseif ($u->role === 'user')
                            <a class="nav-link rounded-3 {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a class="nav-link rounded-3" href="{{ route('home') }}">
                                <i class="bi bi-shop me-2"></i>Storefront
                            </a>
                            <a class="nav-link rounded-3" href="{{ route('orders') }}"><i class="bi bi-box-seam me-2"></i>My orders</a>
                            <a class="nav-link rounded-3" href="{{ route('wishlist') }}"><i class="bi bi-heart me-2"></i>Wishlist</a>
                            <a class="nav-link rounded-3" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>Profile</a>
                            <a class="nav-link rounded-3" href="{{ route('addresses') }}"><i class="bi bi-geo-alt me-2"></i>Addresses</a>
                            <a class="nav-link rounded-3" href="{{ route('checkout') }}"><i class="bi bi-bag-check me-2"></i>Checkout</a>
                        @elseif (in_array($u->role, ['vendor', 'qc_manager', 'qc_staff'], true))
                            @php
                                $vSec = request('section', 'overview');
                                $vActive = $u->account_status === 'active' || session()->has('impersonated_by');
                            @endphp
                            <p class="sidebar-section-label text-uppercase small fw-semibold mb-2">Main</p>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $vSec === 'overview' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'overview']) }}">
                                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $vSec === 'products' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'products']) }}">
                                <i class="bi bi-bag"></i><span>My Products</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $vSec === 'orders' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'orders']) }}">
                                <i class="bi bi-receipt"></i><span>Orders</span>
                            </a>
                            @if ($u->role === 'vendor' && $vActive)
                                <p class="sidebar-section-label text-uppercase small fw-semibold mb-2 mt-3">Business</p>
                                <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $vSec === 'wallet' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'wallet']) }}">
                                    <i class="bi bi-piggy-bank"></i><span>Sales Wallet</span>
                                </a>
                                <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $vSec === 'promotions' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'promotions']) }}">
                                    <i class="bi bi-megaphone"></i><span>Promotions</span>
                                </a>
                                @if (\App\Support\MarketplaceSettings::resellEnabled())
                                    <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('manage.resell.catalog') ? 'active fw-semibold' : '' }}" href="{{ route('manage.resell.catalog') }}">
                                        <i class="bi bi-arrow-left-right"></i><span>Resell catalog</span>
                                    </a>
                                @endif
                                <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $vSec === 'referrals' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'referrals']) }}">
                                    <i class="bi bi-share"></i><span>Refer & Earn</span>
                                </a>
                                <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $vSec === 'questions' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'questions']) }}">
                                    <i class="bi bi-chat-left-text"></i><span>Q&A</span>
                                </a>
                            @endif
                            <hr class="border-secondary opacity-25 my-3">
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2" href="{{ route('home') }}">
                                <i class="bi bi-shop"></i><span>View Store</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2" href="{{ route('profile') }}">
                                <i class="bi bi-person"></i><span>Profile</span>
                            </a>
                        @endif
                    @endauth
                </nav>
                    </aside>
                </div>
            </div>
            <section class="col-lg-9 p-3 p-md-4 dashboard-main">
                @if ($u->role === 'vendor')
                    <div class="d-flex justify-content-end mb-3">
                        @include('partials.vendor-notifications')
                    </div>
                @endif
                @yield('dashboard')
            </section>
        </div>
    </div>
</div>
@endsection
