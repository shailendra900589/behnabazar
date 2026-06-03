/**
 * Behna Bazar — core storefront JS (no build step).
 * Full SPA-like AJAX: cart, wishlist, forms update without page refresh.
 */
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const baseUrl = document.querySelector('meta[name="app-base-url"]')?.content || '';

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
            const errData = await response.json().catch(() => ({}));
            const err = new Error(errData.message || 'Request failed');
            err.data = errData;
            err.status = response.status;
            throw err;
        }

        return response.json();
    };

    window.bbToast = function bbToast(message, type) {
        type = type || 'success';
        if (type === 'danger') type = 'error';

        var colors = { success: '#10b981', error: '#ef4444', warning: '#f59e0b', info: '#6366f1' };
        var icons = { success: '\u2713', error: '\u2717', warning: '\u26A0', info: '\u2139' };
        var toast = document.createElement('div');
        toast.className = 'bb-toast-native';
        toast.innerHTML = '<span style="color:' + (colors[type] || colors.info) + ';font-weight:700;margin-right:8px">' + (icons[type] || '') + '</span>' + message;
        document.body.appendChild(toast);
        requestAnimationFrame(function () { toast.classList.add('show'); });
        setTimeout(function () {
            toast.classList.remove('show');
            setTimeout(function () { toast.remove(); }, 300);
        }, 3000);
    };

    function setLoading(el, state) {
        if (!el) return;
        if (state) {
            el.dataset.origHtml = el.innerHTML;
            el.disabled = true;
            el.classList.add('bb-loading');
            const w = el.offsetWidth;
            el.style.minWidth = w + 'px';
            el.innerHTML = '<span class="bb-spinner"></span>';
        } else {
            el.disabled = false;
            el.classList.remove('bb-loading');
            el.style.minWidth = '';
            if (el.dataset.origHtml) {
                el.innerHTML = el.dataset.origHtml;
                delete el.dataset.origHtml;
            }
        }
    }

    function updateCartBadges(count, total) {
        if (count !== undefined) {
            document.querySelectorAll('[data-cart-count]').forEach(function (el) {
                el.textContent = count;
                el.classList.toggle('d-none', count <= 0);
            });
        }
        if (total !== undefined) {
            document.querySelectorAll('[data-cart-total]').forEach(function (el) {
                el.textContent = '₹' + total;
            });
        }
    }

    function refreshCartUI(data) {
        if (!data) return;
        if (typeof window.bbApplyCartPayload === 'function') {
            window.bbApplyCartPayload(data);
        } else {
            updateCartBadges(data.cart_count, data.cart_total);
            if (data.cart_dropdown_html != null) {
                var menu = document.getElementById('bbCartDropdownMenu');
                if (menu) menu.innerHTML = data.cart_dropdown_html;
            }
        }
        if (data.cart_count !== undefined) {
            updateCartPageTotals(data);
        }
    }

    function initCartDropdownRefresh() {
        /* Handled in partials/cart-dropdown-script (always synced with Blade deploy). */
    }

    function initAjaxForms() {
        document.addEventListener('submit', async function (event) {
            const form = event.target.closest('[data-ajax-form]');
            if (!form) return;

            event.preventDefault();
            const btn = form.querySelector('[type="submit"], button:not([type])');
            setLoading(btn, true);

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

                if (data.cart_count !== undefined || data.cart_dropdown_html != null) {
                    refreshCartUI(data);
                }

                if (data.operation === 'removed') {
                    const row = form.closest('.bb-cart-item') || form.closest('.cart-line-item');
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-30px)';
                        setTimeout(function () {
                            row.remove();
                            if (!document.querySelector('.bb-cart-item') && !document.querySelector('.cart-line-item')) {
                                window.location.reload();
                            }
                        }, 320);
                    }
                }

                if (form.dataset.reload === 'true') {
                    setTimeout(function () { window.location.reload(); }, 500);
                }

                if (form.dataset.resetOnSuccess !== undefined) {
                    form.reset();
                }
            } catch (error) {
                const msg = error.data?.message || error.message || 'Something went wrong.';
                window.bbToast(msg, 'danger');
            } finally {
                setLoading(btn, false);
            }
        });
    }

    function initCartRemoveAjax() {
        document.addEventListener('submit', async function (event) {
            const form = event.target.closest('form .bb-cart-remove')?.closest('form');
            if (!form || form.hasAttribute('data-ajax-form')) return;

            event.preventDefault();
            const btn = form.querySelector('[type="submit"], button');
            setLoading(btn, true);

            try {
                const payload = new FormData(form);
                payload.append('_method', 'DELETE');
                const data = await window.bbRequest(form.action, { method: 'POST', body: payload });
                window.bbToast(data.message || 'Item removed');
                refreshCartUI(data);

                const row = form.closest('.bb-cart-item') || form.closest('.cart-line-item');
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-30px)';
                    setTimeout(function () {
                        row.remove();
                        if (!document.querySelector('.bb-cart-item') && !document.querySelector('.cart-line-item')) {
                            window.location.reload();
                        }
                    }, 320);
                }
            } catch (error) {
                window.bbToast('Failed to remove item.', 'danger');
            } finally {
                setLoading(btn, false);
            }
        });
    }

    function updateCartPageTotals(data) {
        if (data.cart_total) {
            document.querySelectorAll('[data-cart-subtotal]').forEach(el => {
                el.textContent = '₹' + data.cart_total;
            });
            document.querySelectorAll('[data-cart-order-total]').forEach(el => {
                el.textContent = '₹' + data.cart_total;
            });
        }
    }

    var _qtyDebounce = {};
    function initQuantityControls() {
        document.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-qty-btn]');
            if (!btn) return;

            const wrapper = btn.closest('[data-qty-control]');
            if (!wrapper) return;

            const input = wrapper.querySelector('input[name="quantity"]');
            if (!input) return;

            const current = parseInt(input.value) || 1;
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 20;
            const dir = btn.dataset.qtyBtn;

            if (dir === 'minus' && current > min) {
                input.value = current - 1;
            } else if (dir === 'plus' && current < max) {
                input.value = current + 1;
            }

            const form = input.closest('form[data-ajax-form]');
            if (form) {
                var itemId = form.closest('[data-item-id]');
                var key = itemId ? itemId.dataset.itemId : 'default';
                clearTimeout(_qtyDebounce[key]);
                _qtyDebounce[key] = setTimeout(function () {
                    form.requestSubmit();
                }, 500);
            }
        });
    }

    function initWishlistToggles() {
        document.addEventListener('click', async function (event) {
            const btn = event.target.closest('[data-wishlist-toggle]');
            if (!btn) return;

            event.preventDefault();
            const icon = btn.querySelector('i');
            const origClass = icon?.className;

            if (icon) {
                icon.className = 'bi bi-arrow-repeat bb-spin';
            }

            try {
                const data = await window.bbRequest(btn.dataset.wishlistToggle, { method: 'POST' });
                const added = data.operation === 'added';
                btn.classList.toggle('text-danger', added);
                btn.classList.toggle('bb-wishlist-active', added);
                if (icon) {
                    icon.className = added ? 'bi bi-heart-fill' : 'bi bi-heart';
                }
                document.querySelectorAll('[data-wishlist-count]').forEach(function (el) {
                    el.textContent = data.wishlist_count;
                });
                window.bbToast(added ? 'Added to wishlist' : 'Removed from wishlist');
            } catch (error) {
                if (icon) icon.className = origClass;
                window.bbToast('Please login first.', 'warning');
            }
        });
    }

    function initCopyButtons() {
        document.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-copy-target]');
            if (!btn) return;

            const el = document.getElementById(btn.getAttribute('data-copy-target'));
            if (!el) return;

            const text = el.value || el.textContent || '';
            if (!text) return;

            navigator.clipboard.writeText(text.trim()).then(function () {
                window.bbToast('Copied to clipboard');
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = 'bi bi-check-lg text-success';
                    setTimeout(() => { icon.className = 'bi bi-clipboard'; }, 1500);
                }
            }).catch(function () {
                window.bbToast('Could not copy', 'warning');
            });
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

        if (window.matchMedia('(max-width: 767px)').matches && videoWrap && !sessionStorage.getItem('bbVideoMinimized')) {
            videoWrap.classList.add('is-minimized');
            sessionStorage.setItem('bbVideoMinimized', '1');
        }
    }

    function initDashboardOffcanvas() {
        document.querySelectorAll('#dashboardSidebar .nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                const panel = document.getElementById('dashboardSidebar');
                if (panel && window.bootstrap && window.getComputedStyle(panel).position === 'fixed') {
                    window.bootstrap.Offcanvas.getInstance(panel)?.hide();
                }
            });
        });
    }

    function initLiveSearch() {
        document.querySelectorAll('[data-live-search]').forEach(function (form) {
            const input = form.querySelector('input[name="search"]');
            const box = form.querySelector('.bb-live-search-results');
            if (!input || !box) return;

            let timeout = null;
            let abort = null;

            input.addEventListener('input', function () {
                clearTimeout(timeout);
                const q = this.value.trim();
                if (q.length < 2) { box.style.display = 'none'; return; }

                timeout = setTimeout(function () {
                    if (abort) abort.abort();
                    abort = new AbortController();
                    fetch('/api/search?q=' + encodeURIComponent(q), { signal: abort.signal })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            if (!data.length) {
                                box.innerHTML = '<div class="text-muted text-center p-3 small">No products found</div>';
                            } else {
                                box.innerHTML = data.map(function (item) {
                                    return '<a href="' + item.url + '" class="dropdown-item d-flex align-items-center gap-3 p-2 rounded-3">'
                                        + '<img src="' + item.image + '" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:8px">'
                                        + '<div class="min-w-0"><div class="fw-semibold text-truncate" style="max-width:250px">' + item.title + '</div>'
                                        + '<div class="small">'
                                        + (item.percent_off ? '<span class="badge bg-danger me-1" style="font-size:0.65rem">' + item.percent_off + '% off</span>' : '')
                                        + '<span class="text-bloom fw-bold">' + item.formatted_price + '</span>'
                                        + (item.formatted_mrp ? '<span class="text-muted text-decoration-line-through ms-1">' + item.formatted_mrp + '</span>' : '')
                                        + '</div></div></a>';
                                }).join('');
                            }
                            box.style.display = 'block';
                        })
                        .catch(function () {});
                }, 300);
            });

            document.addEventListener('click', function (e) {
                if (!form.contains(e.target)) box.style.display = 'none';
            });
        });
    }

    function initVisitorCounter() {
        var el = document.getElementById('liveVisitorCount');
        if (!el) return;
        setInterval(function () {
            fetch('/api/visitor-count')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.count) el.textContent = data.count.toLocaleString('en-IN');
                })
                .catch(function () {});
        }, 30000);
    }

    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    function initImageLazyLoad() {
        if ('loading' in HTMLImageElement.prototype) return;
        const images = document.querySelectorAll('img[loading="lazy"]');
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src || img.src;
                        observer.unobserve(img);
                    }
                });
            });
            images.forEach(function (img) { observer.observe(img); });
        }
    }

    function initImageFallback() {
        document.addEventListener('error', function (e) {
            if (e.target.tagName !== 'IMG') return;
            if (e.target.dataset.fallbackApplied) return;
            e.target.dataset.fallbackApplied = '1';
            e.target.src = 'data:image/svg+xml,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><rect fill="#f1f5f9" width="200" height="200"/><text x="50%" y="50%" font-family="sans-serif" font-size="14" fill="#94a3b8" text-anchor="middle" dy=".3em">No Image</text></svg>');
            e.target.style.objectFit = 'contain';
        }, true);
    }

    function initPullToRefreshBlock() {
        let startY = 0;
        document.addEventListener('touchstart', function (e) { startY = e.touches[0].pageY; }, { passive: true });
        document.addEventListener('touchmove', function (e) {
            if (window.scrollY === 0 && e.touches[0].pageY > startY + 60) {
                // Prevent accidental pull-to-refresh on mobile
            }
        }, { passive: true });
    }

    function onReady() {
        initAjaxForms();
        initCartDropdownRefresh();
        initCartRemoveAjax();
        initQuantityControls();
        initWishlistToggles();
        initCopyButtons();
        initSiteVideo();
        initDashboardOffcanvas();
        initLiveSearch();
        initVisitorCounter();
        initSmoothScroll();
        initImageLazyLoad();
        initImageFallback();
        initPullToRefreshBlock();

        document.querySelectorAll('.product-card, .bb-card, .stat-card, .table-card, .trust-badge').forEach(function (el, i) {
            el.classList.add('reveal');
            el.style.transitionDelay = Math.min(i * 0.04, 0.3) + 's';
        });

        const revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });

        document.querySelectorAll('.reveal, .product-card, .bb-card, .stat-card, .table-card, .trust-badge').forEach(function (el, i) {
            if (!el.classList.contains('reveal')) {
                el.classList.add('reveal');
                el.style.transitionDelay = Math.min(i * 0.04, 0.3) + 's';
            }
            revealObserver.observe(el);
        });

        document.querySelectorAll('[data-ad-id]').forEach(function (card) {
            if (sessionStorage.getItem(card.dataset.adId) === 'closed') {
                card.classList.add('is-hidden');
            }
        });
        document.querySelectorAll('[data-ad-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                const key = button.dataset.adClose;
                sessionStorage.setItem(key, 'closed');
                button.closest('[data-ad-id]')?.classList.add('is-hidden');
            });
        });
    }

    if (typeof jQuery !== 'undefined') {
        jQuery(onReady);
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }
})();
