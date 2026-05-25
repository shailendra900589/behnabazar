@extends('layouts.app')
@section('title', $product->title)
@section('content')
<section class="container py-1 py-md-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-2 mb-md-4">
        <ol class="breadcrumb small mb-0 flex-nowrap overflow-auto">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Shop</a></li>
            @if ($product->category)
                <li class="breadcrumb-item"><a href="{{ route('home', ['cat' => $product->category->slug]) }}" class="text-decoration-none">{{ $product->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ \Illuminate\Support\Str::limit($product->title, 48) }}</li>
        </ol>
    </nav>

    <div class="row g-3 g-lg-5 align-items-start">
        <div class="col-lg-6">
            <div class="product-hero-card bb-card p-0 p-lg-4 rounded-4 shadow-sm mb-2 mb-lg-3">
                <div class="bb-product-gallery-stage position-relative overflow-hidden rounded-4 zoom-container" data-product-gallery data-interval="5000" style="cursor: crosshair;">
                    @foreach ($galleryImages ?? [$product->imageUrl()] as $idx => $imgUrl)
                        <img src="{{ $imgUrl }}"
                             @if($idx === 0) id="mainProductImage" @endif
                             class="w-100 product-detail-img zoom-image bb-gallery-slide {{ $idx === 0 ? 'is-active' : '' }}"
                             alt="{{ $product->title }} — {{ $idx + 1 }}"
                             style="transition: transform 0.1s ease, opacity 0.35s ease; transform-origin: center center;">
                    @endforeach
                    @if(count($galleryImages ?? []) > 1)
                        <div class="bb-gallery-dots position-absolute bottom-0 start-50 translate-middle-x mb-3"></div>
                        <span class="position-absolute top-0 end-0 m-3 badge bg-dark bg-opacity-75 rounded-pill"><i class="bi bi-images me-1"></i>{{ count($galleryImages) }}/{{ config('product.max_gallery_images', 5) }}</span>
                    @endif
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
                    if (!container || window.matchMedia('(hover: none)').matches) {
                        return;
                    }
                    const img = container.querySelector('.bb-gallery-slide.is-active') || container.querySelector('.zoom-image');
                    
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
            
            @if(isset($ads))
                @include('partials.ad-slot', ['slot' => 'product_top', 'ads' => $ads, 'class' => 'mb-3'])
            @endif

            @if (count($galleryImages ?? []) > 1)
                <div class="d-flex gap-2 overflow-auto pb-2 mt-2">
                    @foreach ($galleryImages as $idx => $imgUrl)
                        <img src="{{ $imgUrl }}" data-gallery-thumb data-src="{{ $imgUrl }}"
                             class="rounded-3 border cursor-pointer product-thumb {{ $idx === 0 ? 'border-bloom border-2' : '' }}"
                             style="width: 80px; height: 80px; object-fit: cover; cursor: pointer; transition: all 0.2s ease; opacity: {{ $idx === 0 ? '1' : '0.75' }};"
                             alt="View {{ $idx + 1 }}">
                    @endforeach
                </div>
            @endif
        </div>
        <div class="col-lg-6">
            <span class="badge badge-soft rounded-pill mb-2">{{ $product->category->name ?? 'Product' }}</span>
            <h1 class="h3 h3-md display-6-lg fw-bold lh-sm mb-1">{{ $product->title }}</h1>
            <p class="text-muted mb-2 product-vendor-meta small">
                <span class="d-block d-sm-inline"><i class="bi bi-shop me-1"></i>Sold by
                @if($product->vendor)
                    <a href="{{ route('vendor.shop', $product->vendor) }}" class="fw-bold text-bloom text-decoration-none">{{ $product->vendor->shop_name }} <i class="bi bi-patch-check-fill text-primary" title="Verified Seller"></i></a>
                @else
                    <strong>Behna Bazar Official <i class="bi bi-patch-check-fill text-primary" title="Verified Seller"></i></strong>
                @endif
                </span>
                <span class="d-block d-sm-inline mt-1 mt-sm-0 ms-sm-3 product-rating-meta"><i class="bi bi-star-fill text-warning me-1"></i>{{ number_format($product->averageRating(), 1) }} / 5 ({{ $product->reviews->count() }} reviews)</span>
            </p>
            <div class="mb-2" id="productPriceBlock">
                @include('partials.product-price', ['product' => $product, 'size' => 'lg'])
                <span class="small text-muted d-block mt-1">Inclusive of handling</span>
            </div>
            @if($product->isResellListing())
                <p class="small text-info mb-2"><i class="bi bi-truck me-1"></i>Fulfilled by source vendor — safe checkout via Behna Bazar.</p>
            @endif
            @if(isset($ads))
                @include('partials.ad-slot', ['slot' => 'product_mid', 'ads' => $ads, 'class' => 'mb-3'])
            @endif
            
            <div class="mb-3 d-flex align-items-center gap-2 text-muted small">
                <i class="bi bi-eye text-primary"></i> 
                <strong>{{ number_format($product->view_count) }}</strong> people have viewed this product
            </div>

            @if($product->variants && $product->variants->count() > 0)
                <div class="mb-4">
                    <label class="form-label fw-bold">Select Variant</label>
                    <select name="variant_id" form="addToCartForm" class="form-select w-auto" onchange="updatePrice(this)">
                        <option value="">Default (₹{{ number_format($product->price, 2) }})</option>
                        @foreach($product->variants as $variant)
                            @php $vp = $variant->pricing($product); @endphp
                            <option value="{{ $variant->id }}"
                                data-price="{{ $vp['sale'] }}"
                                data-mrp="{{ $vp['mrp'] ?? ($product->compare_at_price ?? '') }}">
                                {{ $variant->displayLabel() }} — ₹{{ number_format($vp['sale'], 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <script>
                    function updatePrice(select) {
                        const option = select.options[select.selectedIndex];
                        const sale = parseFloat(option.getAttribute('data-price') || {{ $product->price }});
                        const mrp = parseFloat(option.getAttribute('data-mrp') || {{ $product->compare_at_price ?? 0 }}) || 0;
                        const block = document.getElementById('productPriceBlock');
                        if (!block) return;
                        const saleEl = block.querySelector('.bb-price-sale');
                        const mrpEl = block.querySelector('.bb-price-mrp');
                        const offEl = block.querySelector('.bb-price-off');
                        if (saleEl) saleEl.textContent = '₹' + sale.toFixed(2);
                        if (mrpEl) {
                            if (mrp > sale) {
                                mrpEl.textContent = '₹' + mrp.toFixed(2);
                                mrpEl.style.display = '';
                                if (offEl) {
                                    offEl.textContent = Math.round(((mrp - sale) / mrp) * 100) + '% off';
                                    offEl.style.display = '';
                                }
                            } else {
                                mrpEl.style.display = 'none';
                                if (offEl) offEl.style.display = 'none';
                            }
                        }
                    }
                </script>
            @endif

            <div class="mb-3 bg-light p-2 p-md-3 rounded-3" style="max-width: 400px;">
                <label class="form-label fw-bold small mb-1"><i class="bi bi-geo-alt-fill me-1 text-danger"></i>Check Delivery</label>
                <div class="input-group input-group-sm">
                    <input type="text" id="pincodeInput" class="form-control" placeholder="Enter PIN code" maxlength="6">
                    <button class="btn btn-outline-dark" type="button" onclick="checkPincode()">Check</button>
                </div>
                <div id="pincodeResult" class="small mt-2" style="display:none;"></div>
            </div>
            <script>
                async function checkPincode() {
                    const pin = document.getElementById('pincodeInput').value.replace(/\D/g, '');
                    const res = document.getElementById('pincodeResult');
                    res.style.display = 'block';
                    res.innerHTML = '<span class="text-muted">Checking…</span>';
                    try {
                        const r = await fetch("{{ route('api.delivery-check') }}?pincode=" + encodeURIComponent(pin));
                        const data = await r.json();
                        res.innerHTML = data.ok
                            ? '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>' + data.message + '</span>'
                            : '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>' + data.message + '</span>';
                    } catch (e) {
                        res.innerHTML = '<span class="text-danger">Could not check delivery. Try again.</span>';
                    }
                }
            </script>

            @include('partials.stock-notify-form', ['product' => $product])

            <div class="d-flex flex-wrap gap-2 mb-3">
                <form id="addToCartForm" data-ajax-form action="{{ route('cart.add', $product) }}" method="post" class="d-flex gap-2 flex-wrap align-items-center w-100">
                    @csrf
                    <input class="form-control" style="max-width: 70px" type="number" min="1" max="20" name="quantity" value="1" aria-label="Quantity">
                    <button type="submit" class="btn btn-bloom px-3 px-md-4 flex-grow-1 flex-md-grow-0"><i class="bi bi-bag-plus me-1"></i>Add to cart</button>
                    <button type="submit" name="buy_now" value="1" onclick="this.form.removeAttribute('data-ajax-form');" class="btn btn-dark px-3 px-md-4"><i class="bi bi-lightning-charge me-1"></i>Buy Now</button>
                </form>
                @auth
                    <button type="button" class="btn btn-soft btn-lg" data-wishlist-toggle="{{ route('wishlist.toggle', $product) }}" title="Add to Wishlist"><i class="bi bi-heart"></i></button>
                @endauth
                @include('partials.product-share', ['product' => $product])
            </div>

            <ul class="nav nav-tabs nav-fill product-info-tabs mb-0" id="productTabs" role="tablist">
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
            <div class="tab-content bb-card-lite p-3 p-md-4 rounded-bottom-4 rounded-end-4 border">
                <div class="tab-pane fade show active" id="tab-desc" role="tabpanel">
                    <p class="lead text-muted mb-0">{{ $product->description }}</p>
                </div>
                <div class="tab-pane fade" id="tab-reviews" role="tabpanel">
                    @if(($totalReviews ?? 0) > 0)
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
                                <div class="d-flex align-items-center mb-2">
                                    <div class="text-muted small w-25 text-end pe-2">{{ $star }} Stars</div>
                                    <div class="progress w-75 rounded-pill" style="height: 8px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($totalReviews ?? 0) > 0 ? (($ratingCounts[$star] ?? 0) / $totalReviews) * 100 : 0 }}%"></div>
                                    </div>
                                    <div class="text-muted small ms-2" style="width: 30px;">{{ $ratingCounts[$star] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @auth
                        <form id="reviewForm" method="post" action="{{ route('product.review', $product) }}" class="mb-4 bg-white p-3 rounded-3 border" data-ajax-form>
                            @csrf
                            <h5 class="fw-bold mb-3">Write a review</h5>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Your Rating</label>
                                <input type="hidden" name="rating" id="ratingValue" value="5">
                                <div class="bb-star-rating" id="starRating">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star-fill bb-star" data-star="{{ $i }}"></i>
                                    @endfor
                                    <span class="bb-star-label ms-2" id="starLabel">Excellent</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Comment (Optional)</label>
                                <textarea name="comment" class="form-control" rows="2" placeholder="Tell others what you think"></textarea>
                            </div>
                            <button type="submit" class="btn btn-bloom btn-sm" id="reviewSubmitBtn">
                                <i class="bi bi-send me-1"></i>Submit Review
                            </button>
                        </form>
                        <script>
                        (function(){
                            const labels = {1:'Terrible', 2:'Poor', 3:'Average', 4:'Good', 5:'Excellent'};
                            const stars = document.querySelectorAll('#starRating .bb-star');
                            const input = document.getElementById('ratingValue');
                            const label = document.getElementById('starLabel');
                            let selected = 5;

                            function render(val) {
                                stars.forEach(s => {
                                    const sv = +s.dataset.star;
                                    s.classList.toggle('bi-star-fill', sv <= val);
                                    s.classList.toggle('bi-star', sv > val);
                                    s.classList.toggle('active', sv <= val);
                                });
                                label.textContent = labels[val] || '';
                            }

                            stars.forEach(s => {
                                s.addEventListener('mouseenter', () => render(+s.dataset.star));
                                s.addEventListener('click', () => { selected = +s.dataset.star; input.value = selected; render(selected); });
                            });

                            document.getElementById('starRating').addEventListener('mouseleave', () => render(selected));
                            render(selected);

                            const form = document.getElementById('reviewForm');
                            form.addEventListener('submit', async function(e) {
                                e.preventDefault();
                                const btn = document.getElementById('reviewSubmitBtn');
                                btn.disabled = true;
                                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting...';
                                try {
                                    const res = await fetch(form.action, {
                                        method: 'POST',
                                        headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
                                        body: new FormData(form)
                                    });
                                    const data = await res.json();
                                    if (res.ok && data.status === 'success') {
                                        form.reset();
                                        input.value = 5; selected = 5; render(5);
                                        if (typeof bbToast === 'function') bbToast(data.message || 'Review submitted!', 'success');
                                        else alert(data.message || 'Review submitted!');
                                        if (data.html) {
                                            document.querySelector('.vstack.gap-3.mt-4')?.insertAdjacentHTML('afterbegin', data.html);
                                        }
                                        if (data.avg_rating) {
                                            const metaEl = document.querySelector('.product-rating-meta');
                                            if (metaEl) metaEl.innerHTML = '<i class="bi bi-star-fill text-warning me-1"></i>' + data.avg_rating + ' / 5 (' + data.total_reviews + ' reviews)';
                                        }
                                    } else {
                                        if (typeof bbToast === 'function') bbToast(data.message || 'Error submitting review', 'error');
                                        else alert(data.message || 'Error');
                                    }
                                } catch(err) {
                                    if (typeof bbToast === 'function') bbToast('Network error', 'error');
                                } finally {
                                    btn.disabled = false;
                                    btn.innerHTML = '<i class="bi bi-send me-1"></i>Submit Review';
                                }
                            });
                        })();
                        </script>
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
        <hr class="my-3 my-md-5 opacity-25">
        <div class="d-flex justify-content-between align-items-end mb-2 mb-md-4">
            <h2 class="h5 h4-md fw-bold mb-0">You may also like</h2>
            <a href="{{ route('home', ['cat' => $product->category?->slug]) }}" class="small fw-semibold text-bloom text-decoration-none">More <span class="d-none d-sm-inline">in {{ $product->category->name ?? 'shop' }}</span></a>
        </div>
        <div class="row g-2 g-md-4">
            @foreach ($related as $item)
                <div class="col-6 col-lg-3">@include('partials.product-card', ['product' => $item])</div>
            @endforeach
        </div>
    @endif

    @if (isset($recentProducts) && $recentProducts->isNotEmpty())
        <hr class="my-3 my-md-5 opacity-25">
        <div class="d-flex justify-content-between align-items-end mb-2 mb-md-4">
            <h2 class="h5 h4-md fw-bold mb-0"><i class="bi bi-clock-history me-2 text-muted"></i>Recently Viewed</h2>
        </div>
        <div class="horizontal-scroller">
            @foreach ($recentProducts as $recentItem)
                <div style="width: 240px;">@include('partials.product-card', ['product' => $recentItem])</div>
            @endforeach
        </div>
    @endif

    <!-- Trust Badges -->
    <div class="row g-2 g-md-3 mt-2 mt-md-5 mb-0 mb-md-4">
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
    <div class="container d-flex justify-content-between align-items-center gap-2 gap-md-3">
        <div class="d-flex align-items-center gap-2 gap-md-3 min-w-0 flex-grow-1">
            <img src="{{ $product->imageUrl() }}" alt="" class="rounded-3 sticky-bar-thumb" style="width:44px;height:44px;object-fit:cover;">
            <div class="min-w-0">
                <div class="fw-bold text-truncate sticky-bar-title">{{ $product->title }}</div>
                @include('partials.product-price', ['product' => $product, 'size' => 'sm'])
            </div>
        </div>
        <div class="d-flex gap-2 flex-shrink-0 sticky-bar-actions">
            <form data-ajax-form action="{{ route('cart.add', $product) }}" method="post">
                @csrf<input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-bloom rounded-pill px-3 px-md-4 fw-bold shadow-sm"><i class="bi bi-bag-plus me-1"></i><span class="d-none d-sm-inline">Add to</span> Cart</button>
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
