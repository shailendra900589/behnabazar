@php
    $dp = $product->resellerDp();
    $retail = (float) $product->price;
    $dpValue = $dp ?? $retail;
    $dpOff = $dp && $retail > $dp ? (int) round((($retail - $dp) / $retail) * 100) : null;
@endphp
<div class="bb-price-dp">
    <span class="bb-price-dp-label">DP price</span>
    <span class="bb-price-dp-value">₹{{ number_format($dpValue, 2) }}</span>
    @if($dpOff)
        <span class="bb-price-dp-off">{{ $dpOff }}% below retail</span>
    @endif
    <div class="bb-price-dp-retail small text-muted">
        Retail @include('partials.product-price', ['product' => $product, 'size' => 'sm', 'inline' => true])
    </div>
</div>
