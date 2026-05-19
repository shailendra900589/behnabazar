<footer class="bb-footer mt-5">
    <div class="container bb-footer-main">
        <div class="row g-4 g-lg-5 align-items-start">
            <div class="col-lg-4 col-md-6">
                <a class="bb-footer-brand" href="{{ route('home') }}" aria-label="Behna Bazar home">
                    <img src="{{ asset('images/brand/bb-mark.jpeg') }}" alt="" class="bb-footer-mark" width="48" height="48">
                    <span class="bb-footer-name">Behna Bazar</span>
                </a>
                <p class="bb-footer-tagline">Your trusted multipurpose marketplace for grocery, fashion, electronics, home, beauty, and local sellers.</p>
                <div class="bb-footer-social" aria-label="Social media">
                    <a href="https://x.com/behnabazar" target="_blank" rel="noopener noreferrer" class="bb-footer-social-link" aria-label="X (Twitter)"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://www.instagram.com/behnabazar/" target="_blank" rel="noopener noreferrer" class="bb-footer-social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.facebook.com/behnaBazar/" target="_blank" rel="noopener noreferrer" class="bb-footer-social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.youtube.com/@behnaBazar" target="_blank" rel="noopener noreferrer" class="bb-footer-social-link" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h6 class="bb-footer-heading">Shop</h6>
                <ul class="bb-footer-links list-unstyled mb-0">
                    <li><a href="{{ route('home') }}">All Products</a></li>
                    <li><a href="{{ route('cart') }}">Cart</a></li>
                    <li><a href="{{ route('wishlist') }}">Wishlist</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h6 class="bb-footer-heading">Account</h6>
                <ul class="bb-footer-links list-unstyled mb-0">
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('orders') }}">Orders</a></li>
                    <li><a href="{{ route('profile') }}">Profile</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h6 class="bb-footer-heading">Support</h6>
                <ul class="bb-footer-links list-unstyled mb-0">
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                    <li><a href="mailto:support@behnabazar.in">support@behnabazar.in</a></li>
                    <li><a href="{{ route('local-delivery') }}">Local delivery</a></li>
                    <li><a href="{{ route('returns-policy') }}">Returns policy</a></li>
                </ul>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <h6 class="bb-footer-heading">Sell</h6>
                <ul class="bb-footer-links list-unstyled mb-0">
                    <li><a href="{{ route('vendor.register.create') }}">Become a Seller</a></li>
                    <li><a href="{{ route('login') }}">Seller Sign In</a></li>
                </ul>
            </div>
        </div>

        <div class="bb-footer-newsletter">
            <div class="row g-3 g-lg-4 align-items-center">
                <div class="col-lg-5">
                    <h6 class="bb-footer-heading mb-2">Newsletter</h6>
                    <p class="bb-footer-newsletter-text mb-0">Get special offers, new arrivals, and exclusive deals delivered to your inbox.</p>
                </div>
                <div class="col-lg-7">
                    <form data-ajax-form action="{{ route('newsletter.subscribe') }}" method="post" class="bb-footer-newsletter-form">
                        @csrf
                        <label for="footerNewsletterEmail" class="visually-hidden">Email address</label>
                        <input
                            type="email"
                            id="footerNewsletterEmail"
                            name="email"
                            class="form-control bb-footer-newsletter-input"
                            placeholder="Enter your email"
                            autocomplete="email"
                            required
                        >
                        <button type="submit" class="btn bb-footer-newsletter-btn">
                            <span class="d-none d-sm-inline">Subscribe</span>
                            <i class="bi bi-send-fill d-sm-none" aria-hidden="true"></i>
                            <span class="visually-hidden d-sm-none">Subscribe</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="bb-footer-bottom">
        <div class="container">
            <div class="bb-footer-bottom-inner">
                <p class="mb-0">&copy; {{ date('Y') }} Behna Bazar. All rights reserved.</p>
                <p class="mb-0">
                    Developed by
                    <a href="https://www.nectradigital.com" target="_blank" rel="noopener noreferrer" class="bb-footer-credit">Nectra Digital</a>
                </p>
            </div>
        </div>
    </div>
</footer>
