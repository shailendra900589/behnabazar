@extends('layouts.app')
@section('title', 'Returns & Refunds Policy - Behna Bazar')
@section('content')
<section class="container py-4 py-lg-5">
    @include('partials.store-info-hero', [
        'title' => 'Returns & Refunds',
        'subtitle' => 'Fair, transparent returns for damaged, defective, or incorrect items. We work with sellers to resolve issues quickly.',
        'icon' => 'bi-arrow-repeat',
        'gradient' => 'linear-gradient(135deg, var(--bb-ink) 0%, #4c1d95 100%)',
    ])

    <div class="row g-4 g-lg-5">
        <div class="col-lg-8">
            <div class="bb-card p-4 p-lg-5 rounded-4 shadow-sm mb-4">
                <h2 class="h4 fw-bold mb-3">Return eligibility</h2>
                <p class="text-muted mb-3">You may request a return or refund when:</p>
                <ul class="text-muted mb-4 ps-3">
                    <li class="mb-2">The item arrived damaged, defective, or not as described.</li>
                    <li class="mb-2">You received the wrong product, size, or quantity.</li>
                    <li class="mb-2">The package was missing items listed on your invoice.</li>
                </ul>
                <p class="text-muted small mb-0"><strong class="text-dark">Time limit:</strong> Report the issue within <strong>48 hours</strong> of delivery for the fastest resolution. Some categories (grocery, perishables, personal care) may have shorter windows shown on the product page.</p>
            </div>

            <div class="bb-card p-4 p-lg-5 rounded-4 shadow-sm mb-4">
                <h2 class="h4 fw-bold mb-3">How to start a return</h2>
                <ol class="text-muted mb-0 ps-3">
                    <li class="mb-2">Sign in and open <a href="{{ route('orders') }}" class="text-bloom fw-semibold">My Orders</a>.</li>
                    <li class="mb-2">Select the order and choose <strong>Request return</strong> (available for eligible delivered orders).</li>
                    <li class="mb-2">Describe the issue and upload photos if the item is damaged or incorrect.</li>
                    <li>Our team or the seller will review your request and update the return status by email.</li>
                </ol>
            </div>

            <div class="bb-card p-4 p-lg-5 rounded-4 shadow-sm mb-4">
                <h2 class="h4 fw-bold mb-3">Refund process</h2>
                <ul class="text-muted mb-0 ps-3">
                    <li class="mb-2">Approved refunds are credited to your original payment method within <strong>5–10 business days</strong> after the return is accepted.</li>
                    <li class="mb-2">COD orders may receive refund via UPI or bank transfer after verification.</li>
                    <li class="mb-2">Shipping fees are refunded only when the return is due to seller or platform error.</li>
                    <li>Partial refunds may apply if only some items in a multi-item order are returned.</li>
                </ul>
            </div>

            <div class="bb-card p-4 p-lg-5 rounded-4 shadow-sm">
                <h2 class="h4 fw-bold mb-3">Non-returnable items</h2>
                <p class="text-muted mb-2">For hygiene and safety, the following are generally not eligible for return unless defective:</p>
                <ul class="text-muted mb-0 ps-3">
                    <li class="mb-2">Opened grocery, perishable food, and consumables</li>
                    <li class="mb-2">Personal care, innerwear, and beauty products once opened</li>
                    <li class="mb-2">Custom or made-to-order items</li>
                    <li>Digital goods and gift cards</li>
                </ul>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="bb-card p-4 rounded-4 shadow-sm border-0 border-start border-4 border-bloom mb-4">
                <h3 class="h6 fw-bold text-uppercase text-muted mb-3">Quick tips</h3>
                <ul class="text-muted small mb-0 ps-3">
                    <li class="mb-2">Keep original packaging until your return is approved.</li>
                    <li class="mb-2">Take clear photos of damage or wrong items.</li>
                    <li>Do not discard the product until support confirms next steps.</li>
                </ul>
            </div>

            <div class="bb-card p-4 rounded-4 shadow-sm mb-4">
                <h3 class="h6 fw-bold text-uppercase text-muted mb-3">Cancellations</h3>
                <p class="text-muted small mb-0">Orders that have not yet shipped can often be cancelled from your Orders page. Once dispatched, cancellation may not be possible—please request a return after delivery if needed.</p>
            </div>

            <div class="bb-card p-4 rounded-4 shadow-sm bg-light border-0">
                <h3 class="h6 fw-bold mb-2">Still need help?</h3>
                <p class="text-muted small mb-3">Email <a href="mailto:support@behnabazar.in" class="text-bloom fw-semibold">support@behnabazar.in</a> with your order number.</p>
                <a href="{{ route('contact') }}" class="btn btn-outline-dark btn-sm rounded-pill">Contact support</a>
            </div>
        </div>
    </div>
</section>
@endsection
