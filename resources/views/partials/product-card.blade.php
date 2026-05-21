<div class="bb-card product-card h-100 position-relative group">
    <div class="position-relative overflow-hidden">
        <a href="{{ route('product.show', $product) }}" class="d-block">
            @include('partials.product-card-media', ['product' => $product])
            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-0 transition-opacity duration-300 ease-in-out group-hover:opacity-10 d-none d-lg-block" style="transition: opacity 0.3s ease; opacity: 0;" onmouseover="this.style.opacity='0.2'" onmouseout="this.style.opacity='0'"></div>
        </a>
        
        <div class="position-absolute top-0 start-0 m-2 d-flex flex-column gap-1 align-items-start" style="z-index: 5;">
            @include('partials.product-badges', ['product' => $product])
        </div>
        
        @auth
        <button class="icon-btn position-absolute top-0 end-0 m-3 shadow-sm" style="z-index: 10;" data-wishlist-toggle="{{ route('wishlist.toggle', $product) }}"><i class="bi bi-heart"></i></button>
        @endauth

        <div class="position-absolute bottom-0 start-0 w-100 p-3 d-flex justify-content-center" style="transform: translateY(100%); transition: transform 0.3s ease; opacity: 0; pointer-events: none;" id="quickAdd{{ $product->id }}">
            <form data-ajax-form action="{{ route('cart.add', $product) }}" method="post" class="w-100" style="pointer-events: auto;">
                @csrf<input type="hidden" name="quantity" value="1">
                <button class="btn btn-light w-100 fw-bold shadow-lg rounded-pill" style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.9);"><i class="bi bi-bag-plus me-1"></i> Quick Add</button>
            </form>
        </div>
        
        <script>
            document.currentScript.parentElement.parentElement.addEventListener('mouseenter', function() {
                const el = document.getElementById('quickAdd{{ $product->id }}');
                if(el) { el.style.transform = 'translateY(0)'; el.style.opacity = '1'; }
            });
            document.currentScript.parentElement.parentElement.addEventListener('mouseleave', function() {
                const el = document.getElementById('quickAdd{{ $product->id }}');
                if(el) { el.style.transform = 'translateY(100%)'; el.style.opacity = '0'; }
            });
        </script>
    </div>
    
    <div class="p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="small text-muted text-uppercase tracking-wider fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.05em;">{{ $product->category->name ?? 'Featured' }}</div>
            @if($product->averageRating() > 0)
                <div class="small text-warning fw-bold"><i class="bi bi-star-fill me-1"></i>{{ number_format($product->averageRating(), 1) }}</div>
            @endif
        </div>
        <a href="{{ route('product.show', $product) }}" class="text-decoration-none"><h5 class="fw-bold mb-3 text-dark text-truncate">{{ $product->title }}</h5></a>
        <div class="d-flex align-items-center justify-content-between gap-2">
            @include('partials.product-price', ['product' => $product, 'size' => 'md'])
            <a href="{{ route('product.show', $product) }}#bbShareToggle" class="btn btn-soft btn-sm rounded-circle" title="Share product" aria-label="Share product"><i class="bi bi-share"></i></a>
        </div>
    </div>
</div>
