@extends('layouts.app')
@section('title', 'Unsubscribed')
@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="bb-card p-4 p-lg-5 text-center">
                <i class="bi bi-envelope-check text-bloom display-4 mb-3 d-block"></i>
                <h1 class="h4 fw-bold mb-2">You are unsubscribed</h1>
                <p class="text-muted mb-4">{{ $email }} will no longer receive promotional emails from {{ config('app.name') }}.</p>
                <a href="{{ route('home') }}" class="btn btn-bloom rounded-pill px-4">Back to shop</a>
            </div>
        </div>
    </div>
</section>
@endsection
