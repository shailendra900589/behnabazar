<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.45; }
        .page { padding: 28px 32px; }
        .header { border-bottom: 3px solid #4f46e5; padding-bottom: 16px; margin-bottom: 20px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .brand { font-size: 22px; font-weight: bold; color: #4f46e5; }
        .brand-sub { font-size: 10px; color: #64748b; margin-top: 4px; max-width: 280px; }
        .invoice-badge { text-align: right; }
        .invoice-badge h1 { font-size: 18px; color: #0f172a; letter-spacing: 0.5px; }
        .invoice-badge .no { font-size: 13px; color: #4f46e5; font-weight: bold; margin-top: 4px; }
        .meta { font-size: 10px; color: #64748b; margin-top: 6px; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .grid td { width: 50%; vertical-align: top; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; }
        .grid td + td { border-left: none; }
        .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; font-weight: bold; margin-bottom: 6px; }
        .party-name { font-size: 13px; font-weight: bold; color: #0f172a; }
        .party-line { font-size: 10px; color: #475569; margin-top: 3px; }
        .items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .items th { background: #4f46e5; color: #fff; text-align: left; padding: 10px 8px; font-size: 9px; text-transform: uppercase; }
        .items th.r { text-align: right; }
        .items td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .items td.r { text-align: right; }
        .items tr:nth-child(even) td { background: #f8fafc; }
        .totals-wrap { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .totals-wrap td { vertical-align: top; }
        .qr-box { width: 200px; text-align: center; padding: 10px; border: 1px solid #e2e8f0; background: #fff; }
        .qr-box img { width: 160px; height: 160px; }
        .qr-caption { font-size: 8px; color: #64748b; margin-top: 8px; line-height: 1.35; white-space: pre-line; text-align: left; }
        .totals { width: 100%; max-width: 280px; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 6px 8px; font-size: 11px; }
        .totals .grand td { font-size: 14px; font-weight: bold; color: #4f46e5; border-top: 2px solid #4f46e5; padding-top: 10px; }
        .totals .r { text-align: right; }
        .status-pill { display: inline-block; background: #e0e7ff; color: #3730a3; padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .footer { margin-top: 22px; padding-top: 14px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #64748b; text-align: center; }
        .footer strong { color: #4f46e5; }
        .links { font-size: 9px; color: #4f46e5; margin-top: 8px; word-break: break-all; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand">{{ $site_name }}</div>
                    <div class="brand-sub">{{ $site_tagline }}</div>
                    <div class="meta">{{ $site_url }}</div>
                </td>
                <td class="invoice-badge">
                    <h1>TAX INVOICE</h1>
                    <div class="no">{{ $invoice_number }}</div>
                    <div class="meta">Issued: {{ $issued_at_formatted }}</div>
                    <div class="meta" style="margin-top:8px;"><span class="status-pill">{{ $order_status }}</span></div>
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        <tr>
            <td>
                <div class="label">Sold by (Shop)</div>
                <div class="party-name">{{ $shop_name }}</div>
                @if($shop_phone)<div class="party-line">Phone: {{ $shop_phone }}</div>@endif
                @if($shop_city)<div class="party-line">{{ $shop_city }}</div>@endif
                <div class="party-line">Marketplace: {{ $site_name }}</div>
            </td>
            <td>
                <div class="label">Bill to</div>
                <div class="party-name">{{ $customer_name }}</div>
                <div class="party-line">Phone: {{ $customer_phone }}</div>
                <div class="party-line">{{ $customer_address }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:42%">Product</th>
                <th class="r" style="width:12%">Qty</th>
                <th class="r" style="width:18%">Rate</th>
                <th class="r" style="width:18%">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $product_name }}</strong>
                    @if($variant_label)<br><span style="color:#64748b;">{{ $variant_label }}</span>@endif
                    @if($category_name)<br><span style="color:#64748b;">{{ $category_name }}</span>@endif
                </td>
                <td class="r">{{ $quantity }}</td>
                <td class="r">&#8377;{{ number_format($unit_price, 2) }}</td>
                <td class="r">&#8377;{{ number_format($line_subtotal, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals-wrap">
        <tr>
            <td style="width:200px;">
                <div class="qr-box">
                    <img src="{{ $qr_data_uri }}" alt="Invoice QR">
                    <div class="qr-caption">{{ $qr_human }}</div>
                </div>
            </td>
            <td>
                <table class="totals">
                    <tr>
                        <td>Subtotal</td>
                        <td class="r">&#8377;{{ number_format($line_subtotal, 2) }}</td>
                    </tr>
                    @if($discount_amount > 0)
                    <tr>
                        <td>Coupon @if($coupon_code)({{ $coupon_code }})@endif</td>
                        <td class="r">- &#8377;{{ number_format($discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($coin_discount > 0)
                    <tr>
                        <td>Coins redeemed</td>
                        <td class="r">- &#8377;{{ number_format($coin_discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="grand">
                        <td>Grand total ({{ $payment_method }})</td>
                        <td class="r">&#8377;{{ number_format($total_price, 2) }}</td>
                    </tr>
                </table>
                <div class="links">
                    Product: {{ $product_url }}<br>
                    Track order: {{ $track_url }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>This is a computer-generated invoice from <strong>{{ $site_name }}</strong> ({{ $site_url }}).</p>
        <p>Scan the QR code for product, shop, website &amp; order verification details.</p>
        <p>Order #{{ $order->id }} &middot; {{ $invoice_number }} &middot; Thank you for shopping!</p>
    </div>
</div>
</body>
</html>
