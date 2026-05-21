<?php

namespace App\Services\Invoice;

use App\Models\Order;
use App\Models\User;
use App\Support\SiteBranding;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer as BaconQrWriter;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;
class OrderInvoiceService
{
    public function invoiceNumber(Order $order): string
    {
        $year = $order->created_at?->format('Y') ?? now()->format('Y');

        return 'BB-'.$year.'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }

    /** @return array<string, mixed> */
    public function build(Order $order): array
    {
        $order->loadMissing(['product.vendor', 'product.category', 'variant', 'user']);

        $site = SiteBranding::config();
        $siteName = $site['name'];
        $siteUrl = rtrim((string) config('app.url'), '/');
        $vendor = $this->resolveVendor($order);
        $shopName = $vendor?->shop_name ?: $siteName.' Official Store';
        $variantLabel = $order->variant?->displayLabel();
        $productUrl = $order->product
            ? $siteUrl.route('product.show', $order->product, false)
            : $siteUrl;
        $trackUrl = $siteUrl.route('orders.track', $order, false);
        $invoiceNo = $this->invoiceNumber($order);
        $issuedAt = $order->created_at ?? now();

        $lineSubtotal = round((float) $order->unit_price * (int) $order->quantity, 2);
        $discount = (float) ($order->discount_amount ?? 0);
        $coinDiscount = (float) ($order->coin_discount ?? 0);

        $qrPayload = [
            'type' => 'behna_bazar_invoice',
            'app' => $siteName,
            'website' => $siteUrl,
            'invoice' => $invoiceNo,
            'order_id' => $order->id,
            'date' => $issuedAt->toDateString(),
            'shop' => $shopName,
            'product' => [
                'name' => $order->product_name,
                'variant' => $variantLabel,
                'qty' => (int) $order->quantity,
                'unit_price' => (float) $order->unit_price,
                'line_total' => $lineSubtotal,
            ],
            'customer' => $order->customer_name,
            'phone' => $order->phone,
            'grand_total' => (float) $order->total_price,
            'currency' => 'INR',
            'payment' => strtoupper((string) $order->payment_method),
            'status' => $order->status,
            'product_url' => $productUrl,
            'verify_url' => $trackUrl,
        ];

        $qrHuman = implode("\n", [
            $siteName.' | Tax Invoice',
            'Invoice: '.$invoiceNo,
            'Shop: '.$shopName,
            'Product: '.$order->product_name.($variantLabel ? ' ('.$variantLabel.')' : ''),
            'Qty: '.$order->quantity.' x Rs.'.number_format((float) $order->unit_price, 2),
            'Total: Rs.'.number_format((float) $order->total_price, 2),
            'Pay: '.strtoupper((string) $order->payment_method),
            $siteUrl,
        ]);

        return [
            'order' => $order,
            'invoice_number' => $invoiceNo,
            'issued_at' => $issuedAt,
            'issued_at_formatted' => $issuedAt->timezone(config('app.timezone'))->format('d M Y, h:i A'),
            'site_name' => $siteName,
            'site_tagline' => $site['tagline'] ?? '',
            'site_url' => $siteUrl,
            'shop_name' => $shopName,
            'shop_phone' => $vendor?->phone ?? '',
            'shop_city' => trim(($vendor?->city ?? '').' '.($vendor?->pincode ?? '')),
            'shop_vendor' => $vendor,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->phone,
            'customer_address' => $order->address,
            'product_name' => $order->product_name,
            'variant_label' => $variantLabel,
            'category_name' => $order->product?->category?->name ?? '',
            'quantity' => (int) $order->quantity,
            'unit_price' => (float) $order->unit_price,
            'line_subtotal' => $lineSubtotal,
            'discount_amount' => $discount,
            'coin_discount' => $coinDiscount,
            'coupon_code' => $order->coupon_code,
            'total_price' => (float) $order->total_price,
            'payment_method' => strtoupper((string) $order->payment_method),
            'order_status' => ucfirst(str_replace('_', ' ', (string) $order->status)),
            'product_url' => $productUrl,
            'track_url' => $trackUrl,
            'qr_data_uri' => $this->qrDataUri($qrPayload, $qrHuman),
            'qr_human' => $qrHuman,
        ];
    }

    public function download(Order $order): Response
    {
        $data = $this->build($order);
        $filename = 'Invoice-'.$data['invoice_number'].'.pdf';

        return Pdf::loadView('invoices.order', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    public function stream(Order $order): Response
    {
        $data = $this->build($order);

        return Pdf::loadView('invoices.order', $data)
            ->setPaper('a4', 'portrait')
            ->stream('Invoice-'.$data['invoice_number'].'.pdf');
    }

    private function resolveVendor(Order $order): ?User
    {
        $vendorId = (int) ($order->fulfillment_vendor_id ?? $order->product?->vendor_id);

        return $vendorId > 0 ? User::find($vendorId) : null;
    }

    /** @param  array<string, mixed>  $jsonPayload */
    private function qrDataUri(array $jsonPayload, string $humanFallback): string
    {
        $text = json_encode($jsonPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($text === false) {
            $text = $humanFallback;
        }

        if (extension_loaded('gd')) {
            try {
                $qrCode = new QrCode($text, size: 220, margin: 8);
                $result = (new PngWriter)->write($qrCode);

                return $result->getDataUri();
            } catch (\Throwable) {
                // fall through to SVG
            }
        }

        return $this->qrSvgDataUri($text ?: $humanFallback);
    }

    private function qrSvgDataUri(string $text): string
    {
        $writer = new BaconQrWriter(
            new ImageRenderer(
                new RendererStyle(220, 4),
                new SvgImageBackEnd
            )
        );

        $svg = $writer->writeString($text);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
