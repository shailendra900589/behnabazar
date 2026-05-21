<?php

namespace App\Services;

use App\Mail\BackInStockMail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockAlert;
use App\Support\NotificationSettings;
use App\Support\SiteBranding;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StockAlertService
{
    public function __construct(
        private readonly Sms\SmsService $sms,
        private readonly WhatsApp\WhatsAppService $whatsapp,
    ) {}

    public function subscribe(Product $product, ?int $variantId, ?string $email, ?string $phone, ?int $userId): StockAlert
    {
        $email = $email ? trim($email) : null;
        $phone = $phone ? trim($phone) : null;

        return StockAlert::firstOrCreate(
            [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'email' => $email,
                'phone' => $phone,
                'user_id' => $userId,
            ],
            []
        );
    }

    public function processBackInStock(): int
    {
        if (! NotificationSettings::all()['stock_alert_enabled']) {
            return 0;
        }

        $sent = 0;
        $site = SiteBranding::name();

        StockAlert::pending()
            ->with(['product', 'variant', 'user'])
            ->chunkById(50, function ($alerts) use (&$sent, $site) {
                foreach ($alerts as $alert) {
                    $product = $alert->product;
                    if (! $product || $product->qc_status !== 'approved') {
                        continue;
                    }

                    if (! $this->isAvailable($product, $alert->variant_id)) {
                        continue;
                    }

                    $url = route('product.show', $product);
                    $title = $product->title.($alert->variant ? ' ('.$alert->variant->displayLabel().')' : '');
                    $message = "{$title} is back in stock on {$site}! Shop now: {$url}";

                    $email = $alert->email ?: $alert->user?->email;
                    if ($email) {
                        try {
                            Mail::to($email)->send(new BackInStockMail($product, $alert->variant, $url));
                        } catch (\Throwable $e) {
                            Log::warning('Back in stock email failed', ['id' => $alert->id, 'error' => $e->getMessage()]);
                        }
                    }

                    if ($alert->phone) {
                        $this->sms->send($alert->phone, $message, 'back_in_stock');
                        if (NotificationSettings::all()['whatsapp_enabled']) {
                            $this->whatsapp->send($alert->phone, $message, 'back_in_stock');
                        }
                    }

                    $alert->update(['notified_at' => now()]);
                    $sent++;
                }
            });

        return $sent;
    }

    public function isAvailable(Product $product, ?int $variantId = null): bool
    {
        if ($variantId) {
            $variant = $product->variants()->where('id', $variantId)->first();

            return $variant && (int) $variant->stock > 0;
        }

        if ($product->variants()->exists()) {
            return $product->variants()->where('stock', '>', 0)->exists();
        }

        return true;
    }

    public function isOutOfStock(Product $product, ?int $variantId = null): bool
    {
        return ! $this->isAvailable($product, $variantId);
    }
}
