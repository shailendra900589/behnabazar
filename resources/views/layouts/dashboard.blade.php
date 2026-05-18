@extends('layouts.app')
@section('content')
@php($u = auth()->user())
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
                            @php($admSec = request('section', 'overview'))
                            <p class="sidebar-section-label text-uppercase small fw-semibold mb-2">Navigation</p>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'overview' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'overview']) }}">
                                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                            </a>
                            <a class="nav-link rounded-3 d-flex align-items-center gap-2 {{ request()->routeIs('dashboard') && $admSec === 'products' ? 'active fw-semibold' : '' }}" href="{{ route('dashboard', ['section' => 'products']) }}">
                                <i class="bi bi-grid-1x2"></i><span>Products</span>
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
                            <a class="nav-link rounded-3" href="{{ route('checkout') }}"><i class="bi bi-bag-check me-2"></i>Checkout</a>
                        @elseif (in_array($u->role, ['vendor', 'qc_manager', 'qc_staff'], true))
                            <a class="nav-link rounded-3 {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                            <a class="nav-link rounded-3" href="{{ route('home') }}">
                                <i class="bi bi-shop me-2"></i>Storefront
                            </a>
                            <a class="nav-link rounded-3" href="{{ route('orders') }}"><i class="bi bi-receipt me-2"></i>Orders</a>
                            <a class="nav-link rounded-3" href="{{ route('profile') }}"><i class="bi bi-person me-2"></i>Profile</a>
                        @endif
                    @endauth
                </nav>
            </aside>
            <section class="col-lg-10 p-4 p-lg-5">
                @yield('dashboard')
            </section>
        </div>
    </div>
</div>
@endsection
