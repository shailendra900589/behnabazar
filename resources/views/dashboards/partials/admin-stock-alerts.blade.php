<div class="admin-section" id="tab-alerts">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">Stock alert subscribers</h2>
            <p class="text-muted small mb-0">Customers waiting for back-in-stock (cron: <code>stock:notify</code>).</p>
        </div>
        <span class="badge bg-warning text-dark">{{ $stockAlertCount ?? 0 }} pending</span>
    </div>
    <div class="table-responsive bb-card">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Contact</th>
                    <th>Since</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockAlertsPending ?? [] as $alert)
                    <tr>
                        <td>
                            @if($alert->product)
                                <a href="{{ route('product.show', $alert->product) }}" class="text-decoration-none fw-semibold">{{ $alert->product->title }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="small">{{ $alert->variant?->displayLabel() ?? 'Any' }}</td>
                        <td class="small">
                            @if($alert->email){{ $alert->email }}@endif
                            @if($alert->phone)<br>{{ $alert->phone }}@endif
                        </td>
                        <td class="small text-muted">{{ $alert->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No pending stock alerts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
