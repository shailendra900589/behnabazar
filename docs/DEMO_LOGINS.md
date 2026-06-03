# Behna Bazar — Demo login accounts

After `php artisan marketplace:deploy` (or `php artisan db:seed --class=DemoAccountsSeeder`), these accounts work on **local and live**.

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@behnabazar.test` | `password` |
| Vendor | `vendor@behnabazar.test` | `password` |
| QC Manager | `qc@behnabazar.test` | `password` |
| Customer | `user@behnabazar.test` | `password` |

Deploy resets passwords for these four emails only. Other registered users keep their own passwords.

**Live server:**
```bash
cd ~/behnabazar && git pull && php artisan db:seed --class=DemoAccountsSeeder --force && php artisan config:clear
```
