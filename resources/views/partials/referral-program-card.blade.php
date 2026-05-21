@php
    $refEnabled = $referralEnabled ?? \App\Support\ReferralSettings::enabled();
    $refCode = $referralCode ?? '';
    $refLink = route('home').'?ref='.$refCode;
    $isVendor = ($referralRole ?? auth()->user()?->role) === 'vendor';
@endphp
<div class="bb-card p-4 mb-4" id="referralProgramCard">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1"><i class="bi bi-share me-2 text-bloom"></i>Refer &amp; earn</h2>
            <p class="text-muted small mb-0">
                @if ($refEnabled)
                    Share products or your link. Rewards unlock when friends join and complete the actions your admin has enabled.
                @else
                    The referral program is paused by admin. You can still share products from any listing page.
                @endif
            </p>
        </div>
        @if ($refEnabled)
            <span class="badge rounded-pill bg-success-subtle text-success border">Active</span>
        @else
            <span class="badge rounded-pill bg-secondary-subtle text-secondary border">Paused</span>
        @endif
    </div>

    @if ($refCode)
        <label class="form-label small text-muted mb-1">Your referral code</label>
        <div class="input-group mb-3">
            <input type="text" class="form-control font-monospace" id="bbReferralCode" value="{{ $refCode }}" readonly>
            <button type="button" class="btn btn-soft" data-copy-target="bbReferralCode" title="Copy code"><i class="bi bi-clipboard"></i></button>
        </div>
        <label class="form-label small text-muted mb-1">Invite link</label>
        <div class="input-group mb-3">
            <input type="text" class="form-control small" id="bbReferralLink" value="{{ $refLink }}" readonly>
            <button type="button" class="btn btn-soft" data-copy-target="bbReferralLink" title="Copy link"><i class="bi bi-link-45deg"></i></button>
            @if ($refEnabled)
                <button type="button" class="btn btn-bloom" id="bbReferralNativeShare" data-share-url="{{ $refLink }}" data-share-title="Join Behna Bazar">
                    <i class="bi bi-box-arrow-up"></i>
                </button>
            @endif
        </div>
    @endif

    <ul class="small text-muted mb-3 ps-3">
        @if ($isVendor)
            <li>Earn referral bonuses in your <strong>sales wallet</strong> (claim via payout when balance ≥ ₹500).</li>
            <li>Share a product before a referred vendor’s first sale to qualify for share-based rewards.</li>
        @else
            <li>Earn <strong>coins</strong> when referred friends complete their first order (per admin rules).</li>
            <li>Share any product while logged in — that share is tracked for referral rewards.</li>
        @endif
    </ul>

    @if (isset($referralRewards) && $referralRewards->count())
        <h3 class="h6 fw-bold mb-2">Your referral rewards</h3>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Friend</th>
                        <th>Trigger</th>
                        <th>Reward</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($referralRewards as $reward)
                        <tr>
                            <td class="small">{{ $reward->referee?->name ?? '—' }}</td>
                            <td class="small text-muted">{{ str_replace('_', ' ', $reward->trigger_type) }}</td>
                            <td class="small fw-semibold">
                                @if ($reward->beneficiary_type === 'vendor')
                                    ₹{{ number_format($reward->reward_amount, 0) }}
                                @else
                                    {{ $reward->reward_coins }} coins
                                @endif
                            </td>
                            <td>
                                <span class="badge @if($reward->status === 'paid') bg-success @elseif($reward->status === 'pending') bg-warning text-dark @elseif($reward->status === 'rejected') bg-danger @else bg-info @endif">
                                    {{ ucfirst($reward->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="small text-muted mb-0">No referral rewards yet. Start by sharing a product or your invite link.</p>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-copy-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var el = document.getElementById(btn.getAttribute('data-copy-target'));
            if (!el) return;
            navigator.clipboard.writeText(el.value).then(function () {
                if (typeof window.bbToast === 'function') window.bbToast('Copied!', 'success');
                else alert('Copied!');
            });
        });
    });
    var shareBtn = document.getElementById('bbReferralNativeShare');
    if (shareBtn && navigator.share) {
        shareBtn.addEventListener('click', function () {
            navigator.share({
                title: shareBtn.dataset.shareTitle || 'Behna Bazar',
                url: shareBtn.dataset.shareUrl,
            }).catch(function () {});
        });
    }
});
</script>
