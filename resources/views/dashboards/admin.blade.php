@extends('layouts.dashboard')
@section('title', 'Admin Dashboard')
@section('dashboard')
<div class="admin-console">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                <h1 class="h3 fw-bold mb-0">Admin control center</h1>
                <span class="badge rounded-pill admin-role-badge">Administrator</span>
            </div>
            <p class="text-muted mb-0 small">Catalog, storefront, vendors, QC pipeline, and marketplace health.</p>
        </div>
        <button class="btn btn-bloom shadow-sm" type="button" data-bs-toggle="modal" data-bs-target="#productModal">
            <i class="bi bi-plus-lg me-1"></i> Add official product
        </button>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        @if (($pendingVendors ?? 0) > 0)
            <span class="badge rounded-pill bg-warning text-dark px-3 py-2">{{ $pendingVendors }} vendor(s) awaiting approval</span>
        @endif
        @if (($pendingQc ?? 0) > 0)
            <span class="badge rounded-pill bg-info text-dark px-3 py-2">{{ $pendingQc }} product(s) in QC queue</span>
        @endif
    </div>

    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card admin-stat-tile p-4 h-100">
                <span class="text-muted small">Lifetime revenue</span>
                <div class="h3 fw-bold">₹{{ number_format($revenue ?? 0, 2) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card admin-stat-tile p-4 h-100">
                <span class="text-muted small">Orders</span>
                <div class="h3 fw-bold">{{ $orders->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card admin-stat-tile p-4 h-100">
                <span class="text-muted small">Products</span>
                <div class="h3 fw-bold">{{ $products->count() }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card admin-stat-tile p-4 h-100">
                <span class="text-muted small">Vendors</span>
                <div class="h3 fw-bold">{{ $vendors->count() }}</div>
            </div>
        </div>
    </div>

    <div class="admin-workspace rounded-4 border bg-white shadow-sm p-3 p-lg-4">
        @if (($adminSection ?? 'overview') === 'overview')
        <div class="admin-section" id="tab-overview">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <h2 class="h5 fw-bold mb-1">Live snapshot</h2>
                <p class="text-muted small mb-0">Latest orders and what shoppers are buying most.</p>
            </div>
            <a href="{{ route('dashboard', ['section' => 'orders']) }}" class="btn btn-sm btn-outline-primary rounded-pill">Open all orders</a>
        </div>
        
        <div class="bb-card mb-4 p-4">
            <h3 class="h6 fw-bold mb-3">Revenue (Last 7 Days)</h3>
            <canvas id="revenueChart" height="80"></canvas>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const ctx = document.getElementById('revenueChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($chartLabels),
                            datasets: [{
                                label: 'Daily Revenue (₹)',
                                data: @json($chartData),
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
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

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="table-card">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Recent orders</span>
                        <span class="badge bg-light text-dark border">Live feed</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOrders as $ro)
                                    <tr>
                                        <td class="text-nowrap">#{{ $ro->id }} <span class="text-muted small">{{ \Illuminate\Support\Str::limit($ro->product_name, 22) }}</span></td>
                                        <td>{{ \Illuminate\Support\Str::limit($ro->customer_name, 18) }}</td>
                                        <td>₹{{ number_format($ro->total_price, 2) }}</td>
                                        <td><span class="badge rounded-pill bg-light text-dark border">{{ $ro->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No orders yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="bb-card p-4 h-100">
                    <h3 class="h6 fw-bold mb-3">Top sellers</h3>
                    <div class="vstack gap-3">
                        @forelse ($topProducts as $tp)
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $tp->imageUrl() }}" alt="" class="rounded-3 border" style="width:52px;height:52px;object-fit:cover">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $tp->title }}</div>
                                    <div class="small text-muted">{{ $tp->orders_count }} orders · ₹{{ number_format($tp->price, 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No sales data yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        </div>
        @endif
        @if (($adminSection ?? '') === 'products')
        <div class="admin-section" id="tab-products">
        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Vendor</th>
                            <th>Price</th>
                            <th>QC</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td class="fw-semibold">{{ $product->title }}</td>
                                <td class="text-muted small">{{ $product->category->name ?? 'N/A' }}</td>
                                <td>{{ $product->vendor->shop_name ?? 'Official' }}</td>
                                <td>₹{{ number_format($product->price, 2) }}</td>
                                <td><span class="badge badge-soft">{{ $product->qc_status }}</span></td>
                                <td class="text-end">
                                    <form method="post" action="{{ route('manage.products.delete', $product) }}" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        @endif
        @if (($adminSection ?? '') === 'orders')
        <div class="admin-section" id="tab-orders">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('manage.orders.export') }}" class="btn btn-outline-dark"><i class="bi bi-download me-1"></i>Export to CSV</a>
        </div>
        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>#{{ $order->id }} {{ $order->product_name }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>₹{{ number_format($order->total_price, 2) }}</td>
                                <td>
                                    <form data-ajax-form data-method="PATCH" action="{{ route('manage.orders.update', $order) }}" class="d-flex gap-2 flex-wrap">
                                        <select name="status" class="form-select form-select-sm" style="min-width: 140px">
                                            <option value="pending" @selected($order->status === 'pending')>pending</option>
                                            <option value="processing" @selected($order->status === 'processing')>processing</option>
                                            <option value="shipped" @selected($order->status === 'shipped')>shipped</option>
                                            <option value="out_for_delivery" @selected($order->status === 'out_for_delivery')>out_for_delivery</option>
                                            <option value="delivered" @selected($order->status === 'delivered')>delivered</option>
                                            <option value="cancelled" @selected($order->status === 'cancelled')>cancelled</option>
                                        </select>
                                        <input type="hidden" name="tracking_msg" value="Updated by admin">
                                        <button type="submit" class="btn btn-soft btn-sm">Save</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        @endif
        @if (($adminSection ?? '') === 'vendors')
        <div class="admin-section" id="tab-vendors">
        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Shop</th>
                            <th>Category</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Fee paid</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vendors as $vendor)
                            <tr>
                                <td>{{ $vendor->name }}</td>
                                <td>{{ $vendor->shop_name }}</td>
                                <td>{{ $vendor->product_category ?? 'Not provided' }}</td>
                                <td>
                                    <div>{{ $vendor->phone ?? 'No phone' }}</div>
                                    <div class="small text-muted">{{ $vendor->city }} {{ $vendor->pincode ? ' / '.$vendor->pincode : '' }}</div>
                                </td>
                                <td><span class="badge badge-soft">{{ $vendor->account_status }}</span></td>
                                <td>{{ $vendor->reg_fee_paid ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        @if ($vendor->account_status === 'pending_approval')
                                            <form method="post" action="{{ route('manage.vendors.approve', $vendor) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                            </form>
                                            <form method="post" action="{{ route('manage.vendors.reject', $vendor) }}" onsubmit="return confirm('Reject this vendor?');">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                            </form>
                                        @elseif ($vendor->account_status === 'active')
                                            <a href="{{ route('manage.vendors.impersonate', $vendor) }}" class="btn btn-bloom btn-sm"><i class="bi bi-box-arrow-in-right me-1"></i> Login As</a>
                                            <form method="post" action="{{ route('manage.vendors.reject', $vendor) }}" onsubmit="return confirm('Deactivate this vendor? They will no longer be able to sell.');">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm">Deactivate</button>
                                            </form>
                                            <form method="post" action="{{ route('manage.vendors.delete', $vendor) }}" onsubmit="return confirm('Permanently delete this vendor and all their data? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        @endif
        @if (($adminSection ?? '') === 'payouts')
        <div class="admin-section" id="tab-payouts">
        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Vendor</th>
                            <th>Amount</th>
                            <th>Bank Details</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payoutRequests as $p)
                            <tr>
                                <td>{{ $p->created_at->format('M d, Y') }}</td>
                                <td>{{ $p->vendor->shop_name ?? 'Vendor #'.$p->vendor_id }}</td>
                                <td class="fw-bold">₹{{ number_format($p->amount, 2) }}</td>
                                <td class="small text-muted">{{ $p->bank_details }}</td>
                                <td>
                                    @if($p->status === 'pending') <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($p->status === 'paid') <span class="badge bg-success">Paid</span>
                                    @else <span class="badge bg-danger">Rejected</span> @endif
                                </td>
                                <td class="text-end">
                                    @if($p->status === 'pending')
                                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        <form method="post" action="{{ route('manage.payouts.update', $p) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="paid">
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Mark as paid? Ensure you have actually transferred the money.');">Mark Paid</button>
                                        </form>
                                        <form method="post" action="{{ route('manage.payouts.update', $p) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Reject this payout request?');">Reject</button>
                                        </form>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No payout requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </div>
        </div>
        @endif

        @if (($adminSection ?? '') === 'returns')
        <div class="admin-section" id="tab-returns">
        <div class="table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returnRequests as $r)
                            <tr>
                                <td>#{{ $r->id }}<br><small class="text-muted">{{ $r->product_name }}</small></td>
                                <td>{{ $r->customer_name }}</td>
                                <td style="max-width:250px;">
                                    <span class="d-inline-block text-truncate w-100" title="{{ $r->return_reason }}">{{ $r->return_reason }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-soft">{{ $r->return_status }}</span>
                                </td>
                                <td class="text-end">
                                    @if($r->return_status === 'requested')
                                    <form method="post" action="{{ route('manage.orders.return.update', $r) }}" class="d-flex gap-2 justify-content-end">
                                        @csrf @method('PATCH')
                                        <select name="return_status" class="form-select form-select-sm" style="width:120px">
                                            <option value="approved">Approve Pickup</option>
                                            <option value="refunded">Refund complete</option>
                                            <option value="rejected">Reject</option>
                                        </select>
                                        <button type="submit" class="btn btn-dark btn-sm">Save</button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No return requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        @endif

        @if (($adminSection ?? '') === 'catalog')
        <div class="admin-section" id="tab-catalog">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="bb-card p-4">
                    <h4 class="fw-bold mb-3">Add category</h4>
                    <form method="post" action="{{ route('manage.categories.save') }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Name</label>
                            <input class="form-control" name="name" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bootstrap icon class</label>
                            <input class="form-control" name="icon" placeholder="bi-flower1" maxlength="50">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-bloom">Save category</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="table-card">
                    <div class="p-3 border-bottom fw-bold">Categories</div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr><th>Name</th><th>Slug</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td><i class="{{ $category->icon }} me-2"></i>{{ $category->name }}</td>
                                        <td class="text-muted small">{{ $category->slug }}</td>
                                        <td class="text-end">
                                            <form method="post" action="{{ route('manage.categories.delete', $category) }}" class="d-inline" onsubmit="return confirm('Delete category?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
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
        </div>
        @endif
        @if (($adminSection ?? '') === 'storefront')
        <div class="admin-section" id="tab-storefront">
        @include('dashboards.partials.site-display-admin')
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="bb-card p-4">
                    <h4 class="fw-bold mb-3">Hero banners</h4>
                    <form method="post" action="{{ route('manage.banners.save') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" required accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link URL</label>
                            <input class="form-control" name="link" placeholder="#">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort order</label>
                            <input class="form-control" type="number" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-bloom">Upload banner</button>
                        </div>
                    </form>
                    <hr>
                    <div class="vstack gap-2">
                        @foreach ($banners as $banner)
                            <div class="d-flex align-items-center gap-3 border rounded-3 p-2">
                                <img src="{{ str_starts_with($banner->image, 'http') ? $banner->image : asset('storage/'.$banner->image) }}" alt="" class="rounded-3" style="width:72px;height:48px;object-fit:cover">
                                <div class="flex-grow-1 small text-muted">Order {{ $banner->sort_order }}</div>
                                <form method="post" action="{{ route('manage.banners.delete', $banner) }}" onsubmit="return confirm('Remove banner?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bb-card p-4">
                    <h4 class="fw-bold mb-3">Custom ads</h4>
                    <p class="text-muted small">Create site-wide ads. If no active ad exists or the customer closes it, the storefront layout automatically closes the space.</p>
                    <form method="post" action="{{ route('manage.ads.save') }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Placement</label>
                            <select class="form-select" name="location" required>
                                <option value="home_top">Home top</option>
                                <option value="home_mid">Home middle</option>
                                <option value="home_bottom">Home bottom</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select name="ad_type" class="form-select">
                                <option value="image">Image</option>
                                <option value="code">HTML code</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="title" placeholder="Weekend electronics sale">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CTA text</label>
                            <input class="form-control" name="cta_text" placeholder="Shop now">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subtitle</label>
                            <input class="form-control" name="subtitle" placeholder="Promote grocery, fashion, electronics, home goods, and more.">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Link URL (image ads)</label>
                            <input class="form-control" name="link_url" placeholder="https://">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Image file</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Embed code (code ads)</label>
                            <textarea name="code" class="form-control" rows="3" placeholder="Optional raw HTML"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sort order</label>
                            <input class="form-control" type="number" name="sort_order" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Starts at</label>
                            <input class="form-control" type="datetime-local" name="starts_at">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ends at</label>
                            <input class="form-control" type="datetime-local" name="ends_at">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-bloom">Create ad</button>
                        </div>
                    </form>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Placement</th><th>Title</th><th>Owner</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                @foreach ($ads as $ad)
                                    <tr>
                                        <td>{{ $ad->location }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $ad->title ?? strtoupper($ad->ad_type) }}</div>
                                            <div class="small text-muted">{{ $ad->clicks }} clicks / {{ $ad->status ? 'active' : 'paused' }}</div>
                                        </td>
                                        <td>{{ $ad->source === 'vendor' ? ($ad->vendor->shop_name ?? 'Vendor') : 'Admin' }}</td>
                                        <td class="text-end">
                                            <form method="post" action="{{ route('manage.ads.delete', $ad) }}" class="d-inline" onsubmit="return confirm('Delete ad slot?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
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
        </div>
        @endif
        @if (($adminSection ?? '') === 'marketing')
        <div class="admin-section" id="tab-marketing">
        @include('dashboards.partials.promotion-email-form')
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="bb-card p-4">
                    <h4 class="fw-bold mb-3">Coin rewards</h4>
                    <form method="post" action="{{ route('manage.settings.save') }}">
                        @csrf
                        <label class="form-label">Earn rate (order ₹ per 1 coin)</label>
                        <input class="form-control mb-3" name="settings[coin_earn_rate]" value="{{ $settings['coin_earn_rate'] ?? 10 }}">
                        <label class="form-label">Redeem limit (% of cart)</label>
                        <input class="form-control mb-3" name="settings[coin_redeem_limit]" value="{{ $settings['coin_redeem_limit'] ?? 5 }}">
                        <label class="form-label">Vendor Registration Fee (₹)</label>
                        <input class="form-control mb-3" name="settings[vendor_registration_amount]" value="{{ $settings['vendor_registration_amount'] ?? 150 }}">
                        <label class="form-label">Ad rate: Home top (Rs/day)</label>
                        <input class="form-control mb-3" name="settings[ad_rate_home_top]" value="{{ $settings['ad_rate_home_top'] ?? 500 }}">
                        <label class="form-label">Ad rate: Home middle (Rs/day)</label>
                        <input class="form-control mb-3" name="settings[ad_rate_home_mid]" value="{{ $settings['ad_rate_home_mid'] ?? 300 }}">
                        <label class="form-label">Ad rate: Home bottom (Rs/day)</label>
                        <input class="form-control mb-3" name="settings[ad_rate_home_bottom]" value="{{ $settings['ad_rate_home_bottom'] ?? 200 }}">
                        <label class="form-label">Minimum ad wallet top-up (Rs)</label>
                        <input class="form-control mb-3" name="settings[ad_wallet_min_topup]" value="{{ $settings['ad_wallet_min_topup'] ?? 50 }}">
                        <button type="submit" class="btn btn-bloom">Save settings</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bb-card p-4">
                    <h4 class="fw-bold mb-3">Create offers / coupons</h4>
                    <form method="post" action="{{ route('manage.coupons.save') }}" class="row g-2">
                        @csrf
                        <div class="col-6"><input class="form-control" name="code" placeholder="WELCOME50" required></div>
                        <div class="col-6">
                            <select name="discount_type" class="form-select">
                                <option value="flat">Flat</option>
                                <option value="percent">Percent</option>
                            </select>
                        </div>
                        <div class="col-6"><input class="form-control" name="discount_value" placeholder="Value" required></div>
                        <div class="col-6"><input class="form-control" name="min_cart_value" placeholder="Min cart" value="0"></div>
                        <div class="col-12"><button type="submit" class="btn btn-bloom w-100">Save coupon</button></div>
                    </form>
                    <hr>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>Code</th><th>Value</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                @foreach ($coupons as $coupon)
                                    <tr>
                                        <td class="fw-semibold">{{ $coupon->code }}</td>
                                        <td>{{ $coupon->discount_value }} ({{ $coupon->discount_type }})</td>
                                        <td>{{ $coupon->status ? 'On' : 'Off' }}</td>
                                        <td class="text-end">
                                            <div class="d-flex gap-2 justify-content-end">
                                                <form method="post" action="{{ route('manage.coupons.toggle', $coupon) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-soft btn-sm">Toggle</button>
                                                </form>
                                                <form method="post" action="{{ route('manage.coupons.delete', $coupon) }}" onsubmit="return confirm('Delete coupon?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </div>
        @endif
        @if (($adminSection ?? '') === 'team')
        <div class="admin-section" id="tab-team">
        <div class="bb-card p-4">
            <h4 class="fw-bold mb-3">Create QC user</h4>
            <form method="post" action="{{ route('manage.qc-users.create') }}" class="row g-3">
                @csrf
                <div class="col-md-3"><input class="form-control" name="name" placeholder="Name" required></div>
                <div class="col-md-3"><input class="form-control" name="email" placeholder="Email" required></div>
                <div class="col-md-2"><input class="form-control" name="password" placeholder="Password" required></div>
                <div class="col-md-2">
                    <select class="form-select" name="role">
                        <option value="qc_staff">QC Staff</option>
                        <option value="qc_manager">QC Manager</option>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-bloom w-100">Create</button></div>
            </form>
            <hr>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($qcUsers as $member)
                    <span class="badge badge-soft px-3 py-2">{{ $member->name }} — {{ $member->role }}</span>
                @endforeach
            </div>
        </div>
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" enctype="multipart/form-data" action="{{ route('manage.products.save') }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add official product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-6"><input name="title" class="form-control" placeholder="Title" required></div>
                <div class="col-md-6"><input name="price" type="number" step="0.01" class="form-control" placeholder="Price" required></div>
                <div class="col-md-6">
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
                </div>
                <div class="col-12"><textarea name="description" class="form-control" placeholder="Description" required></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-bloom">Save product</button>
            </div>
        </form>
    </div>
</div>
@endsection
