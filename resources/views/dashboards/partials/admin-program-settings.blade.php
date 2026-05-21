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
                    <label class="form-label">Branded listing fee (₹)</label>
                    <p class="small text-muted">Deducted from reseller sales wallet when they use own title/photos.</p>
                    <input class="form-control mb-3" type="number" min="0" step="0.01" name="resell_customize_fee" value="{{ $settings['resell_customize_fee'] ?? 99 }}" required>
                    <label class="form-label">Bulk min quantity</label>
                    <input class="form-control mb-3" type="number" min="1" name="resell_bulk_min_qty" value="{{ $settings['resell_bulk_min_qty'] ?? 5 }}" required>
                    <label class="form-label">Bulk discount (% off source price)</label>
                    <input class="form-control mb-3" type="number" min="0" max="50" step="0.5" name="resell_bulk_discount_percent" value="{{ $settings['resell_bulk_discount_percent'] ?? 5 }}" required>
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
        <div class="col-lg-6">
            <div class="bb-card p-4 h-100">
                <h3 class="h6 fw-bold mb-3">Storefront branding</h3>
                <p class="small text-muted mb-3">Shown in the header, footer, page titles, and home hero. Leave blank to use the default app name from <code>.env</code>.</p>
                <form method="post" action="{{ route('manage.program-settings.save') }}">
                    @csrf
                    <label class="form-label">Marketplace display name</label>
                    <input class="form-control mb-3" type="text" name="site_display_name" maxlength="80" value="{{ $settings['site_display_name'] ?? '' }}" placeholder="Behna Bazar">
                    <label class="form-label">Footer tagline</label>
                    <textarea class="form-control mb-3" name="site_tagline" rows="3" maxlength="300" placeholder="Short description for the footer">{{ $settings['site_tagline'] ?? '' }}</textarea>
                    <label class="form-label">Navigation “Home” label</label>
                    <input class="form-control mb-3" type="text" name="nav_home_label" maxlength="24" value="{{ $settings['nav_home_label'] ?? '' }}" placeholder="Home">
                    <button type="submit" class="btn btn-bloom">Save branding</button>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bb-card p-4 h-100">
                <h3 class="h6 fw-bold mb-3">Checkout &amp; delivery</h3>
                <form method="post" action="{{ route('manage.program-settings.save') }}">
                    @csrf
                    <input type="hidden" name="cod_enabled" value="0">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="cod_enabled" value="1" id="codOn" @checked(($settings['cod_enabled'] ?? '1') === '1')>
                        <label class="form-check-label" for="codOn">Enable Cash on Delivery (COD)</label>
                    </div>
                    <label class="form-label">Free delivery above (₹)</label>
                    <input class="form-control mb-2" type="number" min="0" name="free_shipping_threshold" value="{{ $settings['free_shipping_threshold'] ?? 499 }}">
                    <label class="form-label">Serviceable PIN codes</label>
                    <textarea class="form-control mb-2" name="delivery_pincodes" rows="2" placeholder="110001, 110002, 201301 (empty = all India)">{{ $settings['delivery_pincodes'] ?? '' }}</textarea>
                    <button type="submit" class="btn btn-bloom btn-sm">Save checkout rules</button>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bb-card p-4 h-100">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-whatsapp text-success me-1"></i> Business WhatsApp</h3>
                @php
                    $cloudReady = app(\App\Services\WhatsApp\WhatsAppCloudSender::class)->isConfigured();
                    $driverLabel = app(\App\Services\WhatsApp\WhatsAppService::class)->activeDriverLabel();
                @endphp
                <p class="small text-muted mb-2">
                    <strong>Poora automatic:</strong> Meta WhatsApp Cloud API (official) — order/cart/stock sab auto.
                    <strong>Free jugad:</strong> Outbox + WhatsApp Web (API key optional).
                </p>
                <div class="alert {{ $cloudReady ? 'alert-success' : 'alert-warning' }} border-0 small py-2 mb-3">
                    Active mode: <strong>{{ $driverLabel }}</strong>
                    @if($cloudReady)
                        — messages server se khud jayengi (cron <code>whatsapp:process-outbox</code> har minute).
                    @else
                        — Cloud API token + Phone ID daalo neeche, ya Outbox use karo.
                    @endif
                </div>
                <form method="post" action="{{ route('manage.program-settings.save') }}">
                    @csrf
                    <input type="hidden" name="notify_sms_enabled" value="0">
                    <input type="hidden" name="notify_whatsapp_enabled" value="0">
                    <input type="hidden" name="notify_order_sms_customer" value="0">
                    <input type="hidden" name="notify_order_sms_vendor" value="0">
                    <input type="hidden" name="notify_order_whatsapp_customer" value="0">
                    <input type="hidden" name="notify_order_whatsapp_admin" value="0">
                    <input type="hidden" name="notify_order_whatsapp_vendor" value="0">
                    <input type="hidden" name="abandoned_cart_enabled" value="0">
                    <input type="hidden" name="stock_alert_enabled" value="0">
                    <input type="hidden" name="notify_order_status_customer" value="0">
                    <label class="form-label small fw-semibold">Business WhatsApp number (sender)</label>
                    <input class="form-control form-control-sm mb-2" name="whatsapp_business_phone" value="{{ $settings['whatsapp_business_phone'] ?? $settings['order_alert_phone'] ?? '' }}" placeholder="9876543210" required>
                    <hr class="opacity-10">
                    <p class="small fw-semibold mb-2">Meta WhatsApp Cloud API (100% automatic)</p>
                    <label class="form-label small">Permanent access token</label>
                    <input class="form-control form-control-sm mb-2" name="whatsapp_cloud_token" value="{{ $settings['whatsapp_cloud_token'] ?? '' }}" placeholder="EAAxxxx..." autocomplete="off">
                    <label class="form-label small">Phone number ID</label>
                    <input class="form-control form-control-sm mb-2" name="whatsapp_cloud_phone_id" value="{{ $settings['whatsapp_cloud_phone_id'] ?? '' }}" placeholder="1234567890">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">API version</label>
                            <input class="form-control form-control-sm" name="whatsapp_cloud_api_version" value="{{ $settings['whatsapp_cloud_api_version'] ?? 'v21.0' }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Template name (optional)</label>
                            <input class="form-control form-control-sm" name="whatsapp_cloud_template" value="{{ $settings['whatsapp_cloud_template'] ?? '' }}" placeholder="order_update">
                        </div>
                    </div>
                    <p class="small text-muted mb-2">
                        <a href="https://developers.facebook.com/docs/whatsapp/cloud-api/get-started" target="_blank" rel="noopener">Meta Developer</a> → App → WhatsApp → API Setup.
                        <code>WHATSAPP_DRIVER=auto</code> in .env
                    </p>
                    <label class="form-label small text-muted">CallMeBot key (optional backup)</label>
                    <input class="form-control form-control-sm mb-2" name="whatsapp_callmebot_api_key" value="{{ $settings['whatsapp_callmebot_api_key'] ?? '' }}" placeholder="optional" autocomplete="off">
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="notify_whatsapp_enabled" value="1" id="waOn" @checked(($settings['notify_whatsapp_enabled'] ?? '1') === '1')>
                        <label class="form-check-label" for="waOn">WhatsApp notifications ON</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="notify_order_whatsapp_customer" value="1" id="waCust" @checked(($settings['notify_order_whatsapp_customer'] ?? '1') === '1')>
                        <label class="form-check-label" for="waCust">WhatsApp to customer (order confirm)</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="notify_order_whatsapp_vendor" value="1" id="waVend" @checked(($settings['notify_order_whatsapp_vendor'] ?? '1') === '1')>
                        <label class="form-check-label" for="waVend">WhatsApp to vendor (new order)</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="notify_order_whatsapp_admin" value="1" id="waAdmin" @checked(($settings['notify_order_whatsapp_admin'] ?? '1') === '1')>
                        <label class="form-check-label" for="waAdmin">WhatsApp copy to admin (optional 2nd number)</label>
                    </div>
                    <label class="form-label small">Admin alert phone (khali = business number par alert)</label>
                    <input class="form-control form-control-sm mb-2" name="order_alert_phone" value="{{ $settings['order_alert_phone'] ?? '' }}" placeholder="same as business or different">
                    <hr class="opacity-10">
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="notify_sms_enabled" value="1" id="smsOn" @checked(($settings['notify_sms_enabled'] ?? '1') === '1')>
                        <label class="form-check-label" for="smsOn">SMS notifications</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="notify_order_sms_customer" value="1" id="smsCust" @checked(($settings['notify_order_sms_customer'] ?? '1') === '1')>
                        <label class="form-check-label" for="smsCust">SMS to customer on order</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="notify_order_sms_vendor" value="1" id="smsVend" @checked(($settings['notify_order_sms_vendor'] ?? '1') === '1')>
                        <label class="form-check-label" for="smsVend">SMS to vendor on order</label>
                    </div>
                    <hr class="opacity-10">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="notify_order_status_customer" value="1" id="statusNotify" @checked(($settings['notify_order_status_customer'] ?? '1') === '1')>
                        <label class="form-check-label" for="statusNotify">Notify customer on shipped / delivered / cancelled</label>
                    </div>
                    <label class="form-label small">Vendor low-stock alert (units)</label>
                    <input type="number" min="1" max="100" class="form-control form-control-sm mb-2" name="vendor_low_stock_threshold" value="{{ $settings['vendor_low_stock_threshold'] ?? 5 }}">
                    <hr class="opacity-10">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="abandoned_cart_enabled" value="1" id="abCart" @checked(($settings['abandoned_cart_enabled'] ?? '1') === '1')>
                        <label class="form-check-label" for="abCart">Abandoned cart reminders</label>
                    </div>
                    <input type="hidden" name="abandoned_cart_email" value="0">
                    <input type="hidden" name="abandoned_cart_sms" value="0">
                    <input type="hidden" name="abandoned_cart_whatsapp" value="0">
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="abandoned_cart_email" value="1" id="abEmail" @checked(($settings['abandoned_cart_email'] ?? '1') === '1')>
                        <label class="form-check-label" for="abEmail">Abandoned cart — Email</label>
                    </div>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="checkbox" name="abandoned_cart_sms" value="1" id="abSms" @checked(($settings['abandoned_cart_sms'] ?? '1') === '1')>
                        <label class="form-check-label" for="abSms">Abandoned cart — SMS</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="abandoned_cart_whatsapp" value="1" id="abWa" @checked(($settings['abandoned_cart_whatsapp'] ?? '1') === '1')>
                        <label class="form-check-label" for="abWa">Abandoned cart — WhatsApp</label>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Idle hours</label>
                            <input type="number" min="1" class="form-control form-control-sm" name="abandoned_cart_idle_hours" value="{{ $settings['abandoned_cart_idle_hours'] ?? 24 }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Cooldown hrs</label>
                            <input type="number" min="24" class="form-control form-control-sm" name="abandoned_cart_cooldown_hours" value="{{ $settings['abandoned_cart_cooldown_hours'] ?? 72 }}">
                        </div>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="stock_alert_enabled" value="1" id="stockAlert" @checked(($settings['stock_alert_enabled'] ?? '1') === '1')>
                        <label class="form-check-label" for="stockAlert">Back-in-stock alerts</label>
                    </div>
                    <p class="small text-muted mb-2">Cron: <code>php artisan schedule:work</code> or server cron <code>* * * * * php artisan schedule:run</code></p>
                    <button type="submit" class="btn btn-bloom btn-sm">Save notifications</button>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bb-card p-4 h-100">
                <h3 class="h6 fw-bold mb-3">SEO, GEO &amp; AEO</h3>
                <p class="small text-muted mb-3">Auto SEO runs on all products and pages. Set your business location for Google Maps / local search and answer-engine rich results.</p>
                <form method="post" action="{{ route('manage.program-settings.save') }}">
                    @csrf
                    <label class="form-label">City / locality</label>
                    <input class="form-control mb-2" type="text" name="seo_locality" maxlength="120" value="{{ $settings['seo_locality'] ?? '' }}" placeholder="e.g. Delhi NCR">
                    <label class="form-label">Region code</label>
                    <input class="form-control mb-2" type="text" name="seo_region" maxlength="10" value="{{ $settings['seo_region'] ?? 'IN' }}" placeholder="IN">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small">Latitude</label>
                            <input class="form-control" type="text" name="seo_latitude" value="{{ $settings['seo_latitude'] ?? '' }}" placeholder="28.6139">
                        </div>
                        <div class="col-6">
                            <label class="form-label small">Longitude</label>
                            <input class="form-control" type="text" name="seo_longitude" value="{{ $settings['seo_longitude'] ?? '' }}" placeholder="77.2090">
                        </div>
                    </div>
                    <label class="form-label">Support email (schema.org)</label>
                    <input class="form-control mb-2" type="email" name="seo_contact_email" value="{{ $settings['seo_contact_email'] ?? '' }}">
                    <label class="form-label">Support phone</label>
                    <input class="form-control mb-3" type="text" name="seo_contact_phone" value="{{ $settings['seo_contact_phone'] ?? '' }}">
                    <p class="small text-muted mb-2"><a href="{{ route('sitemap') }}" target="_blank" rel="noopener">sitemap.xml</a> · <a href="{{ route('robots') }}" target="_blank" rel="noopener">robots.txt</a></p>
                    <button type="submit" class="btn btn-bloom btn-sm">Save SEO / GEO</button>
                </form>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bb-card p-4 h-100">
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
