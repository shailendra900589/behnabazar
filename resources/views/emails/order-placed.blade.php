<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Order confirmed</title></head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="color: #4f46e5; font-size: 1.35rem;">Thank you, {{ $customer->name }}!</h1>
    <p>Your order at <strong>{{ $siteName }}</strong> is confirmed. Payment: <strong>{{ $paymentMethod }}</strong>.</p>
    <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
        <thead>
            <tr style="background: #f1f5f9;">
                <th style="text-align: left; padding: 8px;">Item</th>
                <th style="text-align: right; padding: 8px;">Qty</th>
                <th style="text-align: right; padding: 8px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{{ $order->product_name }}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: right;">{{ $order->quantity }}</td>
                    <td style="padding: 8px; border-bottom: 1px solid #e2e8f0; text-align: right;">₹{{ number_format($order->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="font-size: 1.1rem;"><strong>Order total: ₹{{ number_format($orderTotal, 2) }}</strong></p>
    <p style="margin: 12px 0;">Download your tax invoice (PDF with QR verification):</p>
    <ul style="padding-left: 20px; margin-bottom: 16px;">
        @foreach($invoiceLinks as $link)
            <li style="margin-bottom: 6px;">
                {{ $link['product'] }} —
                <a href="{{ $link['url'] }}" style="color: #4f46e5;">PDF Invoice {{ $link['number'] }}</a>
            </li>
        @endforeach
    </ul>
    <p><a href="{{ $ordersUrl }}" style="display: inline-block; background: #4f46e5; color: #fff; padding: 10px 20px; border-radius: 999px; text-decoration: none;">Track orders</a></p>
    <p style="font-size: 0.85rem; color: #64748b;">Questions? Reply to this email or visit our help center.</p>
</body>
</html>
