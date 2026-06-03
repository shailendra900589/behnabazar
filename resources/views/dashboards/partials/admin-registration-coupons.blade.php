<div class="bb-card p-4 mb-4">
    <h4 class="fw-bold mb-2">Vendor registration coupons</h4>
    <p class="text-muted small mb-3">One-time codes waive the vendor registration fee. Each code is unique and can be used only once. Issue in the recipient&apos;s name and share the code with them.</p>
    <form method="post" action="{{ route('manage.registration-coupons.save') }}" class="row g-2 mb-4">
        @csrf
        <div class="col-md-4">
            <label class="form-label small">Coupon code <span class="text-muted">(leave blank to auto-generate)</span></label>
            <input class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" maxlength="32" placeholder="REGSELLER01" pattern="[A-Za-z0-9_-]+">
            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label small">Issued to (name) <span class="text-danger">*</span></label>
            <input class="form-control @error('issued_to_name') is-invalid @enderror" name="issued_to_name" value="{{ old('issued_to_name') }}" required maxlength="120" placeholder="Rahul Sharma">
            @error('issued_to_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label small">Issued to email <span class="text-muted">(optional lock)</span></label>
            <input class="form-control" type="email" name="issued_to_email" value="{{ old('issued_to_email') }}" maxlength="150" placeholder="seller@example.com">
            <div class="form-text">If set, only this email can redeem the coupon.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label small">Phone</label>
            <input class="form-control" name="issued_to_phone" value="{{ old('issued_to_phone') }}" maxlength="30">
        </div>
        <div class="col-md-8">
            <label class="form-label small">Notes</label>
            <input class="form-control" name="notes" value="{{ old('notes') }}" maxlength="500" placeholder="Campaign / referral source">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-bloom">Create registration coupon</button>
        </div>
    </form>

    <div class="table-responsive mb-4">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Issued to</th>
                    <th>Status</th>
                    <th>Used by</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registrationCoupons ?? [] as $regCoupon)
                    <tr>
                        <td class="fw-semibold font-monospace">{{ $regCoupon->code }}</td>
                        <td>
                            <div>{{ $regCoupon->issued_to_name }}</div>
                            @if($regCoupon->issued_to_email)
                                <div class="small text-muted">{{ $regCoupon->issued_to_email }}</div>
                            @endif
                        </td>
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
                            @if($regCoupon->usedBy)
                                <div>{{ $regCoupon->usedBy->name }}</div>
                                <div class="small text-muted">{{ $regCoupon->usedBy->email }}</div>
                                <div class="small text-muted">{{ $regCoupon->used_at?->format('d M Y, H:i') }}</div>
                            @else
                                <span class="text-muted">—</span>
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
                    <tr><td colspan="6" class="text-muted">No registration coupons yet.</td></tr>
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
                    <th>Person</th>
                    <th>By</th>
                    <th>Notes</th>
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
