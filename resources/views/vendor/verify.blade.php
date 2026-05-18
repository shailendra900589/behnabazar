@extends('layouts.app')
@section('title', 'Verify shop email')
@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="bb-card p-4 p-lg-5 text-center">
                <div class="rounded-circle bg-light text-bloom d-inline-flex align-items-center justify-content-center mb-3 border" style="width:64px;height:64px">
                    <i class="bi bi-envelope-check fs-3"></i>
                </div>
                <h1 class="h4 fw-bold mb-2">Enter verification code</h1>
                <p class="text-muted small mb-4">Check your inbox for the 6-digit code. It expires in 10 minutes.</p>
                <form method="post" action="{{ route('vendor.verify.submit') }}" class="vstack gap-3">
                    @csrf
                    <input class="form-control form-control-lg text-center tracking-wide" name="otp" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="••••••" required autocomplete="one-time-code">
                    <button type="submit" class="btn btn-bloom py-2">Verify &amp; continue</button>
                </form>
                <form method="post" action="{{ route('vendor.verify.resend') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm text-decoration-none">Resend code</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
