<?php

use App\Http\Controllers\AccountVerificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\VendorRegistrationController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/api/search', [StorefrontController::class, 'liveSearch'])->name('api.search');
Route::post('/newsletter/subscribe', [StorefrontController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe', [StorefrontController::class, 'unsubscribeNewsletter'])->name('newsletter.unsubscribe');
Route::get('/ads/{ad}/click', [StorefrontController::class, 'adClick'])->name('ads.click');
Route::get('/product/{product:slug}', [StorefrontController::class, 'product'])->name('product.show');
Route::post('/product/{product:slug}/review', [StorefrontController::class, 'postReview'])->name('product.review')->middleware('auth');
Route::post('/product/{product:slug}/question', [StorefrontController::class, 'postQuestion'])->name('product.question')->middleware('auth');
Route::view('/contact', 'store.contact')->name('contact');
Route::view('/local-delivery', 'store.local-delivery')->name('local-delivery');
Route::view('/returns-policy', 'store.returns-policy')->name('returns-policy');
Route::redirect('/shops', '/');
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
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

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
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout/payment-order', [CheckoutController::class, 'createPaymentOrder'])->name('checkout.payment-order');
    Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/orders', [StorefrontController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}/track', [StorefrontController::class, 'track'])->name('orders.track');
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
