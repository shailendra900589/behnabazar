<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Invoice\OrderInvoiceService;
use Illuminate\Http\Response;

class OrderInvoiceController extends Controller
{
    public function __construct(
        private readonly OrderInvoiceService $invoices,
    ) {}

    public function download(Order $order): Response
    {
        abort_unless($this->canView($order), 403);

        return $this->invoices->download($order);
    }

    private function canView(Order $order): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ((int) $order->user_id === (int) $user->id) {
            return true;
        }

        if ($user->role === 'vendor') {
            $vendorId = (int) $user->id;

            return (int) $order->fulfillment_vendor_id === $vendorId
                || (int) ($order->product?->vendor_id) === $vendorId;
        }

        return false;
    }
}
