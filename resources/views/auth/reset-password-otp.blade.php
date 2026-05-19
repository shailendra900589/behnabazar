@extends('layouts.app')
@section('title', 'Reset password with code')
@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="bb-card p-4 p-lg-5">
                <h1 class="h4 fw-bold mb-2">Enter reset code</h1>
                <p class="text-muted small mb-4">We sent a 6-digit code to <strong>{{ $email }}</strong>. Enter it below with your new password.</p>
                <form method="post" action="{{ route('password.verify.submit') }}">
                    @csrf
                    <input type="hidden" name="email" value="{{ old('email', $email) }}">
                    <div class="mb-3">
                        <label class="form-label">6-digit code</label>
                        <input class="form-control text-center fs-4" type="text" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" value="{{ old('otp') }}" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New password</label>
                        <input class="form-control" type="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm password</label>
                        <input class="form-control" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-bloom w-100 py-2">Update password</button>
                </form>
                <form method="post" action="{{ route('password.verify.resend') }}" class="mt-3 text-center">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm text-muted">Resend code</button>
                </form>
                <p class="text-center mt-4 mb-0 small"><a href="{{ route('login') }}">Back to sign in</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
