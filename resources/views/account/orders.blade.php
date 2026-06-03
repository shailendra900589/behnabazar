@extends('layouts.app')
@section('title','Orders')
@section('content')
<section class="container py-3 py-md-5 bb-orders-page">
    <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
        <h1 class="fw-bold mb-0 h4 h-md-auto">My Orders</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-soft btn-sm">← Account</a>
    </div>
    <div class="vstack gap-2 gap-md-3">
        @forelse($orders as $order)
            <div class="bb-card-lite bb-order-card p-3">
                <div class="d-flex gap-2 gap-md-3 align-items-start">
                <img src="{{ $order->product->imageUrl() }}" class="rounded-3 flex-shrink-0 bb-order-card-img" alt="">
                <div class="flex-grow-1 min-w-0">
                    <h5 class="fw-bold mb-1 h6">{{ $order->product_name }}</h5>
                    <div class="d-flex flex-wrap gap-1 align-items-center mb-1">
                        <span class="badge badge-soft">{{ str_replace('_',' ', $order->status) }}</span>
                        @if($order->return_status)
                            <span class="badge bg-warning text-dark">Return: {{ ucfirst($order->return_status) }}</span>
                        @endif
                        <span class="fw-semibold text-bloom">₹{{ number_format($order->total_price,2) }}</span>
                    </div>
                </div>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-2 pt-2 border-top">
                    <a class="btn btn-bloom" href="{{ route('orders.track',$order) }}">Track Order</a>
                    <a class="btn btn-outline-secondary" href="{{ route('orders.invoice', $order) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i> Invoice</a>
                    @if($order->product && $order->product->qc_status === 'approved')
                        <form method="post" action="{{ route('cart.add', $order->product) }}" class="d-inline">
                            @csrf
                            @if($order->variant_id)<input type="hidden" name="variant_id" value="{{ $order->variant_id }}">@endif
                            <input type="hidden" name="quantity" value="{{ max(1, $order->quantity) }}">
                            <button type="submit" class="btn btn-outline-primary">Buy again</button>
                        </form>
                    @endif
                    @if(in_array($order->status, ['pending', 'processing']))
                        <form method="post" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">Cancel</button>
                        </form>
                    @elseif($order->status === 'delivered' && empty($order->return_status))
                        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#returnModal{{ $order->id }}">Return</button>
                        
                        <div class="modal fade" id="returnModal{{ $order->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                                <form method="post" action="{{ route('orders.return', $order) }}" class="modal-content">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Request Return</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Why are you returning this item?</label>
                                        <textarea class="form-control" name="return_reason" rows="3" required placeholder="E.g. Damaged product, changed my mind..."></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-bloom">Submit Request</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bb-card p-5 text-center text-muted">No orders yet.</div>
        @endforelse
    </div>
</section>
@endsection
