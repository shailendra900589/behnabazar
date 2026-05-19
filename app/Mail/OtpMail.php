<?php

namespace App\Mail;

use App\Support\MailConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $intro;

    public function __construct(
        public string $otp,
        public string $purpose = 'customer'
    ) {
        $this->intro = match ($this->purpose) {
            'vendor' => 'Enter this code to verify your seller registration:',
            'password_reset' => 'Enter this code to reset your account password:',
            default => 'Enter this code to verify your account:',
        };
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->purpose) {
            'vendor' => 'Verify your Behna Bazar seller account',
            'password_reset' => 'Your Behna Bazar password reset code',
            default => 'Your Behna Bazar account verification code',
        };

        return new Envelope(
            from: MailConfig::from(),
            replyTo: [MailConfig::replyTo()],
            subject: $subject,
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Auto-Response-Suppress' => 'OOF, AutoReply',
                'Auto-Submitted' => 'auto-generated',
                'X-Entity-Ref-ID' => (string) Str::uuid(),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            text: 'emails.otp-text',
            with: [
                'intro' => $this->intro,
                'otp' => $this->otp,
            ],
        );
    }
}
