<div class="admin-section" id="tab-reviews">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Review moderation</h2>
            <p class="text-muted small mb-0">Approve customer reviews before they appear on product pages.</p>
        </div>
        <span class="badge bg-warning text-dark">{{ $pendingReviews->count() }} pending</span>
    </div>
    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingReviews as $review)
                        <tr>
                            <td>
                                <a href="{{ route('product.show', $review->product) }}" target="_blank" rel="noopener">{{ $review->product->title ?? '—' }}</a>
                            </td>
                            <td>{{ $review->user->name ?? '—' }}</td>
                            <td><span class="text-warning">@for($i=0;$i<$review->rating;$i++)<i class="bi bi-star-fill"></i>@endfor</span></td>
                            <td class="small" style="max-width:280px">{{ \Illuminate\Support\Str::limit($review->comment, 120) }}</td>
                            <td class="text-end text-nowrap">
                                <form method="post" action="{{ route('manage.reviews.approve', $review) }}" class="d-inline">@csrf<button class="btn btn-success btn-sm">Approve</button></form>
                                <form method="post" action="{{ route('manage.reviews.delete', $review) }}" class="d-inline" onsubmit="return confirm('Delete review?')">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Delete</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No reviews waiting for approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
