@extends('layouts.app')
@section('content')
@php
    $u = auth()->user();
@endphp
<div class="dashboard-shell">
    <div class="container-fluid">
        <div class="row">
            <aside class="col-lg-2 p-4 dashboard-sidebar">
                <a class="bb-logo-link d-inline-flex mb-4" href="{{ route('home') }}" aria-label="Behna Bazar home">
                    <img src="{{ asset('images/brand/behna-bazar-wordmark.jpeg') }}" alt="Behna Bazar" class="bb-logo bb-logo-sidebar">
                </a>
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
                            <a class="nav-link rounded-3 {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a class="nav-link rounded-3" href="{{ route('home') }}">
                                <i class="bi bi-shop me-2"></i>Storefront
                            </a>
                            <a class="nav-link rounded-3" href="{{ route('orders') }}"><i class="bi bi-receipt me-2"></i>Orders</a>
                            @if ($u->role === 'vendor' && ($u->account_status === 'active' || session()->has('impersonated_by')) && \App\Support\MarketplaceSettings::resellEnabled())
                                <a class="nav-link rounded-3 {{ request()->routeIs('manage.resell.catalog') ? 'active fw-semibold' : '' }}" href="{{ route('manage.resell.catalog') }}"><i class="bi bi-arrow-left-right me-2"></i>Resell catalog</a>
                            @endif
                            <a class="nav-link rounded-3" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>Profile</a>
                        @endif
                    @endauth
                </nav>
            </aside>
            <section class="col-lg-10 p-4 p-lg-5">
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
