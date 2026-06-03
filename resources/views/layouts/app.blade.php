<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-base-url" content="{{ url('/') }}">
    <meta name="application-name" content="{{ $siteBranding['name'] }}">
    <meta name="theme-color" content="#4f46e5">
    @include('partials.seo-head')
    <link rel="icon" type="image/jpeg" href="{{ \App\Support\BbAsset::url('images/brand/bb-mark.jpeg') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="mobile-web-app-capable" content="yes">
    @include('partials.assets-head')
</head>
<body class="bb-storefront">
<div id="bb-toasts" class="toast-container position-fixed top-0 end-0 p-3"></div>
<header class="bb-site-header-sticky">
@include('partials.header-marquee')
<nav class="navbar navbar-expand-lg bb-navbar bb-navbar-compact shadow-sm">
    <div class="container">
        <a class="navbar-brand bb-logo-link me-4" href="{{ route('home') }}" aria-label="{{ $siteBranding['name'] }} home">
            <img src="{{ asset('images/brand/behna-bazar-wordmark.jpeg') }}" alt="{{ $siteBranding['name'] }}" class="bb-logo bb-logo-nav">
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list fs-4 text-dark"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <form class="d-none d-lg-flex mx-lg-4 my-3 my-lg-0 flex-grow-1 position-relative" style="max-width: 500px" action="{{ route('home') }}" data-live-search>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-4"><i class="bi bi-search text-muted"></i></span>
                    <input class="form-control bg-light border-start-0 rounded-end-pill bb-search-input" name="search" autocomplete="off" value="{{ request('search') }}" placeholder="Search products, brands...">
                </div>
                <div class="bb-live-search-results dropdown-menu w-100 shadow-lg border-0 rounded-4 mt-2 p-2" style="top:100%;display:none;"></div>
            </form>
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1 bb-nav-links">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">{{ $siteBranding['nav_home_label'] }}</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Categories</a>
                    <ul class="dropdown-menu shadow-sm border-0 rounded-4 mt-2">
                        @foreach($categories as $cat)
                            <li><a class="dropdown-item py-2" href="{{ route('home', ['cat' => $cat->slug]) }}"><i class="bi {{ $cat->icon }} me-2 text-muted"></i>{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </li>
                @if($referralEnabled)
                    <li class="nav-item d-none d-xl-block">
                        <a class="nav-link" href="{{ auth()->check() ? route('referral') : route('register') }}">
                            <i class="bi bi-gift me-1"></i>Refer &amp; earn
                        </a>
                    </li>
                @endif
                <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                @auth
                    <li class="nav-item d-none d-lg-block">
                        <a class="nav-link bb-coin-pill" href="{{ route('dashboard') }}" title="Your reward coins">
                            <i class="bi bi-coin text-warning"></i>
                            <span class="bb-coin-amount">{{ number_format($userCoins) }}</span>
                            <span class="d-none d-md-inline small text-muted">coins</span>
                        </a>
                    </li>
                    <li class="nav-item d-none d-lg-block"><a class="nav-link position-relative" href="{{ route('wishlist') }}">
                        <i class="bi bi-heart"></i>
                        <span class="position-absolute top-25 start-100 translate-middle badge rounded-pill bg-danger border border-white" data-wishlist-count>{{ $wishlistCount ?? 0 }}</span>
                    </a></li>
                @endauth
                <li class="nav-item dropdown dropdown-cart d-none d-lg-block">
                    <a class="nav-link position-relative dropdown-toggle" href="{{ route('cart') }}" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                        <i class="bi bi-bag"></i>
                        <span class="position-absolute top-25 start-100 translate-middle badge rounded-pill bg-dark border border-white" data-cart-count>{{ $cartCount ?? 0 }}</span>
                    </a>
                    <div id="bbCartDropdownMenu" class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-3" style="width: 300px; max-width: calc(100vw - 2rem); right: 0;">
                        @include('partials.cart-dropdown-menu', [
                            'cartItemsPreview' => $cartItemsPreview ?? collect(),
                            'cartCount' => $cartCount ?? 0,
                        ])
                    </div>
                </li>
                @auth
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 px-3 py-2 bg-light rounded-pill border" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-5 text-bloom"></i>
                            <span>{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-2">
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2 text-muted"></i>Dashboard</a></li>
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('profile') }}"><i class="bi bi-person me-2 text-muted"></i>Profile</a></li>
                            <li><a class="dropdown-item rounded-3 py-2" href="{{ route('orders') }}"><i class="bi bi-box-seam me-2 text-muted"></i>Orders</a></li>
                            <li><hr class="dropdown-divider opacity-25 my-2"></li>
                            <li>
                                <form method="post" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item rounded-3 py-2 text-danger fw-bold"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item ms-lg-1"><a class="btn btn-soft btn-sm rounded-pill px-3" href="{{ route('login') }}">Sign In</a></li>
                    <li class="nav-item"><a class="btn btn-bloom btn-sm rounded-pill px-3" href="{{ route('register') }}">Join</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>
</header>
@include('partials.mobile-search-bar')
@include('partials.site-video')
<main class="bb-main">@yield('content')</main>

@include('partials.assets-foot')
@if (session('status') || session('warning') || session('info') || $errors->any())
<script>
document.addEventListener('DOMContentLoaded',function(){
    @if (session('status'))
    window.bbToast("{{ session('status') }}");
    @endif
    @if (session('warning'))
    window.bbToast("{{ session('warning') }}", "warning");
    @endif
    @if (session('info'))
    window.bbToast("{{ session('info') }}", "info");
    @endif
    @if ($errors->any())
    window.bbToast("{{ $errors->first() }}", "danger");
    @endif
});
</script>
@endif
@include('partials.footer')
@include('partials.mobile-bottom-nav')
<button class="back-to-top" id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top"><i class="bi bi-chevron-up"></i></button>
<script>
const backToTop=document.getElementById('backToTop');
window.addEventListener('scroll',()=>{backToTop.classList.toggle('visible',window.scrollY>400);},{passive:true});
</script>
</body>
</html>
