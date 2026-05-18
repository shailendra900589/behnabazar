@extends('layouts.app')
@section('title','Orders')
@section('content')
<section class="container py-5">
    <h1 class="fw-bold mb-4">My Orders</h1>
    <div class="vstack gap-3">
        @forelse($orders as $order)
            <div class="bb-card-lite p-3 d-flex flex-wrap align-items-center gap-3">
                <img src="{{ $order->product->imageUrl() }}" class="rounded-4" style="width:84px;height:84px;object-fit:cover">
                <div class="flex-grow-1">
                    <h5 class="fw-bold">{{ $order->product_name }}</h5>
                    <span class="badge badge-soft">{{ str_replace('_',' ', $order->status) }}</span>
                    @if($order->return_status)
                        <span class="badge bg-warning text-dark ms-1">Return: {{ ucfirst($order->return_status) }}</span>
                    @endif
                    <span class="text-muted ms-2">₹{{ number_format($order->total_price,2) }}</span>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-bloom" href="{{ route('orders.track',$order) }}">Track Order</a>
                    @if(in_array($order->status, ['pending', 'processing']))
                        <form method="post" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">Cancel</button>
                        </form>
                    @elseif($order->status === 'delivered' && empty($order->return_status))
                        <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#returnModal{{ $order->id }}">Return</button>
                        
                        <div class="modal fade" id="returnModal{{ $order->id }}" tabindex="-1">
                            <div class="modal-dialog">
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
