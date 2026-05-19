@if (!empty($siteDisplay['marquee']['enabled']))
<div class="bb-marquee-bar" role="marquee" aria-label="Site announcements">
    <div class="bb-marquee-track">
        @for ($i = 0; $i < 4; $i++)
            <span class="bb-marquee-item">
                @if (!empty($siteDisplay['marquee']['link']))
                    <a href="{{ $siteDisplay['marquee']['link'] }}" class="bb-marquee-link">{{ $siteDisplay['marquee']['text'] }}</a>
                @else
                    {{ $siteDisplay['marquee']['text'] }}
                @endif
                <span class="bb-marquee-dot" aria-hidden="true">•</span>
            </span>
        @endfor
    </div>
</div>
@endif
