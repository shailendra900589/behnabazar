@extends('layouts.app')
@section('title', 'Forgot password')
@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="bb-card p-4 p-lg-5">
                <h1 class="h4 fw-bold mb-2">Reset your password</h1>
                <p class="text-muted small mb-4">Enter the email on your account. We will send a 6-digit code to reset your password.</p>
                <form method="post" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-bloom w-100 py-2">Send reset code</button>
                </form>
                <p class="text-center mt-4 mb-0 small"><a href="{{ route('login') }}">Back to sign in</a></p>
            </div>
        </div>
    </div>
</section>
@endsection
