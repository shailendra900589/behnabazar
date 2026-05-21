@php
    $stockService = app(\App\Services\StockAlertService::class);
    $selectedVariant = request('variant_id');
    $showAlert = $stockService->isOutOfStock($product, $selectedVariant ? (int) $selectedVariant : null);
@endphp
@if($showAlert)
<div class="bb-stock-alert bb-card-lite p-3 rounded-4 border mb-4">
    <h6 class="fw-bold mb-2"><i class="bi bi-bell text-warning me-1"></i> Notify when back in stock</h6>
    <p class="small text-muted mb-2">Enter email or phone — we will alert you when this item is available again.</p>
    <form method="post" action="{{ route('product.stock-alert', $product) }}" class="row g-2 align-items-end">
        @csrf
        @if($product->variants && $product->variants->count() > 0)
            <div class="col-12">
                <select name="variant_id" class="form-select form-select-sm">
                    <option value="">Any variant</option>
                    @foreach($product->variants as $v)
                        <option value="{{ $v->id }}" @selected(request('variant_id') == $v->id)>
                            {{ $v->displayLabel() }} @if($v->stock <= 0)(out)@endif
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-md-5">
            <input type="email" name="email" class="form-control form-control-sm" placeholder="Email" value="{{ auth()->user()?->email }}">
        </div>
        <div class="col-md-4">
            <input type="text" name="phone" class="form-control form-control-sm" placeholder="Phone" maxlength="15" value="{{ auth()->user()?->phone }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-bloom btn-sm w-100">Notify me</button>
        </div>
    </form>
</div>
@endif
