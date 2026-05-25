@extends('layouts.app')
@section('title', ($siteBranding['name'] ?? config('app.name')).' — Multipurpose Marketplace')
@section('content')
@php
    $filterQs = array_filter([
        'search' => request('search'),
        'sort' => request('sort'),
        'min_price' => request('min_price'),
        'max_price' => request('max_price'),
    ], fn ($v) => $v !== null && $v !== '');
@endphp
<section class="container py-2 py-md-4 py-lg-5">
    @include('partials.ad-slot', ['slot' => 'home_top', 'class' => 'mb-2 mb-md-4'])

    <!-- Category strip - Flipkart/Meesho style -->
    <div class="bb-category-strip mb-2 mb-md-4">
        <a href="{{ route('home', ['cat' => 'grocery']) }}" class="bb-cat-item">
            <div class="bb-cat-icon"><i class="bi bi-basket2"></i></div>
            <span>Grocery</span>
        </a>
        <a href="{{ route('home', ['cat' => 'organic']) }}" class="bb-cat-item">
            <div class="bb-cat-icon"><i class="bi bi-flower1"></i></div>
            <span>Organic</span>
        </a>
        <a href="{{ route('home', ['cat' => 'electronics']) }}" class="bb-cat-item">
            <div class="bb-cat-icon"><i class="bi bi-cpu"></i></div>
            <span>Electronics</span>
        </a>
        <a href="{{ route('home', ['cat' => 'fashion']) }}" class="bb-cat-item">
            <div class="bb-cat-icon"><i class="bi bi-bag-heart"></i></div>
            <span>Fashion</span>
        </a>
        <a href="{{ route('home', ['cat' => 'home-living']) }}" class="bb-cat-item">
            <div class="bb-cat-icon"><i class="bi bi-house-heart"></i></div>
            <span>Home</span>
        </a>
        <a href="{{ route('home', ['cat' => 'beauty']) }}" class="bb-cat-item">
            <div class="bb-cat-icon"><i class="bi bi-stars"></i></div>
            <span>Beauty</span>
        </a>
        @if($referralEnabled ?? false)
        <a href="{{ auth()->check() ? route('dashboard').'#referralProgramCard' : route('register') }}" class="bb-cat-item">
            <div class="bb-cat-icon bb-cat-icon--accent"><i class="bi bi-gift"></i></div>
            <span>Refer</span>
        </a>
        @endif
    </div>

    @if ($banners->isNotEmpty())
        <div id="homeHero" class="carousel slide bb-card overflow-hidden rounded-4 mb-3 mb-md-5 shadow-sm" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach ($banners as $i => $b)
                    <button type="button" data-bs-target="#homeHero" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach ($banners as $i => $banner)
                    @php
                        $bannerSrc = str_starts_with($banner->image, 'http') ? $banner->image : asset('storage/'.$banner->image);
                    @endphp
                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                        <a href="{{ $banner->link ?: '#' }}" class="d-block position-relative">
                            <img src="{{ $bannerSrc }}" class="d-block w-100 hero-carousel-img" alt="Banner">
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
        <div class="hero p-3 p-lg-5 mb-3 mb-md-5 rounded-4">
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

    <!-- Compact trust strip - desktop only, mobile uses footer badges -->
    <div class="d-none d-md-flex justify-content-center gap-4 mb-4 py-2 border-top border-bottom small text-muted">
        <span><i class="bi bi-patch-check text-primary me-1"></i>Verified sellers</span>
        <span><i class="bi bi-truck text-success me-1"></i>Fast delivery</span>
        <span><i class="bi bi-coin text-warning me-1"></i>Coin rewards</span>
        <span><i class="bi bi-shield-check text-bloom me-1"></i>Secure payments</span>
    </div>

    @if ($categories->isNotEmpty())
        @include('partials.category-chips', ['categories' => $categories, 'filterQs' => $filterQs])
    @endif

    @if (! empty($flashDeal))
        <div class="bb-card p-3 p-md-4 rounded-4 mb-3 mb-md-5 shadow-sm position-relative overflow-hidden" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); border-left: 4px solid #d9534f;">
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
                    <div class="mb-3">
                        @php
                            $flashSale = $flashDeal->compare_at_price ? (float) $flashDeal->price : (float) $flashDeal->price * 0.8;
                            $flashMrp = $flashDeal->compare_at_price ? (float) $flashDeal->compare_at_price : (float) $flashDeal->price;
                        @endphp
                        @include('partials.product-price', ['product' => $flashDeal, 'variantSale' => $flashSale, 'variantMrp' => $flashMrp, 'size' => 'lg'])
                    </div>
                    <a href="{{ route('product.show', $flashDeal) }}" class="btn btn-dark rounded-pill px-4">Claim Deal <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
                <div class="col-md-4 text-center mt-4 mt-md-0">
                    <img src="{{ $flashDeal->imageUrl() }}" alt="{{ $flashDeal->title }}" class="img-fluid rounded-3 shadow-sm" style="max-height: 200px; object-fit: contain;">
                </div>
            </div>
        </div>
        @verbatim
        <script>
            (function () {
                var time = 5 * 3600 + 59 * 60 + 59;
                setInterval(function () {
                    var h = Math.floor(time / 3600).toString().padStart(2, '0');
                    var m = Math.floor((time % 3600) / 60).toString().padStart(2, '0');
                    var s = (time % 60).toString().padStart(2, '0');
                    var el = document.getElementById('flash-timer');
                    if (el) { el.innerText = 'Ends in: ' + h + ':' + m + ':' + s; }
                    if (time > 0) { time--; }
                }, 1000);
            })();
        </script>
        @endverbatim
    @endif

    @if ($newArrivals->isNotEmpty())
        <div class="d-flex align-items-end justify-content-between mb-3">
            <h2 class="fw-bold mb-0">New arrivals</h2>
            <a href="#products" class="small text-bloom fw-semibold text-decoration-none">View all</a>
        </div>
        <div class="row g-3 g-md-4 mb-4 mb-md-5 bb-product-rail">
            @foreach ($newArrivals as $product)
                <div class="col-6 col-sm-6 col-lg-3 bb-product-rail-item">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    @endif

    @include('partials.ad-slot', ['slot' => 'home_mid', 'class' => 'mb-3 mb-md-5'])

    @if (isset($hotProducts) && $hotProducts->isNotEmpty())
        <div class="d-flex align-items-end justify-content-between mb-3">
            <h2 class="fw-bold mb-0">Trending now</h2>
            <span class="small text-muted">Based on recent orders</span>
        </div>
        <div class="row g-3 g-md-4 mb-4 mb-md-5 bb-product-rail">
            @foreach ($hotProducts as $product)
                <div class="col-6 col-sm-6 col-lg-3 bb-product-rail-item">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    @endif

    @if (isset($recentlyViewed) && $recentlyViewed->isNotEmpty())
        <div class="d-flex align-items-end justify-content-between mb-3">
            <h2 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-muted"></i>Recently viewed</h2>
        </div>
        <div class="row g-3 g-md-4 mb-4 mb-md-5 bb-product-rail">
            @foreach ($recentlyViewed as $product)
                <div class="col-6 col-sm-6 col-lg-3 bb-product-rail-item">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    @endif

    <div id="products" class="mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
            <div>
                <h2 class="fw-bold mb-1">Shop products</h2>
                <p class="text-muted mb-0">Sort, filter by price, and shop approved listings across all categories.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap bb-sort-rail">
                <a class="btn btn-outline-secondary btn-sm rounded-pill {{ ($sort ?? 'new') === 'new' ? 'active' : '' }}" href="{{ route('home', array_filter(array_merge($filterQs, ['cat' => request('cat'), 'sort' => 'new']))) }}">Newest</a>
                <a class="btn btn-outline-secondary btn-sm rounded-pill {{ ($sort ?? '') === 'popular' ? 'active' : '' }}" href="{{ route('home', array_filter(array_merge($filterQs, ['cat' => request('cat'), 'sort' => 'popular']))) }}">Bestsellers</a>
                <a class="btn btn-outline-secondary btn-sm rounded-pill {{ ($sort ?? '') === 'price_low' ? 'active' : '' }}" href="{{ route('home', array_filter(array_merge($filterQs, ['cat' => request('cat'), 'sort' => 'price_low']))) }}">Price ↑</a>
                <a class="btn btn-outline-secondary btn-sm rounded-pill {{ ($sort ?? '') === 'price_high' ? 'active' : '' }}" href="{{ route('home', array_filter(array_merge($filterQs, ['cat' => request('cat'), 'sort' => 'price_high']))) }}">Price ↓</a>
            </div>
        </div>
        <div class="store-filter-card bb-card-lite p-3 p-md-4 rounded-4 mb-4">
            <form class="row g-2 g-md-3 align-items-end store-filter-form" method="get" action="{{ route('home') }}">
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
                    <input type="number" name="min_price" class="form-control" min="0" step="1" placeholder="{{ (int) ($priceCatalogMax ?? 0) > 0 ? (int) $priceCatalogMin : 'Min' }}" value="{{ request()->has('min_price') && request('min_price') !== '' && (float) request('min_price') > 0 ? request('min_price') : '' }}">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label small text-muted mb-1">Max price (₹)</label>
                    <input type="number" name="max_price" class="form-control" min="0" step="1" placeholder="{{ (int) ($priceCatalogMax ?? 0) > 0 ? (int) $priceCatalogMax : 'Max' }}" value="{{ request()->has('max_price') && request('max_price') !== '' && (float) request('max_price') > 0 ? request('max_price') : '' }}">
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
    <div class="row g-3 g-md-4 bb-product-grid">
        @forelse ($products as $index => $product)
            <div class="col-6 col-md-6 col-lg-4 col-xl-3">@include('partials.product-card', ['product' => $product])</div>
            @if ($index === 3)
                <div class="col-12">@include('partials.ad-slot', ['slot' => 'home_grid_1', 'ads' => $ads, 'card' => true, 'class' => 'mb-2'])</div>
            @elseif ($index === 7)
                <div class="col-12">@include('partials.ad-slot', ['slot' => 'home_grid_2', 'ads' => $ads, 'card' => true])</div>
            @elseif ($index === 11)
                <div class="col-sm-6 col-lg-4 col-xl-3">@include('partials.ad-slot', ['slot' => 'home_grid_3', 'ads' => $ads, 'card' => true])</div>
            @endif
        @empty
            <div class="col-12"><div class="bb-card p-5 text-center text-muted">No products found.</div></div>
        @endforelse
    </div>
    @include('partials.ad-slot', ['slot' => 'home_bottom', 'class' => 'mt-4'])
    <div class="mt-4">{{ $products->links() }}</div>
</section>
@endsection
