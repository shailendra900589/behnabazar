@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
<section class="container py-2 py-md-4 py-lg-5 bb-checkout-page">
    @include('partials.trust-strip')
    <h1 class="fw-bold mb-2 mb-md-4 h4 h-md-auto">Checkout</h1>
    <div class="row g-3 g-md-4">
        <div class="col-lg-8 order-2 order-lg-1">
            <div class="bb-card p-3 p-md-4 mb-3 mb-md-4 shadow-sm rounded-4 border-0">
                <form method="get">
                    <label class="form-label fw-semibold">Coupon Code</label>
                    <div class="input-group mb-3">
                        <input name="coupon_code" class="form-control form-control-lg bg-light border-0" value="{{ request('coupon_code') }}" placeholder="Enter code here...">
                        <button class="btn btn-dark px-4">Apply</button>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="use_coins" id="useCoins" value="1" onchange="this.form.submit()" @checked(request('use_coins'))>
                        <label class="form-check-label fw-semibold" for="useCoins">Use Behna Coins (Balance: <i class="bi bi-coin text-warning"></i> {{ auth()->user()->coins }})</label>
                    </div>
                </form>
            </div>

            <form method="post" action="{{ route('checkout.place') }}" class="bb-card p-3 p-md-4 shadow-sm rounded-4 border-0" id="checkoutForm">
                @csrf
                <input type="hidden" name="coupon_code" value="{{ request('coupon_code') }}">
                <input type="hidden" name="razorpay_order_id" id="checkoutRazorpayOrderId">
                <input type="hidden" name="razorpay_payment_id" id="checkoutRazorpayPaymentId">
                <input type="hidden" name="razorpay_signature" id="checkoutRazorpaySignature">
                @if(request('use_coins'))
                    <input type="hidden" name="use_coins" value="1">
                @endif
                
                <h4 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill me-2 text-primary"></i>Shipping Address</h4>
                
                @if($addresses->isNotEmpty())
                    <div class="row g-3 mb-4">
                        @foreach($addresses as $addr)
                        <div class="col-12 col-md-6">
                            <label class="border rounded-4 p-3 d-block h-100 cursor-pointer position-relative bb-checkout-address" style="cursor:pointer;" onclick="document.getElementById('addr_{{ $addr->id }}').checked = true; document.getElementById('new_address_block').classList.add('d-none'); document.getElementById('addressTextarea').required = false; document.getElementById('phoneInput').required = false;">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="address_id" id="addr_{{ $addr->id }}" value="{{ $addr->id }}" {{ ($addr->is_default || ($loop->first && !$addresses->contains('is_default', true))) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold d-block" for="addr_{{ $addr->id }}">
                                        {{ $addr->name ?? auth()->user()->name }}
                                        @if($addr->is_default)<span class="badge bg-primary ms-1">Default</span>@endif
                                    </label>
                                </div>
                                <div class="small text-muted mt-2 ps-4">
                                    {{ $addr->address }}<br>
                                    @if($addr->city){{ $addr->city }} - {{ $addr->pincode }}<br>@endif
                                    <i class="bi bi-telephone mt-1 d-inline-block"></i> {{ $addr->phone }}
                                </div>
                            </label>
                        </div>
                        @endforeach
                        <div class="col-12 col-md-6">
                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-center h-100 text-primary fw-semibold bb-checkout-address" style="cursor:pointer; border-style: dashed !important;" onclick="document.getElementById('addr_new').checked = true; document.getElementById('new_address_block').classList.remove('d-none'); document.getElementById('addressTextarea').required = true; document.getElementById('phoneInput').required = true;">
                                <div class="form-check m-0 d-flex align-items-center gap-2">
                                    <input class="form-check-input m-0" type="radio" name="address_id" id="addr_new" value="">
                                    <span>Add New Address</span>
                                </div>
                            </label>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="address_id" value="">
                @endif

                @if(request('coupon_code'))
                    <input type="hidden" name="coupon_code" value="{{ request('coupon_code') }}">
                @endif
                @if(request('use_coins'))
                    <input type="hidden" name="use_coins" value="1">
                @endif

                <div id="new_address_block" class="{{ $addresses->isNotEmpty() ? 'd-none' : '' }}">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Street Address</label>
                        <textarea id="addressTextarea" class="form-control bg-light border-0" name="address" rows="3" placeholder="House/Flat No., Building, Street..." {{ $addresses->isEmpty() ? 'required' : '' }}>{{ $addresses->isEmpty() ? auth()->user()->address : '' }}</textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input id="phoneInput" class="form-control bg-light border-0" name="phone" value="{{ $addresses->isEmpty() ? auth()->user()->phone : '' }}" {{ $addresses->isEmpty() ? 'required' : '' }}>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">PIN</label>
                            <input class="form-control bg-light border-0" name="pincode" maxlength="6" placeholder="6-digit">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">City</label>
                            <input class="form-control bg-light border-0" name="city" value="{{ auth()->user()->city }}">
                        </div>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="save_address" value="1" id="saveAddr">
                        <label class="form-check-label" for="saveAddr">Save to address book</label>
                    </div>
                </div>

                <hr class="opacity-10 my-4">

                <h4 class="fw-bold mb-3"><i class="bi bi-credit-card-fill me-2 text-success"></i>Payment Method</h4>
                <div class="d-flex flex-column gap-2 mb-4">
                    <label class="border rounded-4 p-3 d-flex gap-3 align-items-center cursor-pointer payment-option border-bloom bg-light">
                        <input type="radio" name="payment_method" value="Prepaid" class="form-check-input m-0" checked>
                        <div>
                            <div class="fw-bold"><i class="bi bi-shield-lock me-1 text-bloom"></i> Pay online (Razorpay)</div>
                            <div class="small text-muted">UPI, cards, netbanking — instant confirmation</div>
                        </div>
                    </label>
                    @if($codEnabled ?? true)
                    <label class="border rounded-4 p-3 d-flex gap-3 align-items-center cursor-pointer payment-option">
                        <input type="radio" name="payment_method" value="COD" class="form-check-input m-0">
                        <div>
                            <div class="fw-bold"><i class="bi bi-cash-coin me-1 text-success"></i> Cash on Delivery</div>
                            <div class="small text-muted">Pay when your order arrives</div>
                        </div>
                    </label>
                    @endif
                </div>

                <button type="submit" id="placeOrderBtn" class="btn btn-bloom btn-lg w-100 rounded-pill shadow-sm py-2 py-md-3 fw-bold mt-2 position-relative overflow-hidden">
                    <span id="btnText"><i class="bi bi-lock-fill me-2 opacity-50"></i> Place Order • ₹{{ number_format($total, 2) }}</span>
                </button>
            </form>

            <!-- Payment Processing Modal overlay -->
            <div id="paymentOverlay" class="position-fixed top-0 start-0 w-100 h-100 z-3 d-none flex-column justify-content-center align-items-center" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(5px);">
                <div class="text-center">
                    <div class="spinner-border text-bloom mb-4" role="status" style="width: 4rem; height: 4rem; border-width: 0.4rem;" id="paymentSpinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <i class="bi bi-check-circle-fill text-success d-none mb-3" style="font-size: 5rem;" id="paymentSuccessIcon"></i>
                    <h3 class="fw-bold mb-2" id="paymentStatus">Connecting to Secure Gateway...</h3>
                    <p class="text-muted" id="paymentSubtext">Please do not close or refresh this page.</p>
                </div>
            </div>

            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <script>
                const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
                const btnText = document.getElementById('btnText');
                function selectedPayment() {
                    return document.querySelector('input[name="payment_method"]:checked')?.value || 'Prepaid';
                }
                paymentRadios.forEach(r => r.addEventListener('change', () => {
                    btnText.innerHTML = r.value === 'COD'
                        ? '<i class="bi bi-cash me-2"></i> Place COD Order • ₹{{ number_format($total, 2) }}'
                        : '<i class="bi bi-lock-fill me-2 opacity-50"></i> Place Order • ₹{{ number_format($total, 2) }}';
                }));

                document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
                    if (selectedPayment() === 'COD') {
                        return true;
                    }
                    e.preventDefault();
                    const form = this;
                        const overlay = document.getElementById('paymentOverlay');
                        const status = document.getElementById('paymentStatus');
                        const subtext = document.getElementById('paymentSubtext');
                        const spinner = document.getElementById('paymentSpinner');
                        const successIcon = document.getElementById('paymentSuccessIcon');
                        
                        overlay.classList.remove('d-none');
                        overlay.classList.add('d-flex');
                        document.body.style.overflow = 'hidden';
                        
                    const response = await fetch("{{ route('checkout.payment-order') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            coupon_code: "{{ request('coupon_code') }}",
                            use_coins: "{{ request('use_coins') ? 1 : 0 }}"
                        })
                    });

                    if (!response.ok) {
                        overlay.classList.add('d-none');
                        Toast.fire({ icon: 'error', title: 'Unable to start payment. Check Razorpay keys.' });
                        return;
                    }

                    const data = await response.json();
                    overlay.classList.add('d-none');
                    new Razorpay({
                        key: data.key,
                        amount: data.amount,
                        currency: data.currency,
                        name: data.name,
                        description: 'Order payment',
                        order_id: data.order_id,
                        handler: function(payment) {
                            document.getElementById('checkoutRazorpayOrderId').value = payment.razorpay_order_id;
                            document.getElementById('checkoutRazorpayPaymentId').value = payment.razorpay_payment_id;
                            document.getElementById('checkoutRazorpaySignature').value = payment.razorpay_signature;
                            overlay.classList.remove('d-none');
                            overlay.classList.add('d-flex');
                            status.innerText = "Payment Successful!";
                            subtext.innerText = "Verifying payment and creating your order...";
                            spinner.classList.add('d-none');
                            successIcon.classList.remove('d-none');
                            form.submit();
                        }
                    }).open();
                });
            </script>
        </div>
        <div class="col-lg-4 order-1 order-lg-2">
            <div class="bb-card bb-checkout-summary p-3 p-md-4 shadow-sm rounded-4 border-0 sticky-lg-top" style="top: 100px;">
                <h4 class="fw-bold mb-3 mb-md-4 h6 h-md-auto">Order Summary</h4>
                @include('partials.free-shipping-bar', ['cartTotal' => $subtotal, 'freeShippingThreshold' => $freeShippingThreshold ?? 0])
                
                <form method="GET" action="{{ route('checkout') }}" class="mb-4">
                    <div class="input-group mb-3 shadow-sm rounded-pill overflow-hidden border">
                        <input type="text" name="coupon_code" class="form-control border-0 bg-light px-4" placeholder="Enter coupon code" value="{{ request('coupon_code') }}">
                        @if(request('use_coins'))
                            <input type="hidden" name="use_coins" value="1">
                        @endif
                        <button class="btn btn-dark px-4 fw-bold" type="submit">Apply</button>
                    </div>
                </form>

                @if(isset($availableCoupons) && $availableCoupons->isNotEmpty())
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-tags-fill text-success"></i>
                            <span class="fw-bold small text-uppercase tracking-wider">Available Offers</span>
                        </div>
                        <div class="d-flex flex-column gap-2 max-h-200 overflow-auto pe-2">
                            @foreach($availableCoupons as $avail)
                                <div class="border border-success border-opacity-25 bg-success bg-opacity-10 rounded-3 p-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-success font-monospace small">{{ $avail->code }}</div>
                                        <div class="small text-muted" style="font-size: 0.7rem;">Save {{ $avail->discount_type === 'flat' ? '₹'.$avail->discount_value : $avail->discount_value.'%' }}</div>
                                    </div>
                                    <form method="GET" action="{{ route('checkout') }}" class="m-0">
                                        <input type="hidden" name="coupon_code" value="{{ $avail->code }}">
                                        @if(request('use_coins')) <input type="hidden" name="use_coins" value="1"> @endif
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3 py-1" style="font-size: 0.7rem;">Apply</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(auth()->user()->coins > 0)
                <div class="bb-card-lite p-3 mb-4 rounded-4 border d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-warning"><i class="bi bi-coin me-1"></i>Behna Coins</div>
                        <div class="small text-muted">You have {{ number_format(auth()->user()->coins) }} coins available.</div>
                    </div>
                    <form method="GET" action="{{ route('checkout') }}">
                        @if(request('coupon_code'))
                            <input type="hidden" name="coupon_code" value="{{ request('coupon_code') }}">
                        @endif
                        @if(request('use_coins'))
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">Remove</button>
                        @else
                            <input type="hidden" name="use_coins" value="1">
                            <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark">Use Coins</button>
                        @endif
                    </form>
                </div>
                @endif
                <div class="d-flex justify-content-between py-2 text-muted">
                    <span>Subtotal ({{ $items->sum('quantity') }} items)</span>
                    <strong class="text-dark">₹{{ number_format($subtotal, 2) }}</strong>
                </div>
                @if($discount > 0)
                <div class="d-flex justify-content-between py-2 text-success">
                    <span>Coupon Discount</span>
                    <strong>-₹{{ number_format($discount, 2) }}</strong>
                </div>
                @endif
                @if($coinDiscount > 0)
                <div class="d-flex justify-content-between py-2 text-warning">
                    <span>Coins Redeemed</span>
                    <strong>-₹{{ number_format($coinDiscount, 2) }}</strong>
                </div>
                @endif
                <div class="d-flex justify-content-between py-2 text-muted">
                    <span>Shipping</span>
                    <strong class="text-success">FREE</strong>
                </div>
                <hr class="my-3 opacity-10">
                <div class="d-flex justify-content-between align-items-center h4 mb-0">
                    <span class="fw-semibold">Total</span>
                    <strong class="text-bloom">₹{{ number_format($total, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
