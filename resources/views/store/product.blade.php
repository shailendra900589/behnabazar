@extends('layouts.app')
@section('title', $product->title)
@section('content')
<section class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Shop</a></li>
            @if ($product->category)
                <li class="breadcrumb-item"><a href="{{ route('home', ['cat' => $product->category->slug]) }}" class="text-decoration-none">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($product->title, 48) }}</li>
        </ol>
    </nav>

    <div class="row g-5 align-items-start">
        <div class="col-lg-6">
            <div class="product-hero-card bb-card p-3 p-lg-4 rounded-4 shadow-sm mb-3">
                <div class="position-relative overflow-hidden rounded-4 zoom-container" style="cursor: crosshair;">
                    <img id="mainProductImage" src="{{ $product->imageUrl() }}" class="w-100 product-detail-img zoom-image" alt="{{ $product->title }}" style="transition: transform 0.1s ease; transform-origin: center center;">
                    <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2" style="pointer-events: none;">
                        <span class="badge rounded-pill bg-dark bg-opacity-75">QC approved</span>
                        @if (($product->orders_count ?? $product->orders()->count()) >= 3)
                            <span class="badge rounded-pill bg-danger border border-white shadow-sm"><i class="bi bi-fire me-1"></i>Trending</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const container = document.querySelector('.zoom-container');
                    const img = document.querySelector('.zoom-image');
                    
                    container.addEventListener('mousemove', (e) => {
                        const rect = container.getBoundingClientRect();
                        const x = ((e.clientX - rect.left) / rect.width) * 100;
                        const y = ((e.clientY - rect.top) / rect.height) * 100;
                        
                        img.style.transformOrigin = `${x}% ${y}%`;
                        img.style.transform = 'scale(2)';
                    });
                    
                    container.addEventListener('mouseleave', () => {
                        img.style.transform = 'scale(1)';
                        setTimeout(() => { img.style.transformOrigin = 'center center'; }, 100);
                    });
                });
            </script>
            
            <div class="d-flex gap-2 overflow-auto pb-2">
                @php
                    $images = [$product->imageUrl()];
                    if ($product->image2) $images[] = str_starts_with($product->image2, 'http') ? $product->image2 : asset('storage/'.$product->image2);
                    if ($product->image3) $images[] = str_starts_with($product->image3, 'http') ? $product->image3 : asset('storage/'.$product->image3);
                    if ($product->image4) $images[] = str_starts_with($product->image4, 'http') ? $product->image4 : asset('storage/'.$product->image4);
                @endphp
                @if (count($images) > 1)
                    @foreach ($images as $idx => $imgUrl)
                        <img src="{{ $imgUrl }}" class="rounded-3 border cursor-pointer product-thumb {{ $idx === 0 ? 'border-bloom border-2' : '' }}" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer; transition: all 0.2s ease;" onclick="document.getElementById('mainProductImage').src=this.src; document.querySelectorAll('.product-thumb').forEach(t => { t.classList.remove('border-bloom','border-2'); t.style.opacity='0.7'; }); this.classList.add('border-bloom','border-2'); this.style.opacity='1';" onmouseover="this.style.opacity='1'" onmouseout="if(!this.classList.contains('border-bloom')) this.style.opacity='0.7'" alt="View {{ $idx + 1 }}">
                    @endforeach
                    <span class="badge bg-dark bg-opacity-75 rounded-pill align-self-center ms-1 px-3">{{ count($images) }} photos</span>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <span class="badge badge-soft rounded-pill mb-3">{{ $product->category->name ?? 'Product' }}</span>
            <h1 class="display-6 fw-bold lh-sm mb-2">{{ $product->title }}</h1>
            <p class="text-muted mb-3">
                <i class="bi bi-shop me-1"></i>Sold by 
                @if($product->vendor)
                    <a href="{{ route('vendor.shop', $product->vendor) }}" class="fw-bold text-bloom text-decoration-none">{{ $product->vendor->shop_name }} <i class="bi bi-patch-check-fill text-primary" title="Verified Seller"></i></a>
                @else
                    <strong>Behna Bazar Official <i class="bi bi-patch-check-fill text-primary" title="Verified Seller"></i></strong>
                @endif
                <span class="ms-3"><i class="bi bi-star-fill text-warning me-1"></i>{{ number_format($product->averageRating(), 1) }} / 5 ({{ $product->reviews->count() }} reviews)</span>
            </p>
            <div class="d-flex align-items-baseline gap-3 mb-3">
                <span class="display-6 fw-bold text-bloom" id="productPriceDisplay">₹{{ number_format($product->price, 2) }}</span>
                <span class="small text-muted">Inclusive of handling</span>
            </div>
            
            <div class="mb-4 d-flex align-items-center gap-2 text-muted small">
                <i class="bi bi-eye text-primary"></i> 
                <strong>{{ number_format($product->view_count) }}</strong> people have viewed this product
            </div>

            @if($product->variants && $product->variants->count() > 0)
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Variant</label>
                    <select name="variant_id" form="addToCartForm" class="form-select w-auto" onchange="updatePrice(this)">
                        <option value="">Default (₹{{ number_format($product->price, 2) }})</option>
                        @foreach($product->variants as $variant)
                            <option value="{{ $variant->id }}" data-price="{{ $variant->price ?? $product->price }}">
                                {{ $variant->color }} {{ $variant->size }} - ₹{{ number_format($variant->price ?? $product->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <script>
                    function updatePrice(select) {
                        const option = select.options[select.selectedIndex];
                        const price = option.getAttribute('data-price');
                        if (price) {
                            document.getElementById('productPriceDisplay').innerText = '₹' + parseFloat(price).toFixed(2);
                        } else {
                            document.getElementById('productPriceDisplay').innerText = '₹{{ number_format($product->price, 2) }}';
                        }
                    }
                </script>
            @endif

            <div class="mb-4 bg-light p-3 rounded-3" style="max-width: 400px;">
                <label class="form-label fw-bold small mb-1"><i class="bi bi-geo-alt-fill me-1 text-danger"></i>Check Delivery</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="pincodeInput" class="form-control" placeholder="Enter PIN code" maxlength="6">
                    <button class="btn btn-outline-dark" type="button" onclick="checkPincode()">Check</button>
                </div>
                <div id="pincodeResult" class="small mt-2" style="display:none;"></div>
            </div>
            <script>
                function checkPincode() {
                    const pin = document.getElementById('pincodeInput').value;
                    const res = document.getElementById('pincodeResult');
                    res.style.display = 'block';
                    if (pin.length === 6 && !isNaN(pin)) {
                        res.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Delivery available by ' + new Date(Date.now() + 3*24*60*60*1000).toLocaleDateString() + '</span>';
                    } else {
                        res.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Please enter a valid 6-digit PIN code.</span>';
                    }
                }
            </script>

            <div class="d-flex flex-wrap gap-2 mb-4">
                <form id="addToCartForm" data-ajax-form action="{{ route('cart.add', $product) }}" method="post" class="d-flex gap-2 flex-wrap align-items-center">
                    @csrf
                    <input class="form-control" style="max-width: 100px" type="number" min="1" max="20" name="quantity" value="1" aria-label="Quantity">
                    <button type="submit" class="btn btn-bloom btn-lg px-4"><i class="bi bi-bag-plus me-1"></i>Add to cart</button>
                    <button type="submit" name="buy_now" value="1" onclick="this.form.removeAttribute('data-ajax-form');" class="btn btn-dark btn-lg px-4"><i class="bi bi-lightning-charge me-1"></i>Buy Now</button>
                </form>
                @auth
                    <button type="button" class="btn btn-soft btn-lg" data-wishlist-toggle="{{ route('wishlist.toggle', $product) }}" title="Add to Wishlist"><i class="bi bi-heart"></i></button>
                @endauth
                <div class="dropdown">
                    <button class="btn btn-soft btn-lg dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Share Product">
                        <i class="bi bi-share"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4">
                        <li><h6 class="dropdown-header">Share via</h6></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="https://api.whatsapp.com/send?text={{ urlencode('Check out '.$product->title.' on Behna Bazar! '.url()->current()) }}" target="_blank"><i class="bi bi-whatsapp text-success"></i> WhatsApp</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank"><i class="bi bi-facebook text-primary"></i> Facebook</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="https://twitter.com/intent/tweet?text={{ urlencode('Check out '.$product->title.' on Behna Bazar!') }}&url={{ urlencode(url()->current()) }}" target="_blank"><i class="bi bi-twitter-x text-dark"></i> X (Twitter)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><button class="dropdown-item d-flex align-items-center gap-2" type="button" onclick="navigator.clipboard.writeText(window.location.href); alert('Product link copied to clipboard!');"><i class="bi bi-link-45deg"></i> Copy Link</button></li>
                    </ul>
                </div>
            </div>

            <ul class="nav nav-tabs nav-fill product-info-tabs mb-3" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-top-3" id="tab-desc-tab" data-bs-toggle="tab" data-bs-target="#tab-desc" type="button" role="tab">Details</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-top-3" id="tab-reviews-tab" data-bs-toggle="tab" data-bs-target="#tab-reviews" type="button" role="tab">Reviews ({{ $product->reviews->count() }})</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-top-3" id="tab-ship-tab" data-bs-toggle="tab" data-bs-target="#tab-ship" type="button" role="tab">Shipping</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-top-3" id="tab-qa-tab" data-bs-toggle="tab" data-bs-target="#tab-qa" type="button" role="tab">Q&A ({{ $product->questions->where('status', 'answered')->count() }})</button>
                </li>
            </ul>
            <div class="tab-content bb-card-lite p-4 rounded-bottom-4 rounded-end-4 border">
                <div class="tab-pane fade show active" id="tab-desc" role="tabpanel">
                    <p class="lead text-muted mb-0">{{ $product->description }}</p>
                </div>
                <div class="tab-pane fade" id="tab-reviews" role="tabpanel">
                    @php
                        $totalReviews = $product->reviews->where('is_approved', true)->count();
                        $avgRating = $product->averageRating();
                        $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                        foreach($product->reviews->where('is_approved', true) as $r) { $ratingCounts[$r->rating]++; }
                    @endphp

                    @if($totalReviews > 0)
                    <div class="row g-4 mb-5 align-items-center bg-white p-4 rounded-4 border shadow-sm">
                        <div class="col-md-4 text-center border-md-end">
                            <h2 class="display-3 fw-bold text-dark mb-0">{{ number_format($avgRating, 1) }}</h2>
                            <div class="text-warning fs-5 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            <p class="text-muted small mb-0">Based on {{ $totalReviews }} reviews</p>
                        </div>
                        <div class="col-md-8">
                            @foreach([5, 4, 3, 2, 1] as $star)
                                @php $pct = $totalReviews > 0 ? ($ratingCounts[$star] / $totalReviews) * 100 : 0; @endphp
                                <div class="d-flex align-items-center mb-2">
                                    <div class="text-muted small w-25 text-end pe-2">{{ $star }} Stars</div>
                                    <div class="progress w-75 rounded-pill" style="height: 8px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <div class="text-muted small ms-2" style="width: 30px;">{{ $ratingCounts[$star] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @auth
                        <form method="post" action="{{ route('product.review', $product) }}" class="mb-4 bg-white p-3 rounded-3 border">
                            @csrf
                            <h5 class="fw-bold mb-3">Write a review</h5>
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <select name="rating" class="form-select w-auto">
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Good</option>
                                    <option value="3">3 - Average</option>
                                    <option value="2">2 - Poor</option>
                                    <option value="1">1 - Terrible</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comment (Optional)</label>
                                <textarea name="comment" class="form-control" rows="2" placeholder="Tell others what you think"></textarea>
                            </div>
                            <button type="submit" class="btn btn-bloom btn-sm">Submit Review</button>
                        </form>
                    @else
                        <div class="alert alert-light border text-center">
                            Please <a href="{{ route('login') }}" class="fw-bold text-bloom text-decoration-none">login</a> to write a review.
                        </div>
                    @endauth

                    <div class="vstack gap-3 mt-4">
                        @forelse ($product->reviews as $review)
                            @if($review->is_approved)
                            <div class="border-bottom pb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-semibold">{{ $review->user->name }}</span>
                                    <span class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                        @endfor
                                    </span>
                                </div>
                                @if($review->comment)
                                    <p class="text-muted small mb-0">{{ $review->comment }}</p>
                                @endif
                                <div class="small text-muted mt-1 opacity-50">{{ $review->created_at->diffForHumans() }}</div>
                            </div>
                            @endif
                        @empty
                            <p class="text-muted small text-center mb-0">No reviews yet. Be the first to review this product!</p>
                        @endforelse
                    </div>
                </div>
                <div class="tab-pane fade" id="tab-ship" role="tabpanel">
                    <ul class="mb-3 ps-3 text-muted">
                        <li class="mb-2">Local delivery partners update status at every milestone.</li>
                        <li class="mb-2">Typical dispatch within 1–2 business days after payment confirmation.</li>
                        <li>Report issues within 48 hours of delivery for the fastest resolution.</li>
                    </ul>
                    <a href="{{ route('local-delivery') }}" class="small fw-semibold text-bloom text-decoration-none me-3">Local delivery details <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ route('returns-policy') }}" class="small fw-semibold text-bloom text-decoration-none">Returns policy <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="tab-pane fade" id="tab-qa" role="tabpanel">
                    @auth
                        <form method="post" action="{{ route('product.question', $product) }}" class="mb-4 bg-light p-3 rounded-3 border">
                            @csrf
                            <h5 class="fw-bold mb-3">Have a question?</h5>
                            <div class="mb-3">
                                <textarea name="question" class="form-control bg-white" rows="2" placeholder="Ask something about this product..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark btn-sm">Ask Question</button>
                        </form>
                    @else
                        <div class="alert alert-light border text-center">
                            Please <a href="{{ route('login') }}" class="fw-bold text-bloom text-decoration-none">login</a> to ask a question.
                        </div>
                    @endauth

                    <div class="vstack gap-3 mt-4">
                        @forelse ($product->questions()->where('status', 'answered')->get() as $q)
                            <div class="bg-white p-3 rounded-4 border shadow-sm">
                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge bg-dark">Q</span>
                                    <strong class="text-dark">{{ $q->question }}</strong>
                                </div>
                                <div class="d-flex gap-2 ps-2 border-start border-3 border-primary">
                                    <span class="badge bg-primary">A</span>
                                    <div class="text-muted small">{{ $q->answer }}</div>
                                </div>
                                <div class="small text-muted mt-2 text-end" style="font-size: 0.7rem;">Asked by {{ $q->user->name }}</div>
                            </div>
                        @empty
                            <p class="text-muted small text-center mb-0">No questions asked yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <hr class="my-5 opacity-25">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h4 fw-bold mb-0">You may also like</h2>
            <a href="{{ route('home', ['cat' => $product->category?->slug]) }}" class="small fw-semibold text-bloom text-decoration-none">More in {{ $product->category->name ?? 'shop' }}</a>
        </div>
        <div class="row g-4">
            @foreach ($related as $item)
                <div class="col-sm-6 col-lg-3">@include('partials.product-card', ['product' => $item])</div>
            @endforeach
        </div>
    @endif

    @php
        $recentIds = session()->get('recently_viewed', []);
        $recentProducts = !empty($recentIds)
            ? \App\Models\Product::whereIn('id', $recentIds)->where('qc_status','approved')->whereKeyNot($product->id)->get()->sortBy(fn($p) => array_search($p->id, $recentIds))->take(6)
            : collect();
    @endphp
    @if ($recentProducts->isNotEmpty())
        <hr class="my-5 opacity-25">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <h2 class="h4 fw-bold mb-0"><i class="bi bi-clock-history me-2 text-muted"></i>Recently Viewed</h2>
        </div>
        <div class="horizontal-scroller">
            @foreach ($recentProducts as $recentItem)
                <div style="width: 240px;">@include('partials.product-card', ['product' => $recentItem])</div>
            @endforeach
        </div>
    @endif

    <!-- Trust Badges -->
    <div class="row g-3 mt-5 mb-4">
        <div class="col-6 col-md-3">
            <div class="trust-badge">
                <i class="bi bi-shield-check text-success"></i>
                <div>
                    <div class="fw-bold small">100% Authentic</div>
                    <div class="text-muted" style="font-size: 0.75rem;">QC Verified products</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="trust-badge">
                <i class="bi bi-truck text-primary"></i>
                <div>
                    <div class="fw-bold small">Fast Delivery</div>
                    <div class="text-muted" style="font-size: 0.75rem;">1-3 business days</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="trust-badge">
                <i class="bi bi-arrow-repeat text-warning"></i>
                <div>
                    <div class="fw-bold small">Easy Returns</div>
                    <div class="text-muted" style="font-size: 0.75rem;">48h return window</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="trust-badge">
                <i class="bi bi-lock-fill text-bloom"></i>
                <div>
                    <div class="fw-bold small">Secure Payments</div>
                    <div class="text-muted" style="font-size: 0.75rem;">End-to-end encrypted</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sticky Bottom Add-to-Cart Bar -->
<div class="sticky-product-bar" id="stickyProductBar">
    <div class="container d-flex justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3 min-w-0">
            <img src="{{ $product->imageUrl() }}" alt="" class="rounded-3 d-none d-md-block" style="width:48px;height:48px;object-fit:cover;">
            <div class="min-w-0">
                <div class="fw-bold text-truncate">{{ $product->title }}</div>
                <div class="text-bloom fw-bold">₹{{ number_format($product->price, 2) }}</div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            <form data-ajax-form action="{{ route('cart.add', $product) }}" method="post">
                @csrf<input type="hidden" name="quantity" value="1">
                <button class="btn btn-bloom rounded-pill px-4 fw-bold shadow-sm"><i class="bi bi-bag-plus me-1"></i> Add to Cart</button>
            </form>
            <form action="{{ route('cart.add', $product) }}" method="post">
                @csrf<input type="hidden" name="quantity" value="1"><input type="hidden" name="buy_now" value="1">
                <button class="btn btn-dark rounded-pill px-4 fw-bold d-none d-md-inline-block"><i class="bi bi-lightning-charge me-1"></i> Buy Now</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Show sticky bar when original Add to Cart button scrolls out of view
    document.addEventListener('DOMContentLoaded', function() {
        const addToCartForm = document.getElementById('addToCartForm');
        const stickyBar = document.getElementById('stickyProductBar');
        if (addToCartForm && stickyBar) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) {
                        stickyBar.classList.add('visible');
                    } else {
                        stickyBar.classList.remove('visible');
                    }
                });
            }, { threshold: 0 });
            observer.observe(addToCartForm);
        }
    });
</script>
@endsection
