Product update from {{ config('app.name') }}

{{ $product->title }}

@if ($customMessage)
{{ $customMessage }}
@else
We thought you might like this product from our verified sellers.
@endif

Price: ₹{{ number_format((float) $product->price, 2) }}

View product: {{ $productUrl }}

—

You received this because you subscribed or shop with {{ config('app.name') }}.
Unsubscribe: {{ $unsubscribeUrl }}

{{ \App\Support\MailConfig::supportEmail() }}
