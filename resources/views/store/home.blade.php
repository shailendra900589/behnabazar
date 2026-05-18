@extends('layouts.app')
@section('title', 'Behna Bazar - Multipurpose Marketplace')
@section('content')
@php
    $filterQs = array_filter([
        'search' => request('search'),
        'sort' => request('sort'),
        'min_price' => request('min_price'),
        'max_price' => request('max_price'),
    ], fn ($v) => $v !== null && $v !== '');
@endphp
<section class="container py-4 py-lg-5">
    @include('partials.ad-slot', ['slot' => 'home_top', 'class' => 'mb-4'])

    <div class="marketplace-intro mb-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <span class="marketplace-kicker">Multipurpose marketplace</span>
                <h1 class="marketplace-title">Everything for daily life, home, work, style, and celebrations.</h1>
                <p class="marketplace-copy">Shop organic and non-organic grocery, fresh essentials, fashion, electronics, home products, beauty, accessories, and verified local finds in one trusted Behna Bazar experience.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#products" class="btn btn-bloom rounded-pill px-4">Explore products</a>
                    <a href="{{ route('vendor.register.create') }}" class="btn btn-light border rounded-pill px-4">Sell on Behna Bazar</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="marketplace-category-grid">
                    <div><i class="bi bi-basket2"></i><span>Grocery</span></div>
                    <div><i class="bi bi-flower1"></i><span>Organic</span></div>
                    <div><i class="bi bi-cpu"></i><span>Electronics</span></div>
                    <div><i class="bi bi-bag-heart"></i><span>Fashion</span></div>
                    <div><i class="bi bi-house-heart"></i><span>Home</span></div>
                    <div><i class="bi bi-stars"></i><span>Beauty</span></div>
                </div>
            </div>
        </div>
    </div>

    @if ($banners->isNotEmpty())
        <div id="homeHero" class="carousel slide bb-card overflow-hidden rounded-4 mb-5 shadow-sm" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach ($banners as $i => $b)
                    <button type="button" data-bs-target="#homeHero" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach ($banners as $i => $banner)
                    @php($src = str_starts_with($banner->image, 'http') ? $banner->image : asset('storage/'.$banner->image))
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <a href="{{ $banner->link ?: '#' }}" class="d-block position-relative">
                            <img src="{{ $src }}" class="d-block w-100 hero-carousel-img" alt="Banner">
                            <div class="position-absolute bottom-0 start-0 end-0 p-4 p-lg-5 text-white hero-carousel-overlay d-flex flex-column justify-content-end pb-lg-5">
                                <div>
                                    <span class="badge bg-white text-dark rounded-pill mb-3 px-3 py-2 fw-bold tracking-wide shadow-sm" style="font-size: 0.8rem">Featured Marketplace Picks</span>
                                    <h2 class="display-5 fw-bolder mb-2 text-shadow-sm">Curated for every cart</h2>
                                    <p class="lead text-white-50 mb-0 d-none d-md-block" style="max-width: 600px;">Find grocery, fashion, electronics, home goods, beauty, and local products from verified sellers.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#homeHero" data-bs-slide="prev">
                <span class="carousel-control-prev-icon rounded-circle bg-dark bg-opacity-50 p-3" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homeHero" data-bs-slide="next">
                <span class="carousel-control-next-icon rounded-circle bg-dark bg-opacity-50 p-3" aria-hidden="true"></span>
            </button>
        </div>
    @else
        <div class="hero p-4 p-lg-5 mb-5 rounded-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge badge-soft rounded-pill mb-3">Shop everything. Sell smarter.</span>
                    <h1 class="display-5 fw-bold lh-1 mb-3">A polished marketplace for every category.</h1>
                    <p class="lead text-muted">Discover grocery, fashion, electronics, home goods, beauty, local products, and more from approved sellers.</p>
                    <a href="#products" class="btn btn-bloom btn-lg rounded-pill px-4">Start shopping</a>
                </div>
                <div class="col-lg-5">
                    <div class="bb-card p-4">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="h2 fw-bold text-bloom">{{ $products->total() }}</div>
                                <div class="text-muted small">Products</div>
                            </div>
                            <div class="col-6">
                                <div class="h2 fw-bold text-bloom">{{ $categories->count() }}</div>
                                <div class="text-muted small">Categories</div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-4 bg-light small text-muted">Trusted sellers · QC-approved listings · Coins on every order</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4 mb-5 text-center">
        <div class="col-md-4">
            <div class="feature-card p-4 p-lg-5 h-100">
                <div class="feature-icon-wrapper mx-auto">
                    <i class="bi bi-truck fs-3"></i>
                </div>
                <h3 class="h5 fw-bold mb-2">All-category shopping</h3>
                <p class="small text-muted mb-0">Grocery, fashion, electronics, home, and more</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 p-lg-5 h-100">
                <div class="feature-icon-wrapper mx-auto">
                    <i class="bi bi-patch-check fs-3"></i>
                </div>
                <h3 class="h5 fw-bold mb-2">Verified sellers</h3>
                <p class="small text-muted mb-0">Products reviewed before going live</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card p-4 p-lg-5 h-100">
                <div class="feature-icon-wrapper mx-auto">
                    <i class="bi bi-wallet2 fs-3"></i>
                </div>
                <h3 class="h5 fw-bold mb-2">Rewards wallet</h3>
                <p class="small text-muted mb-0">Earn coins across every category</p>
            </div>
        </div>
    </div>

    @if ($categories->isNotEmpty())
        <div class="d-flex gap-2 overflow-auto mb-4 pb-2">
            <a class="btn btn-soft rounded-pill {{ ! request('cat') ? 'active' : '' }}" href="{{ route('home', $filterQs) }}">All</a>
            @foreach ($categories as $cat)
                <a class="btn btn-light border rounded-pill {{ request('cat') === $cat->slug ? 'active' : '' }}" href="{{ route('home', array_merge($filterQs, ['cat' => $cat->slug])) }}">
                    <i class="bi {{ $cat->icon }} me-1"></i>{{ $cat->name }}
                </a>
            @endforeach
        </div>
        </div>
    @endif

    @if (isset($flashDeal) && $flashDeal)
        <div class="bb-card p-4 rounded-4 mb-5 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); border-left: 4px solid #d9534f;">
            <div class="position-absolute top-0 end-0 p-3 opacity-25">
                <i class="bi bi-lightning-fill" style="font-size: 8rem; color: #d9534f;"></i>
            </div>
            <div class="row align-items-center position-relative" style="z-index: 1;">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-danger text-uppercase tracking-wider shadow-sm"><i class="bi bi-lightning-charge-fill me-1"></i>Flash Deal</span>
                        <span class="text-danger fw-bold small" id="flash-timer">Ends in: 04:59:59</span>
                    </div>
                    <h2 class="fw-bold mb-2 text-dark">{{ $flashDeal->title }}</h2>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="fs-4 fw-bolder text-dark">₹{{ number_format($flashDeal->price * 0.8, 2) }}</span>
                        <span class="text-muted text-decoration-line-through">₹{{ number_format($flashDeal->price, 2) }}</span>
                        <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 rounded-pill">20% OFF</span>
                    </div>
                    <a href="{{ route('product.show', $flashDeal) }}" class="btn btn-dark rounded-pill px-4">Claim Deal <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
                <div class="col-md-4 text-center mt-4 mt-md-0">
                    <img src="{{ $flashDeal->imageUrl() }}" alt="{{ $flashDeal->title }}" class="img-fluid rounded-3 shadow-sm" style="max-height: 200px; object-fit: contain;">
                </div>
            </div>
        </div>
        <script>
            // Flash Deal Timer Logic
            let time = 5 * 3600 + 59 * 60 + 59; // 5 hours, 59 mins, 59 secs
            setInterval(() => {
                let h = Math.floor(time / 3600).toString().padStart(2, '0');
                let m = Math.floor((time % 3600) / 60).toString().padStart(2, '0');
                let s = (time % 60).toString().padStart(2, '0');
                const el = document.getElementById('flash-timer');
                if (el) el.innerText = `Ends in: ${h}:${m}:${s}`;
                if (time > 0) time--;
            }, 1000);
        </script>
    @endif

    @if ($newArrivals->isNotEmpty())
        <div class="d-flex align-items-end justify-content-between mb-3">
            <h2 class="fw-bold mb-0">New arrivals</h2>
            <a href="#products" class="small text-bloom fw-semibold text-decoration-none">View all</a>
        </div>
        <div class="row g-4 mb-5">
            @foreach ($newArrivals as $product)
                <div class="col-sm-6 col-lg-3">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    @endif

    @include('partials.ad-slot', ['slot' => 'home_mid', 'class' => 'mb-5'])

    @if (isset($hotProducts) && $hotProducts->isNotEmpty())
        <div class="d-flex align-items-end justify-content-between mb-3">
            <h2 class="fw-bold mb-0">Trending now</h2>
            <span class="small text-muted">Based on recent orders</span>
        </div>
        <div class="row g-4 mb-5">
            @foreach ($hotProducts as $product)
                <div class="col-sm-6 col-lg-3">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    @endif

    @if (isset($recentlyViewed) && $recentlyViewed->isNotEmpty())
        <div class="d-flex align-items-end justify-content-between mb-3">
            <h2 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-muted"></i>Recently viewed</h2>
        </div>
        <div class="row g-4 mb-5">
            @foreach ($recentlyViewed as $product)
                <div class="col-sm-6 col-lg-3">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    @endif

    <div id="products" class="mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
            <div>
                <h2 class="fw-bold mb-1">Shop products</h2>
                <p class="text-muted mb-0">Sort, filter by price, and shop approved listings across all categories.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-outline-secondary btn-sm rounded-pill {{ ($sort ?? 'new') === 'new' ? 'active' : '' }}" href="{{ route('home', array_filter(array_merge($filterQs, ['cat' => request('cat'), 'sort' => 'new']))) }}">Newest</a>
                <a class="btn btn-outline-secondary btn-sm rounded-pill {{ ($sort ?? '') === 'popular' ? 'active' : '' }}" href="{{ route('home', array_filter(array_merge($filterQs, ['cat' => request('cat'), 'sort' => 'popular']))) }}">Bestsellers</a>
                <a class="btn btn-outline-secondary btn-sm rounded-pill {{ ($sort ?? '') === 'price_low' ? 'active' : '' }}" href="{{ route('home', array_filter(array_merge($filterQs, ['cat' => request('cat'), 'sort' => 'price_low']))) }}">Price ↑</a>
                <a class="btn btn-outline-secondary btn-sm rounded-pill {{ ($sort ?? '') === 'price_high' ? 'active' : '' }}" href="{{ route('home', array_filter(array_merge($filterQs, ['cat' => request('cat'), 'sort' => 'price_high']))) }}">Price ↓</a>
            </div>
        </div>
        <div class="store-filter-card bb-card-lite p-4 rounded-4 mb-4">
            <form class="row g-3 align-items-end" method="get" action="{{ route('home') }}">
                @if (request('cat'))
                    <input type="hidden" name="cat" value="{{ request('cat') }}">
                @endif
                @if (request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                @if (request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Min price (₹)</label>
                    <input type="number" name="min_price" class="form-control" min="0" step="1" placeholder="{{ (int) ($priceCatalogMin ?? 0) }}" value="{{ request('min_price') }}">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Max price (₹)</label>
                    <input type="number" name="max_price" class="form-control" min="0" step="1" placeholder="{{ (int) ($priceCatalogMax ?? 0) }}" value="{{ request('max_price') }}">
                </div>
                <div class="col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-bloom w-100">Apply filters</button>
                </div>
                <div class="col-sm-6 col-md-3">
                    <a href="{{ route('home', array_filter(['cat' => request('cat'), 'search' => request('search'), 'sort' => request('sort')])) }}" class="btn btn-outline-secondary w-100 rounded-pill">Clear prices</a>
                </div>
            </form>
            @if ((int) ($priceCatalogMax ?? 0) > 0)
                <p class="small text-muted mb-0 mt-2">Catalog spans roughly ₹{{ number_format($priceCatalogMin, 0) }} – ₹{{ number_format($priceCatalogMax, 0) }}.</p>
            @endif
        </div>
    </div>
    <div class="row g-4">
        @forelse ($products as $product)
            <div class="col-sm-6 col-lg-4 col-xl-3">@include('partials.product-card', ['product' => $product])</div>
        @empty
            <div class="col-12"><div class="bb-card p-5 text-center text-muted">No products found.</div></div>
        @endforelse
    </div>
    @include('partials.ad-slot', ['slot' => 'home_bottom', 'class' => 'mt-4'])
    <div class="mt-4">{{ $products->links() }}</div>
</section>
@endsection
