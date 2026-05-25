<div class="bb-card product-card h-100 position-relative">
    <div class="position-relative overflow-hidden product-card-media-wrap">
        <a href="{{ route('product.show', $product) }}" class="d-block">
            @include('partials.product-card-media', ['product' => $product])
        </a>

        <div class="position-absolute top-0 start-0 m-2 d-flex flex-column gap-1 align-items-start product-card-badges">
            @include('partials.product-badges', ['product' => $product])
        </div>

        @auth
        <button type="button" class="icon-btn product-card-wishlist position-absolute top-0 end-0 m-2 shadow-sm" data-wishlist-toggle="{{ route('wishlist.toggle', $product) }}" aria-label="Add to wishlist">
            <i class="bi bi-heart"></i>
        </button>
        @endauth

        <div class="product-card-quick-add">
            <form data-ajax-form action="{{ route('cart.add', $product) }}" method="post" class="w-100">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-bloom w-100 fw-semibold rounded-pill product-card-add-btn">
                    <i class="bi bi-bag-plus me-1"></i> Add
                </button>
            </form>
        </div>
    </div>

    <div class="product-card-body p-3">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
            <div class="product-card-category">{{ $product->category->name ?? 'Featured' }}</div>
            @if($product->averageRating() > 0)
                <div class="product-card-rating"><i class="bi bi-star-fill me-1"></i>{{ number_format($product->averageRating(), 1) }}</div>
            @endif
        </div>
        <a href="{{ route('product.show', $product) }}" class="text-decoration-none d-block mb-2">
            <h3 class="product-card-title">{{ $product->title }}</h3>
        </a>
        <div class="d-flex align-items-end justify-content-between gap-2">
            @include('partials.product-price', ['product' => $product, 'size' => 'md'])
            <a href="{{ route('product.show', $product) }}#bbShareToggle" class="btn btn-soft btn-sm rounded-circle product-card-share flex-shrink-0" title="Share" aria-label="Share product">
                <i class="bi bi-share"></i>
            </a>
        </div>
    </div>
</div>
