@extends('layouts.app')
@section('title', 'Track order')
@section('content')
@php
    $steps = [
        'pending' => 'Order placed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for delivery',
        'delivered' => 'Delivered',
    ];
    $keys = array_keys($steps);
    $current = $order->status === 'cancelled' ? -1 : array_search($order->status, $keys, true);
    if ($current === false) {
        $current = 0;
    }
@endphp
<section class="container py-5 no-print">
    <div class="bb-card p-4 p-lg-5 rounded-4 shadow-sm">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-5 border-bottom pb-4">
            <div>
                <p class="text-muted small mb-1">Order #{{ $order->id }}</p>
                <h1 class="h3 fw-bold mb-2">{{ $order->product_name }}</h1>
                <p class="text-muted mb-0"><span class="fw-semibold text-body">₹{{ number_format($order->total_price, 2) }}</span> · {{ $order->tracking_msg }}</p>
            </div>
            <div class="d-flex gap-2">
                @if($order->status === 'delivered')
                    <a href="{{ route('product.show', $order->product->slug ?? '') }}#reviews" class="btn btn-warning rounded-pill align-self-start fw-bold text-dark"><i class="bi bi-star-fill me-1"></i> Leave Review</a>
                @endif
                <a href="{{ route('orders.invoice', $order) }}" class="btn btn-outline-dark rounded-pill align-self-start" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf me-1"></i> Download PDF Invoice</a>
                <a href="{{ route('orders') }}" class="btn btn-bloom rounded-pill align-self-start">All orders</a>
            </div>
        </div>

        @if ($order->status === 'cancelled')
            <div class="alert alert-danger rounded-4 border-0 shadow-sm">This order was cancelled.</div>
        @else
            <div class="timeline-horizontal mt-4 mb-5">
                @foreach ($steps as $key => $label)
                    <div class="timeline-step @if (array_search($key, $keys, true) !== false && array_search($key, $keys, true) <= $current) active @endif">
                        <span class="timeline-dot"><i class="bi bi-check-lg fs-5"></i></span>
                        <div>
                            <h2 class="h6 fw-bold mb-1">{{ $label }}</h2>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-5">
                <h4 class="fw-bold mb-4">Detailed Tracking History</h4>
                @if($order->trackings->isEmpty())
                    <p class="text-muted small">No detailed tracking updates available yet.</p>
                @else
                    <div class="timeline-vertical ms-3 border-start border-2 border-primary ps-4 position-relative">
                        @foreach($order->trackings as $track)
                            <div class="mb-4 position-relative">
                                <span class="position-absolute bg-primary rounded-circle border border-white border-3" style="width: 16px; height: 16px; left: -33px; top: 4px;"></span>
                                <div class="fw-bold text-dark">{{ ucfirst(str_replace('_', ' ', $track->status)) }}</div>
                                @if($track->location)
                                    <div class="small fw-semibold text-primary"><i class="bi bi-geo-alt me-1"></i>{{ $track->location }}</div>
                                @endif
                                @if($track->message)
                                    <div class="text-muted small">{{ $track->message }}</div>
                                @endif
                                <div class="text-muted mt-1" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>{{ $track->created_at->format('M d, Y h:i A') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>

<!-- Hidden Printable Invoice -->
<div id="printable-invoice" style="display: none;">
    <div style="padding: 40px; font-family: sans-serif;">
        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 30px;">
            <div>
                <img src="{{ asset('images/brand/behna-bazar-wordmark.jpeg') }}" alt="Behna Bazar" style="display: block; width: 210px; max-height: 64px; object-fit: contain; object-position: left center;">
                <p style="margin: 5px 0 0; color: #666;">Marketplace Official Invoice</p>
            </div>
            <div style="text-align: right;">
                <h2 style="margin: 0; font-size: 24px;">INVOICE</h2>
                <p style="margin: 5px 0 0;">Order #{{ $order->id }}</p>
                <p style="margin: 5px 0 0;">Date: {{ $order->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 40px;">
            <div>
                <h3 style="margin: 0 0 10px; font-size: 16px; color: #333;">Billed To:</h3>
                <p style="margin: 0; color: #555;">{{ $order->customer_name ?? ($order->user->name ?? 'Customer') }}</p>
                <p style="margin: 5px 0 0; color: #555;">{{ $order->address }}</p>
                <p style="margin: 5px 0 0; color: #555;">Phone: {{ $order->phone }}</p>
            </div>
            @if($order->product && $order->product->vendor)
            <div style="text-align: right;">
                <h3 style="margin: 0 0 10px; font-size: 16px; color: #333;">Sold By:</h3>
                <p style="margin: 0; color: #555;">{{ $order->product->vendor->shop_name }}</p>
            </div>
            @else
            <div style="text-align: right;">
                <h3 style="margin: 0 0 10px; font-size: 16px; color: #333;">Sold By:</h3>
                <p style="margin: 0; color: #555;">Behna Bazar Official</p>
            </div>
            @endif
        </div>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th style="padding: 12px; text-align: left; border-bottom: 2px solid #e2e8f0;">Item Description</th>
                    <th style="padding: 12px; text-align: center; border-bottom: 2px solid #e2e8f0;">Qty</th>
                    <th style="padding: 12px; text-align: right; border-bottom: 2px solid #e2e8f0;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 15px 12px; border-bottom: 1px solid #e2e8f0;">
                        <strong>{{ $order->product_name }}</strong>
                        @if($order->variant)
                            <br><small style="color: #666;">Variant: {{ $order->variant->displayLabel() }}</small>
                        @endif
                    </td>
                    <td style="padding: 15px 12px; text-align: center; border-bottom: 1px solid #e2e8f0;">{{ $order->quantity }}</td>
                    <td style="padding: 15px 12px; text-align: right; border-bottom: 1px solid #e2e8f0;">₹{{ number_format($order->total_price, 2) }}</td>
                </tr>
            </tbody>
        </table>
        
        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 300px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="color: #666;">Subtotal</span>
                    <span>₹{{ number_format($order->total_price, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="color: #666;">Coupon Discount</span>
                    <span style="color: #16a34a;">-₹{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($order->coin_discount > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <span style="color: #666;">Coin Discount</span>
                    <span style="color: #ea580c;">-₹{{ number_format($order->coin_discount, 2) }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-weight: bold; font-size: 18px;">
                    <span>Total Paid ({{ $order->payment_method }})</span>
                    <span>₹{{ number_format($order->total_price - $order->discount_amount - $order->coin_discount, 2) }}</span>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 50px; text-align: center; color: #666; font-size: 14px;">
            Thank you for shopping on Behna Bazar!
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printable-invoice, #printable-invoice * {
        visibility: visible;
    }
    #printable-invoice {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        display: block !important;
    }
    @page { margin: 0; }
    body { padding: 20px; }
}
</style>

<script>
function printInvoice() {
    window.print();
}
</script>
@endsection
