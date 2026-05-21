<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Cart reminder</title></head>
<body style="font-family: system-ui, sans-serif; line-height: 1.5; color: #1e293b; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="color: #4f46e5;">Still thinking it over?</h1>
    <p>Hi {{ $user->name }}, you left {{ $items->sum('quantity') }} item(s) in your {{ $siteName }} cart.</p>
    <table style="width:100%; border-collapse: collapse; margin: 16px 0;">
        @foreach($items as $item)
            <tr>
                <td style="padding:8px; border-bottom:1px solid #e2e8f0;">{{ $item->product->title }}</td>
                <td style="padding:8px; border-bottom:1px solid #e2e8f0; text-align:right;">×{{ $item->quantity }}</td>
            </tr>
        @endforeach
    </table>
    <p><strong>Cart total: ₹{{ number_format($cartTotal, 2) }}</strong></p>
    <p><a href="{{ $cartUrl }}" style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 24px;border-radius:999px;text-decoration:none;">Complete checkout</a></p>
</body>
</html>
