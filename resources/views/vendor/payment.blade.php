@extends('layouts.app')
@section('title', 'Registration fee')
@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="bb-card p-4 p-lg-5">
                <h1 class="h3 fw-bold mb-2">Registration fee</h1>
                <p class="text-muted">Hi <strong>{{ $vendor->name }}</strong>, complete this secure Razorpay payment so we can queue <strong>{{ $vendor->shop_name }}</strong> for admin approval.</p>
                <div class="row g-4 my-4">
                    <div class="col-md-6">
                        <div class="border rounded-4 p-4 h-100 bg-light">
                            <div class="text-muted small">Amount due</div>
                            <div class="display-6 fw-bold text-bloom">Rs. {{ $registrationAmount }}</div>
                            <div class="small text-muted mt-2">One-time vendor onboarding fee</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled small text-muted vstack gap-2 mb-0">
                            <li><i class="bi bi-check-circle text-success me-2"></i>Email verified</li>
                            <li><i class="bi bi-credit-card-2-front text-bloom me-2"></i>Razorpay payment verification</li>
                            <li><i class="bi bi-hourglass-split text-warning me-2"></i>Admin approval after payment</li>
                            <li><i class="bi bi-shield-check text-bloom me-2"></i>QC on every product listing</li>
                        </ul>
                    </div>
                </div>
                <form method="post" action="{{ route('vendor.payment.complete') }}" class="d-grid" id="vendorFeeForm">
                    @csrf
                    <input type="hidden" name="razorpay_order_id" id="vendorRazorpayOrderId">
                    <input type="hidden" name="razorpay_payment_id" id="vendorRazorpayPaymentId">
                    <input type="hidden" name="razorpay_signature" id="vendorRazorpaySignature">
                    <button type="button" class="btn btn-bloom btn-lg py-3" id="vendorFeePayBtn">Pay securely with Razorpay</button>
                </form>
                <div class="position-relative my-4">
                    <hr>
                    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 small text-muted">OR</span>
                </div>
                <form method="post" action="{{ route('vendor.payment.coupon') }}" class="vstack gap-3">
                    @csrf
                    <div>
                        <label class="form-label fw-semibold">Have a registration coupon?</label>
                        <input type="text" name="registration_coupon_code" class="form-control form-control-lg @error('registration_coupon_code') is-invalid @enderror" value="{{ old('registration_coupon_code') }}" placeholder="Enter one-time coupon code" maxlength="32" required autocomplete="off">
                        @error('registration_coupon_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Admin-issued coupons waive the registration fee. Each code works only once.</div>
                    </div>
                    <button type="submit" class="btn btn-outline-secondary btn-lg">Apply coupon &amp; complete registration</button>
                </form>
                <p class="text-center text-muted small mt-3 mb-0">Your shop moves to admin approval after payment or a valid coupon.</p>
            </div>
        </div>
    </div>
</section>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('vendorFeePayBtn')?.addEventListener('click', async function () {
        const response = await fetch("{{ route('vendor.payment.order') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            Toast.fire({ icon: 'error', title: 'Unable to start payment. Check Razorpay keys.' });
            return;
        }

        const data = await response.json();
        new Razorpay({
            key: data.key,
            amount: data.amount,
            currency: data.currency,
            name: data.name,
            description: 'Vendor registration fee',
            order_id: data.order_id,
            handler: function (payment) {
                document.getElementById('vendorRazorpayOrderId').value = payment.razorpay_order_id;
                document.getElementById('vendorRazorpayPaymentId').value = payment.razorpay_payment_id;
                document.getElementById('vendorRazorpaySignature').value = payment.razorpay_signature;
                document.getElementById('vendorFeeForm').submit();
            }
        }).open();
    });
</script>
@endsection
