
@extends('layouts.app')
@section('title','Cart')
@section('content')
<section class="container py-5">
    <h1 class="fw-bold mb-4">Shopping Cart</h1>
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
                        <div class="bb-card-lite p-3 d-flex gap-3 align-items-center">
                            <img src="{{ $item->product->imageUrl() }}" class="rounded-4" style="width:92px;height:92px;object-fit:cover">
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1">{{ $item->product->title }}</h5>
                                @if($item->variant)
                                    <div class="small text-muted mb-1">{{ $item->variant->displayLabel() }}</div>
                                @endif
                                @include('partials.product-price', [
                                    'product' => $item->product,
                                    'variantSale' => $item->variant ? ($item->variant->price ?? $item->product->price) : $item->product->price,
                                    'size' => 'sm',
                                ])
                            </div>
                            <form data-ajax-form data-method="PATCH" data-reload="true" action="{{ route('cart.update',$item) }}" class="d-flex gap-2 align-items-center">
                                @csrf
                                <input class="form-control" style="width:86px" type="number" min="1" max="20" name="quantity" value="{{ $item->quantity }}">
                                <button class="btn btn-soft btn-sm">Update</button>
                            </form>
                            <form method="post" action="{{ route('cart.remove',$item) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
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
                        <strong>₹{{ number_format($total,2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-3 border-bottom">
                        <span>Shipping</span>
                        <span class="badge badge-soft">Free</span>
                    </div>
                    <div class="d-flex justify-content-between py-3 h4">
                        <span>Total</span>
                        <strong>₹{{ number_format($total,2) }}</strong>
                    </div>
                    <a href="{{ route('checkout') }}" class="btn btn-bloom w-100 py-2">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
