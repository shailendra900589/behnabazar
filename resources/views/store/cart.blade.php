
@extends('layouts.app')
@section('title','Cart')
@section('content')
<section class="container py-2 py-md-4 py-lg-5">
    <h1 class="h4 fw-bold mb-2 mb-md-4">Shopping Cart</h1>
    @if($items->isEmpty())
        <div class="bb-card p-4 p-md-5 text-center">
            <i class="bi bi-bag display-3 text-bloom opacity-50"></i>
            <h2 class="h5 mt-3">Your cart is empty</h2>
            <p class="text-muted small">Add items to get started</p>
            <a href="{{ route('home') }}" class="btn btn-bloom rounded-pill px-4 mt-2">Shop Now</a>
        </div>
    @else
        <div class="row g-3 g-lg-4">
            <div class="col-lg-8">
                <div class="vstack gap-2 gap-md-3">
                    @foreach($items as $item)
                        <div class="bb-cart-item" data-item-id="{{ $item->id }}">
                            <div class="d-flex gap-2 gap-md-3">
                                <a href="{{ route('product.show', $item->product) }}" class="flex-shrink-0">
                                    <img src="{{ $item->product->imageUrl() }}" class="bb-cart-item-img" alt="{{ $item->product->title }}">
                                </a>
                                <div class="flex-grow-1 min-w-0">
                                    <a href="{{ route('product.show', $item->product) }}" class="text-decoration-none">
                                        <h6 class="fw-bold mb-0 text-dark text-truncate">{{ $item->product->title }}</h6>
                                    </a>
                                    @if($item->variant)
                                        <div class="text-muted" style="font-size:0.7rem">{{ $item->variant->displayLabel() }}</div>
                                    @endif
                                    <div class="mt-1">
                                        @include('partials.product-price', [
                                            'product' => $item->product,
                                            'variantSale' => $item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price,
                                            'size' => 'sm',
                                        ])
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <form data-ajax-form data-method="PATCH" action="{{ route('cart.update',$item) }}" class="d-flex align-items-center">
                                            @csrf
                                            <div class="bb-qty-control d-flex align-items-center" data-qty-control>
                                                <button type="button" class="bb-qty-btn" data-qty-btn="minus"><i class="bi bi-dash"></i></button>
                                                <input class="bb-qty-value" type="number" min="1" max="20" name="quantity" value="{{ $item->quantity }}">
                                                <button type="button" class="bb-qty-btn" data-qty-btn="plus"><i class="bi bi-plus"></i></button>
                                            </div>
                                        </form>
                                        <form action="{{ route('cart.remove',$item) }}" method="post">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="bb-cart-remove-btn bb-cart-remove"><i class="bi bi-trash3"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4">
                <div class="bb-cart-summary">
                    <h5 class="fw-bold mb-3">Order Summary</h5>
                    @include('partials.free-shipping-bar', ['cartTotal' => $total, 'freeShippingThreshold' => $freeShippingThreshold ?? 0])
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Subtotal</span>
                        <strong data-cart-subtotal>&#8377;{{ number_format($total,2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Shipping</span>
                        <span class="text-success fw-bold small">Free</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 fw-bold">
                        <span>Total</span>
                        <span class="fs-5 text-bloom" data-cart-order-total>&#8377;{{ number_format($total,2) }}</span>
                    </div>
                    <a href="{{ route('checkout') }}" class="btn btn-bloom w-100 py-2 mt-3 rounded-pill fw-bold">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
