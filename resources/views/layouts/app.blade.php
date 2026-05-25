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
@include('partials.header-marquee')
<nav class="navbar navbar-expand-lg sticky-top bb-navbar bb-navbar-compact shadow-sm">
    <div class="container">
        <a class="navbar-brand bb-logo-link me-4" href="{{ route('home') }}" aria-label="{{ $siteBranding['name'] }} home">
            <img src="{{ asset('images/brand/behna-bazar-wordmark.jpeg') }}" alt="{{ $siteBranding['name'] }}" class="bb-logo bb-logo-nav">
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list fs-4 text-dark"></i>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <form class="d-none d-lg-flex mx-lg-4 my-3 my-lg-0 flex-grow-1 position-relative" style="max-width: 500px" action="{{ route('home') }}">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-4"><i class="bi bi-search text-muted"></i></span>
                    <input class="form-control bg-light border-start-0 rounded-end-pill bb-search-input" id="liveSearchInput" name="search" autocomplete="off" value="{{ request('search') }}" placeholder="Search products, brands...">
                </div>
                <div id="liveSearchResults" class="dropdown-menu w-100 shadow-lg border-0 rounded-4 mt-2 p-2 position-absolute" style="top: 100%; display: none;">
                    <!-- Results injected here -->
                </div>
            </form>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInput = document.getElementById('liveSearchInput');
                    const resultsBox = document.getElementById('liveSearchResults');
                    let timeout = null;
                    let searchAbort = null;

                    searchInput.addEventListener('input', function() {
                        clearTimeout(timeout);
                        const query = this.value.trim();
                        
                        if (query.length < 2) {
                            resultsBox.style.display = 'none';
                            return;
                        }

                        timeout = setTimeout(() => {
                            if (searchAbort) searchAbort.abort();
                            searchAbort = new AbortController();
                            fetch(`/api/search?q=${encodeURIComponent(query)}`, { signal: searchAbort.signal })
                                .then(res => res.json())
                                .then(data => {
                                    resultsBox.innerHTML = '';
                                    if (data.length === 0) {
                                        resultsBox.innerHTML = '<div class="text-muted text-center p-3 small">No products found</div>';
                                    } else {
                                        data.forEach(item => {
                                            resultsBox.innerHTML += `
                                                <a href="${item.url}" class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3">
                                                    <img src="${item.image}" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">
                                                    <div class="min-w-0">
                                                        <div class="fw-semibold text-truncate text-wrap" style="max-width: 250px">${item.title}</div>
                                                        <div class="small">
                                                            ${item.percent_off ? `<span class="badge bg-danger me-1" style="font-size:0.65rem">${item.percent_off}% off</span>` : ''}
                                                            <span class="text-bloom fw-bold">${item.formatted_price}</span>
                                                            ${item.formatted_mrp ? `<span class="text-muted text-decoration-line-through ms-1">${item.formatted_mrp}</span>` : ''}
                                                        </div>
                                                    </div>
                                                </a>
                                            `;
                                        });
                                    }
                                    resultsBox.style.display = 'block';
                                })
                                .catch(() => {});
                        }, 300);
                    });

                    document.addEventListener('click', function(e) {
                        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
                            resultsBox.style.display = 'none';
                        }
                    });
                });
            </script>
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
                        <a class="nav-link" href="{{ auth()->check() ? route('dashboard').'#referralProgramCard' : route('register') }}">
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
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-2 p-3" style="width: 320px;">
                        <h6 class="fw-bold mb-3">Your Cart</h6>
                        @if(isset($cartItemsPreview) && $cartItemsPreview->isNotEmpty())
                            <div class="d-flex flex-column gap-3 mb-3 max-h-300 overflow-auto">
                                @foreach($cartItemsPreview as $item)
                                    <div class="d-flex gap-3 align-items-center">
                                        <img src="{{ $item->product->imageUrl() }}" class="rounded-3 object-fit-cover" width="50" height="50">
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="fw-semibold text-truncate small">{{ $item->product->title }}</div>
                                            <div class="small text-muted">{{ $item->quantity }} x â‚¹{{ number_format($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price, 2) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($cartCount > 3)
                                <div class="text-center small text-muted mb-3">+ {{ $cartCount - 3 }} more items</div>
                            @endif
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('cart') }}" class="btn btn-light btn-sm rounded-pill w-100">View Cart</a>
                                <a href="{{ route('checkout') }}" class="btn btn-bloom btn-sm rounded-pill w-100">Checkout</a>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-bag text-muted fs-1 mb-2 d-block opacity-50"></i>
                                <span class="text-muted small">Your cart is empty.</span>
                            </div>
                        @endif
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
@include('partials.mobile-search-bar')
@include('partials.site-video')
<main class="bb-main">@yield('content')</main>

@include('partials.assets-foot')
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    @if (session('status'))
        Toast.fire({ icon: 'success', title: "{{ session('status') }}" });
    @endif
    @if (session('warning'))
        Toast.fire({ icon: 'warning', title: "{{ session('warning') }}" });
    @endif
    @if (session('info'))
        Toast.fire({ icon: 'info', title: "{{ session('info') }}" });
    @endif
    @if ($errors->any())
        Toast.fire({ icon: 'error', title: "{{ $errors->first() }}" });
    @endif
</script>
@include('partials.footer')
@include('partials.mobile-bottom-nav')

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="bi bi-chevron-up"></i>
</button>

<script>
    // Back to Top visibility
    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 400) {
            backToTop.classList.add('visible');
        } else {
            backToTop.classList.remove('visible');
        }
    });

    // Scroll Reveal - animate elements when they come into viewport
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-ad-id]').forEach((card) => {
            if (sessionStorage.getItem(card.dataset.adId) === 'closed') {
                card.classList.add('is-hidden');
            }
        });

        document.querySelectorAll('[data-ad-close]').forEach((button) => {
            button.addEventListener('click', () => {
                const key = button.dataset.adClose;
                sessionStorage.setItem(key, 'closed');
                button.closest('[data-ad-id]')?.classList.add('is-hidden');
            });
        });

        // Auto-apply reveal to content sections
        document.querySelectorAll('.product-card, .bb-card, .stat-card, .table-card, .trust-badge').forEach((el, i) => {
            el.classList.add('reveal');
            el.style.transitionDelay = `${Math.min(i * 0.05, 0.4)}s`;
            revealObserver.observe(el);
        });
    });
</script>
</body>
</html>
