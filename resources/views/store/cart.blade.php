
@extends('layouts.app')
@section('title','Cart')
@section('content')
<section class="container py-3 py-md-4 py-lg-5">
    <h1 class="fw-bold mb-3 mb-md-4">Shopping Cart</h1>
    @include('partials.trust-strip')
    @if($items->isEmpty())
        <div class="bb-card p-5 text-center">
            <i class="bi bi-bag display-1 text-bloom"></i>
            <h2 class="mt-3">Your cart is empty</h2>
            <a href="{{ route('home') }}" class="btn btn-bloom mt-3">Shop Now</a>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="vstack gap-3">
                    @foreach($items as $item)
                        <div class="bb-card-lite p-3 cart-line-item" data-item-id="{{ $item->id }}">
                            <div class="cart-line-main d-flex gap-3 align-items-start">
                                <a href="{{ route('product.show', $item->product) }}">
                                    <img src="{{ $item->product->imageUrl() }}" class="rounded-3 cart-line-img" alt="{{ $item->product->title }}">
                                </a>
                                <div class="flex-grow-1 min-w-0">
                                    <a href="{{ route('product.show', $item->product) }}" class="text-decoration-none">
                                        <h5 class="fw-bold mb-1 cart-line-title text-dark">{{ $item->product->title }}</h5>
                                    </a>
                                    @if($item->variant)
                                        <div class="small text-muted mb-1">{{ $item->variant->displayLabel() }}</div>
                                    @endif
                                    @include('partials.product-price', [
                                        'product' => $item->product,
                                        'variantSale' => $item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price,
                                        'size' => 'sm',
                                    ])
                                </div>
                            </div>
                            <div class="cart-line-actions d-flex gap-2 align-items-center justify-content-between mt-3">
                                <form data-ajax-form data-method="PATCH" action="{{ route('cart.update',$item) }}" class="d-flex align-items-center gap-2">
                                    @csrf
                                    <div class="bb-qty-control d-flex align-items-center" data-qty-control>
                                        <button type="button" class="bb-qty-btn" data-qty-btn="minus" aria-label="Decrease quantity"><i class="bi bi-dash"></i></button>
                                        <input class="bb-qty-value" type="number" min="1" max="20" name="quantity" value="{{ $item->quantity }}" aria-label="Quantity">
                                        <button type="button" class="bb-qty-btn" data-qty-btn="plus" aria-label="Increase quantity"><i class="bi bi-plus"></i></button>
                                    </div>
                                </form>
                                <form action="{{ route('cart.remove',$item) }}" method="post">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm bb-cart-remove" aria-label="Remove item"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4">
                <div class="bb-card p-4 sticky-top" style="top:110px">
                    <h4 class="fw-bold">Order Summary</h4>
                    @include('partials.free-shipping-bar', ['cartTotal' => $total, 'freeShippingThreshold' => $freeShippingThreshold ?? 0])
                    <div class="d-flex justify-content-between py-3 border-bottom">
                        <span>Subtotal</span>
                        <strong data-cart-subtotal>₹{{ number_format($total,2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-3 border-bottom">
                        <span>Shipping</span>
                        <span class="badge badge-soft">Free</span>
                    </div>
                    <div class="d-flex justify-content-between py-3 h4">
                        <span>Total</span>
                        <strong data-cart-order-total>₹{{ number_format($total,2) }}</strong>
                    </div>
                    <a href="{{ route('checkout') }}" class="btn btn-bloom w-100 py-2">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
