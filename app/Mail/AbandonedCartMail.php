<?php

namespace App\Mail;

use App\Models\User;
use App\Support\MailConfig;
use App\Support\SiteBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AbandonedCartMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Collection $items,
        public float $cartTotal,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: MailConfig::from(),
            replyTo: [MailConfig::replyTo()],
            subject: 'You left items in your cart — '.SiteBranding::name(),
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
            view: 'emails.abandoned-cart',
            text: 'emails.abandoned-cart-text',
            with: [
                'user' => $this->user,
                'items' => $this->items,
                'cartTotal' => $this->cartTotal,
                'cartUrl' => route('cart'),
                'siteName' => SiteBranding::name(),
            ],
        );
    }
}
