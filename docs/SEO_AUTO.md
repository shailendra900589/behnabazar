# Automatic SEO & search indexing (Behna Bazar)

Everything below runs **without manual SEO work** after one-time site setup.

## What runs automatically

| Trigger | Action |
|--------|--------|
| Product save / approve | Auto `seo_title`, `seo_description`, keywords; sitemap cache cleared; **IndexNow** notifies Bing/Yandex/etc. |
| Daily 03:30 (cron) | `php artisan marketplace:seo-index` — rebuild sitemap, ping Google & Bing, batch IndexNow |
| `php artisan marketplace:deploy` | Same SEO index step after deploy |
| Every public page | Meta title/description, Open Graph, Twitter cards, JSON-LD (`partials/seo-head`) |

## Public URLs

- `https://behnabazar.in/sitemap.xml` — products, categories, shops, static pages (with product images)
- `https://behnabazar.in/robots.txt` — allows storefront; blocks dashboard, cart, checkout, API
- `https://behnabazar.in/{32-char-key}.txt` — IndexNow key (created automatically on first index run)

## `.env` (production)

```env
APP_URL=https://behnabazar.in
SEO_ENABLED=true
SEO_AUTO_INDEX=true

# Optional — or set in Admin → Program → SEO / GEO
GOOGLE_SITE_VERIFICATION=
BING_SITE_VERIFICATION=
INDEXNOW_KEY=
```

## One-time (recommended, ~5 minutes)

Search engines still need **ownership verification** once:

1. [Google Search Console](https://search.google.com/search-console) — add property `behnabazar.in`, choose **HTML tag** verification, copy the `content="..."` value into Admin → **SEO / GEO → Google Search Console verification** (or `GOOGLE_SITE_VERIFICATION` in `.env`).
2. [Bing Webmaster Tools](https://www.bing.com/webmasters) — same for **Bing verification** / `BING_SITE_VERIFICATION`.
3. Submit sitemap once in each panel: `https://behnabazar.in/sitemap.xml` (pings also run daily automatically).

After that, new/updated approved products are submitted via IndexNow + sitemap pings without further action.

## Server cron (Hostinger)

Add to crontab (so daily SEO + cart/stock jobs run):

```cron
* * * * * cd /home/USER/behnabazar && php artisan schedule:run >> /dev/null 2>&1
```

## Manual run

```bash
php artisan marketplace:seo-index
```

Admin → Program → SEO shows **Last auto-index** time and links to sitemap / robots / IndexNow key.

## Notes

- Google/Bing **sitemap ping** does not guarantee instant ranking; it asks crawlers to revisit.
- **IndexNow** speeds up Bing, Yandex, Seznam, Naver for URL updates.
- Private routes (`/dashboard`, `/cart`, etc.) are excluded from sitemap and disallowed in `robots.txt`.
