@extends('layouts.app')
@section('title', 'Choose new password')
@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="bb-card p-4 p-lg-5">
                <h1 class="h4 fw-bold mb-2">Choose a new password</h1>
                <p class="text-muted small mb-4">Use at least 8 characters.</p>
                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email', $email) }}" required>
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
            </div>
        </div>
    </div>
</section>
@endsection
