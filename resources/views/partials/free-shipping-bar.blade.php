@php
    $threshold = (float) ($freeShippingThreshold ?? 0);
    $current = (float) ($cartTotal ?? 0);
@endphp
@if($threshold > 0 && $current > 0)
    @php
        $remaining = max(0, $threshold - $current);
        $pct = min(100, ($current / $threshold) * 100);
    @endphp
    <div class="bb-free-ship-bar mb-3 p-3 rounded-4 border bg-light">
        @if($remaining <= 0)
            <div class="small fw-semibold text-success"><i class="bi bi-truck me-1"></i> You unlocked free delivery!</div>
        @else
            <div class="small fw-semibold mb-1">Add ₹{{ number_format($remaining, 0) }} more for free delivery</div>
        @endif
        <div class="progress rounded-pill" style="height: 6px;">
            <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
        </div>
    </div>
@endif
