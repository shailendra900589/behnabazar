@php
    $slides = $product->galleryUrls();
    $multi = count($slides) > 1;
@endphp
<div class="bb-card-carousel position-relative overflow-hidden {{ $multi ? 'bb-card-carousel--multi' : '' }}" @if($multi) data-card-carousel data-interval="4500" @endif>
    @foreach ($slides as $idx => $imgUrl)
        <img src="{{ $imgUrl }}"
             class="product-img bb-card-carousel-slide {{ $idx === 0 ? 'is-active' : '' }}"
             alt="{{ $product->title }} — photo {{ $idx + 1 }}"
             loading="lazy">
    @endforeach
    @if ($multi)
        <div class="bb-card-carousel-dots"></div>
        <span class="bb-card-carousel-badge"><i class="bi bi-images me-1"></i>{{ count($slides) }}</span>
    @endif
</div>
