<nav class="bb-mobile-nav d-lg-none" aria-label="Quick shop navigation">
    <a href="{{ route('home') }}" class="bb-mobile-nav-item {{ request()->routeIs('home') && !request('cat') ? 'is-active' : '' }}" aria-label="Home">
        <i class="bi bi-house-door-fill"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('home') }}#products" class="bb-mobile-nav-item" aria-label="Shop products">
        <i class="bi bi-grid-fill"></i>
        <span>Shop</span>
    </a>
    <a href="{{ route('cart') }}" class="bb-mobile-nav-item {{ request()->routeIs('cart') || request()->routeIs('checkout') ? 'is-active' : '' }}" aria-label="Cart">
        <span class="bb-mobile-nav-icon-wrap">
            <i class="bi bi-bag-fill"></i>
            @if(($cartCount ?? 0) > 0)
                <span class="bb-mobile-nav-badge" data-cart-count>{{ $cartCount }}</span>
            @endif
        </span>
        <span>Cart</span>
    </a>
    @auth
        <a href="{{ route('referral') }}" class="bb-mobile-nav-item {{ request()->routeIs('referral') ? 'is-active' : '' }}" aria-label="Refer & Earn">
            <i class="bi bi-gift-fill"></i>
            <span>Refer</span>
        </a>
        <a href="{{ route('dashboard') }}" class="bb-mobile-nav-item {{ request()->routeIs('dashboard') || request()->routeIs('profile') || request()->routeIs('orders') ? 'is-active' : '' }}" aria-label="Account">
            <i class="bi bi-person-circle"></i>
            <span>Account</span>
        </a>
    @else
        <a href="{{ route('register') }}" class="bb-mobile-nav-item" aria-label="Refer & Earn">
            <i class="bi bi-gift"></i>
            <span>Refer</span>
        </a>
        <a href="{{ route('login') }}" class="bb-mobile-nav-item {{ request()->routeIs('login') ? 'is-active' : '' }}" aria-label="Sign in">
            <i class="bi bi-person"></i>
            <span>Sign in</span>
        </a>
    @endauth
</nav>
