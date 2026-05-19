@if (auth()->user()?->role === 'admin')
<div class="bb-card p-4 mb-4">
    <h4 class="fw-bold mb-2">Product promotion email <span class="badge badge-soft ms-1">Admin only</span></h4>
    <p class="text-muted small mb-3">Send a product offer from <strong>{{ config('mail.from.address') }}</strong> to newsletter subscribers and/or verified customers. Only administrators can send promotional emails.</p>
    <form method="post" action="{{ route('manage.promotions.email') }}" class="row g-3 align-items-end">
        @csrf
        <div class="col-lg-4">
            <label class="form-label">Product</label>
            <select name="product_id" class="form-select" required>
                <option value="">Choose approved product</option>
                @foreach ($promotionProducts ?? [] as $product)
                    <option value="{{ $product->id }}">{{ $product->title }} — ₹{{ number_format($product->price, 2) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-3">
            <label class="form-label">Send to</label>
            <select name="audience" class="form-select" required>
                <option value="both">Newsletter + customers</option>
                <option value="newsletter">Newsletter only ({{ $newsletterCount ?? 0 }})</option>
                <option value="customers">Verified customers only</option>
            </select>
        </div>
        <div class="col-lg-4">
            <label class="form-label">Custom message (optional)</label>
            <input type="text" name="message" class="form-control" maxlength="1000" placeholder="Limited-time offer on this pick!">
        </div>
        <div class="col-lg-1">
            <button type="submit" class="btn btn-bloom w-100" onclick="return confirm('Send promotion email to selected audience?');">
                <i class="bi bi-envelope"></i>
            </button>
        </div>
    </form>
</div>
@endif
