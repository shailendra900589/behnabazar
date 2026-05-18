@extends('layouts.dashboard')
@section('title', 'My account')
@section('dashboard')
<div class="customer-dash-hero rounded-4 p-4 p-lg-5 mb-4 text-white position-relative overflow-hidden d-flex justify-content-between align-items-center">
    <div class="position-relative z-1">
        <p class="customer-dash-muted small text-uppercase fw-semibold mb-2 tracking-wide">Welcome back</p>
        <h1 class="display-6 fw-bold mb-2">Hi, {{ auth()->user()->name }}</h1>
        <p class="mb-0 customer-dash-muted lead" style="max-width: 500px">Track deliveries, spend coins at checkout, and curate your wishlist seamlessly.</p>
    </div>
    <div class="d-none d-lg-block z-1 text-white opacity-25">
        <i class="bi bi-person-bounding-box" style="font-size: 8rem;"></i>
    </div>
    <div class="position-absolute top-0 end-0 h-100 w-50" style="background: radial-gradient(circle at right, rgba(255,255,255,0.1) 0%, transparent 70%);"></div>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-6 col-md-3">
        <div class="customer-stat-tile rounded-4 p-4 h-100">
            <div class="small text-muted text-uppercase fw-semibold mb-1">Coins</div>
            <div class="h3 fw-bold text-bloom mb-0">{{ auth()->user()->coins }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="customer-stat-tile rounded-4 p-4 h-100">
            <div class="small text-muted text-uppercase fw-semibold mb-1">Orders</div>
            <div class="h3 fw-bold mb-0">{{ $orderCount }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="customer-stat-tile rounded-4 p-4 h-100">
            <div class="small text-muted text-uppercase fw-semibold mb-1">In progress</div>
            <div class="h3 fw-bold mb-0">{{ $pendingOrders }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="customer-stat-tile rounded-4 p-4 h-100">
            <div class="small text-muted text-uppercase fw-semibold mb-1">Lifetime spend</div>
            <div class="h3 fw-bold mb-0">₹{{ number_format($lifetimeSpend, 0) }}</div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('home') }}" class="btn btn-bloom btn-sm rounded-pill"><i class="bi bi-shop me-1"></i>Continue shopping</a>
    <a href="{{ route('wishlist') }}" class="btn btn-outline-dark btn-sm rounded-pill"><i class="bi bi-heart me-1"></i>Wishlist ({{ $wishlistCount }})</a>
    <a href="{{ route('checkout') }}" class="btn btn-outline-dark btn-sm rounded-pill"><i class="bi bi-bag-check me-1"></i>Checkout</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="table-card">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h2 class="h5 fw-bold mb-0">Recent orders</h2>
                <a href="{{ route('orders') }}" class="small fw-semibold text-bloom text-decoration-none">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($order->product_name, 36) }}</div>
                                    <div class="small text-muted">#{{ $order->id }}</div>
                                </td>
                                <td><span class="badge badge-soft">{{ str_replace('_', ' ', $order->status) }}</span></td>
                                <td class="fw-semibold">₹{{ number_format($order->total_price, 2) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('orders.track', $order) }}" class="btn btn-soft btn-sm rounded-pill">Track</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-5">
                                    <i class="bi bi-bag display-6 d-block mb-2 opacity-50"></i>
                                    No orders yet — start from the shop.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="bb-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-bold mb-0">Wishlist</h2>
                <span class="badge rounded-pill bg-light text-dark border">{{ $wishlistCount }}</span>
            </div>
            <div class="vstack gap-0">
                @forelse ($wishlist as $item)
                    <a href="{{ route('product.show', $item->product) }}" class="d-flex gap-3 align-items-center border-bottom py-3 text-decoration-none text-body customer-wish-row">
                        <img src="{{ $item->product->imageUrl() }}" alt="" class="rounded-3 border" style="width:64px;height:64px;object-fit:cover">
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-semibold text-truncate">{{ $item->product->title }}</div>
                            <div class="small text-bloom fw-bold">₹{{ number_format($item->product->price, 2) }}</div>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                @empty
                    <p class="text-muted mb-0 py-4 text-center">Save items you love — they will appear here.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if (isset($trendingProducts) && $trendingProducts->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 fw-bold mb-0">🔥 Trending right now</h2>
                <a href="{{ route('home') }}" class="small fw-semibold text-bloom text-decoration-none">Shop more</a>
            </div>
            <div class="row g-3">
                @foreach ($trendingProducts as $product)
                    <div class="col-sm-6 col-lg-3">
                        @include('partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@endsection
