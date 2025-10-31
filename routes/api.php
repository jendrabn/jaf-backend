<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\LandingController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

// Landi Page
Route::get('/landing', LandingController::class);

// Public Contact
Route::post('/contact', [ContactMessageController::class, 'store'])
    ->name('api.contact.store');

// Product
Route::controller(ProductController::class)->group(function () {
    Route::get('/categories', 'categories');
    Route::get('/brands', 'brands');
    Route::get('/products', 'list');
    Route::get('/products/suggestions', 'suggestions');
    Route::get('/products/{product:slug}', 'get');
    Route::get('/products/{product:slug}/similars', 'similars');
});

// Region
Route::controller(RegionController::class)->group(function () {
    Route::get('/region/provinces', 'provinces');
    Route::get('/region/cities/{provinceId}', 'cities');
    Route::get('/region/districts/{cityId}', 'districts');
    Route::get('/region/sub-districts/{districtId}', 'subdistricts');
});

// Auth
Route::controller(AuthController::class)->group(function () {
    Route::post('/auth/register', 'register');
    Route::post('/auth/login', 'login');
    Route::post('/auth/google', 'loginWithGoogle');
    Route::post('/auth/verify_login_otp', 'verifyLoginOtp');
    Route::post('/auth/resend_login_otp', 'resendLoginOtp');
    Route::delete('/auth/logout', 'logout')->middleware('auth:sanctum');
    Route::post('/auth/forgot_password', 'sendPasswordResetLink');
    Route::put('/auth/reset_password', 'resetPassword');
});

// Shipping Cost
Route::post('/shipping_costs', [CheckoutController::class, 'shippingCosts']);

// Payment Gateway (Midtrans) Webhook Notification
Route::post('/payments/midtrans/notification', [PaymentGatewayController::class, 'midtransNotification']);

Route::middleware(['auth:sanctum'])->group(function () {

    // User Account
    Route::controller(UserController::class)->group(function () {
        Route::get('/user', 'get');
        Route::get('/me', 'get');
        Route::put('/user', 'update');
        Route::put('/user/change_password', 'updatePassword');

        // Notifications
        Route::get('/notifications', [UserController::class, 'notifications']);
        Route::put('/notifications/{notification}/read', [UserController::class, 'markNotificationAsRead']);
        Route::put('/notifications/read-all', [UserController::class, 'markAllNotificationsAsRead']);
        Route::get('/notifications/unread-count', [UserController::class, 'getUnreadCount']);
    });

    // Checkout
    Route::controller(CheckoutController::class)->group(function () {
        Route::post('/checkout', 'checkout');
        Route::post('/apply_coupon', 'applyCoupon');
        Route::post('/remove_coupon', 'removeCoupon');
    });

    // Order
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders', 'list');
        Route::post('/orders', 'create');
        Route::get('/orders/{order}', 'get');
        Route::post('/orders/{order}/confirm_payment', 'confirmPayment');
        Route::put('/orders/{order}/confirm_order_delivered', 'confirmDelivered');
        // Add Rating
        Route::post('/orders/ratings', 'addRating');
        // track waybill
        Route::get('/orders/{order}/waybill', 'trackWaybill');
    });

    // Wishlist
    Route::controller(WishlistController::class)->group(function () {
        Route::get('/wishlist', 'list');
        Route::post('/wishlist', 'create');
        Route::delete('/wishlist', 'delete');
    });

    // Cart
    Route::controller(CartController::class)->group(function () {
        Route::get('/carts', 'list');
        Route::post('/carts', 'create');
        Route::put('/carts/{cart}', 'update');
        Route::delete('/carts', 'delete');
    });
});

// Blog
Route::controller(BlogController::class)->group(function () {
    Route::get('/blogs', 'list');
    Route::get('/blogs/categories', 'categories');
    Route::get('/blogs/tags', 'tags');
    Route::get('/blogs/{blog:slug}', 'get');
});

// Newsletter
Route::controller(NewsletterController::class)->group(function () {
    Route::post('/newsletter/subscribe', 'subscribe');
    Route::get('/newsletter/unsubscribe/{token}', 'unsubscribe')->name('unsubscribe');
    Route::get('/newsletter/confirm/{token}', 'confirm');

    // Newsletter tracking
    Route::get('/newsletter/track/open/{receipt}/{token}', 'trackOpen')
        ->name('newsletter.track.open');

    Route::get('/newsletter/track/click/{receipt}/{token}', 'trackClick')
        ->name('newsletter.track.click');

    Route::get('/newsletter/webview/{receipt}/{token}', 'webview')
        ->name('newsletter.webview');
});

// Route::fallback(fn () => abort(404));
