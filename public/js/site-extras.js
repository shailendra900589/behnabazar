/** Extra storefront hooks not in the main bundle */
(function () {
    'use strict';

    function initSiteVideo() {
        var videoWrap = document.getElementById('bbSiteVideo');
        var videoToggle = document.getElementById('bbSiteVideoToggle');
        if (!videoToggle || !videoWrap) {
            return;
        }
        videoToggle.addEventListener('click', function () {
            videoWrap.classList.toggle('is-minimized');
            var minimized = videoWrap.classList.contains('is-minimized');
            videoToggle.setAttribute('aria-label', minimized ? 'Expand video' : 'Minimize video');
            videoToggle.innerHTML = minimized
                ? '<i class="bi bi-chevron-up"></i>'
                : '<i class="bi bi-dash-lg"></i>';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSiteVideo);
    } else {
        initSiteVideo();
    }
})();
