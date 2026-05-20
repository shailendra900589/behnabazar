@extends('layouts.dashboard')
@section('title', 'Resell catalog')
@section('dashboard')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="fw-bold mb-1">Vendor resell catalog</h1>
        <p class="text-muted mb-0">List another vendor's product on your shop. They fulfill orders; you earn the margin. Customize listing fee: ₹{{ number_format($customizeFee, 0) }}.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill">Back to dashboard</a>
</div>

<div class="row g-4">
    @forelse($products as $source)
        <div class="col-md-6 col-xl-4">
            <div class="bb-card h-100 overflow-hidden">
                <img src="{{ $source->imageUrl() }}" class="w-100" style="height:180px;object-fit:cover" alt="">
                <div class="p-3">
                    <h3 class="h6 fw-bold">{{ $source->title }}</h3>
                    <p class="small text-muted mb-2">{{ $source->vendor?->shop_name }} · ₹{{ number_format($source->price, 2) }} base</p>
                    <button type="button" class="btn btn-bloom btn-sm w-100" data-bs-toggle="modal" data-bs-target="#resellModal{{ $source->id }}">Resell this product</button>
                </div>
            </div>
        </div>

        <div class="modal fade" id="resellModal{{ $source->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="post" action="{{ route('manage.resell.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="source_product_id" value="{{ $source->id }}">
                        <div class="modal-header">
                            <h5 class="modal-title">Resell: {{ $source->title }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your selling price (₹)</label>
                                <input type="number" name="sell_price" class="form-control" min="{{ $source->price }}" step="0.01" value="{{ $source->price + 50 }}" required>
                                <small class="text-muted">Minimum ₹{{ number_format($source->price, 2) }} goes to source vendor per sale.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Listing mode</label>
                                <select name="resell_mode" class="form-select" required>
                                    <option value="direct">Direct resell (use source photos)</option>
                                    <option value="customized">Customize title/photos (fee ₹{{ number_format($customizeFee, 0) }})</option>
                                </select>
                            </div>
                            <div class="col-12 resell-custom-fields d-none">
                                <label class="form-label">Custom title</label>
                                <input name="title" class="form-control" maxlength="255" placeholder="Optional custom title">
                            </div>
                            <div class="col-12 resell-custom-fields d-none">
                                <label class="form-label">Custom description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Optional — defaults to source description"></textarea>
                            </div>
                            <div class="col-md-6 resell-custom-fields d-none">
                                <label class="form-label">Cover image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6 resell-custom-fields d-none">
                                <label class="form-label">Extra images</label>
                                <input type="file" name="images[]" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-bloom">Submit for QC</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="bb-card p-5 text-center text-muted">No products from other vendors available to resell yet.</div></div>
    @endforelse
</div>
<div class="mt-4">{{ $products->links() }}</div>

<script>
document.querySelectorAll('[id^="resellModal"]').forEach(function (modal) {
    modal.addEventListener('show.bs.modal', function () {
        const sel = modal.querySelector('[name="resell_mode"]');
        const toggle = function () {
            const custom = sel.value === 'customized';
            modal.querySelectorAll('.resell-custom-fields').forEach(function (el) {
                el.classList.toggle('d-none', !custom);
            });
        };
        sel.onchange = toggle;
        toggle();
    });
});
</script>
@endsection
