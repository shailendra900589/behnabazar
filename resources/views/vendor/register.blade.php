@extends('layouts.app')
@section('title', 'Vendor Membership Application')
@section('content')
<section class="vendor-application-page">
    <div class="vendor-application-hero">
        <img src="{{ asset('images/brand/behna-bazar-hero.jpeg') }}" alt="Behna Bazar sellers and marketplace products" class="vendor-hero-img">
        <div class="vendor-hero-overlay"></div>
        <div class="container position-relative">
            <div class="row align-items-end g-4">
                <div class="col-lg-7">
                    <div class="vendor-kicker">Vendor membership application</div>
                    <h1>Sell products across every everyday category</h1>
                    <p class="vendor-hero-copy">Join a curated multipurpose marketplace for grocery, organic and non-organic products, electronics, clothing, beauty, home goods, handmade items, and local essentials.</p>
                    <div class="vendor-hero-actions">
                        <a href="#vendorApplicationForm" class="btn btn-bloom btn-lg rounded-pill px-4">Start application</a>
                        <span class="vendor-fee-chip"><i class="bi bi-patch-check-fill"></i> One-time membership fee: Rs. 150</span>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="vendor-brand-panel">
                        <img src="{{ asset('images/brand/bb-mark.jpeg') }}" alt="Behna Bazar BB logo" class="vendor-emblem">
                        <div>
                            <div class="h4 fw-bold mb-1">Behna Bazar</div>
                            <p class="mb-0">A multipurpose marketplace for trusted local and modern sellers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-4 align-items-start">
            <div class="col-xl-4">
                <div class="vendor-info-panel">
                    <img src="{{ asset('images/brand/behna-bazar-wordmark.jpeg') }}" alt="Behna Bazar wordmark" class="vendor-wordmark mb-4">
                    <h2 class="h4 fw-bold mb-3">Who should apply?</h2>
                    <p class="text-muted mb-4">This application is for sellers who can offer reliable products, clear pricing, proper documentation, and service standards that match the Behna Bazar promise of trust and quality.</p>

                    <div class="vendor-focus-list">
                        <div><i class="bi bi-basket2-fill"></i><span>Organic, non-organic, grocery, packaged foods, and daily essentials</span></div>
                        <div><i class="bi bi-cpu-fill"></i><span>Electronics, accessories, appliances, and modern lifestyle products</span></div>
                        <div><i class="bi bi-bag-heart-fill"></i><span>Clothing, footwear, beauty, jewelry, textiles, and fashion goods</span></div>
                        <div><i class="bi bi-house-heart-fill"></i><span>Home decor, handicrafts, kitchenware, stationery, and local products</span></div>
                    </div>

                    <div class="vendor-trust-strip">
                        <img src="{{ asset('images/brand/bb-mark.jpeg') }}" alt="Behna Bazar verified marketplace mark">
                        <div>
                            <strong>Quality first</strong>
                            <span>Every shop is reviewed before activation and every listing is checked for category fit and marketplace quality.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="vendor-application-card" id="vendorApplicationForm">
                    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                        <div>
                            <span class="vendor-section-label">Professional onboarding</span>
                            <h2 class="h3 fw-bold mb-2">Membership application form</h2>
                            <p class="text-muted mb-0">Share accurate business details so our team can verify your shop and prepare your vendor dashboard.</p>
                        </div>
                        <div class="vendor-review-mark">
                            <img src="{{ asset('images/brand/bb-mark.jpeg') }}" alt="Behna Bazar BB mark">
                            <span>3-step review</span>
                        </div>
                    </div>

                    <div class="vendor-steps mb-4">
                        <div><strong>1</strong><span>Email OTP verification</span></div>
                        <div><strong>2</strong><span>Rs. 150 membership fee</span></div>
                        <div><strong>3</strong><span>Admin and QC approval</span></div>
                    </div>

                    <form method="post" action="{{ route('vendor.register.store') }}" class="row g-3" enctype="multipart/form-data">
                        @csrf
                        <div class="col-12">
                            <h3 class="vendor-form-heading">Applicant details</h3>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full name</label>
                            <input class="form-control" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="Applicant or proprietor name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email address</label>
                            <input class="form-control" type="email" name="email" value="{{ old('email') }}" required maxlength="150" placeholder="name@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile number</label>
                            <input class="form-control" name="phone" value="{{ old('phone') }}" required maxlength="30" placeholder="Primary contact number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input class="form-control" name="city" value="{{ old('city') }}" required maxlength="100" placeholder="Your city or nearest town">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Referral code <span class="text-muted fw-normal">(optional)</span></label>
                            <input class="form-control" name="referral_code" value="{{ old('referral_code', session('referral_code')) }}" maxlength="16" placeholder="Invited by a friend?">
                        </div>

                        <div class="col-12 pt-2">
                            <h3 class="vendor-form-heading">Shop and product details</h3>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Shop display name</label>
                            <input class="form-control" name="shop_name" value="{{ old('shop_name') }}" required maxlength="100" placeholder="Example: City Electronics, Fresh Basket, Style Studio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Primary product category</label>
                            <select class="form-select" name="product_category" required>
                                <option value="">Select your main category</option>
                                @foreach(['Grocery and Daily Essentials', 'Organic Products', 'Non-Organic Products', 'Electronics and Appliances', 'Clothing and Fashion', 'Footwear and Accessories', 'Home and Kitchen', 'Beauty and Personal Care', 'Handmade and Local Goods', 'Other Verified Goods'] as $category)
                                    <option value="{{ $category }}" @selected(old('product_category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">This helps our team route your application to the right category reviewer.</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Business address</label>
                            <textarea class="form-control" name="address" rows="3" required maxlength="500" placeholder="Shop/warehouse, street, locality, city, district">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">PIN code</label>
                            <input class="form-control" name="pincode" value="{{ old('pincode') }}" required maxlength="20" placeholder="Postal code">
                        </div>

                        <div class="col-12 pt-2">
                            <h3 class="vendor-form-heading">Verification document</h3>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Document type</label>
                            <select class="form-select" name="document_type" required>
                                <option value="">Select document</option>
                                @foreach(['Aadhaar Card', 'PAN Card', 'GST Certificate', 'Udyam Registration', 'FSSAI License', 'Shop License', 'Business Registration'] as $document)
                                    <option value="{{ $document }}" @selected(old('document_type') === $document)>{{ $document }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload document</label>
                            <input class="form-control" type="file" name="document_file" required accept="image/*,.pdf">
                            @include('partials.upload-size-hint', ['type' => 'vendor_document'])
                        </div>

                        <div class="col-12 pt-2">
                            <h3 class="vendor-form-heading">Secure account</h3>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input class="form-control" type="password" name="password" required minlength="8" placeholder="Minimum 8 characters">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm password</label>
                            <input class="form-control" type="password" name="password_confirmation" required minlength="8" placeholder="Re-enter password">
                        </div>

                        <div class="col-12">
                            <div class="vendor-consent">
                                <input class="form-check-input" type="checkbox" id="vendorConsent" name="vendor_consent" value="1" required>
                                <label class="form-check-label" for="vendorConsent">I confirm that the information provided is accurate and that my products will follow Behna Bazar quality, authenticity, and customer service standards.</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-bloom w-100 py-3 mt-2">Submit application and verify email</button>
                        </div>
                    </form>
                    <p class="text-center text-muted small mt-4 mb-0">Already registered? <a href="{{ route('login') }}" class="fw-semibold text-bloom">Sign in</a> / Need a customer account? <a href="{{ route('register') }}" class="fw-semibold text-bloom">Join as customer</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
