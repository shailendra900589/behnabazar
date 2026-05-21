<?php

namespace App\Mail;

use App\Services\Invoice\OrderInvoiceService;
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

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $customer,
        public Collection $orders,
        public float $orderTotal,
        public string $paymentMethod,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: MailConfig::from(),
            replyTo: [MailConfig::replyTo()],
            subject: 'Order confirmed — '.SiteBranding::name(),
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
            view: 'emails.order-placed',
            text: 'emails.order-placed-text',
            with: [
                'customer' => $this->customer,
                'orders' => $this->orders,
                'orderTotal' => $this->orderTotal,
                'paymentMethod' => $this->paymentMethod,
                'siteName' => SiteBranding::name(),
                'ordersUrl' => route('orders'),
                'invoiceLinks' => $this->orders->map(fn ($order) => [
                    'product' => $order->product_name,
                    'number' => app(OrderInvoiceService::class)->invoiceNumber($order),
                    'url' => route('orders.invoice', $order),
                ]),
            ],
        );
    }
}
