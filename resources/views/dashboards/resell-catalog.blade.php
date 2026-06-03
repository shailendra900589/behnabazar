@extends('layouts.dashboard')
@section('title', 'Resell catalog')
@section('dashboard')
<div class="resell-hub-header mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
        <div class="min-w-0 flex-grow-1">
            <span class="badge badge-soft rounded-pill mb-2">Vendor B2B catalog</span>
            <h1 class="fw-bold mb-1 resell-hub-title">Products from other sellers</h1>
            <p class="text-muted small mb-0">List at your price — quick, branded, or bulk stock.</p>
        </div>
        <a href="{{ route('dashboard', ['section' => 'overview']) }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-shrink-0"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
    </div>
</div>

<div class="row g-2 g-md-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="bb-card p-3 h-100 border-start border-4 border-primary">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="resell-mode-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-lightning-charge"></i></span>
                <h3 class="h6 fw-bold mb-0">Quick resell</h3>
            </div>
            <p class="small text-muted mb-0">Use source photos &amp; title. Set your selling price. No listing fee. Goes to QC.</p>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="bb-card p-3 h-100 border-start border-4 border-warning">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="resell-mode-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-palette"></i></span>
                <h3 class="h6 fw-bold mb-0">Branded listing</h3>
            </div>
            <p class="small text-muted mb-0">Your title, description &amp; photos. Fee <strong>₹{{ number_format($customizeFee, 0) }}</strong> from sales wallet.</p>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="bb-card p-3 h-100 border-start border-4 border-success">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="resell-mode-icon bg-success bg-opacity-10 text-success"><i class="bi bi-boxes"></i></span>
                <h3 class="h6 fw-bold mb-0">Bulk stock</h3>
            </div>
            <p class="small text-muted mb-0">Buy min <strong>{{ $bulkMinQty }}</strong> units at <strong>{{ $bulkDiscountPercent }}% off</strong> source price. Pay from wallet.</p>
        </div>
    </div>
</div>

<div class="bb-card p-3 mb-3 resell-filter-card">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label small text-muted mb-1">Search</label>
            <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="Product or shop name…">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small text-muted mb-1">Category</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-bloom btn-sm flex-grow-1">Filter</button>
            <a href="{{ route('manage.resell.catalog') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
    <p class="small text-muted mb-0 mt-2">
        <i class="bi bi-wallet2 me-1"></i> Sales wallet: <strong>₹{{ number_format($walletBalance, 2) }}</strong>
        · {{ $catalogTotal }} product(s) available
    </p>
</div>

<div class="row g-3 g-md-4">
    @forelse($products as $source)
        @php
            $alreadyListed = in_array($source->id, $myListingIds, true);
            $stock = $inventory->get($source->id);
            $dpBase = $source->effectiveResellerUnitCost();
            $bulkUnit = round($dpBase * (1 - $bulkDiscountPercent / 100), 2);
        @endphp
        <div class="col-12 col-sm-6 col-xl-4">
            <article class="bb-card resell-product-card h-100 overflow-hidden">
                <div class="position-relative">
                    <img src="{{ $source->imageUrl() }}" class="w-100 resell-product-img" alt="">
                    @if($alreadyListed)
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">Listed on your shop</span>
                    @endif
                    @if($stock && $stock->qty_remaining > 0)
                        <span class="badge bg-info text-dark position-absolute top-0 start-0 m-2">{{ $stock->qty_remaining }} bulk in stock</span>
                    @endif
                </div>
                <div class="p-3">
                    <p class="small text-muted mb-1"><i class="bi bi-shop me-1"></i>{{ $source->vendor?->shop_name ?? 'Vendor' }}</p>
                    <h3 class="h6 fw-bold mb-2">{{ $source->title }}</h3>
                    <div class="mb-3">
                        @include('partials.product-price-dp', ['product' => $source])
                    </div>
                    <div class="d-grid gap-2">
                        @if($alreadyListed)
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark btn-sm">Edit in My products</a>
                        @else
                            <button type="button" class="btn btn-bloom btn-sm" data-bs-toggle="modal" data-bs-target="#resellQuick{{ $source->id }}">
                                <i class="bi bi-lightning-charge me-1"></i> Quick resell
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#resellBrand{{ $source->id }}">
                                <i class="bi bi-palette me-1"></i> Branded listing
                            </button>
                        @endif
                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#resellBulk{{ $source->id }}">
                            <i class="bi bi-boxes me-1"></i> Bulk buy (₹{{ number_format($bulkUnit, 2) }}/unit)
                        </button>
                    </div>
                </div>
            </article>
        </div>

        @if(! $alreadyListed)
        <div class="modal fade" id="resellQuick{{ $source->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content">
                    <form method="post" action="{{ route('manage.resell.store') }}">
                        @csrf
                        <input type="hidden" name="source_product_id" value="{{ $source->id }}">
                        <input type="hidden" name="resell_mode" value="direct">
                        <div class="modal-header">
                            <h5 class="modal-title">Quick resell</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted">Uses source photos. Source vendor gets ₹{{ number_format($source->price, 2) }} per sale; you keep the rest.</p>
                            <label class="form-label">Your selling price (₹)</label>
                            <input type="number" name="sell_price" class="form-control" min="{{ $source->price }}" step="0.01" value="{{ $source->price + 50 }}" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-bloom">Submit for QC</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="resellBrand{{ $source->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content">
                    <form method="post" action="{{ route('manage.resell.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="source_product_id" value="{{ $source->id }}">
                        <input type="hidden" name="resell_mode" value="customized">
                        <div class="modal-header">
                            <h5 class="modal-title">Branded listing — {{ Str::limit($source->title, 40) }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body row g-3">
                            <div class="col-12">
                                <div class="alert alert-warning small mb-0">Listing fee ₹{{ number_format($customizeFee, 0) }} charged from sales wallet when you submit.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Your selling price (₹)</label>
                                <input type="number" name="sell_price" class="form-control" min="{{ $source->price }}" step="0.01" value="{{ $source->price + 99 }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Your product title</label>
                                <input name="title" class="form-control" maxlength="255" placeholder="Your shop branding" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Your marketing copy"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cover image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                @include('partials.upload-size-hint', ['type' => 'product_primary'])
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gallery images</label>
                                <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                                @include('partials.upload-size-hint', ['type' => 'product_gallery'])
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-bloom">Pay fee &amp; submit for QC</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <div class="modal fade" id="resellBulk{{ $source->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content">
                    <form method="post" action="{{ route('manage.resell.bulk') }}">
                        @csrf
                        <input type="hidden" name="source_product_id" value="{{ $source->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Bulk stock purchase</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted mb-3">
                                Buy inventory at <strong>₹{{ number_format($bulkUnit, 2) }}</strong>/unit ({{ $bulkDiscountPercent }}% off source ₹{{ number_format($source->price, 2) }}).
                                Payment from sales wallet. Then list with Quick or Branded resell.
                            </p>
                            <label class="form-label">Quantity (min {{ $bulkMinQty }})</label>
                            <input type="number" name="quantity" class="form-control" min="{{ $bulkMinQty }}" value="{{ $bulkMinQty }}" required>
                            <p class="small text-muted mt-2 mb-0">Estimated total: <strong id="bulkTotal{{ $source->id }}">₹{{ number_format($bulkUnit * $bulkMinQty, 2) }}</strong></p>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Purchase from wallet</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        (function(){
            const qty = document.querySelector('#resellBulk{{ $source->id }} input[name=quantity]');
            const out = document.getElementById('bulkTotal{{ $source->id }}');
            if (qty && out) {
                const unit = {{ $bulkUnit }};
                qty.addEventListener('input', function(){ out.textContent = '₹' + (unit * Math.max(1, parseInt(qty.value)||0)).toFixed(2); });
            }
        })();
        </script>
    @empty
        <div class="col-12">
            <div class="bb-card p-5 text-center">
                <i class="bi bi-inbox display-4 text-muted opacity-50 d-block mb-3"></i>
                <h3 class="h5 fw-bold">No products from other vendors yet</h3>
                <p class="text-muted mb-0">When another seller has <strong>approved</strong> listings with resell allowed, they appear here.</p>
            </div>
        </div>
    @endforelse
</div>
<div class="mt-3 resell-pagination">{{ $products->links() }}</div>
@endsection
