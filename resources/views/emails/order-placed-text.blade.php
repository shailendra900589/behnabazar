Thank you, {{ $customer->name }}!

Your order at {{ $siteName }} is confirmed. Payment: {{ $paymentMethod }}.

@foreach($orders as $order)
- {{ $order->product_name }} x{{ $order->quantity }} — ₹{{ number_format($order->total_price, 2) }}
@endforeach

Order total: ₹{{ number_format($orderTotal, 2) }}

PDF Invoices:
@foreach($invoiceLinks as $link)
- {{ $link['product'] }}: {{ $link['url'] }} ({{ $link['number'] }})
@endforeach

Track orders: {{ $ordersUrl }}
