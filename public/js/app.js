/**
 * Behna Bazar — core storefront JS (no build step).
 * Requires: jQuery, Bootstrap, SweetAlert2, NProgress (loaded from layout).
 */
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (typeof NProgress !== 'undefined') {
        NProgress.configure({ showSpinner: false, speed: 400, minimum: 0.1 });
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                NProgress.start();
                window.addEventListener('load', function () { NProgress.done(); });
            });
        } else {
            NProgress.start();
            window.addEventListener('load', function () { NProgress.done(); });
        }
    }

    window.bbRequest = async function bbRequest(url, options = {}) {
        const response = await fetch(url, {
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
            ...options,
        });

        if (!response.ok) {
            throw new Error('Request failed');
        }

        return response.json();
    };

    window.bbToast = function bbToast(message, type = 'success') {
        if (type === 'danger') {
            type = 'error';
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        } else {
            alert(message);
        }
    };

    function initAjaxForms() {
        document.addEventListener('submit', async function (event) {
            const form = event.target.closest('[data-ajax-form]');
            if (!form) {
                return;
            }

            event.preventDefault();

            try {
                const payload = new FormData(form);
                const method = form.dataset.method || form.method || 'POST';
                if (['PATCH', 'DELETE'].includes(method.toUpperCase())) {
                    payload.append('_method', method.toUpperCase());
                }

                const data = await window.bbRequest(form.action, { method: 'POST', body: payload });
                window.bbToast(data.message || data.status || 'Updated');

                if (form.matches('[action*="newsletter"]') && form.querySelector('[name="email"]')) {
                    form.reset();
                }

                if (data.cart_count !== undefined) {
                    document.querySelectorAll('[data-cart-count]').forEach(function (el) {
                        el.textContent = data.cart_count;
                    });
                }

                if (form.dataset.reload === 'true') {
                    setTimeout(function () { window.location.reload(); }, 600);
                }
            } catch (error) {
                window.bbToast('Something went wrong. Please try again.', 'danger');
            }
        });
    }

    function initWishlistToggles() {
        document.addEventListener('click', async function (event) {
            const btn = event.target.closest('[data-wishlist-toggle]');
            if (!btn) {
                return;
            }

            event.preventDefault();

            try {
                const data = await window.bbRequest(btn.dataset.wishlistToggle, { method: 'POST' });
                btn.classList.toggle('text-danger', data.operation === 'added');
                document.querySelectorAll('[data-wishlist-count]').forEach(function (el) {
                    el.textContent = data.wishlist_count;
                });
                window.bbToast(data.operation === 'added' ? 'Added to wishlist' : 'Removed from wishlist');
            } catch (error) {
                window.bbToast('Please login first.', 'warning');
            }
        });
    }

    function initSiteVideo() {
        const videoWrap = document.getElementById('bbSiteVideo');
        const videoToggle = document.getElementById('bbSiteVideoToggle');

        videoToggle?.addEventListener('click', function () {
            videoWrap?.classList.toggle('is-minimized');
            const minimized = videoWrap?.classList.contains('is-minimized');
            videoToggle.setAttribute('aria-label', minimized ? 'Expand video' : 'Minimize video');
            videoToggle.innerHTML = minimized
                ? '<i class="bi bi-chevron-up"></i>'
                : '<i class="bi bi-dash-lg"></i>';
        });
    }

    function onReady() {
        initAjaxForms();
        initWishlistToggles();
        initSiteVideo();
    }

    if (typeof jQuery !== 'undefined') {
        jQuery(onReady);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }
})();
