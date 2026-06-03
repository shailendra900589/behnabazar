<script>
(function () {
    'use strict';

    var previewUrl = @json(route('api.cart-preview'));
    var cartUrl = @json(route('cart'));
    var checkoutUrl = @json(route('checkout'));
    var placeholderImg = @json(\App\Support\PublicStorage::placeholder());

    function escapeHtml(text) {
        var el = document.createElement('div');
        el.textContent = text == null ? '' : String(text);
        return el.innerHTML;
    }

    function renderCartDropdownFromItems(items, count, more) {
        var menu = document.getElementById('bbCartDropdownMenu');
        if (!menu) return;

        count = parseInt(count, 10) || 0;
        more = parseInt(more, 10) || 0;
        items = items || [];

        if (!items.length) {
            menu.innerHTML =
                '<h6 class="fw-bold mb-3">Your Cart</h6>' +
                '<div class="text-center py-4" data-cart-dropdown-empty>' +
                '<i class="bi bi-bag text-muted fs-1 mb-2 d-block opacity-50"></i>' +
                '<span class="text-muted small">Your cart is empty.</span></div>';
            return;
        }

        var html = '<h6 class="fw-bold mb-3">Your Cart</h6>' +
            '<div class="d-flex flex-column gap-3 mb-3 max-h-300 overflow-auto" data-cart-dropdown-items>';

        items.forEach(function (item) {
            var img = escapeHtml(item.image || placeholderImg);
            html += '<div class="d-flex gap-3 align-items-center">' +
                '<img src="' + img + '" alt="" class="rounded-3 object-fit-cover" width="50" height="50" loading="lazy" ' +
                'onerror="this.onerror=null;this.src=' + JSON.stringify(placeholderImg) + '">' +
                '<div class="min-w-0 flex-grow-1">' +
                '<div class="fw-semibold text-truncate small">' + escapeHtml(item.title) + '</div>' +
                '<div class="small text-muted">' + escapeHtml(String(item.quantity)) + ' x &#8377;' + escapeHtml(item.unit_price) + '</div>' +
                '</div></div>';
        });

        html += '</div>';

        if (more > 0) {
            html += '<div class="text-center small text-muted mb-3" data-cart-dropdown-more">+ ' + more + ' more items</div>';
        }

        html += '<div class="d-flex flex-column gap-2">' +
            '<a href="' + escapeHtml(cartUrl) + '" class="btn btn-light btn-sm rounded-pill w-100">View Cart</a>' +
            '<a href="' + escapeHtml(checkoutUrl) + '" class="btn btn-bloom btn-sm rounded-pill w-100">Checkout</a>' +
            '</div>';

        menu.innerHTML = html;
    }

    window.bbApplyCartPayload = function (data) {
        if (!data) return;

        if (data.cart_count !== undefined) {
            document.querySelectorAll('[data-cart-count]').forEach(function (el) {
                el.textContent = data.cart_count;
                el.classList.toggle('d-none', data.cart_count <= 0);
            });
        }

        if (data.cart_total !== undefined) {
            document.querySelectorAll('[data-cart-total]').forEach(function (el) {
                el.textContent = '\u20B9' + data.cart_total;
            });
        }

        if (data.cart_dropdown_html) {
            var menu = document.getElementById('bbCartDropdownMenu');
            if (menu) {
                menu.innerHTML = data.cart_dropdown_html;
            }
        } else if (data.cart_items) {
            renderCartDropdownFromItems(data.cart_items, data.cart_count, data.cart_more);
        }
    };

    function hookBbRequest() {
        var orig = window.bbRequest;
        if (typeof orig !== 'function' || orig.__bbCartHooked) {
            return;
        }

        window.bbRequest = async function (url, options) {
            var data = await orig(url, options);
            if (data && (data.cart_dropdown_html != null || data.cart_items || data.cart_count !== undefined)) {
                window.bbApplyCartPayload(data);
            }
            return data;
        };
        window.bbRequest.__bbCartHooked = true;
    }

    hookBbRequest();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', hookBbRequest);
    }
    setTimeout(hookBbRequest, 50);

    var wrap = document.querySelector('.dropdown-cart');
    if (wrap) {
        wrap.addEventListener('show.bs.dropdown', function () {
            var badge = wrap.querySelector('[data-cart-count]');
            var menu = document.getElementById('bbCartDropdownMenu');
            var count = parseInt(badge && badge.textContent ? badge.textContent : '0', 10) || 0;

            if (!menu || count <= 0) {
                return;
            }

            if (menu.querySelector('[data-cart-dropdown-items]')) {
                return;
            }

            if (typeof window.bbRequest !== 'function') {
                return;
            }

            window.bbRequest(previewUrl).then(window.bbApplyCartPayload).catch(function () {});
        });
    }
})();
</script>
