# Behna Bazar

Multipurpose e-commerce marketplace for grocery, fashion, electronics, home, beauty, and verified local sellers.

## Stack

- PHP 8.2+ / Laravel 12
- SQLite or MySQL
- Front-end assets: **only** files in `public/vendor/`, `public/css/app.css`, and `public/js/app.js` — **no npm, no Vite, no build step**

## Local setup (XAMPP)

1. Copy `.env.example` to `.env` and set:
   - `APP_NAME="Behna Bazar"`
   - `APP_URL=http://localhost` (or your virtual host URL)
2. Run:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve
   ```
3. Open `http://127.0.0.1:8000`

**Demo logins** (after seeding): `admin@behnabazar.test`, `vendor@behnabazar.test`, `customer@behnabazar.test` — password: `password`

## Production deploy

See **[DEPLOY-LIVE.md](DEPLOY-LIVE.md)** for Hostinger / shared hosting.

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan marketplace:deploy
```

1. Point the web root to the `public/` folder (not project root).
2. Set `APP_URL` to your live domain (e.g. `https://behnabazar.in`).
3. Do **not** skip `composer install` — PDF invoices and other features need `vendor/`.
4. Ensure these URLs return **200** (no `npm install` on the server):
   - `/vendor/bootstrap/css/bootstrap.min.css`
   - `/css/app.css`
   - `/vendor/bootstrap-icons/font/bootstrap-icons.min.css`
   - `/js/app.js`

## Support

Marketplace support: **support@behnabazar.in**

Developed by [Nectra Digital](https://nectradigital.com)
