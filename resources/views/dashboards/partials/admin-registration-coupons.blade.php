<div class="bb-card p-4 mb-4">
    <h4 class="fw-bold mb-2">Vendor registration coupons</h4>
    <p class="text-muted small mb-3">Create a one-time code — no user details needed here. Name, email, shop and other info are saved automatically when someone uses the coupon during vendor registration.</p>
    <form method="post" action="{{ route('manage.registration-coupons.save') }}" class="row g-2 mb-4 align-items-end">
        @csrf
        <div class="col-md-8">
            <label class="form-label small">Coupon code <span class="text-muted">(leave blank to auto-generate)</span></label>
            <input class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" maxlength="32" placeholder="REGSELLER01" pattern="[A-Za-z0-9_-]+">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-bloom w-100">Create coupon</button>
        </div>
    </form>

    <div class="table-responsive mb-4">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Used by (from registration)</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registrationCoupons ?? [] as $regCoupon)
                    <tr>
                        <td class="fw-semibold font-monospace">{{ $regCoupon->code }}</td>
                        <td>
                            @if($regCoupon->used_at)
                                <span class="badge text-bg-secondary">Used</span>
                            @elseif($regCoupon->revoked_at)
                                <span class="badge text-bg-danger">Revoked</span>
                            @else
                                <span class="badge text-bg-success">Available</span>
                            @endif
                        </td>
                        <td>
                            @if($regCoupon->used_at)
                                <div class="fw-semibold">{{ $regCoupon->issued_to_name ?? $regCoupon->usedBy?->name }}</div>
                                @if($regCoupon->issued_to_email ?? $regCoupon->usedBy?->email)
                                    <div class="small text-muted">{{ $regCoupon->issued_to_email ?? $regCoupon->usedBy?->email }}</div>
                                @endif
                                @if($regCoupon->issued_to_phone)
                                    <div class="small text-muted">{{ $regCoupon->issued_to_phone }}</div>
                                @endif
                                @if($regCoupon->notes)
                                    <div class="small text-muted">{{ $regCoupon->notes }}</div>
                                @endif
                                <div class="small text-muted">{{ $regCoupon->used_at?->format('d M Y, H:i') }}</div>
                            @else
                                <span class="text-muted">Not used yet</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $regCoupon->created_at?->format('d M Y') }}
                            @if($regCoupon->createdBy)
                                <div>by {{ $regCoupon->createdBy->name }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($regCoupon->isAvailable())
                                <form method="post" action="{{ route('manage.registration-coupons.revoke', $regCoupon) }}" onsubmit="return confirm('Revoke this coupon?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Revoke</button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No registration coupons yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h5 class="fw-semibold mb-3">Coupon history</h5>
    <div class="table-responsive" style="max-height:320px">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Code</th>
                    <th>Action</th>
                    <th>User</th>
                    <th>By</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registrationCouponHistory ?? [] as $entry)
                    <tr>
                        <td class="small text-muted">{{ $entry->created_at?->format('d M Y, H:i') }}</td>
                        <td class="font-monospace small">{{ $entry->coupon?->code }}</td>
                        <td><span class="badge text-bg-light text-dark">{{ ucfirst($entry->action) }}</span></td>
                        <td>
                            @if($entry->subject_name)
                                <div>{{ $entry->subject_name }}</div>
                            @endif
                            @if($entry->subject_email)
                                <div class="small text-muted">{{ $entry->subject_email }}</div>
                            @endif
                        </td>
                        <td class="small">{{ $entry->performedBy?->name ?? '—' }}</td>
                        <td class="small text-muted">{{ $entry->notes }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No history yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
