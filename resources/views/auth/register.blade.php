@extends('layouts.app')
@section('title', 'Register - Behna Bazar')
@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="bb-card p-4 p-lg-5">
                <h1 class="h3 fw-bold mb-2">Create a customer account</h1>
                <p class="text-muted mb-4">We will email a 6-digit code to verify your address before you can checkout or view order history.</p>
                <p class="small mb-4">Want to sell? <a href="{{ route('vendor.register.create') }}" class="fw-semibold text-bloom text-decoration-none">Start vendor onboarding →</a></p>
                <form method="post" action="{{ route('register') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input class="form-control" name="name" value="{{ old('name') }}" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">City <span class="text-muted fw-normal">(optional)</span></label>
                            <input class="form-control" name="city" value="{{ old('city') }}" maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input class="form-control" type="password" name="password" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm password</label>
                            <input class="form-control" type="password" name="password_confirmation" required minlength="8">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-bloom w-100 py-2 mt-4">Send verification code</button>
                </form>
                <p class="text-center text-muted small mt-4 mb-0">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
