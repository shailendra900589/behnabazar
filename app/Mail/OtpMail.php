<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otp,
        public string $purpose = 'verification'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Behna Bazar verification code',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '<p style="font-family:system-ui,sans-serif;font-size:16px">Your one-time code is:</p>'
                .'<p style="font-family:monospace;font-size:28px;font-weight:bold;letter-spacing:4px">'.$this->otp.'</p>'
                .'<p style="color:#666;font-size:14px">This code expires in 10 minutes. If you did not request it, ignore this email.</p>',
        );
    }
}
