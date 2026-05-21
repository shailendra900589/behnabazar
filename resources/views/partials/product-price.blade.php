@php
    $variantSale = $variantSale ?? null;
    $variantMrp = $variantMrp ?? null;
    $pricing = $product->pricing($variantSale, $variantMrp);
    $size = $size ?? 'md';
    $inline = $inline ?? false;
@endphp
<div class="bb-price-display bb-price-{{ $size }} {{ $inline ? 'bb-price-inline' : '' }}">
    @if($pricing['mrp'])
        <span class="bb-price-mrp">₹{{ number_format($pricing['mrp'], 2) }}</span>
    @endif
    <span class="bb-price-sale">₹{{ number_format($pricing['sale'], 2) }}</span>
    @if($pricing['percent_off'])
        <span class="bb-price-off">{{ $pricing['percent_off'] }}% off</span>
    @endif
</div>
