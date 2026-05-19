<?php

namespace App\Support;

use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\URL;

class MailConfig
{
    public static function from(): Address
    {
        return new Address(
            (string) config('mail.from.address'),
            (string) config('mail.from.name'),
        );
    }

    public static function replyTo(): Address
    {
        return new Address(
            (string) config('mail.reply_to.address', config('mail.from.address')),
            (string) config('mail.reply_to.name', config('mail.from.name')),
        );
    }

    public static function supportEmail(): string
    {
        return (string) config('mail.support_address', config('mail.from.address'));
    }

    public static function signedUnsubscribeUrl(string $email): string
    {
        return URL::signedRoute('newsletter.unsubscribe', ['email' => $email]);
    }

    public static function unsubscribeMailto(string $email): string
    {
        $subject = rawurlencode('Unsubscribe from Behna Bazar emails');

        return 'mailto:'.self::supportEmail().'?subject='.$subject.'&body='.rawurlencode('Please unsubscribe: '.$email);
    }

    public static function listUnsubscribeHeader(string $email): string
    {
        return '<'.self::unsubscribeMailto($email).'>, <'.self::signedUnsubscribeUrl($email).'>';
    }
}
