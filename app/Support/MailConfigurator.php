<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;

class MailConfigurator
{
    private const PLACEHOLDER_PASSWORDS = [
        '',
        'null',
        'YOUR_GMAIL_APP_PASSWORD',
        'your-gmail-app-password',
        'YOUR_EMAIL_ACCOUNT_PASSWORD',
        'your-email-account-password',
    ];

    public static function applyTransportDefaults(): void
    {
        if (env('MAIL_SCHEME') || env('MAIL_URL')) {
            return;
        }

        $port = (int) config('mail.mailers.smtp.port', 587);

        if ($port === 465) {
            Config::set('mail.mailers.smtp.scheme', 'smtps');
        }
    }

    public static function isConfigured(): bool
    {
        $mailer = (string) config('mail.default', 'log');

        if ($mailer === 'log' && app()->environment('production')) {
            return false;
        }

        if ($mailer === 'array') {
            return app()->environment('testing');
        }

        if ($mailer === 'smtp') {
            $password = (string) env('MAIL_PASSWORD', '');

            if (self::isPlaceholderSecret($password)) {
                return false;
            }

            return (string) env('MAIL_HOST', '') !== ''
                && (string) env('MAIL_USERNAME', '') !== '';
        }

        return true;
    }

    public static function isPlaceholderSecret(?string $value): bool
    {
        $value = trim((string) $value);

        if (in_array($value, self::PLACEHOLDER_PASSWORDS, true)) {
            return true;
        }

        return $value !== '' && (str_starts_with($value, 'YOUR_') || str_starts_with($value, 'your-'));
    }

    /**
     * @return list<string>
     */
    public static function mailersToTry(): array
    {
        $mailers = [(string) config('mail.default', 'smtp')];

        if (config('mail.default') === 'smtp' && array_key_exists('sendmail', config('mail.mailers', []))) {
            $mailers[] = 'sendmail';
        }

        return array_values(array_unique($mailers));
    }

    /**
     * @return list<string>
     */
    public static function diagnose(): array
    {
        $issues = [];

        if (! self::isConfigured()) {
            $issues[] = 'MAIL_PASSWORD is missing or still a placeholder (YOUR_GMAIL_APP_PASSWORD).';
        }

        if (config('mail.default') === 'log' && app()->environment('production')) {
            $issues[] = 'MAIL_MAILER=log on production — emails are only written to logs, not delivered.';
        }

        if ((string) env('MAIL_HOST', '') === 'smtp.gmail.com' && app()->environment('production')) {
            $issues[] = 'Gmail SMTP is often blocked on shared hosting. Prefer Hostinger email: smtp.hostinger.com port 465.';
        }

        if ((string) config('app.url') === '' || str_contains((string) config('app.url'), 'localhost')) {
            $issues[] = 'APP_URL should be https://behnabazar.in on live server.';
        }

        return $issues;
    }

    public static function userFacingMailError(): string
    {
        return 'We could not send the verification email right now. Open the verification page and tap Resend code, or email '.MailConfig::supportEmail().' for help.';
    }

    public static function adminHint(): string
    {
        $issues = self::diagnose();

        return $issues === []
            ? 'Mail appears configured. Run: php artisan marketplace:mail-test your@email.com'
            : implode(' ', $issues);
    }
}
