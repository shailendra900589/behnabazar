@extends('layouts.app')
@section('title', 'Local Delivery - Behna Bazar')
@section('content')
<section class="container py-4 py-lg-5">
    @include('partials.store-info-hero', [
        'title' => 'Local Delivery',
        'subtitle' => 'Fast, reliable delivery from verified sellers in your area. Track every order from dispatch to your doorstep.',
        'icon' => 'bi-truck',
        'gradient' => 'linear-gradient(135deg, var(--bb-ink) 0%, #065f46 100%)',
    ])

    <div class="row g-4 g-lg-5">
        <div class="col-lg-8">
            <div class="bb-card p-4 p-lg-5 rounded-4 shadow-sm mb-4">
                <h2 class="h4 fw-bold mb-3">How delivery works</h2>
                <p class="text-muted mb-4">Behna Bazar connects you with local sellers and delivery partners so groceries, fashion, electronics, and essentials reach you quickly and safely.</p>
                <ol class="text-muted mb-0 ps-3">
                    <li class="mb-2">Place your order and complete payment confirmation.</li>
                    <li class="mb-2">The seller prepares your items (typically within 1–2 business days).</li>
                    <li class="mb-2">A local delivery partner picks up the package and updates tracking at each milestone.</li>
                    <li>Your order is marked <strong>Delivered</strong> once it arrives at your address.</li>
                </ol>
            </div>

            <div class="bb-card p-4 p-lg-5 rounded-4 shadow-sm mb-4">
                <h2 class="h4 fw-bold mb-3">Delivery timelines</h2>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0 small">
                        <thead class="border-bottom">
                            <tr>
                                <th class="text-muted fw-semibold">Category</th>
                                <th class="text-muted fw-semibold">Estimated time</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted">
                            <tr>
                                <td>Grocery &amp; daily essentials</td>
                                <td>Same day – 2 days (metro &amp; tier-1 cities)</td>
                            </tr>
                            <tr>
                                <td>Fashion, beauty &amp; home</td>
                                <td>2–5 business days</td>
                            </tr>
                            <tr>
                                <td>Electronics &amp; large items</td>
                                <td>3–7 business days</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mb-0 mt-3">Timelines may vary by seller location, product availability, and local holidays.</p>
            </div>

            <div class="bb-card p-4 p-lg-5 rounded-4 shadow-sm">
                <h2 class="h4 fw-bold mb-3">Delivery fees</h2>
                <ul class="text-muted mb-0 ps-3">
                    <li class="mb-2"><strong class="text-dark">Free delivery</strong> on eligible orders above ₹499 (promotions may apply).</li>
                    <li class="mb-2">Standard local delivery charges apply on smaller orders; the exact fee is shown at checkout before you pay.</li>
                    <li class="mb-2">Heavy or bulky items may include an additional handling fee, displayed on the product page.</li>
                    <li>Cash on delivery (COD) availability depends on the seller and your delivery pin code.</li>
                </ul>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="bb-card p-4 rounded-4 shadow-sm border-0 border-start border-4 border-bloom mb-4">
                <h3 class="h6 fw-bold text-uppercase text-muted mb-3">Track your order</h3>
                <p class="text-muted small mb-3">Signed-in customers can follow live status updates from the Orders page.</p>
                @auth
                    <a href="{{ route('orders') }}" class="btn btn-bloom btn-sm rounded-pill w-100">View my orders</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-bloom btn-sm rounded-pill w-100">Sign in to track</a>
                @endauth
            </div>

            <div class="bb-card p-4 rounded-4 shadow-sm mb-4">
                <h3 class="h6 fw-bold text-uppercase text-muted mb-3">Service areas</h3>
                <p class="text-muted small mb-0">We currently serve select pin codes across major cities and expanding towns. Enter your address at checkout to confirm delivery availability for your location.</p>
            </div>

            <div class="bb-card p-4 rounded-4 shadow-sm bg-light border-0">
                <h3 class="h6 fw-bold mb-2">Need help?</h3>
                <p class="text-muted small mb-3">For delayed or missing deliveries, contact us within 48 hours of the expected delivery date.</p>
                <a href="{{ route('contact') }}" class="btn btn-outline-dark btn-sm rounded-pill">Contact support</a>
            </div>
        </div>
    </div>
</section>
@endsection
