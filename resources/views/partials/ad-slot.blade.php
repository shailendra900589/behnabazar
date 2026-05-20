@php
    $items = collect($ads[$slot] ?? [])->filter(fn ($ad) => $ad->isActiveNow());
    $asCard = ($card ?? false) || str_contains($slot, 'grid') || str_contains($slot, 'inline');
@endphp

@if ($items->isNotEmpty())
    <div class="ad-slot ad-slot-{{ $slot }} {{ $class ?? '' }} @if($asCard) ad-slot-as-card @endif" data-ad-slot="{{ $slot }}">
        @foreach ($items as $ad)
            @php
                $adKey = 'bb_ad_'.$ad->id;
                $adImage = $ad->image_path
                    ? (str_starts_with($ad->image_path, 'http') ? $ad->image_path : asset('storage/'.$ad->image_path))
                    : ($ad->product?->imageUrl());
                $adLink = $ad->link_url ?: ($ad->product ? route('product.show', $ad->product) : route('ads.click', $ad));
                if (! $ad->link_url && $ad->product) {
                    $adLink = route('ads.click', $ad);
                } else {
                    $adLink = $ad->link_url ? route('ads.click', $ad) : '#';
                }
                $ytId = null;
                if ($ad->video_url && preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{6,})/', $ad->video_url, $m)) {
                    $ytId = $m[1];
                }
            @endphp
            <div class="ad-card @if($asCard) ad-card-product-style @endif" data-ad-id="{{ $adKey }}">
                <button type="button" class="ad-close" aria-label="Close ad" data-ad-close="{{ $adKey }}">
                    <i class="bi bi-x-lg"></i>
                </button>

                @if ($ad->ad_type === 'youtube' && $ytId)
                    <div class="ad-youtube ratio ratio-16x9">
                        <iframe src="https://www.youtube.com/embed/{{ $ytId }}?rel=0{{ $ad->autoplay ? '&autoplay=1&mute=1' : '' }}" title="{{ $ad->title ?: 'Video ad' }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                    </div>
                @elseif ($ad->ad_type === 'iframe' && $ad->video_url)
                    <div class="ad-iframe-wrap ratio ratio-16x9">
                        <iframe src="{{ $ad->video_url }}" title="{{ $ad->title ?: 'Ad' }}" allowfullscreen loading="lazy" @if($ad->autoplay) allow="autoplay" @endif></iframe>
                    </div>
                @elseif ($ad->ad_type === 'code' || $ad->ad_type === 'html')
                    <div class="ad-code">{!! $ad->code !!}</div>
                @elseif ($ad->ad_type === 'product_card')
                    <a href="{{ route('ads.click', $ad) }}" class="ad-product-card text-decoration-none text-body">
                        @if ($adImage)
                            <img src="{{ $adImage }}" alt="{{ $ad->title ?: 'Promoted' }}" class="ad-product-card-img">
                        @endif
                        <div class="ad-product-card-body p-3">
                            <small class="text-muted">Sponsored</small>
                            <strong class="d-block">{{ $ad->title ?: ($ad->product->title ?? 'Featured') }}</strong>
                            @if ($ad->subtitle)<span class="small text-muted">{{ $ad->subtitle }}</span>@endif
                            <span class="btn btn-sm btn-bloom mt-2">{{ $ad->cta_text ?: 'Shop now' }}</span>
                        </div>
                    </a>
                @else
                    <a href="{{ route('ads.click', $ad) }}" class="ad-creative">
                        @if ($adImage)
                            <img src="{{ $adImage }}" alt="{{ $ad->title ?: 'Promoted product' }}">
                        @endif
                        <span class="ad-copy">
                            <small>{{ $ad->source === 'vendor' ? 'Promoted product' : 'Sponsored' }}</small>
                            <strong>{{ $ad->title ?: ($ad->product->title ?? 'Featured offer') }}</strong>
                            @if ($ad->subtitle)
                                <em>{{ $ad->subtitle }}</em>
                            @endif
                            <b>{{ $ad->cta_text ?: 'Shop now' }}</b>
                        </span>
                    </a>
                @endif
            </div>
        @endforeach
    </div>
@endif
