@php
    $pricing = $product->pricing();
    $isNew = $product->created_at && $product->created_at->gte(now()->subDays(14));
    $lowStock = false;
    if ($product->relationLoaded('variants') && $product->variants->isNotEmpty()) {
        $lowStock = $product->variants->where('stock', '>', 0)->where('stock', '<=', 3)->isNotEmpty();
    }
@endphp
@if($pricing['percent_off'])
    <span class="bb-product-badge bb-product-badge--sale">{{ $pricing['percent_off'] }}% OFF</span>
@endif
@if($isNew)
    <span class="bb-product-badge bb-product-badge--new">NEW</span>
@endif
@if($lowStock)
    <span class="bb-product-badge bb-product-badge--stock">Low stock</span>
@endif
@if(($product->orders_count ?? 0) >= 4)
    <span class="bb-product-badge bb-product-badge--hot"><i class="bi bi-fire"></i> Hot</span>
@endif
