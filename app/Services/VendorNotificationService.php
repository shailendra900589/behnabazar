<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorNotification;
use App\Services\WhatsApp\WhatsAppService;
use App\Support\SiteBranding;

class VendorNotificationService
{
    public function notifyResellListingCreated(Product $listing, Product $source, User $reseller): void
    {
        $sourceVendorId = (int) $source->vendor_id;
        if ($sourceVendorId < 1 || $sourceVendorId === (int) $reseller->id) {
            return;
        }

        $mode = $listing->resell_mode === 'customized' ? 'Branded' : 'Quick';

        VendorNotification::create([
            'vendor_id' => $sourceVendorId,
            'type' => 'resell_listing',
            'title' => $reseller->shop_name.' listed your product for resell',
            'body' => $mode.' resell: "'.$listing->title.'" at ₹'.number_format((float) $listing->price, 2).' (pending QC). You fulfill customer orders.',
            'link' => route('dashboard'),
            'related_product_id' => $source->id,
            'actor_vendor_id' => $reseller->id,
        ]);
    }

    public function notifyResellBulkPurchase(Product $source, User $buyer, int $qty, float $total): void
    {
        $sourceVendorId = (int) $source->vendor_id;
        if ($sourceVendorId < 1 || $sourceVendorId === (int) $buyer->id) {
            return;
        }

        VendorNotification::create([
            'vendor_id' => $sourceVendorId,
            'type' => 'resell_bulk',
            'title' => $buyer->shop_name.' bought bulk stock',
            'body' => $qty.' × "'.$source->title.'" — ₹'.number_format($total, 2).' credited to your sales wallet.',
            'link' => route('dashboard'),
            'related_product_id' => $source->id,
            'actor_vendor_id' => $buyer->id,
        ]);
    }

    public function notifyNewOrder(Order $order, User $customer): void
    {
        $vendorId = (int) ($order->fulfillment_vendor_id ?? $order->product?->vendor_id);
        if ($vendorId < 1) {
            return;
        }

        $site = SiteBranding::name();
        $waText = "New {$site} order #{$order->id}: {$order->product_name} x{$order->quantity} — ₹".number_format((float) $order->total_price, 2)." ({$order->payment_method})";
        $vendor = User::find($vendorId);
        $waLink = $vendor?->phone
            ? app(WhatsAppService::class)->waMeUrl($vendor->phone, $waText)
            : null;

        VendorNotification::create([
            'vendor_id' => $vendorId,
            'type' => 'new_order',
            'title' => 'New order #'.$order->id,
            'body' => $customer->name.' ordered '.$order->product_name.' × '.$order->quantity.' — ₹'.number_format((float) $order->total_price, 2).' ('.$order->payment_method.')'.($waLink ? ' WhatsApp: '.$waLink : ''),
            'link' => route('dashboard'),
            'related_product_id' => $order->product_id,
            'actor_vendor_id' => null,
        ]);
    }

    public function notifyLowStock(Product $product, string $variantLabel, int $stock): void
    {
        $vendorId = (int) $product->vendor_id;
        if ($vendorId < 1) {
            return;
        }

        $recent = VendorNotification::where('vendor_id', $vendorId)
            ->where('type', 'low_stock')
            ->where('related_product_id', $product->id)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($recent) {
            return;
        }

        VendorNotification::create([
            'vendor_id' => $vendorId,
            'type' => 'low_stock',
            'title' => 'Low stock: '.$product->title,
            'body' => $variantLabel.' — only '.$stock.' left. Restock soon.',
            'link' => route('dashboard'),
            'related_product_id' => $product->id,
            'actor_vendor_id' => null,
        ]);
    }

    public function unreadCount(int $vendorId): int
    {
        return VendorNotification::where('vendor_id', $vendorId)->whereNull('read_at')->count();
    }
}
