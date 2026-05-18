@php
    $items = collect($ads[$slot] ?? [])->filter(fn ($ad) => $ad->isActiveNow());
@endphp

@if ($items->isNotEmpty())
    <div class="ad-slot ad-slot-{{ $slot }} {{ $class ?? '' }}" data-ad-slot="{{ $slot }}">
        @foreach ($items as $ad)
            @php
                $adKey = 'bb_ad_'.$ad->id;
                $adImage = $ad->image_path
                    ? (str_starts_with($ad->image_path, 'http') ? $ad->image_path : asset('storage/'.$ad->image_path))
                    : ($ad->product?->imageUrl());
                $adLink = route('ads.click', $ad);
            @endphp
            <div class="ad-card" data-ad-id="{{ $adKey }}">
                <button type="button" class="ad-close" aria-label="Close ad" data-ad-close="{{ $adKey }}">
                    <i class="bi bi-x-lg"></i>
                </button>

                @if ($ad->ad_type === 'code' && $ad->code)
                    <div class="ad-code">{!! $ad->code !!}</div>
                @else
                    <a href="{{ $adLink }}" class="ad-creative">
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
