@if (!empty($siteDisplay['video']['enabled']) && !empty($siteDisplay['video']['embed_html']))
<aside class="bb-site-video" id="bbSiteVideo" aria-label="Promotional video">
    <div class="bb-site-video-card">
        <button type="button" class="bb-site-video-minimize" id="bbSiteVideoToggle" aria-label="Minimize video" title="Minimize">
            <i class="bi bi-dash-lg"></i>
        </button>
        <div class="bb-site-video-frame ratio ratio-16x9">
            {!! $siteDisplay['video']['embed_html'] !!}
        </div>
    </div>
</aside>
@endif
