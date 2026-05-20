@php($shareProductSlug = $product->slug)
<div class="dropdown bb-share-dropdown"
     data-product-slug="{{ $shareProductSlug }}"
     data-share-payload-url="{{ route('product.share.payload', $product) }}"
     data-share-record-url="{{ auth()->check() ? route('product.share.record', $product) : '' }}">
    <button class="btn btn-soft btn-lg dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Share product" id="bbShareToggle">
        <i class="bi bi-share"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 p-2 bb-share-menu" style="min-width: 280px;">
        <li><h6 class="dropdown-header px-2">Share product</h6></li>
        <li>
            <button type="button" class="dropdown-item rounded-3 d-flex align-items-center gap-2 bb-share-native" data-product-slug="{{ $shareProductSlug }}">
                <i class="bi bi-box-arrow-up"></i> Share via apps…
            </button>
        </li>
        <li><hr class="dropdown-divider my-2"></li>
        <li><a class="dropdown-item rounded-3 d-flex align-items-center gap-2 bb-share-link" data-channel="whatsapp" href="#" target="_blank" rel="noopener"><i class="bi bi-whatsapp text-success"></i> WhatsApp</a></li>
        <li><a class="dropdown-item rounded-3 d-flex align-items-center gap-2 bb-share-link" data-channel="facebook" href="#" target="_blank" rel="noopener"><i class="bi bi-facebook text-primary"></i> Facebook</a></li>
        <li><a class="dropdown-item rounded-3 d-flex align-items-center gap-2 bb-share-link" data-channel="twitter" href="#" target="_blank" rel="noopener"><i class="bi bi-twitter-x"></i> X (Twitter)</a></li>
        <li><a class="dropdown-item rounded-3 d-flex align-items-center gap-2 bb-share-link" data-channel="telegram" href="#" target="_blank" rel="noopener"><i class="bi bi-telegram text-info"></i> Telegram</a></li>
        <li><a class="dropdown-item rounded-3 d-flex align-items-center gap-2 bb-share-link" data-channel="linkedin" href="#" target="_blank" rel="noopener"><i class="bi bi-linkedin text-primary"></i> LinkedIn</a></li>
        <li><a class="dropdown-item rounded-3 d-flex align-items-center gap-2 bb-share-link" data-channel="email" href="#"><i class="bi bi-envelope"></i> Email</a></li>
        <li><a class="dropdown-item rounded-3 d-flex align-items-center gap-2 bb-share-link" data-channel="sms" href="#"><i class="bi bi-chat-dots"></i> SMS</a></li>
        <li><hr class="dropdown-divider my-2"></li>
        <li>
            <button type="button" class="dropdown-item rounded-3 d-flex align-items-center gap-2 bb-share-copy" data-product-slug="{{ $shareProductSlug }}">
                <i class="bi bi-link-45deg"></i> Copy link
            </button>
        </li>
    </ul>
</div>
