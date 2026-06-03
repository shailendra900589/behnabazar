@extends('layouts.dashboard')
@section('title', 'Vendor Dashboard')
@section('dashboard')
@php
    $active = auth()->user()->account_status === 'active' || session()->has('impersonated_by');
    $section = request('section', 'overview');
@endphp

@if (auth()->user()->account_status === 'pending_approval')
    <div class="alert alert-info rounded-4 border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-hourglass-split fs-4"></i>
            <div>
                <h2 class="h5 fw-bold mb-1">Shop pending approval</h2>
                <p class="mb-0 text-muted">Your registration fee is recorded. An admin will activate your shop soon.</p>
            </div>
        </div>
    </div>
@elseif (auth()->user()->account_status === 'rejected')
    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-octagon-fill fs-4"></i>
            <div>
                <h2 class="h5 fw-bold mb-1">Account Deactivated</h2>
                <p class="mb-0 text-muted">Your vendor account has been deactivated. Please contact support.</p>
            </div>
        </div>
    </div>
@endif

@if(session()->has('impersonated_by'))
    <div class="alert alert-warning border-warning shadow-sm rounded-4 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="fw-bold text-dark"><i class="bi bi-shield-lock-fill me-2"></i> Admin Impersonation Mode</div>
        <a href="{{ route('manage.vendors.leave_impersonation') }}" class="btn btn-dark btn-sm rounded-pill px-3">Return to Admin Console</a>
    </div>
@endif

{{-- ═══════ SECTION: OVERVIEW ═══════ --}}
@if ($section === 'overview')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="fw-bold mb-0" style="font-size:1.3rem;">Vendor dashboard</h1>
        <p class="text-muted small mb-0">{{ auth()->user()->shop_name }} · {{ auth()->user()->city }}</p>
    </div>
    @if ($active)
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-bloom btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#productModal">
                <i class="bi bi-plus-lg me-1"></i>Add product
            </button>
        </div>
    @else
        <span class="badge bg-secondary-subtle text-secondary border" style="font-size:0.65rem;">Unlocks after admin approval</span>
    @endif
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-muted small">Total Products</span>
                <span class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.85rem;"><i class="bi bi-box-seam"></i></span>
            </div>
            <div class="h2 fw-bold">{{ $products->count() }}</div>
            <div class="small text-muted"><i class="bi bi-check-circle text-success me-1"></i>{{ $products->where('qc_status','approved')->count() }} approved</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-muted small">Total Orders</span>
                <span class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.85rem;"><i class="bi bi-receipt"></i></span>
            </div>
            <div class="h2 fw-bold">{{ $orders->count() }}</div>
            <div class="small text-muted"><i class="bi bi-clock text-warning me-1"></i>{{ $orders->where('status','pending')->count() }} pending</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-muted small">Revenue</span>
                <span class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.85rem;"><i class="bi bi-currency-rupee"></i></span>
            </div>
            <div class="h2 fw-bold">₹{{ number_format($orders->sum('total_price'), 0) }}</div>
            <div class="small text-muted"><i class="bi bi-graph-up-arrow text-success me-1"></i>Lifetime earnings</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="text-muted small">Conversion</span>
                <span class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.85rem;"><i class="bi bi-bullseye"></i></span>
            </div>
            <div class="h2 fw-bold">{{ $conversionRate ?? 0 }}%</div>
            <div class="small text-muted"><i class="bi bi-eye text-primary me-1"></i>{{ number_format($viewsTotal ?? 0) }} views</div>
        </div>
    </div>
</div>

<div class="bb-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h6 fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>My Revenue (Last 7 Days)</h3>
    </div>
    <canvas id="vendorRevenueChart" height="70"></canvas>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('vendorRevenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Revenue (₹)',
                        data: @json($chartData),
                        backgroundColor: 'rgba(79,70,229,0.75)',
                        hoverBackgroundColor: '#4f46e5',
                        borderRadius: 6,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e293b', titleFont: { size: 11 }, bodyFont: { size: 11 } } },
                    scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } }
                }
            });
        });
    </script>
</div>
@endif {{-- end overview --}}

{{-- Resell lives on manage.resell.catalog — sidebar links there directly --}}

{{-- ═══════ SECTION: REFERRALS ═══════ --}}
@if ($section === 'referrals')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="fw-bold mb-0" style="font-size:1.3rem;"><i class="bi bi-share me-2 text-success"></i>Refer & Earn</h1>
</div>
@include('partials.referral-program-card', [
    'referralCode' => $referralCode ?? '',
    'referralRewards' => $referralRewards ?? collect(),
    'referralEnabled' => \App\Support\ReferralSettings::enabled(),
    'referralRole' => 'vendor',
])
@endif {{-- end referrals --}}

{{-- ═══════ SECTION: WALLET ═══════ --}}
@if ($section === 'wallet')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="fw-bold mb-0" style="font-size:1.3rem;"><i class="bi bi-piggy-bank me-2 text-info"></i>Sales Wallet & Payouts</h1>
</div>

@if ($active)
<div class="bb-card mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:0.8rem;"><i class="bi bi-piggy-bank"></i></span>
            <div>
                <h3 class="h6 fw-bold mb-0">Sales wallet & payouts</h3>
                <p class="text-muted small mb-0">Claim to bank when balance is ₹{{ number_format($payoutMin ?? 500, 0) }}+</p>
            </div>
        </div>
        <div class="text-end">
            <span class="text-muted small d-block">Available for Payout</span>
            <span class="h5 fw-bold text-bloom">₹{{ number_format($availableBalance, 2) }}</span>
        </div>
    </div>
    
    @if ($availableBalance >= ($payoutMin ?? 500))
        <form method="post" action="{{ route('manage.payouts.request') }}" class="d-flex flex-wrap gap-2 align-items-end mb-4 bg-light p-3 rounded-3">
            @csrf
            <div>
                <label class="form-label small">Amount (₹)</label>
                <input type="number" name="amount" min="{{ $payoutMin ?? 500 }}" max="{{ $availableBalance }}" value="{{ $availableBalance }}" class="form-control form-control-sm" required>
            </div>
            <div class="flex-grow-1">
                <label class="form-label small">Bank Details (Account No, IFSC, Name)</label>
                <input type="text" name="bank_details" class="form-control form-control-sm" placeholder="e.g. AC: 123456789, IFSC: SBIN0001234, John Doe" required>
            </div>
            <div>
                <button type="submit" class="btn btn-dark btn-sm">Request Payout</button>
            </div>
        </form>
    @else
        <div class="alert alert-soft small mb-4">You need at least ₹{{ number_format($payoutMin ?? 500, 0) }} in delivered earnings to request a payout.</div>
    @endif

    @if($payouts->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Bank Details</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payouts as $p)
                        <tr>
                            <td>{{ $p->created_at->format('M d, Y') }}</td>
                            <td class="fw-bold">₹{{ number_format($p->amount, 2) }}</td>
                            <td class="small text-muted">{{ $p->bank_details }}</td>
                            <td>
                                @if($p->status === 'pending') <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($p->status === 'paid') <span class="badge bg-success">Paid</span>
                                @else <span class="badge bg-danger">Rejected</span> @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(isset($salesWalletTransactions) && $salesWalletTransactions->count())
        <h4 class="h6 fw-bold mt-4 mb-2">Wallet activity</h4>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salesWalletTransactions as $tx)
                        <tr>
                            <td class="small">{{ $tx->created_at->format('M d, Y') }}</td>
                            <td class="small text-muted">{{ str_replace('_', ' ', $tx->type) }}</td>
                            <td class="fw-semibold @if($tx->amount < 0) text-danger @else text-success @endif">
                                {{ $tx->amount < 0 ? '−' : '+' }}₹{{ number_format(abs($tx->amount), 2) }}
                            </td>
                            <td class="small text-muted">{{ $tx->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif
@endif {{-- end wallet --}}

{{-- ═══════ SECTION: PROMOTIONS ═══════ --}}
@if ($section === 'promotions')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="fw-bold mb-0" style="font-size:1.3rem;"><i class="bi bi-megaphone me-2 text-warning"></i>Promotions & Ads</h1>
</div>

@if ($active)
<div class="bb-card mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:0.8rem;"><i class="bi bi-megaphone"></i></span>
            <h3 class="h6 fw-bold mb-0">Promote your products</h3>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $promotions->count() }} promotion(s)</span>
    </div>
    <p class="text-muted small mb-3">Feature an approved listing on storefront ad placements. Ad space auto-adjusts when no ad is available.</p>

    <form method="post" action="{{ route('manage.promotions.save') }}" enctype="multipart/form-data" class="row g-2 align-items-end bg-light rounded-3 p-3 mb-3 border">
        @csrf
        <div class="col-md-4 col-6">
            <label class="form-label">Approved product</label>
            <select name="product_id" class="form-select form-select-sm" required>
                <option value="">Choose product</option>
                @foreach ($products->where('qc_status', 'approved') as $product)
                    <option value="{{ $product->id }}">{{ $product->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-6">
            <label class="form-label">Placement</label>
            <select name="location" class="form-select form-select-sm" required>
                <option value="home_mid">Home mid - ₹{{ number_format($adRates['home_mid'], 0) }}/day</option>
                <option value="home_top">Home top - ₹{{ number_format($adRates['home_top'], 0) }}/day</option>
                <option value="home_bottom">Home bottom - ₹{{ number_format($adRates['home_bottom'], 0) }}/day</option>
            </select>
        </div>
        <div class="col-md-5 col-12">
            <label class="form-label">Headline</label>
            <input class="form-control form-control-sm" name="title" maxlength="160" placeholder="Feature offer headline">
        </div>
        <div class="col-md-4 col-6">
            <label class="form-label">Short message</label>
            <input class="form-control form-control-sm" name="subtitle" maxlength="255" placeholder="Why customers should click">
        </div>
        <div class="col-md-2 col-6">
            <label class="form-label">CTA</label>
            <input class="form-control form-control-sm" name="cta_text" maxlength="80" placeholder="Shop now">
        </div>
        <div class="col-md-3 col-6">
            <label class="form-label">Ad image</label>
            <input class="form-control form-control-sm" type="file" name="image" accept="image/*">
        </div>
        <div class="col-md-3 col-6">
            <label class="form-label">Starts</label>
            <input class="form-control form-control-sm" type="datetime-local" name="starts_at">
        </div>
        <div class="col-md-3 col-6">
            <label class="form-label">Ends</label>
            <input class="form-control form-control-sm" type="datetime-local" name="ends_at">
        </div>
        <div class="col-md-3 col-6 d-grid">
            <button type="submit" class="btn btn-bloom btn-sm"><i class="bi bi-plus-lg me-1"></i>Create</button>
        </div>
    </form>

    @if($promotions->count())
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Placement</th>
                    <th>Status</th>
                    <th>Clicks</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($promotions as $promotion)
                    <tr>
                        <td class="fw-semibold">{{ $promotion->product->title ?? $promotion->title }}</td>
                        <td class="text-muted">{{ str_replace('_', ' ', $promotion->location) }}</td>
                        <td><span class="badge {{ $promotion->isActiveNow() ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}" style="font-size:0.6rem;">{{ $promotion->isActiveNow() ? 'active' : 'ended' }}</span></td>
                        <td class="fw-semibold">{{ $promotion->clicks }}</td>
                        <td class="text-end">
                            <form method="post" action="{{ route('manage.promotions.delete', $promotion) }}" onsubmit="return confirm('Remove this promotion?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p class="text-muted small text-center py-2 mb-0">No promotions yet. Create one above.</p>
    @endif
</div>

@if ($active)
<div class="bb-card mb-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:0.8rem;"><i class="bi bi-wallet2"></i></span>
        <h3 class="h6 fw-bold mb-0">Ad Wallet</h3>
    </div>
    <div class="row g-3 align-items-center">
        <div class="col-lg-4">
            <div class="bg-light rounded-3 p-3 text-center">
                <div class="text-muted small mb-1">Balance</div>
                <div class="h4 fw-bold text-bloom mb-0">₹{{ number_format(auth()->user()->ad_wallet_balance, 2) }}</div>
                <div class="small text-muted mt-1">Min top-up: ₹{{ number_format($adWalletMinTopup, 0) }}</div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="small text-muted mb-2 fw-semibold">Current ad rates</div>
            <div class="d-flex flex-column gap-1 small">
                <span class="d-flex justify-content-between"><span>Home top</span><strong>₹{{ number_format($adRates['home_top'], 0) }}/day</strong></span>
                <span class="d-flex justify-content-between"><span>Home middle</span><strong>₹{{ number_format($adRates['home_mid'], 0) }}/day</strong></span>
                <span class="d-flex justify-content-between"><span>Home bottom</span><strong>₹{{ number_format($adRates['home_bottom'], 0) }}/day</strong></span>
            </div>
        </div>
        <div class="col-lg-4">
            <form id="adWalletTopupForm" method="post" action="{{ route('manage.ad-wallet.verify') }}" class="d-grid gap-2">
                @csrf
                <input type="number" min="{{ $adWalletMinTopup }}" step="1" name="amount" id="adWalletAmount" class="form-control form-control-sm" placeholder="Amount, e.g. 1000" required>
                <input type="hidden" name="razorpay_order_id" id="walletRazorpayOrderId">
                <input type="hidden" name="razorpay_payment_id" id="walletRazorpayPaymentId">
                <input type="hidden" name="razorpay_signature" id="walletRazorpaySignature">
                <button type="button" class="btn btn-dark btn-sm" id="adWalletPayBtn"><i class="bi bi-plus-lg me-1"></i>Top up wallet</button>
            </form>
        </div>
    </div>
    @if($walletTransactions->isNotEmpty())
        <div class="table-responsive mt-4">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Notes</th></tr></thead>
                <tbody>
                    @foreach($walletTransactions as $txn)
                        <tr>
                            <td>{{ $txn->created_at->format('M d, Y') }}</td>
                            <td><span class="badge badge-soft">{{ $txn->type }}</span></td>
                            <td>Rs. {{ number_format($txn->amount, 2) }}</td>
                            <td class="small text-muted">{{ $txn->notes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif
@endif

@if ($active)
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('adWalletPayBtn')?.addEventListener('click', async function () {
        const amountInput = document.getElementById('adWalletAmount');
        if (!amountInput.value || Number(amountInput.value) < Number(amountInput.min)) {
            amountInput.reportValidity();
            return;
        }

        const response = await fetch("{{ route('manage.ad-wallet.order') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json'
            },
            body: JSON.stringify({ amount: amountInput.value })
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
            description: 'Ad wallet top-up',
            order_id: data.order_id,
            handler: function (payment) {
                document.getElementById('walletRazorpayOrderId').value = payment.razorpay_order_id;
                document.getElementById('walletRazorpayPaymentId').value = payment.razorpay_payment_id;
                document.getElementById('walletRazorpaySignature').value = payment.razorpay_signature;
                document.getElementById('adWalletTopupForm').submit();
            }
        }).open();
    });
</script>
@endif
@endif {{-- end promotions --}}

{{-- ═══════ SECTION: PRODUCTS ═══════ --}}
@if ($section === 'products')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="fw-bold mb-0" style="font-size:1.3rem;"><i class="bi bi-bag me-2 text-primary"></i>My Products</h1>
    @if ($active)
        <button class="btn btn-bloom btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="bi bi-plus-lg me-1"></i>Add product
        </button>
    @endif
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>QC</th>
                    @if ($active)
                        <th class="text-end">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $product->title }}</div>
                            @if($product->isResellListing())
                                <span class="badge bg-info-subtle text-info border mt-1" style="font-size:0.6rem;">
                                    {{ $product->resell_mode === 'customized' ? 'Branded' : 'Quick resell' }}
                                </span>
                            @else
                                <span class="badge bg-light text-muted border mt-1" style="font-size:0.6rem;">Your listing</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $product->category->name ?? 'N/A' }}</td>
                        <td class="fw-semibold">₹{{ number_format($product->price, 0) }}</td>
                        <td>
                            <span class="badge {{ $product->qc_status === 'approved' ? 'bg-success-subtle text-success' : ($product->qc_status === 'pending' ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') }}" style="font-size:0.65rem;">{{ $product->qc_status }}</span>
                        </td>
                        @if ($active)
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <button type="button" class="btn btn-soft btn-sm btn-edit-product"
                                        data-bs-toggle="modal" data-bs-target="#productEditModal"
                                        data-id="{{ $product->id }}"
                                        data-title="{{ $product->title }}"
                                        data-price="{{ $product->price }}"
                                        data-category-id="{{ $product->category_id }}"
                                        data-description="{{ e($product->description) }}"
                                        data-qc-status="{{ $product->qc_status }}"
                                        data-resell="{{ $product->isResellListing() ? '1' : '0' }}"
                                        data-resell-min="{{ $product->source_base_price ?? '' }}"
                                        data-compare-at="{{ $product->compare_at_price ?? '' }}"
                                        data-reseller-dp="{{ $product->reseller_dp_price ?? '' }}"
                                        data-resell-allowed="{{ $product->resell_allowed ? '1' : '0' }}"
                                        data-images='@json($product->images->map(fn($i) => ["id" => $i->id, "url" => $i->url()])->values())'>
                                        <i class="bi bi-pencil me-1"></i>Edit
                                    </button>
                                    <form method="post" action="{{ route('manage.products.delete', $product) }}" class="d-inline" onsubmit="return confirm('Delete this listing?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No products yet. Add your first product!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif {{-- end products --}}

{{-- ═══════ SECTION: ORDERS ═══════ --}}
@if ($section === 'orders')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="fw-bold mb-0" style="font-size:1.3rem;"><i class="bi bi-receipt me-2 text-success"></i>Orders</h1>
    <a href="{{ route('manage.orders.export') }}" class="btn btn-outline-dark btn-sm rounded-pill"><i class="bi bi-download me-1"></i>Export CSV</a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->product_name }}</td>
                        <td>
                            <span class="badge {{ $order->status === 'delivered' ? 'bg-success-subtle text-success' : ($order->status === 'cancelled' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }}" style="font-size:0.65rem;">{{ $order->status }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <form data-ajax-form data-method="PATCH" action="{{ route('manage.orders.update', $order) }}" class="d-flex gap-1 flex-wrap align-items-center">
                                    <select name="status" class="form-select form-select-sm" style="min-width:120px;" @disabled(! $active)>
                                        <option value="pending" @selected($order->status === 'pending')>pending</option>
                                        <option value="processing" @selected($order->status === 'processing')>processing</option>
                                        <option value="shipped" @selected($order->status === 'shipped')>shipped</option>
                                        <option value="out_for_delivery" @selected($order->status === 'out_for_delivery')>out for delivery</option>
                                        <option value="delivered" @selected($order->status === 'delivered')>delivered</option>
                                        <option value="cancelled" @selected($order->status === 'cancelled')>cancelled</option>
                                    </select>
                                    <input type="text" name="tracking_msg" class="form-control form-control-sm" style="min-width:130px;" placeholder="Tracking message" value="{{ $order->tracking_msg }}" @disabled(! $active)>
                                    <button type="submit" class="btn btn-soft btn-sm" @disabled(! $active)>Update</button>
                                </form>
                                <a href="{{ route('orders.invoice', $order) }}" class="btn btn-outline-secondary btn-sm" target="_blank" title="Invoice"><i class="bi bi-file-pdf"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif {{-- end orders --}}

{{-- ═══════ SECTION: Q&A ═══════ --}}
@if ($section === 'questions')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="fw-bold mb-0" style="font-size:1.3rem;"><i class="bi bi-chat-left-text me-2 text-primary"></i>Customer Questions</h1>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Question</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($questions as $q)
                    <tr>
                        <td class="fw-semibold" style="max-width:150px;">{{ $q->product->title }}</td>
                        <td>
                            <div class="small text-muted mb-1">Asked by {{ $q->user->name }}</div>
                            <div class="fw-semibold">{{ $q->question }}</div>
                        </td>
                        <td>
                            <form method="post" action="{{ route('manage.questions.update', $q) }}" class="d-flex gap-1 flex-wrap align-items-center">
                                @csrf @method('PATCH')
                                <input type="text" name="answer" class="form-control form-control-sm" style="min-width:180px;" placeholder="Write your answer..." required @disabled(! $active)>
                                <button type="submit" name="status" value="answered" class="btn btn-dark btn-sm">Answer</button>
                                <button type="submit" name="status" value="rejected" class="btn btn-outline-danger btn-sm" formnovalidate @disabled(! $active)>Reject</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No pending questions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif {{-- end questions --}}

@if ($active)
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="post" enctype="multipart/form-data" action="{{ route('manage.products.save') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload product for QC</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input name="title" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sale price (₹)</label>
                        <input name="price" type="number" step="0.01" min="1" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">MRP / cross price (₹) <span class="text-danger">*</span></label>
                        <input name="compare_at_price" type="number" step="0.01" min="1" class="form-control" placeholder="e.g. 599" required>
                        <small class="text-muted">Must be higher than sale — shown crossed on shop</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reseller DP (₹)</label>
                        <input name="reseller_dp_price" type="number" step="0.01" min="0" class="form-control" placeholder="Price for other vendors">
                        <small class="text-muted">Shown in resell catalog to resellers</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Primary Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gallery images (max {{ config('product.max_gallery_images', 5) }})</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Up to {{ config('product.max_gallery_images', 5) }} photos total (including primary). Cards auto-rotate when multiple.</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="resell_allowed" value="1" id="resellAllowedNew" checked>
                            <label class="form-check-label" for="resellAllowedNew">Allow other vendors to resell this product</label>
                        </div>
                    </div>
                    @include('partials.variant-builder', ['prefix' => 'variants', 'containerId' => 'variantsContainer', 'rowIndex' => 0])
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-bloom">Submit for QC</button>
                </div>
            </form>
        </div>
    </div>
    
    @include('partials.product-edit-modal', ['categories' => $categories])
@endif
@endsection
