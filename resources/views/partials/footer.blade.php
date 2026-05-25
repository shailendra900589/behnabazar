@php
    $brandName = $siteBranding['name'] ?? config('app.name', 'Behna Bazar');
    $brandTagline = $siteBranding['tagline'] ?? '';
    $homeLabel = $siteBranding['nav_home_label'] ?? 'Home';
@endphp
<footer class="bb-footer" role="contentinfo">
    <div class="bb-footer-accent" aria-hidden="true"></div>
    <div class="container bb-footer-body">
        <div class="bb-footer-grid">
            <div class="bb-footer-col bb-footer-col--brand">
                <a class="bb-footer-brand" href="{{ route('home') }}" aria-label="{{ $brandName }} home">
                    <img src="{{ asset('images/brand/bb-mark.jpeg') }}" alt="" class="bb-footer-mark" width="44" height="44">
                    <span class="bb-footer-name">{{ $brandName }}</span>
                </a>
                <p class="bb-footer-tagline">{{ $brandTagline }}</p>
                <div class="bb-footer-social" aria-label="Social media">
                    <a href="https://x.com/behnabazar" target="_blank" rel="noopener noreferrer" class="bb-footer-social-link" aria-label="X"><i class="bi bi-twitter-x"></i></a>
                    <a href="https://www.instagram.com/behnabazar/" target="_blank" rel="noopener noreferrer" class="bb-footer-social-link" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.facebook.com/behnaBazar/" target="_blank" rel="noopener noreferrer" class="bb-footer-social-link" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="https://www.youtube.com/@behnaBazar" target="_blank" rel="noopener noreferrer" class="bb-footer-social-link" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <nav class="bb-footer-col" aria-label="Shop">
                <h6 class="bb-footer-heading">Shop</h6>
                <ul class="bb-footer-links">
                    <li><a href="{{ route('home') }}">{{ $homeLabel }}</a></li>
                    <li><a href="{{ route('home') }}#products">All products</a></li>
                    <li><a href="{{ route('shops') }}">All sellers</a></li>
                    <li><a href="{{ route('cart') }}">Cart</a></li>
                    <li><a href="{{ route('wishlist') }}">Wishlist</a></li>
                    <li><a href="{{ route('local-delivery') }}">Local delivery</a></li>
                </ul>
            </nav>

            <nav class="bb-footer-col" aria-label="Account">
                <h6 class="bb-footer-heading">Account</h6>
                <ul class="bb-footer-links">
                    @auth
                        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ route('orders') }}">Orders</a></li>
                        <li><a href="{{ route('profile') }}">Profile</a></li>
                    @else
                        <li><a href="{{ route('login') }}">Sign in</a></li>
                        <li><a href="{{ route('register') }}">Create account</a></li>
                    @endauth
                    <li><a href="{{ route('returns-policy') }}">Returns policy</a></li>
                </ul>
            </nav>

            <nav class="bb-footer-col" aria-label="Support">
                <h6 class="bb-footer-heading">Support</h6>
                <ul class="bb-footer-links">
                    <li><a href="{{ route('contact') }}">Contact us</a></li>
                    <li><a href="mailto:support@behnabazar.in">support@behnabazar.in</a></li>
                </ul>
            </nav>

            <nav class="bb-footer-col" aria-label="Partners">
                <h6 class="bb-footer-heading">Partners</h6>
                <ul class="bb-footer-links">
                    <li><a href="{{ route('vendor.register.create') }}" class="bb-footer-link-highlight">Become a seller</a></li>
                    <li><a href="{{ route('login') }}">Seller sign in</a></li>
                    @if($referralEnabled ?? false)
                        <li>
                            <a href="{{ auth()->check() ? route('dashboard').'#referralProgramCard' : route('register') }}">Referral program</a>
                        </li>
                    @endif
                </ul>
                @auth
                    @if(($referralCode ?? '') !== '' && ($referralEnabled ?? false))
                        <div class="bb-footer-ref">
                            <span class="bb-footer-ref-label">Your referral code</span>
                            <span class="bb-footer-ref-value">
                                <code id="footerRefCode">{{ $referralCode }}</code>
                                <button type="button" class="bb-footer-ref-copy" data-copy-target="footerRefCode" title="Copy code" aria-label="Copy referral code">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </span>
                        </div>
                    @endif
                @endauth
            </nav>
        </div>

        <div class="bb-footer-newsletter">
            <form data-ajax-form action="{{ route('newsletter.subscribe') }}" method="post" class="bb-footer-newsletter-inner">
                @csrf
                <div class="bb-footer-newsletter-copy">
                    <h6 class="bb-footer-heading bb-footer-heading--inline">Newsletter</h6>
                    <p class="bb-footer-newsletter-text">Get special offers, new arrivals, and exclusive deals delivered to your inbox.</p>
                </div>
                <div class="bb-footer-newsletter-action">
                    <label for="footerNewsletterEmail" class="visually-hidden">Email address</label>
                    <input
                        type="email"
                        id="footerNewsletterEmail"
                        name="email"
                        class="bb-footer-newsletter-input"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required
                    >
                    <button type="submit" class="bb-footer-newsletter-btn">Subscribe</button>
                </div>
            </form>
        </div>

        <div class="bb-footer-features" aria-label="Marketplace highlights">
            <div class="bb-footer-feature">
                <span class="bb-footer-feature-icon"><i class="bi bi-patch-check"></i></span>
                <span class="bb-footer-feature-text">QC verified listings</span>
            </div>
            <div class="bb-footer-feature">
                <span class="bb-footer-feature-icon"><i class="bi bi-coin"></i></span>
                <span class="bb-footer-feature-text">Coin rewards</span>
            </div>
            <div class="bb-footer-feature">
                <span class="bb-footer-feature-icon"><i class="bi bi-truck"></i></span>
                <span class="bb-footer-feature-text">Local delivery</span>
            </div>
            @if($referralEnabled ?? false)
                <div class="bb-footer-feature">
                    <span class="bb-footer-feature-icon"><i class="bi bi-gift"></i></span>
                    <span class="bb-footer-feature-text">Refer &amp; earn</span>
                </div>
            @endif
        </div>
    </div>

    <div class="bb-footer-bottom">
        <div class="container bb-footer-bottom-inner">
            <div class="bb-visitor-counter">
                <i class="bi bi-eye-fill"></i>
                <span>Total Visitors:</span>
                <strong id="liveVisitorCount">{{ number_format($visitorCount ?? 0) }}</strong>
            </div>
            <p class="mb-0">&copy; {{ date('Y') }} {{ $brandName }}. All rights reserved.</p>
            <nav class="bb-footer-bottom-meta" aria-label="Legal and credits">
                <a href="{{ route('returns-policy') }}">Returns</a>
                <a href="{{ route('local-delivery') }}">Delivery</a>
                <a href="{{ route('contact') }}">Contact</a>
                <a href="https://www.nectradigital.com" target="_blank" rel="noopener noreferrer" class="bb-footer-credit">Nectra Digital</a>
            </nav>
        </div>
    </div>
</footer>
