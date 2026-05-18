@extends('layouts.app')
@section('title', $vendor->shop_name ?? 'Vendor Store')
@section('content')
<section class="container py-4 py-lg-5">
    <div class="bb-card rounded-4 p-4 p-lg-5 mb-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--bb-ink) 0%, #312e81 100%); box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="row align-items-center position-relative z-1">
            <div class="col-md-8 text-center text-md-start mb-4 mb-md-0">
                <div class="d-inline-block bg-white p-2 rounded-circle mb-3 shadow-sm">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-dark" style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold;">
                        {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                    </div>
                </div>
                <h1 class="display-5 fw-bolder mb-2 d-flex align-items-center justify-content-center justify-content-md-start gap-2 text-white">
                    {{ $vendor->shop_name }} 
                    <i class="bi bi-patch-check-fill text-warning" style="font-size: 1.75rem;" title="Verified Seller"></i>
                </h1>
                <p class="mb-0 text-white-50 lead"><i class="bi bi-geo-alt-fill me-2 text-danger"></i>{{ $vendor->city ?? 'Local Business' }}, {{ $vendor->pincode ?? 'India' }}</p>
            </div>
            <div class="col-md-4">
                <div class="row g-2">
                    <div class="col-4">
                        <div class="bg-white bg-opacity-10 rounded-4 p-3 text-center border border-white border-opacity-10" style="backdrop-filter: blur(10px);">
                            <div class="h3 fw-bold mb-0 text-white">{{ $products->total() }}</div>
                            <div class="small text-white-50 text-uppercase tracking-wider" style="font-size: 0.7rem;">Products</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white bg-opacity-10 rounded-4 p-3 text-center border border-white border-opacity-10" style="backdrop-filter: blur(10px);">
                            <div class="h3 fw-bold mb-0 text-white">{{ $vendor->created_at->format('Y') }}</div>
                            <div class="small text-white-50 text-uppercase tracking-wider" style="font-size: 0.7rem;">Joined</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="bg-white bg-opacity-10 rounded-4 p-3 text-center border border-white border-opacity-10" style="backdrop-filter: blur(10px);">
                            <div class="h3 fw-bold mb-0 text-warning"><i class="bi bi-star-fill"></i></div>
                            <div class="small text-white-50 text-uppercase tracking-wider" style="font-size: 0.7rem;">Verified</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h2 class="h4 fw-bold mb-0">All Products by {{ $vendor->shop_name }}</h2>
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill bg-light text-dark border px-3 py-2">{{ $products->total() }} items</span>
            <div class="dropdown">
                <button class="btn btn-soft btn-sm rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-sort-down me-1"></i> Sort
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 mt-2 p-2">
                    <li><a class="dropdown-item rounded-3 py-2 {{ request('sort','new') === 'new' ? 'active' : '' }}" href="{{ route('vendor.shop', $vendor) }}?sort=new">Newest First</a></li>
                    <li><a class="dropdown-item rounded-3 py-2 {{ request('sort') === 'price_low' ? 'active' : '' }}" href="{{ route('vendor.shop', $vendor) }}?sort=price_low">Price: Low to High</a></li>
                    <li><a class="dropdown-item rounded-3 py-2 {{ request('sort') === 'price_high' ? 'active' : '' }}" href="{{ route('vendor.shop', $vendor) }}?sort=price_high">Price: High to Low</a></li>
                    <li><a class="dropdown-item rounded-3 py-2 {{ request('sort') === 'popular' ? 'active' : '' }}" href="{{ route('vendor.shop', $vendor) }}?sort=popular">Most Popular</a></li>
                </ul>
            </div>
        </div>
    </div>

    @if ($products->count() > 0)
        <div class="row g-4 mb-5">
            @foreach ($products as $product)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-center">
            {{ $products->withQueryString()->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-box display-1 text-muted opacity-50 mb-3 d-block"></i>
            <h4 class="text-muted">No products available at the moment.</h4>
            <p class="text-muted">This vendor hasn't listed any products yet. Check back soon!</p>
        </div>
    @endif
</section>
@endsection
