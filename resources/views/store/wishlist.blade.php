@extends('layouts.app')
@section('title', 'My Wishlist')
@section('content')
<section class="container py-3 py-md-4 py-lg-5 bb-wishlist-page">
    <div class="bb-card rounded-4 p-3 p-md-4 p-lg-5 mb-3 mb-md-5 text-white position-relative overflow-hidden d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, var(--bb-ink) 0%, #7e22ce 100%);">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="position-relative z-1 text-center text-md-start w-100">
            <h1 class="bb-wishlist-title fw-bold mb-2 mb-md-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2 flex-wrap">My Wishlist <i class="bi bi-heart-fill text-danger"></i></h1>
            <p class="mb-0 text-white-50 small">Save products you love and add them to cart anytime.</p>
        </div>
        <div class="d-none d-lg-block z-1 text-white opacity-25 me-5 pe-5">
            <i class="bi bi-bookmark-heart" style="font-size: 8rem;"></i>
        </div>
    </div>

    <div class="row g-2 g-md-3 g-lg-4">
        @forelse($items as $item)
            @if($item->product)
                <div class="col-6 col-sm-6 col-lg-3">
                    @include('partials.product-card', ['product' => $item->product])
                </div>
            @endif
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-heart-break display-1 text-muted opacity-25 mb-3 d-block"></i>
                <h3 class="fw-bold text-muted">Your wishlist is empty</h3>
                <p class="text-muted">Browse the catalog and tap the heart icon to save items here.</p>
                <a href="{{ route('home') }}#products" class="btn btn-dark rounded-pill px-4 mt-3">Explore Products</a>
            </div>
        @endforelse
    </div>
</section>
@endsection
