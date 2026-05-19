@php
    $userTriggers = array_filter(explode(',', $settings['referral_user_triggers'] ?? 'share_first_purchase'));
    $vendorTriggers = array_filter(explode(',', $settings['referral_vendor_triggers'] ?? 'referee_first_sale,referee_first_product'));
@endphp
<div class="admin-section" id="tab-referrals">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Referral program</h2>
            <p class="text-muted small mb-0">Control triggers, rewards, and approve payouts for users (coins) and vendors (sales wallet).</p>
        </div>
        <a href="{{ route('dashboard', ['section' => 'marketing']) }}" class="btn btn-sm btn-outline-secondary rounded-pill">Coin &amp; coupon settings</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="bb-card p-4 h-100">
                <h3 class="h6 fw-bold mb-3">Program settings</h3>
                <form method="post" action="{{ route('manage.referral-settings.save') }}">
                    @csrf
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="referral_program_enabled" value="1" id="refEnabled" @checked(($settings['referral_program_enabled'] ?? '1') === '1')>
                        <label class="form-check-label" for="refEnabled">Referral program enabled</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="referral_require_admin_approval" value="1" id="refApproval" @checked(($settings['referral_require_admin_approval'] ?? '1') === '1')>
                        <label class="form-check-label" for="refApproval">Require admin approval before paying rewards</label>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small">User reward (coins)</label>
                            <input class="form-control" type="number" min="0" name="referral_user_reward_coins" value="{{ $settings['referral_user_reward_coins'] ?? 50 }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Vendor reward (₹ → sales wallet)</label>
                            <input class="form-control" type="number" min="0" step="0.01" name="referral_vendor_reward_amount" value="{{ $settings['referral_vendor_reward_amount'] ?? 100 }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Min. first order amount (₹)</label>
                            <input class="form-control" type="number" min="0" step="0.01" name="referral_min_order_amount" value="{{ $settings['referral_min_order_amount'] ?? 0 }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Share validity (days)</label>
                            <input class="form-control" type="number" min="1" name="referral_share_validity_days" value="{{ $settings['referral_share_validity_days'] ?? 30 }}" required>
                        </div>
                    </div>

                    <p class="small fw-semibold mb-2">User triggers <span class="text-muted fw-normal">(select all that apply)</span></p>
                    <div class="vstack gap-2 mb-3">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="referral_user_triggers[]" value="share_first_purchase" @checked(in_array('share_first_purchase', $userTriggers, true))>
                            <span class="form-check-label">User shared product → friend’s <strong>first delivered order</strong></span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="referral_user_triggers[]" value="first_purchase" @checked(in_array('first_purchase', $userTriggers, true))>
                            <span class="form-check-label">Referred friend’s <strong>first delivered order</strong> (any link/code)</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="referral_user_triggers[]" value="signup_with_code" @checked(in_array('signup_with_code', $userTriggers, true))>
                            <span class="form-check-label">Friend registers with referral code</span>
                        </label>
                    </div>

                    <p class="small fw-semibold mb-2">Vendor triggers</p>
                    <div class="vstack gap-2 mb-3">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="referral_vendor_triggers[]" value="referee_first_sale" @checked(in_array('referee_first_sale', $vendorTriggers, true))>
                            <span class="form-check-label">Referred vendor’s <strong>first delivered sale</strong></span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="referral_vendor_triggers[]" value="referee_first_product" @checked(in_array('referee_first_product', $vendorTriggers, true))>
                            <span class="form-check-label">Referred vendor lists <strong>first approved product</strong></span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="referral_vendor_triggers[]" value="share_first_sale" @checked(in_array('share_first_sale', $vendorTriggers, true))>
                            <span class="form-check-label">You shared product → referred vendor’s <strong>first sale</strong></span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-bloom">Save referral settings</button>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bb-card p-4 h-100">
                <h3 class="h6 fw-bold mb-2">How it works</h3>
                <ul class="small text-muted mb-0 ps-3">
                    <li class="mb-2">Logged-in users share from any product page; share is recorded with a referral link (<code>?ref=CODE</code>).</li>
                    <li class="mb-2">New visitors who register or checkout keep the referrer in session from that link.</li>
                    <li class="mb-2">When enabled triggers match, a reward row is created — pending or auto-approved per your setting.</li>
                    <li class="mb-2">Users receive <strong>coins</strong>; vendors receive ₹ in <strong>sales wallet</strong> (same balance used for payout claims).</li>
                    <li>Delivered orders credit vendor sales wallet automatically; referral bonuses add on top after approval.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bb-card p-4">
        <h3 class="h6 fw-bold mb-3">Referral rewards queue</h3>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Referrer</th>
                        <th>Referee</th>
                        <th>Type</th>
                        <th>Trigger</th>
                        <th>Reward</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($referralRewards ?? [] as $reward)
                        <tr>
                            <td class="small">{{ $reward->referrer?->name }}<br><span class="text-muted">{{ $reward->referrer?->email }}</span></td>
                            <td class="small">{{ $reward->referee?->name }}</td>
                            <td><span class="badge badge-soft">{{ $reward->beneficiary_type }}</span></td>
                            <td class="small text-muted">{{ str_replace('_', ' ', $reward->trigger_type) }}</td>
                            <td class="fw-semibold">
                                @if ($reward->beneficiary_type === 'vendor')
                                    ₹{{ number_format($reward->reward_amount, 2) }}
                                @else
                                    {{ $reward->reward_coins }} coins
                                @endif
                            </td>
                            <td>
                                <span class="badge @if($reward->status === 'paid') bg-success @elseif($reward->status === 'pending') bg-warning text-dark @elseif($reward->status === 'rejected') bg-danger @else bg-secondary @endif">
                                    {{ ucfirst($reward->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if ($reward->status === 'pending')
                                    <form method="post" action="{{ route('manage.referral-rewards.approve', $reward) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="redirect_section" value="referrals">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="post" action="{{ route('manage.referral-rewards.reject', $reward) }}" class="d-inline ms-1" onsubmit="return confirm('Reject this reward?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                    </form>
                                @else
                                    <span class="small text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No referral rewards yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
