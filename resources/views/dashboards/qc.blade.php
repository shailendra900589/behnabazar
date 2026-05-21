@extends('layouts.dashboard')
@section('title', 'QC Dashboard')
@section('dashboard')
<h1 class="fw-bold">QC Dashboard</h1>
<p class="text-muted mb-4">Review all product photos before approving listings.</p>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="table-card">
            <div class="p-4 border-bottom">
                <h4 class="fw-bold mb-0">Pending review</h4>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Photos</th>
                            <th>Product</th>
                            <th>Vendor</th>
                            <th>Price</th>
                            <th>Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pending as $product)
                            @php
                                $gallery = $product->galleryUrls();
                            @endphp
                            <tr>
                                <td style="min-width: 200px;">
                                    <div class="d-flex gap-1 flex-wrap">
                                        @foreach(array_slice($gallery, 0, 4) as $img)
                                            <a href="{{ $img }}" target="_blank" rel="noopener">
                                                <img src="{{ $img }}" alt="" class="rounded border" style="width:56px;height:56px;object-fit:cover">
                                            </a>
                                        @endforeach
                                        @if(count($gallery) > 4)
                                            <span class="badge bg-light text-dark align-self-center">+{{ count($gallery) - 4 }}</span>
                                        @endif
                                    </div>
                                    @if($product->isResellListing())
                                        <span class="badge bg-info text-dark mt-1">Resell listing</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $product->title }}</div>
                                    <div class="small text-muted">{{ $product->category->name ?? '' }}</div>
                                    <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#qcModal{{ $product->id }}">View all {{ count($gallery) }} photos</button>
                                </td>
                                <td>{{ $product->vendor->shop_name ?? 'Official' }}</td>
                                <td>₹{{ number_format($product->price, 2) }}</td>
                                <td>
                                    <form data-ajax-form data-method="PATCH" data-reload="true" action="{{ route('manage.products.review', $product) }}" class="mb-2">
                                        <input type="hidden" name="decision" value="approved">
                                        <button class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="post" action="{{ route('manage.products.review', $product) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="decision" value="rejected">
                                        <input class="form-control form-control-sm mb-1" name="reject_reason" placeholder="Reject reason" required>
                                        <button class="btn btn-outline-danger btn-sm w-100">Reject</button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="qcModal{{ $product->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $product->title }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                @foreach($gallery as $img)
                                                    <div class="col-6 col-md-4">
                                                        <a href="{{ $img }}" target="_blank"><img src="{{ $img }}" class="img-fluid rounded border w-100" style="aspect-ratio:1;object-fit:cover" alt=""></a>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <p class="mt-3 small text-muted">{{ $product->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">All caught up.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="bb-card p-4">
            <h4 class="fw-bold">QC team</h4>
            @foreach($team as $member)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span>{{ $member->name }}</span>
                    <span class="badge badge-soft">{{ str_replace('_', ' ', $member->role) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
