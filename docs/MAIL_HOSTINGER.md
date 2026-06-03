# Email / OTP on Hostinger (behnabazar.in)

Verification codes (register, vendor signup, password reset) are sent with Laravel mail. If users see **"Could not send verification email"**, SMTP is not working on the server.

## Recommended: Hostinger mailbox (not Gmail)

1. In **hPanel → Emails**, use mailbox **`no-reply@behnabazar.in`** (same password as webmail).
2. On the server, copy `deploy/hostinger-mail.env.example` → `deploy/hostinger-mail.env` and set `MAIL_PASSWORD` (quoted if it contains `@`).

```bash
cd ~/behnabazar
cp deploy/hostinger-mail.env.example deploy/hostinger-mail.env
nano deploy/hostinger-mail.env   # set MAIL_PASSWORD="your-password"
php artisan marketplace:apply-mail-env
php artisan config:clear
php artisan marketplace:mail-test your@gmail.com
```

Or edit `~/behnabazar/.env` directly — use **`no-reply@behnabazar.in`** and quote the password if it contains special characters: `MAIL_PASSWORD="your-password"`.

## Gmail on shared hosting

Gmail (`smtp.gmail.com`) often fails on Hostinger (blocked ports or invalid app password). If you use Gmail:

- Use a [Google App Password](https://myaccount.google.com/apppasswords), not your normal password.
- Set `MAIL_PASSWORD` to that 16-character app password (no spaces).

## After changing `.env`

```bash
php artisan config:clear
php artisan optimize:clear
```

Do **not** leave `MAIL_PASSWORD=YOUR_GMAIL_APP_PASSWORD` — that is a template placeholder.

## Logs

Failed sends are logged in `storage/logs/laravel.log` with `OTP mail failed`.
