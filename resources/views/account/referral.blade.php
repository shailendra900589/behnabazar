@extends('layouts.app')
@section('title', 'Refer & Earn')
@section('content')
<section class="container py-3 py-md-4 py-lg-5 bb-account-page">
    <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
        <div>
            <h1 class="fw-bold mb-1 h4 h-md-auto">Refer & Earn</h1>
            <p class="text-muted small mb-0">Share with friends and earn rewards!</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-soft btn-sm">← Dashboard</a>
    </div>

    @include('partials.referral-program-card', [
        'referralCode' => $referralCode,
        'referralRewards' => $referralRewards,
        'referralEnabled' => \App\Support\ReferralSettings::enabled(),
        'referralRole' => $user->role,
    ])

    <div class="bb-card p-4 rounded-4">
        <h2 class="h6 fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>How it works</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-center p-3 bg-light rounded-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:40px;height:40px;"><i class="bi bi-share fs-5"></i></div>
                    <h3 class="h6 fw-bold mb-1">1. Share</h3>
                    <p class="small text-muted mb-0">Share your referral link with friends</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-3 bg-light rounded-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:40px;height:40px;"><i class="bi bi-person-plus fs-5"></i></div>
                    <h3 class="h6 fw-bold mb-1">2. Friend Joins</h3>
                    <p class="small text-muted mb-0">They sign up using your link</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-3 bg-light rounded-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:40px;height:40px;"><i class="bi bi-gift fs-5"></i></div>
                    <h3 class="h6 fw-bold mb-1">3. Earn Rewards</h3>
                    <p class="small text-muted mb-0">Get coins or wallet credit on their first order</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
