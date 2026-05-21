<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Back in stock</title></head>
<body style="font-family: system-ui, sans-serif; max-width: 560px; margin: 0 auto; padding: 24px;">
    <h1 style="color: #16a34a;">It's back in stock!</h1>
    <p><strong>{{ $product->title }}</strong>@if($variant) ({{ $variant->displayLabel() }})@endif is available again on {{ $siteName }}.</p>
    <p><a href="{{ $productUrl }}" style="display:inline-block;background:#4f46e5;color:#fff;padding:12px 24px;border-radius:999px;text-decoration:none;">Shop now</a></p>
</body>
</html>
