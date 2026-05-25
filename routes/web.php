<?php

use App\Http\Controllers\AccountAddressController;
use App\Http\Controllers\AccountVerificationController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\VendorRegistrationController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ResellController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\OrderInvoiceController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\WhatsAppOutboxController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/api/search', [StorefrontController::class, 'liveSearch'])->name('api.search');
Route::get('/api/delivery-check', [DeliveryController::class, 'checkPincode'])->name('api.delivery-check');
Route::get('/api/visitor-count', function () {
    $count = \Illuminate\Support\Facades\DB::table('site_visits')->where('id', 1)->value('total_count') ?? 0;
    return response()->json(['count' => (int) $count]);
})->name('api.visitor-count');
Route::post('/newsletter/subscribe', [StorefrontController::class, 'subscribeNewsletter'])
    ->middleware('throttle:10,1')
    ->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe', [StorefrontController::class, 'unsubscribeNewsletter'])->name('newsletter.unsubscribe');
Route::get('/ads/{ad}/click', [StorefrontController::class, 'adClick'])->name('ads.click');
Route::get('/product/{product:slug}', [StorefrontController::class, 'product'])->name('product.show');
Route::get('/product/{product:slug}/share', [ReferralController::class, 'sharePayload'])->name('product.share.payload');
Route::middleware('auth')->post('/product/{product:slug}/share', [ReferralController::class, 'recordShare'])->name('product.share.record');
Route::post('/product/{product:slug}/review', [StorefrontController::class, 'postReview'])->name('product.review')->middleware('auth');
Route::post('/product/{product:slug}/question', [StorefrontController::class, 'postQuestion'])->name('product.question')->middleware('auth');
Route::post('/product/{product:slug}/stock-alert', [StockAlertController::class, 'store'])->name('product.stock-alert');
Route::view('/contact', 'store.contact')->name('contact');
Route::view('/local-delivery', 'store.local-delivery')->name('local-delivery');
Route::view('/returns-policy', 'store.returns-policy')->name('returns-policy');
Route::get('/shops', [StorefrontController::class, 'shops'])->name('shops');
Route::get('/shop/{vendor}', [StorefrontController::class, 'vendorShop'])->name('vendor.shop');
Route::get('/cart', [CartController::class, 'index'])->name('cart');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/sell/payment', [VendorRegistrationController::class, 'paymentShow'])->name('vendor.payment.show');
Route::post('/sell/payment/order', [VendorRegistrationController::class, 'paymentOrder'])->name('vendor.payment.order');
Route::post('/sell/payment', [VendorRegistrationController::class, 'paymentComplete'])->name('vendor.payment.complete');

Route::get('/account/verify', [AccountVerificationController::class, 'show'])->name('account.verify.show');
Route::post('/account/verify', [AccountVerificationController::class, 'verify'])->name('account.verify.submit');
Route::post('/account/verify/resend', [AccountVerificationController::class, 'resend'])->name('account.verify.resend');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:8,1');

    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
    Route::get('/reset-password/verify', [PasswordResetController::class, 'showVerify'])->name('password.verify.show');
    Route::post('/reset-password/verify', [PasswordResetController::class, 'resetWithOtp'])->name('password.verify.submit');
    Route::post('/reset-password/resend', [PasswordResetController::class, 'resendOtp'])->name('password.verify.resend');

    Route::prefix('sell')->name('vendor.')->group(function () {
        Route::get('/register', [VendorRegistrationController::class, 'create'])->name('register.create');
        Route::post('/register', [VendorRegistrationController::class, 'store'])->name('register.store');
        Route::get('/verify', [VendorRegistrationController::class, 'verifyShow'])->name('verify.show');
        Route::post('/verify', [VendorRegistrationController::class, 'verify'])->name('verify.submit');
        Route::post('/verify/resend', [VendorRegistrationController::class, 'resendOtp'])->name('verify.resend');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'account.ready'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [StorefrontController::class, 'profile'])->name('profile');
    Route::patch('/profile', [StorefrontController::class, 'updateProfile'])->name('profile.update');
    Route::get('/addresses', [AccountAddressController::class, 'index'])->name('addresses');
    Route::post('/addresses', [AccountAddressController::class, 'store'])->name('addresses.store');
    Route::delete('/addresses/{address}', [AccountAddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/default', [AccountAddressController::class, 'makeDefault'])->name('addresses.default');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout/payment-order', [CheckoutController::class, 'createPaymentOrder'])->name('checkout.payment-order');
    Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/orders', [StorefrontController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}/track', [StorefrontController::class, 'track'])->name('orders.track');
    Route::get('/orders/{order}/invoice', [OrderInvoiceController::class, 'download'])->name('orders.invoice');
    Route::post('/orders/{order}/cancel', [StorefrontController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/orders/{order}/return', [StorefrontController::class, 'returnOrder'])->name('orders.return');

    Route::prefix('manage')->name('manage.')->group(function () {
        Route::get('/orders/export', [DashboardController::class, 'exportOrders'])->name('orders.export');
        Route::post('/categories', [DashboardController::class, 'saveCategory'])->name('categories.save');
        Route::delete('/categories/{category}', [DashboardController::class, 'deleteCategory'])->name('categories.delete');
        Route::post('/promotions/email', [DashboardController::class, 'sendPromotionEmail'])
            ->middleware('admin')
            ->name('promotions.email');
        Route::post('/coupons', [DashboardController::class, 'saveCoupon'])->name('coupons.save');
        Route::patch('/coupons/{coupon}/toggle', [DashboardController::class, 'toggleCoupon'])->name('coupons.toggle');
        Route::delete('/coupons/{coupon}', [DashboardController::class, 'deleteCoupon'])->name('coupons.delete');
        Route::post('/settings', [DashboardController::class, 'saveSettings'])->name('settings.save');
        Route::post('/referral-settings', [DashboardController::class, 'saveReferralSettings'])->name('referral-settings.save');
        Route::post('/program-settings', [DashboardController::class, 'saveProgramSettings'])->name('program-settings.save');
        Route::post('/whatsapp-outbox/{outbox}/sent', [WhatsAppOutboxController::class, 'markSent'])
            ->middleware('admin')
            ->name('whatsapp-outbox.sent');
        Route::post('/whatsapp-outbox/{outbox}/skip', [WhatsAppOutboxController::class, 'skip'])
            ->middleware('admin')
            ->name('whatsapp-outbox.skip');
        Route::post('/whatsapp-outbox/skip-all', [WhatsAppOutboxController::class, 'skipAllPending'])
            ->middleware('admin')
            ->name('whatsapp-outbox.skip-all');
        Route::post('/referral-rewards/{reward}/approve', [DashboardController::class, 'approveReferralReward'])->name('referral-rewards.approve');
        Route::post('/referral-rewards/{reward}/reject', [DashboardController::class, 'rejectReferralReward'])->name('referral-rewards.reject');
        Route::post('/site-display', [DashboardController::class, 'saveSiteDisplay'])
            ->middleware('admin')
            ->name('site-display.save');
        Route::post('/vendors/{user}/approve', [DashboardController::class, 'approveVendor'])->name('vendors.approve');
        Route::post('/vendors/{user}/reject', [DashboardController::class, 'rejectVendor'])->name('vendors.reject');
        Route::delete('/vendors/{user}', [DashboardController::class, 'deleteVendor'])->name('vendors.delete');
        Route::get('/vendors/{user}/impersonate', [DashboardController::class, 'impersonateVendor'])->name('vendors.impersonate');
        Route::get('/leave-impersonation', [DashboardController::class, 'leaveImpersonation'])->name('vendors.leave_impersonation');
        Route::post('/qc-users', [DashboardController::class, 'createQcUser'])->name('qc-users.create');
        Route::post('/products', [DashboardController::class, 'saveProduct'])->name('products.save');
        Route::patch('/products/{product}', [DashboardController::class, 'updateProduct'])->name('products.update');
        Route::post('/reviews/{review}/approve', [DashboardController::class, 'approveReview'])->middleware('admin')->name('reviews.approve');
        Route::delete('/reviews/{review}', [DashboardController::class, 'deleteReview'])->middleware('admin')->name('reviews.delete');
        Route::get('/resell-catalog', [ResellController::class, 'catalog'])->name('resell.catalog');
        Route::post('/resell', [ResellController::class, 'store'])->name('resell.store');
        Route::post('/resell/bulk', [ResellController::class, 'bulkPurchase'])->name('resell.bulk');
        Route::post('/notifications/{notification}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
        Route::delete('/products/{product}', [DashboardController::class, 'deleteProduct'])->name('products.delete');
        Route::post('/banners', [DashboardController::class, 'saveBanner'])->name('banners.save');
        Route::delete('/banners/{banner}', [DashboardController::class, 'deleteBanner'])->name('banners.delete');
        Route::post('/ads', [DashboardController::class, 'saveAd'])->name('ads.save');
        Route::delete('/ads/{ad}', [DashboardController::class, 'deleteAd'])->name('ads.delete');
        Route::post('/promotions', [DashboardController::class, 'savePromotion'])->name('promotions.save');
        Route::delete('/promotions/{ad}', [DashboardController::class, 'deletePromotion'])->name('promotions.delete');
        Route::post('/ad-wallet/order', [DashboardController::class, 'createAdWalletOrder'])->name('ad-wallet.order');
        Route::post('/ad-wallet/verify', [DashboardController::class, 'verifyAdWalletPayment'])->name('ad-wallet.verify');
        Route::patch('/orders/{order}', [DashboardController::class, 'updateOrder'])->name('orders.update');
        Route::patch('/orders/{order}/return', [DashboardController::class, 'updateReturn'])->name('orders.return.update');
        Route::patch('/products/{product}/review', [DashboardController::class, 'reviewProduct'])->name('products.review');
        Route::patch('/questions/{question}', [DashboardController::class, 'updateQuestion'])->name('questions.update');
        Route::post('/payouts', [DashboardController::class, 'requestPayout'])->name('payouts.request');
        Route::patch('/payouts/{payout}', [DashboardController::class, 'updatePayout'])->name('payouts.update');
    });
});
