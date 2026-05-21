<?php

namespace App\Services;

use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\User;
use App\Services\Sms\SmsService;
use App\Services\WhatsApp\WhatsAppService;
use App\Support\NotificationSettings;
use App\Support\SiteBranding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    private const STATUS_LABELS = [
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    public function __construct(
        private readonly VendorNotificationService $vendorNotifications,
        private readonly SmsService $sms,
        private readonly WhatsAppService $whatsapp,
    ) {}

    /** @param  Collection<int, Order>  $orders */
    public function orderPlaced(User $customer, Collection $orders, float $total, string $paymentMethod): void
    {
        $settings = NotificationSettings::all();
        $site = SiteBranding::name();
        $orderIds = $orders->pluck('id')->implode(', ');
        $summary = "Order #{$orderIds} — ₹".number_format($total, 2)." ({$paymentMethod}) from {$customer->name}";

        foreach ($orders as $order) {
            $vendorId = (int) ($order->fulfillment_vendor_id ?? $order->product?->vendor_id);
            if ($vendorId > 0) {
                $this->vendorNotifications->notifyNewOrder($order, $customer);

                if ($settings['whatsapp_enabled'] && $settings['order_whatsapp_vendor']) {
                    $vendor = User::find($vendorId);
                    if ($vendor?->phone) {
                        $waMsg = "🛒 New order on {$site}\n{$order->product_name} × {$order->quantity}\n₹".number_format((float) $order->total_price, 2)."\n{$customer->name} — {$order->phone}";
                        $this->whatsapp->send($vendor->phone, $waMsg, 'vendor_new_order', 'Vendor: '.($vendor->shop_name ?? $vendor->name));
                    }
                }

                if ($settings['sms_enabled'] && $settings['order_sms_vendor']) {
                    $vendor = $vendor ?? User::find($vendorId);
                    if ($vendor?->phone) {
                        $this->sms->send(
                            $vendor->phone,
                            "New {$site} order: {$order->product_name} x{$order->quantity}. Total ₹".number_format((float) $order->total_price, 2).'.',
                            'vendor_new_order'
                        );
                    }
                }
            }
        }

        if ($customer->email) {
            try {
                Mail::to($customer->email)->send(new OrderPlacedMail($customer, $orders, $total, $paymentMethod));
            } catch (\Throwable $e) {
                Log::warning('Order confirmation email failed', [
                    'user_id' => $customer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($settings['sms_enabled'] && $settings['order_sms_customer'] && $customer->phone) {
            $this->sms->send(
                $customer->phone,
                "Thank you! Your {$site} order is confirmed. Total ₹".number_format($total, 2).". Track: ".route('orders'),
                'customer_order_confirm'
            );
        }

        $customerPhone = $customer->phone ?: $orders->first()?->phone;
        if ($settings['whatsapp_enabled'] && $settings['order_whatsapp_customer'] && $customerPhone) {
            $waMsg = "Thank you {$customer->name}! Order #{$orderIds} confirmed.\nTotal ₹".number_format($total, 2)." ({$paymentMethod})\nTrack: ".route('orders');
            $this->whatsapp->send($customerPhone, $waMsg, 'customer_order_confirm', 'Customer: '.$customer->name);
        }

        $adminPhone = NotificationSettings::adminAlertPhone() ?: config('notifications.whatsapp.admin_phone');
        if ($adminPhone && $settings['whatsapp_enabled'] && $settings['order_whatsapp_admin']) {
            $waMsg = "🛒 New order\n{$summary}\nPay: {$paymentMethod}";
            $this->whatsapp->send($adminPhone, $waMsg, 'admin_new_order', 'Admin alert');
        }
    }

    public function orderStatusChanged(Order $order, string $previousStatus): void
    {
        if ($order->status === $previousStatus) {
            return;
        }

        if (! isset(self::STATUS_LABELS[$order->status])) {
            return;
        }

        $settings = NotificationSettings::all();
        if (! $settings['notify_order_status_customer']) {
            return;
        }

        $order->loadMissing('user');
        $site = SiteBranding::name();
        $label = self::STATUS_LABELS[$order->status];
        $trackUrl = route('orders.track', $order);
        $tracking = trim((string) $order->tracking_msg);
        $msg = "Order #{$order->id} — {$label}\n{$order->product_name}";
        if ($tracking !== '' && $tracking !== 'Updated by admin') {
            $msg .= "\n{$tracking}";
        }
        $msg .= "\nTrack: {$trackUrl}";

        $phone = $order->user?->phone ?: $order->phone;
        $email = $order->user?->email;

        if ($settings['sms_enabled'] && $phone) {
            $this->sms->send($phone, "{$site}: {$msg}", 'order_status_'.$order->status);
        }

        if ($settings['whatsapp_enabled'] && $phone) {
            $this->whatsapp->send($phone, $msg, 'order_status_'.$order->status, $order->customer_name);
        }

        if ($email && in_array($order->status, ['shipped', 'delivered'], true)) {
            try {
                Mail::raw($msg, fn ($m) => $m->to($email)->subject("{$site} — Order #{$order->id} {$label}"));
            } catch (\Throwable $e) {
                Log::warning('Order status email failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
