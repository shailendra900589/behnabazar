<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\MailConfig;
use App\Support\SiteBranding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class BackInStockMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Product $product,
        public ?ProductVariant $variant,
        public string $productUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: MailConfig::from(),
            replyTo: [MailConfig::replyTo()],
            subject: 'Back in stock: '.$this->product->title.' — '.SiteBranding::name(),
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
            view: 'emails.back-in-stock',
            text: 'emails.back-in-stock-text',
            with: [
                'product' => $this->product,
                'variant' => $this->variant,
                'productUrl' => $this->productUrl,
                'siteName' => SiteBranding::name(),
            ],
        );
    }
}
