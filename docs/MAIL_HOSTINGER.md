# Email / OTP on Hostinger (behnabazar.in)

Verification codes (register, vendor signup, password reset) are sent with Laravel mail. If users see **"Could not send verification email"**, SMTP is not working on the server.

## Recommended: Hostinger mailbox (not Gmail)

1. In **hPanel → Emails**, create e.g. `noreply@behnabazar.in` and note the password.
2. SSH into the server and edit `~/behnabazar/.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=noreply@behnabazar.in
MAIL_PASSWORD=your-real-email-password
MAIL_FROM_ADDRESS="noreply@behnabazar.in"
MAIL_FROM_NAME="Behna Bazar"
MAIL_REPLY_TO_ADDRESS="noreply@behnabazar.in"
MAIL_SUPPORT_ADDRESS="noreply@behnabazar.in"
```

3. Clear config cache and test:

```bash
cd ~/behnabazar
php artisan config:clear
php artisan marketplace:mail-test your@gmail.com
```

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
