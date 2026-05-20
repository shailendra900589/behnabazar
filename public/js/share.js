/**
 * Behna Bazar product share — Web Share API + social deep links.
 */
(function () {
    'use strict';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    function baseUrl() {
        const meta = document.querySelector('meta[name="app-base-url"]');
        if (meta?.content) {
            return meta.content.replace(/\/$/, '');
        }
        if (window.BB_BASE) {
            return String(window.BB_BASE).replace(/\/$/, '');
        }
        return '';
    }

    function apiUrl(path) {
        return baseUrl() + path;
    }

    function toast(msg, type) {
        if (typeof window.bbToast === 'function') {
            window.bbToast(msg, type || 'success');
        } else {
            alert(msg);
        }
    }

    function dropdownRoot(el) {
        return el?.closest('.bb-share-dropdown');
    }

    function slugFromDropdown(root) {
        return root?.dataset?.productSlug
            || root?.querySelector('[data-product-slug]')?.dataset?.productSlug;
    }

    function payloadUrl(root) {
        return root?.dataset?.sharePayloadUrl || null;
    }

    function recordUrl(root) {
        return root?.dataset?.shareRecordUrl || null;
    }

    async function fetchPayload(root) {
        const url = payloadUrl(root);
        if (!url) {
            throw new Error('Missing share payload URL');
        }
        const res = await fetch(url, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) {
            throw new Error('Could not load share data');
        }
        return res.json();
    }

    async function recordShare(root, channel) {
        const url = recordUrl(root);
        if (!url || !csrf) {
            return null;
        }
        const res = await fetch(url, {
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

    function bindLinks(root, payload) {
        if (!payload?.links) {
            return;
        }
        root.querySelectorAll('.bb-share-link').forEach(function (link) {
            const channel = link.dataset.channel;
            if (payload.links[channel]) {
                link.href = payload.links[channel];
                link.setAttribute('target', channel === 'email' || channel === 'sms' ? '_self' : '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });
    }

    async function ensurePayload(root) {
        if (root._bbSharePayload) {
            return root._bbSharePayload;
        }
        const data = await fetchPayload(root);
        root._bbSharePayload = data;
        bindLinks(root, data);
        return data;
    }

    document.addEventListener('show.bs.dropdown', function (e) {
        const root = e.target.closest?.('.bb-share-dropdown') || (e.target.classList?.contains('bb-share-dropdown') ? e.target : null);
        if (!root) {
            return;
        }
        ensurePayload(root).catch(function () {
            toast('Could not load share links. Check your connection.', 'warning');
        });
    });

    document.addEventListener('click', async function (e) {
        const nativeBtn = e.target.closest('.bb-share-native');
        if (nativeBtn) {
            e.preventDefault();
            const root = dropdownRoot(nativeBtn);
            try {
                const recorded = await recordShare(root, 'native');
                const payload = recorded || await ensurePayload(root);
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
            const root = dropdownRoot(copyBtn);
            try {
                const recorded = await recordShare(root, 'copy');
                const payload = recorded || await ensurePayload(root);
                const url = payload.share_url || payload.url;
                await navigator.clipboard.writeText(url);
                toast('Product link copied!');
            } catch (err) {
                toast('Could not copy link.', 'danger');
            }
            return;
        }

        const social = e.target.closest('.bb-share-link');
        if (social) {
            const root = dropdownRoot(social);
            if (!root) {
                return;
            }
            const href = social.getAttribute('href');
            if (!href || href === '#') {
                e.preventDefault();
                try {
                    const payload = await ensurePayload(root);
                    const channel = social.dataset.channel;
                    if (payload.links?.[channel]) {
                        social.href = payload.links[channel];
                        window.open(payload.links[channel], '_blank', 'noopener,noreferrer');
                    }
                } catch (err) {
                    toast('Could not open share link.', 'warning');
                }
                recordShare(root, social.dataset.channel || 'social');
                return;
            }
            recordShare(root, social.dataset.channel || 'social');
        }
    });
})();
