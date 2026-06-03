<h6 class="fw-bold mb-3">Your Cart</h6>
@if(isset($cartItemsPreview) && $cartItemsPreview->isNotEmpty())
    <div class="d-flex flex-column gap-3 mb-3 max-h-300 overflow-auto" data-cart-dropdown-items>
        @foreach($cartItemsPreview as $item)
            <div class="d-flex gap-3 align-items-center">
                <img src="{{ $item->product->imageUrl() }}" alt="" class="rounded-3 object-fit-cover" width="50" height="50" loading="lazy">
                <div class="min-w-0 flex-grow-1">
                    <div class="fw-semibold text-truncate small">{{ $item->product->title }}</div>
                    <div class="small text-muted">{{ $item->quantity }} x &#8377;{{ number_format($item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price, 2) }}</div>
                </div>
            </div>
        @endforeach
    </div>
    @if($cartCount > 3)
        <div class="text-center small text-muted mb-3" data-cart-dropdown-more>+ {{ $cartCount - 3 }} more items</div>
    @endif
    <div class="d-flex flex-column gap-2">
        <a href="{{ route('cart') }}" class="btn btn-light btn-sm rounded-pill w-100">View Cart</a>
        <a href="{{ route('checkout') }}" class="btn btn-bloom btn-sm rounded-pill w-100">Checkout</a>
    </div>
@else
    <div class="text-center py-4" data-cart-dropdown-empty>
        <i class="bi bi-bag text-muted fs-1 mb-2 d-block opacity-50"></i>
        <span class="text-muted small">Your cart is empty.</span>
    </div>
@endif
