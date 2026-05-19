@extends('emails.layout')

@section('body')
    <p style="margin:0 0 8px;font-size:14px;color:#64748b;">Product update from {{ config('app.name') }}</p>
    <h1 style="margin:0 0 16px;font-size:22px;line-height:1.3;color:#0f172a;">{{ $product->title }}</h1>

    @if ($customMessage)
        <p style="margin:0 0 16px;">{{ $customMessage }}</p>
    @else
        <p style="margin:0 0 16px;">We thought you might like this product from our verified sellers.</p>
    @endif

    @if ($imageUrl)
        <p style="margin:0 0 16px;">
            <img src="{{ $imageUrl }}" alt="{{ $product->title }}" width="480" style="max-width:100%;height:auto;border-radius:8px;display:block;border:0;">
        </p>
    @endif

    <p style="margin:0 0 20px;font-size:20px;font-weight:700;color:#4f46e5;">₹{{ number_format((float) $product->price, 2) }}</p>

    <p style="margin:0 0 20px;">
        <a href="{{ $productUrl }}" style="display:inline-block;background-color:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:999px;font-weight:600;font-size:15px;">View product</a>
    </p>
@endsection

@section('footer')
    <p style="margin:0;">You are receiving this because you subscribed to updates or have a {{ config('app.name') }} account.</p>
    <p style="margin:10px 0 0;">
        <a href="{{ $unsubscribeUrl }}" style="color:#4f46e5;">Unsubscribe</a>
    </p>
@endsection
