<div class="admin-section" id="tab-program">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Program &amp; marketplace settings</h2>
            <p class="text-muted small mb-0">Resell rules, vendor fees, and payout limits. Coins, coupons, and ads are under Rewards &amp; Storefront tabs.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="bb-card p-4 h-100">
                <h3 class="h6 fw-bold mb-3">Vendor resell program</h3>
                <form method="post" action="{{ route('manage.program-settings.save') }}">
                    @csrf
                    <input type="hidden" name="resell_program_enabled" value="0">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="resell_program_enabled" value="1" id="resellOn" @checked(($settings['resell_program_enabled'] ?? '1') === '1')>
                        <label class="form-check-label" for="resellOn">Enable vendor-to-vendor resell</label>
                    </div>
                    <label class="form-label">Customize listing fee (₹)</label>
                    <p class="small text-muted">Deducted from vendor sales wallet when they customize title/photos for a resell listing.</p>
                    <input class="form-control mb-3" type="number" min="0" step="0.01" name="resell_customize_fee" value="{{ $settings['resell_customize_fee'] ?? 99 }}" required>
                    <button type="submit" class="btn btn-bloom">Save resell settings</button>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bb-card p-4 h-100">
                <h3 class="h6 fw-bold mb-3">Vendor onboarding &amp; payouts</h3>
                <form method="post" action="{{ route('manage.program-settings.save') }}">
                    @csrf
                    <label class="form-label">Vendor registration fee (₹)</label>
                    <input class="form-control mb-3" type="number" min="0" step="0.01" name="vendor_registration_amount" value="{{ $settings['vendor_registration_amount'] ?? 150 }}" required>
                    <label class="form-label">Minimum payout claim (₹)</label>
                    <input class="form-control mb-3" type="number" min="1" step="0.01" name="payout_min_amount" value="{{ $settings['payout_min_amount'] ?? 500 }}" required>
                    <label class="form-label">Minimum ad wallet top-up (₹)</label>
                    <input class="form-control mb-3" type="number" min="1" step="0.01" name="ad_wallet_min_topup" value="{{ $settings['ad_wallet_min_topup'] ?? 50 }}" required>
                    <button type="submit" class="btn btn-bloom">Save vendor &amp; wallet rules</button>
                </form>
            </div>
        </div>
        <div class="col-12">
            <div class="bb-card p-4">
                <h3 class="h6 fw-bold mb-3">Product QC on edit</h3>
                <form method="post" action="{{ route('manage.program-settings.save') }}">
                    @csrf
                    <input type="hidden" name="product_edit_requires_qc" value="0">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="product_edit_requires_qc" value="1" id="qcOnEdit" @checked(($settings['product_edit_requires_qc'] ?? '1') === '1')>
                        <label class="form-check-label" for="qcOnEdit">When an <strong>approved</strong> product is edited, send it back to QC (pending)</label>
                    </div>
                    <p class="small text-muted mb-3">Vendors and admins (including “Login as vendor”) can always edit listings. With this on, approved products go to QC again after changes.</p>
                    <button type="submit" class="btn btn-outline-dark btn-sm">Save QC rule</button>
                </form>
            </div>
        </div>
    </div>
</div>
