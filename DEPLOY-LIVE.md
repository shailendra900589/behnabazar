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

## Hostinger: document root

Your domain must serve the Laravel **`public`** folder:

- Correct: `.../behnabazar/public` → document root  
- Wrong: `.../behnabazar` (root) — site may work partially but assets/routes break

In hPanel → Domains → your site → **Document root** → set to `public` inside project.

Or symlink:

```bash
# if public_html is separate (adjust paths)
rm -rf ~/public_html
ln -s ~/behnabazar/public ~/public_html
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

Git should show commit `9707406` or newer.
