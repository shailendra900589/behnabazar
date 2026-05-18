@extends('layouts.app')
@section('title', 'All Vendors & Shops')
@section('content')
<section class="container py-4 py-lg-5">
    <div class="bb-card rounded-4 p-4 p-lg-5 mb-5 text-white position-relative overflow-hidden d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, var(--bb-ink) 0%, #1e1b4b 100%);">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="position-relative z-1 text-center text-md-start w-100">
            <h1 class="display-5 fw-bold mb-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2">Official Verified Vendors <i class="bi bi-patch-check-fill text-warning"></i></h1>
            <p class="mb-0 text-white-50 lead" style="max-width: 600px;">Explore storefronts from verified sellers across grocery, electronics, clothing, home, beauty, handmade goods, and daily essentials. Every vendor goes through onboarding before selling.</p>
        </div>
        <div class="d-none d-lg-block z-1 text-white opacity-25 me-5 pe-5">
            <i class="bi bi-shop" style="font-size: 8rem;"></i>
        </div>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-8 col-lg-6">
            <form method="GET" action="{{ route('home') }}" class="d-flex bg-white rounded-pill p-2 shadow-sm border">
                <input type="text" name="search" class="form-control border-0 bg-transparent px-4" placeholder="Search by shop name or city..." value="{{ request('search') }}">
                <button class="btn btn-dark rounded-pill px-4" type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse($vendors as $vendor)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('vendor.shop', $vendor) }}" class="text-decoration-none group">
                    <div class="bb-card p-4 rounded-4 h-100 position-relative overflow-hidden border transition-all duration-300 hover:shadow-lg">
                        <div class="position-absolute top-0 end-0 p-3 opacity-25 group-hover:opacity-100 transition-opacity">
                            <i class="bi bi-arrow-up-right-circle-fill fs-3 text-bloom"></i>
                        </div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-dark fw-bold border" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-1">{{ $vendor->shop_name }} <i class="bi bi-patch-check-fill text-primary small" title="Verified"></i></h3>
                                <span class="text-muted small"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $vendor->city ?? 'Local Business' }}</span>
                            </div>
                        </div>
                        <hr class="opacity-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark border">{{ $vendor->products_count }} active products</span>
                            <span class="text-muted small">Joined {{ $vendor->created_at->format('Y') }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-shop-window display-1 text-muted opacity-25 mb-3 d-block"></i>
                <h3 class="fw-bold text-muted">No vendors found</h3>
                <p class="text-muted">Currently there are no active vendors on the platform.</p>
            </div>
        @endforelse
    </div>
    
    <div class="mt-5 d-flex justify-content-center">
        {{ $vendors->links() }}
    </div>
</section>
@endsection
