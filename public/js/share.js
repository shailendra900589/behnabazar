/**
 * Behna Bazar product share — Web Share API + social deep links.
 */
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let shareCache = null;

    async function fetchPayload(slug) {
        const res = await fetch('/product/' + encodeURIComponent(slug) + '/share', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) {
            throw new Error('Could not load share data');
        }
        return res.json();
    }

    async function recordShare(slug, channel) {
        if (!csrf) {
            return null;
        }
        const res = await fetch('/product/' + encodeURIComponent(slug) + '/share', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ channel: channel }),
        });
        if (!res.ok) {
            return null;
        }
        return res.json();
    }

    function toast(msg, type) {
        if (typeof window.bbToast === 'function') {
            window.bbToast(msg, type || 'success');
        } else {
            alert(msg);
        }
    }

    function bindLinks(payload) {
        document.querySelectorAll('.bb-share-link').forEach(function (link) {
            const channel = link.dataset.channel;
            if (payload.links && payload.links[channel]) {
                link.href = payload.links[channel];
            }
        });
    }

    async function getPayload(slug) {
        if (shareCache && shareCache.slug === slug) {
            return shareCache.data;
        }
        const data = await fetchPayload(slug);
        shareCache = { slug: slug, data: data };
        bindLinks(data);
        return data;
    }

    document.addEventListener('click', async function (e) {
        const nativeBtn = e.target.closest('.bb-share-native');
        if (nativeBtn) {
            e.preventDefault();
            const slug = nativeBtn.dataset.productSlug;
            try {
                const recorded = await recordShare(slug, 'native');
                const payload = recorded || await getPayload(slug);
                const url = payload.share_url || payload.url;
                const shareData = {
                    title: payload.title || 'Behna Bazar',
                    text: payload.text || '',
                    url: url,
                };
                if (navigator.share) {
                    await navigator.share(shareData);
                    toast('Thanks for sharing!');
                } else {
                    await navigator.clipboard.writeText(url);
                    toast('Link copied (native share not supported on this device).');
                }
            } catch (err) {
                if (err.name !== 'AbortError') {
                    toast('Could not open share.', 'warning');
                }
            }
            return;
        }

        const copyBtn = e.target.closest('.bb-share-copy');
        if (copyBtn) {
            e.preventDefault();
            const slug = copyBtn.dataset.productSlug;
            try {
                const recorded = await recordShare(slug, 'copy');
                const url = recorded?.share_url || (await getPayload(slug)).url;
                await navigator.clipboard.writeText(url);
                toast('Product link copied!');
            } catch (err) {
                toast('Could not copy link.', 'danger');
            }
            return;
        }

        const social = e.target.closest('.bb-share-link');
        if (social) {
            const slug = social.closest('.bb-share-dropdown')?.querySelector('[data-product-slug]')?.dataset.productSlug;
            if (slug) {
                recordShare(slug, social.dataset.channel || 'social');
                try {
                    await getPayload(slug);
                } catch (err) {
                    /* links may still work from cache */
                }
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const first = document.querySelector('.bb-share-native[data-product-slug]');
        if (first) {
            getPayload(first.dataset.productSlug).catch(function () {});
        }
    });
})();
