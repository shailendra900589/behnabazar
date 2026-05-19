<?php

namespace App\Mail;

use App\Models\Product;
use App\Support\MailConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProductPromotionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $productUrl;

    public ?string $imageUrl;

    public string $unsubscribeUrl;

    public function __construct(
        public Product $product,
        public string $recipientEmail,
        public ?string $customMessage = null,
    ) {
        $this->productUrl = route('product.show', $this->product);
        $this->imageUrl = $this->product->emailSafeImageUrl();
        $this->unsubscribeUrl = MailConfig::signedUnsubscribeUrl($this->recipientEmail);
    }

    public function envelope(): Envelope
    {
        $title = Str::limit($this->product->title, 50);

        return new Envelope(
            from: MailConfig::from(),
            replyTo: [MailConfig::replyTo()],
            subject: 'Featured on Behna Bazar: '.$title,
        );
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => MailConfig::listUnsubscribeHeader($this->recipientEmail),
                'X-Entity-Ref-ID' => (string) Str::uuid(),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.promotion',
            text: 'emails.promotion-text',
            with: [
                'product' => $this->product,
                'customMessage' => $this->customMessage,
                'productUrl' => $this->productUrl,
                'imageUrl' => $this->imageUrl,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }
}
