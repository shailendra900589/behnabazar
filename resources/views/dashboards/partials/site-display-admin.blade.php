<div class="bb-card p-4 mb-4">
    <h4 class="fw-bold mb-2">Header marquee &amp; fixed video <span class="badge badge-soft ms-1">Admin only</span></h4>
    <p class="text-muted small mb-3">Marquee scrolls at the top of every page. Video stays fixed bottom-right with autoplay (muted).</p>
    <form method="post" action="{{ route('manage.site-display.save') }}" class="row g-3" id="siteDisplayForm">
        @csrf
        <div class="col-12">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="header_marquee_enabled" value="1" id="marqueeEnabled"
                    {{ ($settings['header_marquee_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="marqueeEnabled">Show header marquee</label>
            </div>
            <label class="form-label">Marquee message</label>
            <input type="text" name="header_marquee_text" class="form-control" maxlength="500"
                value="{{ $settings['header_marquee_text'] ?? '' }}"
                placeholder="Free delivery on orders above ₹499 • New sellers welcome • Weekend sale live now">
            <label class="form-label mt-2">Marquee link (optional)</label>
            <input type="url" name="header_marquee_link" class="form-control" maxlength="500"
                value="{{ $settings['header_marquee_link'] ?? '' }}" placeholder="https://">
        </div>
        <div class="col-12"><hr class="my-1"></div>
        <div class="col-12">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="site_video_enabled" value="1" id="videoEnabled"
                    {{ ($settings['site_video_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="videoEnabled">Show fixed video (all pages)</label>
            </div>
            <label class="form-label">Video source</label>
            <select name="site_video_type" class="form-select" id="siteVideoType">
                <option value="youtube" {{ ($settings['site_video_type'] ?? 'youtube') === 'youtube' ? 'selected' : '' }}>YouTube link</option>
                <option value="iframe" {{ ($settings['site_video_type'] ?? '') === 'iframe' ? 'selected' : '' }}>Embed / iframe code</option>
            </select>
        </div>
        <div class="col-12" id="youtubeField">
            <label class="form-label">YouTube URL</label>
            <input type="url" name="site_video_url" class="form-control" maxlength="500"
                value="{{ $settings['site_video_url'] ?? '' }}"
                placeholder="https://www.youtube.com/watch?v=... or youtu.be/...">
            <p class="form-text mb-0">Autoplay + loop enabled automatically.</p>
        </div>
        <div class="col-12 d-none" id="iframeField">
            <label class="form-label">Iframe embed code</label>
            <textarea name="site_video_embed" class="form-control" rows="4" maxlength="8000"
                placeholder='<iframe src="https://www.youtube.com/embed/..." ...></iframe>'>{{ $settings['site_video_embed'] ?? '' }}</textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-bloom">Save marquee &amp; video</button>
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('siteVideoType');
        const youtubeField = document.getElementById('youtubeField');
        const iframeField = document.getElementById('iframeField');
        function toggleVideoFields() {
            const isIframe = typeSelect?.value === 'iframe';
            youtubeField?.classList.toggle('d-none', isIframe);
            iframeField?.classList.toggle('d-none', !isIframe);
        }
        typeSelect?.addEventListener('change', toggleVideoFields);
        toggleVideoFields();
    });
</script>
