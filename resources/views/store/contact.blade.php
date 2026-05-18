@extends('layouts.app')
@section('title', 'Help Center & Contact')
@section('content')
<section class="container py-4 py-lg-5">
    <div class="bb-card rounded-4 p-4 p-lg-5 mb-5 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--bb-ink) 0%, #065f46 100%);">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="row align-items-center position-relative z-1">
            <div class="col-md-7 text-center text-md-start mb-4 mb-md-0">
                <h1 class="display-5 fw-bold mb-3 d-flex align-items-center justify-content-center justify-content-md-start gap-2">We're here to help <i class="bi bi-chat-heart-fill text-warning"></i></h1>
                <p class="mb-0 text-white-50 lead" style="max-width: 600px;">Have a question about an order, category, vendor, return, or seller application? Send us a message and our support team will get back to you.</p>
            </div>
            <div class="col-md-5 text-center text-md-end d-none d-md-block">
                <i class="bi bi-headset text-white opacity-25" style="font-size: 8rem;"></i>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-lg-4">
            <h3 class="fw-bold mb-4">Get in touch</h3>
            
            <div class="d-flex align-items-start gap-3 mb-4 bb-card p-4 rounded-4 shadow-sm border-0 border-start border-4 border-bloom">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-bloom shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Office Address</h5>
                    <p class="text-muted mb-0 small">123 Commerce Avenue<br>Business District<br>New Delhi, 110001</p>
                </div>
            </div>

            <div class="d-flex align-items-start gap-3 mb-4 bb-card p-4 rounded-4 shadow-sm border-0 border-start border-4 border-bloom">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-bloom shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Email Support</h5>
                    <p class="text-muted mb-0 small">support@behnabazar.in<br>vendors@behnabazar.in</p>
                </div>
            </div>

            <div class="d-flex align-items-start gap-3 bb-card p-4 rounded-4 shadow-sm border-0 border-start border-4 border-bloom">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center text-bloom shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-telephone-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Call Us</h5>
                    <p class="text-muted mb-0 small">+91 1800-BEHNA-BAZAR<br>Mon-Fri, 9AM-6PM</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="bb-card p-4 p-lg-5 rounded-4 shadow-sm">
                <h3 class="fw-bold mb-4">Send a Message</h3>
                <form action="#" method="POST" onsubmit="event.preventDefault(); Toast.fire({icon:'success', title:'Message sent successfully! Our team will contact you shortly.'}); this.reset();">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Your Name</label>
                            <input type="text" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Subject</label>
                            <select class="form-select" required>
                                <option value="">Select a topic...</option>
                                <option value="order">Question about an Order</option>
                                <option value="return">Returns & Refunds</option>
                                <option value="vendor">Vendor/Seller Application</option>
                                <option value="category">Product Category Support</option>
                                <option value="other">General Inquiry</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Message</label>
                            <textarea class="form-control" rows="5" placeholder="How can we help you today?" required></textarea>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-bloom btn-lg rounded-pill px-5 shadow-sm fw-bold">
                                <i class="bi bi-send-fill me-2"></i> Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
