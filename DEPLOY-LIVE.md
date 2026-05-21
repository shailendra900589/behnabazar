# Live server deploy (Hostinger / shared hosting)

If `git pull` + `marketplace:setup` ran but **website shows no changes**, run these on SSH **inside project folder** (`behnabazar`):

```bash
cd ~/behnabazar   # your actual path

git pull origin main

composer install --no-dev --optimize-autoloader

php artisan marketplace:deploy
```

## What `marketplace:deploy` does

1. Clears **config / route / view / bootstrap** cache (main reason live stays old)
2. Runs all **migrations**
3. Seeds **marketplace settings**
4. Verifies DB tables (`stock_alerts`, `whatsapp_outbox`, `seo_title`, etc.)
5. Checks **composer** packages (PDF invoice needs dompdf)

## Hostinger: split `public_html` + `behnabazar` (your setup)

If **CSS/JS are in `domains/behnabazar.in/public_html`** but Laravel code is in **`~/behnabazar`**, the site will show **old design** until both are synced.

Add to `.env` on server:

```env
APP_URL=https://behnabazar.in
PUBLIC_HTML_PATH=/home/u991240931/domains/behnabazar.in/public_html
BB_LARAVEL_ROOT=/home/u991240931/behnabazar
```

Find your real `public_html` path:

```bash
find ~ -maxdepth 4 -type d -name public_html 2>/dev/null
```

Then deploy (copies `behnabazar/public` → `public_html` and wires `index.php` to Laravel):

```bash
cd ~/behnabazar
git pull origin main
php artisan marketplace:deploy
# or only assets:
php artisan hostinger:sync-public
```

**Verify in browser:** View page source → link should be `css/app.css?v=...` (new theme), not only old `behnabazar.min.css`.

**Best long-term:** hPanel → Domains → Document root = `.../behnabazar/public` (single folder, no split).

Or symlink:

```bash
# only if you can remove old public_html contents first
ln -sfn ~/behnabazar/public ~/domains/behnabazar.in/public_html
```

## .env on live

```env
APP_URL=https://your-actual-domain.com
APP_ENV=production
APP_DEBUG=false
```

Then:

```bash
php artisan optimize:clear
php artisan marketplace:deploy
```

## See new admin features

Login as admin → Dashboard:

- `?section=whatsapp` — WhatsApp outbox  
- `?section=notifications` — SMS/WhatsApp log  
- `?section=program` — Business WhatsApp, COD, SEO  
- `?section=alerts` — Stock alerts  

Hard refresh browser: **Ctrl+Shift+R**

## Verify deploy worked

```bash
php artisan marketplace:deploy
php artisan about
```

Git should show commit `fdd5fa7` or newer (same as GitHub `main`).

## Cron (Hostinger) — do NOT paste in SSH terminal

The line `* * * * * cd ...` is for **crontab**, not bash. Wrong paste causes: `bash: app: command not found`.

```bash
crontab -e
```

Add this one line (your real path):

```cron
* * * * * cd /home/u991240931/behnabazar && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Save and exit. Check: `crontab -l`

## If deploy shows `APP_URL: http://localhost`

Code is already latest; **`.env` on server is wrong or missing `APP_URL`**.

```bash
cd /home/u991240931/behnabazar
grep APP_URL .env
nano .env
```

Must have:

```env
APP_URL=https://behnabazar.in
APP_ENV=production
APP_DEBUG=false
FILESYSTEM_DISK=public
```

Then:

```bash
php artisan optimize:clear
php artisan marketplace:deploy
```

Deploy should print `.env APP_URL line: https://behnabazar.in` (not localhost).
