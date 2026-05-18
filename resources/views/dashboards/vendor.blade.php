@extends('layouts.dashboard')
@section('title', 'Vendor Dashboard')
@section('dashboard')
@php($active = auth()->user()->account_status === 'active')

@if (auth()->user()->account_status === 'pending_approval')
    <div class="alert alert-info rounded-4 border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-hourglass-split fs-4"></i>
            <div>
                <h2 class="h5 fw-bold mb-1">Shop pending approval</h2>
                <p class="mb-0 text-muted">Your registration fee is recorded. An admin will activate your shop soon. You can browse the storefront; product uploads unlock once you are active.</p>
            </div>
        </div>
    </div>
@elseif (auth()->user()->account_status === 'rejected')
    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-exclamation-octagon-fill fs-4"></i>
            <div>
                <h2 class="h5 fw-bold mb-1">Account Deactivated</h2>
                <p class="mb-0 text-muted">Your vendor account has been deactivated by the administrator. You cannot add products or process new orders. Please contact support.</p>
            </div>
        </div>
    </div>
@endif

@if(session()->has('impersonated_by'))
    <div class="alert alert-warning border-warning shadow-sm rounded-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="fw-bold text-dark"><i class="bi bi-shield-lock-fill me-2"></i> Admin Impersonation Mode</div>
        <a href="{{ route('manage.vendors.leave_impersonation') }}" class="btn btn-dark btn-sm rounded-pill px-3">Return to Admin Console</a>
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h1 class="fw-bold mb-1">Vendor dashboard</h1>
        <p class="text-muted mb-0">{{ auth()->user()->shop_name }} · {{ auth()->user()->city }}</p>
    </div>
    @if ($active)
        <button class="btn btn-bloom" type="button" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="bi bi-plus-lg"></i> Add product
        </button>
    @endif
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Total Products</span>
                <span class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="bi bi-box-seam"></i></span>
            </div>
            <div class="h2 fw-bold mb-0">{{ $products->count() }}</div>
            <div class="small text-muted mt-1"><i class="bi bi-check-circle text-success me-1"></i>{{ $products->where('qc_status','approved')->count() }} approved</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Total Orders</span>
                <span class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="bi bi-receipt"></i></span>
            </div>
            <div class="h2 fw-bold mb-0">{{ $orders->count() }}</div>
            <div class="small text-muted mt-1"><i class="bi bi-clock text-warning me-1"></i>{{ $orders->where('status','pending')->count() }} pending</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Revenue</span>
                <span class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="bi bi-currency-rupee"></i></span>
            </div>
            <div class="h2 fw-bold mb-0">₹{{ number_format($orders->sum('total_price'), 0) }}</div>
            <div class="small text-muted mt-1"><i class="bi bi-graph-up-arrow text-success me-1"></i>Lifetime earnings</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Conversion</span>
                <span class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;"><i class="bi bi-bullseye"></i></span>
            </div>
            <div class="h2 fw-bold mb-0">{{ $conversionRate ?? 0 }}%</div>
            <div class="small text-muted mt-1"><i class="bi bi-eye text-primary me-1"></i>{{ number_format($viewsTotal ?? 0) }} views</div>
        </div>
    </div>
</div>

<div class="bb-card mb-4 p-4">
    <h3 class="h6 fw-bold mb-3">My Revenue (Last 7 Days)</h3>
    <canvas id="vendorRevenueChart" height="80"></canvas>
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
                        backgroundColor: '#4f46e5',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        });
    </script>
</div>

</div>

@if ($active)
<div class="bb-card mb-4 p-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h3 class="h6 fw-bold mb-1">Earnings & Payouts</h3>
            <p class="text-muted small mb-0">Request a payout to your bank account when you have available earnings from delivered orders.</p>
        </div>
        <div class="text-end">
            <span class="text-muted small d-block">Available for Payout</span>
            <span class="h4 fw-bold text-bloom">₹{{ number_format($availableBalance, 2) }}</span>
        </div>
    </div>
    
    @if ($availableBalance >= 500)
        <form method="post" action="{{ route('manage.payouts.request') }}" class="d-flex flex-wrap gap-2 align-items-end mb-4 bg-light p-3 rounded-3">
            @csrf
            <div>
                <label class="form-label small">Amount (₹)</label>
                <input type="number" name="amount" min="500" max="{{ $availableBalance }}" value="{{ $availableBalance }}" class="form-control form-control-sm" required>
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
        <div class="alert alert-soft small mb-4">You need at least ₹500 in delivered earnings to request a payout.</div>
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
</div>
@endif

@if ($active)
<div class="bb-card mb-4 p-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h3 class="h6 fw-bold mb-1">Promote your products</h3>
            <p class="text-muted small mb-0">Feature an approved listing on storefront ad placements. The ad space auto-adjusts when no ad is available or a shopper closes it.</p>
        </div>
        <span class="badge badge-soft">{{ $promotions->count() }} promotion(s)</span>
    </div>

    <form method="post" action="{{ route('manage.promotions.save') }}" enctype="multipart/form-data" class="row g-3 align-items-end bg-light rounded-4 p-3 mb-4">
        @csrf
        <div class="col-md-4">
            <label class="form-label small">Approved product</label>
            <select name="product_id" class="form-select" required>
                <option value="">Choose product</option>
                @foreach ($products->where('qc_status', 'approved') as $product)
                    <option value="{{ $product->id }}">{{ $product->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Placement</label>
            <select name="location" class="form-select" required>
                <option value="home_mid">Home middle - Rs. {{ number_format($adRates['home_mid'], 2) }}/day</option>
                <option value="home_top">Home top - Rs. {{ number_format($adRates['home_top'], 2) }}/day</option>
                <option value="home_bottom">Home bottom - Rs. {{ number_format($adRates['home_bottom'], 2) }}/day</option>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label small">Headline</label>
            <input class="form-control" name="title" maxlength="160" placeholder="Feature offer headline">
        </div>
        <div class="col-md-5">
            <label class="form-label small">Short message</label>
            <input class="form-control" name="subtitle" maxlength="255" placeholder="Why customers should click">
        </div>
        <div class="col-md-3">
            <label class="form-label small">CTA</label>
            <input class="form-control" name="cta_text" maxlength="80" placeholder="Shop now">
        </div>
        <div class="col-md-4">
            <label class="form-label small">Optional ad image</label>
            <input class="form-control" type="file" name="image" accept="image/*">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Starts at</label>
            <input class="form-control" type="datetime-local" name="starts_at">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Ends at</label>
            <input class="form-control" type="datetime-local" name="ends_at">
        </div>
        <div class="col-md-6 d-grid">
            <button type="submit" class="btn btn-bloom">Create promotion</button>
        </div>
    </form>

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
                @forelse ($promotions as $promotion)
                    <tr>
                        <td>{{ $promotion->product->title ?? $promotion->title }}</td>
                        <td>{{ str_replace('_', ' ', $promotion->location) }}</td>
                        <td><span class="badge badge-soft">{{ $promotion->isActiveNow() ? 'active' : 'scheduled/ended' }}</span></td>
                        <td>{{ $promotion->clicks }}</td>
                        <td class="text-end">
                            <form method="post" action="{{ route('manage.promotions.delete', $promotion) }}" onsubmit="return confirm('Remove this promotion?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No promotions yet. Create one from an approved product.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($active)
<div class="bb-card mb-4 p-4">
    <div class="row g-4 align-items-center">
        <div class="col-lg-4">
            <div class="text-muted small">Ad wallet balance</div>
            <div class="display-6 fw-bold text-bloom">Rs. {{ number_format(auth()->user()->ad_wallet_balance, 2) }}</div>
            <div class="small text-muted">Top up with Razorpay before creating promotions. Minimum: Rs. {{ number_format($adWalletMinTopup, 2) }}</div>
        </div>
        <div class="col-lg-4">
            <div class="small text-muted mb-2">Current ad rates</div>
            <div class="d-flex flex-column gap-1 small">
                <span>Home top: Rs. {{ number_format($adRates['home_top'], 2) }}/day</span>
                <span>Home middle: Rs. {{ number_format($adRates['home_mid'], 2) }}/day</span>
                <span>Home bottom: Rs. {{ number_format($adRates['home_bottom'], 2) }}/day</span>
            </div>
        </div>
        <div class="col-lg-4">
            <form id="adWalletTopupForm" method="post" action="{{ route('manage.ad-wallet.verify') }}" class="d-grid gap-2">
                @csrf
                <input type="number" min="{{ $adWalletMinTopup }}" step="1" name="amount" id="adWalletAmount" class="form-control" placeholder="Top-up amount, e.g. 1000" required>
                <input type="hidden" name="razorpay_order_id" id="walletRazorpayOrderId">
                <input type="hidden" name="razorpay_payment_id" id="walletRazorpayPaymentId">
                <input type="hidden" name="razorpay_signature" id="walletRazorpaySignature">
                <button type="button" class="btn btn-dark" id="adWalletPayBtn">Top up ad wallet</button>
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

<div class="row g-4">
    <div class="col-xl-6">
        <div class="table-card">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0">My products</h4>
            </div>
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
                        @foreach ($products as $product)
                            <tr>
                                <td class="fw-semibold">{{ $product->title }}</td>
                                <td class="text-muted small">{{ $product->category->name ?? 'N/A' }}</td>
                                <td>₹{{ number_format($product->price, 2) }}</td>
                                <td><span class="badge badge-soft">{{ $product->qc_status }}</span></td>
                                @if ($active)
                                    <td class="text-end">
                                        <form method="post" action="{{ route('manage.products.delete', $product) }}" class="d-inline" onsubmit="return confirm('Delete this draft/listing?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="table-card">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0">Orders</h4>
                <a href="{{ route('manage.orders.export') }}" class="btn btn-outline-dark btn-sm rounded-pill"><i class="bi bi-download me-1"></i>Export CSV</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>{{ $order->product_name }}</td>
                                <td><span class="badge badge-soft">{{ $order->status }}</span></td>
                                <td>
                                    <form data-ajax-form data-method="PATCH" action="{{ route('manage.orders.update', $order) }}" class="d-flex gap-2 flex-wrap">
                                        <select name="status" class="form-select form-select-sm" style="min-width: 130px" @disabled(! $active)>
                                            <option value="pending" @selected($order->status === 'pending')>pending</option>
                                            <option value="processing" @selected($order->status === 'processing')>processing</option>
                                            <option value="shipped" @selected($order->status === 'shipped')>shipped</option>
                                            <option value="out_for_delivery" @selected($order->status === 'out_for_delivery')>out_for_delivery</option>
                                            <option value="delivered" @selected($order->status === 'delivered')>delivered</option>
                                            <option value="cancelled" @selected($order->status === 'cancelled')>cancelled</option>
                                        </select>
                                        <input type="text" name="location" class="form-control form-control-sm" style="min-width: 130px;" placeholder="Location (e.g. Delhi Hub)" @disabled(! $active)>
                                        <input type="text" name="tracking_msg" class="form-control form-control-sm" style="min-width: 150px;" placeholder="Message (e.g. In transit)" value="{{ $order->tracking_msg }}" @disabled(! $active)>
                                        <button type="submit" class="btn btn-soft btn-sm" @disabled(! $active)>Add Update</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="table-card">
            <div class="p-4 border-bottom"><h4 class="fw-bold mb-0">Customer Questions (Q&A)</h4></div>
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
                                <td>{{ $q->product->title }}</td>
                                <td>
                                    <div class="small text-muted mb-1">Asked by {{ $q->user->name }}</div>
                                    <div class="fw-bold">{{ $q->question }}</div>
                                </td>
                                <td>
                                    <form method="post" action="{{ route('manage.questions.update', $q) }}" class="d-flex gap-2">
                                        @csrf @method('PATCH')
                                        <input type="text" name="answer" class="form-control form-control-sm" style="min-width: 250px" placeholder="Write your answer..." required @disabled(! $active)>
                                        <button type="submit" name="status" value="answered" class="btn btn-dark btn-sm" @disabled(! $active)>Answer</button>
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
    </div>
</div>

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
                    <div class="col-md-6">
                        <label class="form-label">Price</label>
                        <input name="price" type="number" step="0.01" class="form-control" required>
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
                        <label class="form-label">Additional Images (max 3)</label>
                        <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted">Left, right, up, down views</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>
                    
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0 fw-bold">Product Variants (Optional)</label>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addVariantRow()">+ Add Variant</button>
                        </div>
                        <div id="variantsContainer">
                            <div class="row g-2 variant-row mb-2">
                                <div class="col-md-3">
                                    <input type="text" name="variants[0][color]" class="form-control form-control-sm" placeholder="Color (e.g. Red)">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="variants[0][size]" class="form-control form-control-sm" placeholder="Size (e.g. XL)">
                                </div>
                                <div class="col-md-3">
                                    <input type="number" step="0.01" name="variants[0][price]" class="form-control form-control-sm" placeholder="Price override">
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="variants[0][stock]" class="form-control form-control-sm" placeholder="Stock" value="10">
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.variant-row').remove()"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-bloom">Submit for QC</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let variantCount = 1;
        function addVariantRow() {
            const container = document.getElementById('variantsContainer');
            const row = document.createElement('div');
            row.className = 'row g-2 variant-row mb-2';
            row.innerHTML = `
                <div class="col-md-3">
                    <input type="text" name="variants[${variantCount}][color]" class="form-control form-control-sm" placeholder="Color">
                </div>
                <div class="col-md-3">
                    <input type="text" name="variants[${variantCount}][size]" class="form-control form-control-sm" placeholder="Size">
                </div>
                <div class="col-md-3">
                    <input type="number" step="0.01" name="variants[${variantCount}][price]" class="form-control form-control-sm" placeholder="Price">
                </div>
                <div class="col-md-2">
                    <input type="number" name="variants[${variantCount}][stock]" class="form-control form-control-sm" placeholder="Stock" value="10">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="this.closest('.variant-row').remove()"><i class="bi bi-trash"></i></button>
                </div>
            `;
            container.appendChild(row);
            variantCount++;
        }
    </script>
@endif
@endsection
