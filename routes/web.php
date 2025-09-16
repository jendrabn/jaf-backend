<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EwalletController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductBrandController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/home', function () {
    if (session('status')) {
        return redirect()->route('admin.home')->with('status', session('status'));
    }

    return redirect()->route('admin.home');
});

// Auth
Route::middleware('guest')->prefix('auth')->name('auth.')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::get('forgot_password', [AuthController::class, 'forgotPassword'])->name('forgot_password');
    Route::post('forgot_password', [AuthController::class, 'sendResetPasswordLink'])->name('forgot_password.post');
    Route::get('reset_password', [AuthController::class, 'resetPassword'])->name('reset_password');
    Route::put('reset_password', [AuthController::class, 'resetPassword'])->name('reset_password.put');
});
Route::middleware(['auth', 'role:admin|user'])->get('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    // Users
    Route::delete('users/destroy', [UserController::class, 'massDestroy'])->name('users.massDestroy');
    Route::resource('users', UserController::class);

    // Order
    Route::delete('orders/destroy', [OrderController::class, 'massDestroy'])->name('orders.massDestroy');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::put('orders/{order}/confirm_shipping', [OrderController::class, 'confirmShipping'])->name('orders.confirm-shipping');
    Route::put('orders/{order}/confirm_payment', [OrderController::class, 'confirmPayment'])->name('orders.confirm-payment');
    Route::post('orders/invoices', [OrderController::class, 'generateInvoicePdf'])->name('orders.invoices');

    // Product Category
    Route::delete('product-categories/destroy', [ProductCategoryController::class, 'massDestroy'])->name('product-categories.massDestroy');
    Route::post('product-categories/media', [ProductCategoryController::class, 'storeMedia'])->name('product-categories.storeMedia');
    Route::resource('product-categories', ProductCategoryController::class, ['except' => ['show']]);

    // Product Brand
    Route::delete('product-brands/destroy', [ProductBrandController::class, 'massDestroy'])->name('product-brands.massDestroy');
    Route::post('product-brands/media', [ProductBrandController::class, 'storeMedia'])->name('product-brands.storeMedia');
    Route::resource('product-brands', ProductBrandController::class, ['except' => ['show']]);

    // Bank
    Route::delete('banks/destroy', [BankController::class, 'massDestroy'])->name('banks.massDestroy');
    Route::post('banks/media', [BankController::class, 'storeMedia'])->name('banks.storeMedia');
    Route::post('banks/ckmedia', [BankController::class, 'storeCKEditorImages'])->name('banks.storeCKEditorImages');
    Route::resource('banks', BankController::class)->except(['show']);

    // E-Wallet
    Route::delete('ewallets/destroy', [EwalletController::class, 'massDestroy'])->name('ewallets.massDestroy');
    Route::post('ewallets/media', [EwalletController::class, 'storeMedia'])->name('ewallets.storeMedia');
    Route::post('ewallets/ckmedia', [EwalletController::class, 'storeCKEditorImages'])->name('ewallets.storeCKEditorImages');
    Route::resource('ewallets', EwalletController::class)->except(['show']);

    // Banner
    Route::delete('banners/destroy', [BannerController::class, 'massDestroy'])->name('banners.massDestroy');
    Route::post('banners/media', [BannerController::class, 'storeMedia'])->name('banners.storeMedia');
    Route::post('banners/ckmedia', [BannerController::class, 'storeCKEditorImages'])->name('banners.storeCKEditorImages');
    Route::resource('banners', BannerController::class, ['except' => ['show']]);

    // Product
    Route::delete('products/destroy', [ProductController::class, 'massDestroy'])->name('products.massDestroy');
    Route::post('products/media', [ProductController::class, 'storeMedia'])->name('products.storeMedia');
    Route::post('products/ckmedia', [ProductController::class, 'storeCKEditorImages'])->name('products.storeCKEditorImages');
    Route::resource('products', ProductController::class);

    // Blog Tag
    Route::delete('blog-tags/destroy', [BlogTagController::class, 'massDestroy'])->name('blog-tags.massDestroy');
    Route::resource('blog-tags', BlogTagController::class, ['except' => ['show', 'edit']]);

    // Blog Category
    Route::delete('blog-categories/destroy', [BlogCategoryController::class, 'massDestroy'])->name('blog-categories.massDestroy');
    Route::resource('blog-categories', BlogCategoryController::class, ['except' => ['show', 'edit']]);

    // Blog
    Route::delete('blogs/destroy', [BlogController::class, 'massDestroy'])->name('blogs.massDestroy');
    Route::put('blogs/published/{blog}', [BlogController::class, 'published'])->name('blogs.published');
    Route::post('blogs/media', [BlogController::class, 'storeMedia'])->name('blogs.storeMedia');
    Route::post('blogs/ckmedia', [ProductController::class, 'storeCKEditorImages'])->name('blogs.storeCKEditorImages');
    Route::resource('blogs', BlogController::class);

    // Courier
    Route::resource('couriers', \App\Http\Controllers\Admin\CourierController::class)->only(['index', 'update']);

    // Coupon
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);
    Route::post('coupons/destroy', [\App\Http\Controllers\Admin\CouponController::class, 'massDestroy'])->name('coupons.massDestroy');
});

Route::get('swagger', function () {
    return view('docs');
});
