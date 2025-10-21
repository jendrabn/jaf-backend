<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\ContactReplyController as AdminContactReplyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EwalletController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductBrandController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\ContactMessageController as PublicContactController;
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

Route::post('/contact', [PublicContactController::class, 'storeWeb'])
    ->name('contact.store');

// ======================= AUTH =======================
Route::middleware('guest')->prefix('auth')->name('auth.')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');

    Route::get('forgot_password', [AuthController::class, 'forgotPassword'])->name('forgot_password');
    Route::post('forgot_password', [AuthController::class, 'sendResetPasswordLink'])->name('forgot_password.post');

    Route::get('reset_password', [AuthController::class, 'resetPassword'])->name('reset_password');
    Route::put('reset_password', [AuthController::class, 'resetPassword'])->name('reset_password.put');

    Route::get('google/redirect', [AuthController::class, 'googleRedirect'])->name('google.redirect');
    Route::get('google/callback', [AuthController::class, 'googleCallback'])->name('google.callback');
});

Route::middleware(['auth', 'permission:backoffice.access'])
    ->get('auth/logout', [AuthController::class, 'logout'])
    ->name('auth.logout');

// ======================= ADMIN (BACKOFFICE) =======================
Route::middleware(['auth', 'permission:backoffice.access'])
    ->name('admin.')
    ->group(function () {

        // -------- Dashboard (ubah ke /dashboard biar tidak bentrok dengan /) --------
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('home')
            ->middleware('permission:dashboard.view');

        // -------- Profile --------
        Route::get('profile', [ProfileController::class, 'index'])
            ->name('profile.index')
            ->middleware('permission:profile.view');

        Route::put('profile', [ProfileController::class, 'updateProfile'])
            ->name('profile.update')
            ->middleware('permission:profile.update');

        Route::put('profile/password', [ProfileController::class, 'updatePassword'])
            ->name('profile.update-password')
            ->middleware('permission:profile.update_password');

        // -------- Users (resource + massDestroy) --------
        Route::delete('users/destroy', [UserController::class, 'massDestroy'])
            ->name('users.massDestroy')
            ->middleware('permission:users.mass_delete');

        Route::get('users', [UserController::class, 'index'])
            ->name('users.index')
            ->middleware('permission:users.view');

        Route::get('users/create', [UserController::class, 'create'])
            ->name('users.create')
            ->middleware('permission:users.create');

        Route::post('users', [UserController::class, 'store'])
            ->name('users.store')
            ->middleware('permission:users.create');

        Route::get('users/{user}/edit', [UserController::class, 'edit'])
            ->whereNumber('user')
            ->name('users.edit')
            ->middleware('permission:users.edit');

        Route::put('users/{user}', [UserController::class, 'update'])
            ->whereNumber('user')
            ->name('users.update')
            ->middleware('permission:users.edit');

        Route::get('users/{user}', [UserController::class, 'show'])
            ->whereNumber('user')
            ->name('users.show')
            ->middleware('permission:users.show');

        Route::delete('users/{user}', [UserController::class, 'destroy'])
            ->whereNumber('user')
            ->name('users.destroy')
            ->middleware('permission:users.delete');

        // -------- Orders (custom + massDestroy + generate invoice) --------
        Route::delete('orders/destroy', [OrderController::class, 'massDestroy'])
            ->name('orders.massDestroy')
            ->middleware('permission:orders.mass_delete');

        Route::get('orders', [OrderController::class, 'index'])
            ->name('orders.index')
            ->middleware('permission:orders.view');

        Route::get('orders/{order}', [OrderController::class, 'show'])
            ->whereNumber('order')
            ->name('orders.show')
            ->middleware('permission:orders.show');

        Route::delete('orders/{order}', [OrderController::class, 'destroy'])
            ->whereNumber('order')
            ->name('orders.destroy')
            ->middleware('permission:orders.delete');

        Route::put('orders/{order}/confirm_shipping', [OrderController::class, 'confirmShipping'])
            ->whereNumber('order')
            ->name('orders.confirm-shipping')
            ->middleware('permission:orders.confirm_shipping');

        Route::put('orders/{order}/confirm_payment', [OrderController::class, 'confirmPayment'])
            ->whereNumber('order')
            ->name('orders.confirm-payment')
            ->middleware('permission:orders.confirm_payment');

        Route::post('orders/invoices', [OrderController::class, 'generateInvoicePdf'])
            ->name('orders.invoices')
            ->middleware('permission:orders.generate_invoice');

        Route::get('orders/{order}/waybill', [OrderController::class, 'trackWaybill'])
            ->whereNumber('order')
            ->name('orders.track-waybill');
        // ->middleware('permission:orders.track_waybill');

        // -------- Product Categories (except show) + storeMedia + massDestroy --------
        Route::delete('product-categories/destroy', [ProductCategoryController::class, 'massDestroy'])
            ->name('product-categories.massDestroy')
            ->middleware('permission:product_categories.mass_delete');

        Route::post('product-categories/media', [ProductCategoryController::class, 'storeMedia'])
            ->name('product-categories.storeMedia')
            ->middleware('permission:product_categories.upload_media');

        Route::get('product-categories', [ProductCategoryController::class, 'index'])
            ->name('product-categories.index')
            ->middleware('permission:product_categories.view');

        Route::get('product-categories/create', [ProductCategoryController::class, 'create'])
            ->name('product-categories.create')
            ->middleware('permission:product_categories.create');

        Route::post('product-categories', [ProductCategoryController::class, 'store'])
            ->name('product-categories.store')
            ->middleware('permission:product_categories.create');

        Route::get('product-categories/{product_category}/edit', [ProductCategoryController::class, 'edit'])
            ->whereNumber('product_category')
            ->name('product-categories.edit')
            ->middleware('permission:product_categories.edit');

        Route::put('product-categories/{product_category}', [ProductCategoryController::class, 'update'])
            ->whereNumber('product_category')
            ->name('product-categories.update')
            ->middleware('permission:product_categories.edit');

        Route::delete('product-categories/{product_category}', [ProductCategoryController::class, 'destroy'])
            ->whereNumber('product_category')
            ->name('product-categories.destroy')
            ->middleware('permission:product_categories.delete');

        // -------- Product Brands (except show) + storeMedia + massDestroy --------
        Route::delete('product-brands/destroy', [ProductBrandController::class, 'massDestroy'])
            ->name('product-brands.massDestroy')
            ->middleware('permission:product_brands.mass_delete');

        Route::post('product-brands/media', [ProductBrandController::class, 'storeMedia'])
            ->name('product-brands.storeMedia')
            ->middleware('permission:product_brands.upload_media');

        Route::get('product-brands', [ProductBrandController::class, 'index'])
            ->name('product-brands.index')
            ->middleware('permission:product_brands.view');

        Route::get('product-brands/create', [ProductBrandController::class, 'create'])
            ->name('product-brands.create')
            ->middleware('permission:product_brands.create');

        Route::post('product-brands', [ProductBrandController::class, 'store'])
            ->name('product-brands.store')
            ->middleware('permission:product_brands.create');

        Route::get('product-brands/{product_brand}/edit', [ProductBrandController::class, 'edit'])
            ->whereNumber('product_brand')
            ->name('product-brands.edit')
            ->middleware('permission:product_brands.edit');

        Route::put('product-brands/{product_brand}', [ProductBrandController::class, 'update'])
            ->whereNumber('product_brand')
            ->name('product-brands.update')
            ->middleware('permission:product_brands.edit');

        Route::delete('product-brands/{product_brand}', [ProductBrandController::class, 'destroy'])
            ->whereNumber('product_brand')
            ->name('product-brands.destroy')
            ->middleware('permission:product_brands.delete');

        // -------- Banks (except show) + storeMedia + ckmedia + massDestroy --------
        Route::delete('banks/destroy', [BankController::class, 'massDestroy'])
            ->name('banks.massDestroy')
            ->middleware('permission:banks.mass_delete');

        Route::post('banks/media', [BankController::class, 'storeMedia'])
            ->name('banks.storeMedia')
            ->middleware('permission:banks.upload_media');

        Route::post('banks/ckmedia', [BankController::class, 'storeCKEditorImages'])
            ->name('banks.storeCKEditorImages')
            ->middleware('permission:banks.upload_ckeditor');

        Route::get('banks', [BankController::class, 'index'])
            ->name('banks.index')
            ->middleware('permission:banks.view');

        Route::get('banks/create', [BankController::class, 'create'])
            ->name('banks.create')
            ->middleware('permission:banks.create');

        Route::post('banks', [BankController::class, 'store'])
            ->name('banks.store')
            ->middleware('permission:banks.create');

        Route::get('banks/{bank}/edit', [BankController::class, 'edit'])
            ->whereNumber('bank')
            ->name('banks.edit')
            ->middleware('permission:banks.edit');

        Route::put('banks/{bank}', [BankController::class, 'update'])
            ->whereNumber('bank')
            ->name('banks.update')
            ->middleware('permission:banks.edit');

        Route::delete('banks/{bank}', [BankController::class, 'destroy'])
            ->whereNumber('bank')
            ->name('banks.destroy')
            ->middleware('permission:banks.delete');

        // -------- Taxes (resource + massDestroy) --------
        Route::delete('taxes/destroy', [TaxController::class, 'massDestroy'])
            ->name('taxes.massDestroy');

        Route::resource('taxes', TaxController::class)->except(['show']);

        // -------- E-Wallets (except show) + storeMedia + ckmedia + massDestroy --------
        Route::delete('ewallets/destroy', [EwalletController::class, 'massDestroy'])
            ->name('ewallets.massDestroy')
            ->middleware('permission:ewallets.mass_delete');

        Route::post('ewallets/media', [EwalletController::class, 'storeMedia'])
            ->name('ewallets.storeMedia')
            ->middleware('permission:ewallets.upload_media');

        Route::post('ewallets/ckmedia', [EwalletController::class, 'storeCKEditorImages'])
            ->name('ewallets.storeCKEditorImages')
            ->middleware('permission:ewallets.upload_ckeditor');

        Route::get('ewallets', [EwalletController::class, 'index'])
            ->name('ewallets.index')
            ->middleware('permission:ewallets.view');

        Route::get('ewallets/create', [EwalletController::class, 'create'])
            ->name('ewallets.create')
            ->middleware('permission:ewallets.create');

        Route::post('ewallets', [EwalletController::class, 'store'])
            ->name('ewallets.store')
            ->middleware('permission:ewallets.create');

        Route::get('ewallets/{ewallet}/edit', [EwalletController::class, 'edit'])
            ->whereNumber('ewallet')
            ->name('ewallets.edit')
            ->middleware('permission:ewallets.edit');

        Route::put('ewallets/{ewallet}', [EwalletController::class, 'update'])
            ->whereNumber('ewallet')
            ->name('ewallets.update')
            ->middleware('permission:ewallets.edit');

        Route::delete('ewallets/{ewallet}', [EwalletController::class, 'destroy'])
            ->whereNumber('ewallet')
            ->name('ewallets.destroy')
            ->middleware('permission:ewallets.delete');

        // -------- Banners (except show) + storeMedia + ckmedia + massDestroy --------
        Route::delete('banners/destroy', [BannerController::class, 'massDestroy'])
            ->name('banners.massDestroy')
            ->middleware('permission:banners.mass_delete');

        Route::post('banners/media', [BannerController::class, 'storeMedia'])
            ->name('banners.storeMedia')
            ->middleware('permission:banners.upload_media');

        Route::post('banners/ckmedia', [BannerController::class, 'storeCKEditorImages'])
            ->name('banners.storeCKEditorImages')
            ->middleware('permission:banners.upload_ckeditor');

        Route::get('banners', [BannerController::class, 'index'])
            ->name('banners.index')
            ->middleware('permission:banners.view');

        Route::get('banners/create', [BannerController::class, 'create'])
            ->name('banners.create')
            ->middleware('permission:banners.create');

        Route::post('banners', [BannerController::class, 'store'])
            ->name('banners.store')
            ->middleware('permission:banners.create');

        Route::get('banners/{banner}/edit', [BannerController::class, 'edit'])
            ->whereNumber('banner')
            ->name('banners.edit')
            ->middleware('permission:banners.edit');

        Route::put('banners/{banner}', [BannerController::class, 'update'])
            ->whereNumber('banner')
            ->name('banners.update')
            ->middleware('permission:banners.edit');

        Route::delete('banners/{banner}', [BannerController::class, 'destroy'])
            ->whereNumber('banner')
            ->name('banners.destroy')
            ->middleware('permission:banners.delete');

        // -------- Products (resource + uploads + massDestroy) --------
        Route::delete('products/destroy', [ProductController::class, 'massDestroy'])
            ->name('products.massDestroy')
            ->middleware('permission:products.mass_delete');

        Route::post('products/media', [ProductController::class, 'storeMedia'])
            ->name('products.storeMedia')
            ->middleware('permission:products.upload_media');

        Route::post('products/ckmedia', [ProductController::class, 'storeCKEditorImages'])
            ->name('products.storeCKEditorImages')
            ->middleware('permission:products.upload_ckeditor');

        Route::get('products', [ProductController::class, 'index'])
            ->name('products.index')
            ->middleware('permission:products.view');

        Route::get('products/create', [ProductController::class, 'create'])
            ->name('products.create')
            ->middleware('permission:products.create');

        Route::post('products', [ProductController::class, 'store'])
            ->name('products.store')
            ->middleware('permission:products.create');

        Route::get('products/{product}/edit', [ProductController::class, 'edit'])
            ->whereNumber('product')
            ->name('products.edit')
            ->middleware('permission:products.edit');

        Route::put('products/{product}', [ProductController::class, 'update'])
            ->whereNumber('product')
            ->name('products.update')
            ->middleware('permission:products.edit');

        Route::get('products/{product}', [ProductController::class, 'show'])
            ->whereNumber('product')
            ->name('products.show')
            ->middleware('permission:products.show');

        Route::delete('products/{product}', [ProductController::class, 'destroy'])
            ->whereNumber('product')
            ->name('products.destroy')
            ->middleware('permission:products.delete');

        // -------- Blog Tags (except show, edit view) + massDestroy --------
        Route::delete('blog-tags/destroy', [BlogTagController::class, 'massDestroy'])
            ->name('blog-tags.massDestroy')
            ->middleware('permission:blog_tags.mass_delete');

        Route::get('blog-tags', [BlogTagController::class, 'index'])
            ->name('blog-tags.index')
            ->middleware('permission:blog_tags.view');

        Route::get('blog-tags/create', [BlogTagController::class, 'create'])
            ->name('blog-tags.create')
            ->middleware('permission:blog_tags.create');

        Route::post('blog-tags', [BlogTagController::class, 'store'])
            ->name('blog-tags.store')
            ->middleware('permission:blog_tags.create');

        Route::get('blog-tags/{blog_tag}/edit', [BlogTagController::class, 'edit'])
            ->whereNumber('blog_tag')
            ->name('blog-tags.edit')
            ->middleware('permission:blog_tags.edit');

        Route::put('blog-tags/{blog_tag}', [BlogTagController::class, 'update'])
            ->whereNumber('blog_tag')
            ->name('blog-tags.update')
            ->middleware('permission:blog_tags.edit');

        Route::delete('blog-tags/{blog_tag}', [BlogTagController::class, 'destroy'])
            ->whereNumber('blog_tag')
            ->name('blog-tags.destroy')
            ->middleware('permission:blog_tags.delete');

        // -------- Blog Categories (except show, edit view) + massDestroy --------
        Route::delete('blog-categories/destroy', [BlogCategoryController::class, 'massDestroy'])
            ->name('blog-categories.massDestroy')
            ->middleware('permission:blog_categories.mass_delete');

        Route::get('blog-categories', [BlogCategoryController::class, 'index'])
            ->name('blog-categories.index')
            ->middleware('permission:blog_categories.view');

        Route::get('blog-categories/create', [BlogCategoryController::class, 'create'])
            ->name('blog-categories.create')
            ->middleware('permission:blog_categories.create');

        Route::post('blog-categories', [BlogCategoryController::class, 'store'])
            ->name('blog-categories.store')
            ->middleware('permission:blog_categories.create');

        Route::get('blog-categories/{blog_category}/edit', [BlogCategoryController::class, 'edit'])
            ->whereNumber('blog_category')
            ->name('blog-categories.edit')
            ->middleware('permission:blog_categories.edit');

        Route::put('blog-categories/{blog_category}', [BlogCategoryController::class, 'update'])
            ->whereNumber('blog_category')
            ->name('blog-categories.update')
            ->middleware('permission:blog_categories.edit');

        Route::delete('blog-categories/{blog_category}', [BlogCategoryController::class, 'destroy'])
            ->whereNumber('blog_category')
            ->name('blog-categories.destroy')
            ->middleware('permission:blog_categories.delete');

        // -------- Blogs (resource + published + uploads + massDestroy) --------
        Route::delete('blogs/destroy', [BlogController::class, 'massDestroy'])
            ->name('blogs.massDestroy')
            ->middleware('permission:blogs.mass_delete');

        Route::put('blogs/published/{blog}', [BlogController::class, 'published'])
            ->whereNumber('blog')
            ->name('blogs.published')
            ->middleware('permission:blogs.published');

        Route::post('blogs/media', [BlogController::class, 'storeMedia'])
            ->name('blogs.storeMedia')
            ->middleware('permission:blogs.upload_media');

        // Perbaikan: ckmedia blog diarahkan ke BlogController
        Route::post('blogs/ckmedia', [BlogController::class, 'storeCKEditorImages'])
            ->name('blogs.storeCKEditorImages')
            ->middleware('permission:blogs.upload_ckeditor');

        Route::get('blogs', [BlogController::class, 'index'])
            ->name('blogs.index')
            ->middleware('permission:blogs.view');

        Route::get('blogs/create', [BlogController::class, 'create'])
            ->name('blogs.create')
            ->middleware('permission:blogs.create');

        Route::post('blogs', [BlogController::class, 'store'])
            ->name('blogs.store')
            ->middleware('permission:blogs.create');

        Route::get('blogs/{blog}/edit', [BlogController::class, 'edit'])
            ->whereNumber('blog')
            ->name('blogs.edit')
            ->middleware('permission:blogs.edit');

        Route::put('blogs/{blog}', [BlogController::class, 'update'])
            ->whereNumber('blog')
            ->name('blogs.update')
            ->middleware('permission:blogs.edit');

        Route::get('blogs/{blog}', [BlogController::class, 'show'])
            ->whereNumber('blog')
            ->name('blogs.show')
            ->middleware('permission:blogs.show');

        Route::delete('blogs/{blog}', [BlogController::class, 'destroy'])
            ->whereNumber('blog')
            ->name('blogs.destroy')
            ->middleware('permission:blogs.delete');

        // -------- Couriers (only index & update) --------
        Route::get('couriers', [\App\Http\Controllers\Admin\CourierController::class, 'index'])
            ->name('couriers.index')
            ->middleware('permission:couriers.view');

        Route::put('couriers/{courier}', [\App\Http\Controllers\Admin\CourierController::class, 'update'])
            ->whereNumber('courier')
            ->name('couriers.update')
            ->middleware('permission:couriers.update');

        // -------- Coupons (resource + custom massDestroy) --------
        Route::delete('coupons/destroy', [\App\Http\Controllers\Admin\CouponController::class, 'massDestroy'])
            ->name('coupons.massDestroy')
            ->middleware('permission:coupons.mass_delete');

        Route::get('coupons', [\App\Http\Controllers\Admin\CouponController::class, 'index'])
            ->name('coupons.index')
            ->middleware('permission:coupons.view');

        Route::get('coupons/create', [\App\Http\Controllers\Admin\CouponController::class, 'create'])
            ->name('coupons.create')
            ->middleware('permission:coupons.create');

        Route::post('coupons', [\App\Http\Controllers\Admin\CouponController::class, 'store'])
            ->name('coupons.store')
            ->middleware('permission:coupons.create');

        Route::get('coupons/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'show'])
            ->whereNumber('coupon')
            ->name('coupons.show')
            ->middleware('permission:coupons.show');

        Route::get('coupons/{coupon}/edit', [\App\Http\Controllers\Admin\CouponController::class, 'edit'])
            ->whereNumber('coupon')
            ->name('coupons.edit')
            ->middleware('permission:coupons.edit');

        Route::put('coupons/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'update'])
            ->whereNumber('coupon')
            ->name('coupons.update')
            ->middleware('permission:coupons.edit');

        Route::delete('coupons/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'destroy'])
            ->whereNumber('coupon')
            ->name('coupons.destroy')
            ->middleware('permission:coupons.delete');

        // -------- Roles (resource except show) --------
        Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])
            ->name('roles.index')
            ->middleware('permission:roles.view');

        Route::get('roles/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])
            ->name('roles.create')
            ->middleware('permission:roles.create');

        Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])
            ->name('roles.store')
            ->middleware('permission:roles.create');

        Route::get('roles/{role}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])
            ->whereNumber('role')
            ->name('roles.edit')
            ->middleware('permission:roles.edit');

        Route::put('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])
            ->whereNumber('role')
            ->name('roles.update')
            ->middleware('permission:roles.edit');

        Route::delete('roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])
            ->whereNumber('role')
            ->name('roles.destroy')
            ->middleware('permission:roles.delete');

        // -------- Audit Logs --------
        Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])
            ->name('audit-logs.index')
            ->middleware('permission:audit_logs.view');

        Route::get('audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'show'])
            ->whereNumber('auditLog')
            ->name('audit-logs.show')
            ->middleware('permission:audit_logs.show');

        Route::delete('audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'destroy'])
            ->whereNumber('auditLog')
            ->name('audit-logs.destroy')
            ->middleware('permission:audit_logs.delete');
        Route::delete('audit-logs/massDestroy', [\App\Http\Controllers\Admin\AuditLogController::class, 'massDestroy'])
            ->name('audit-logs.massDestroy')
            ->middleware('permission:audit_logs.mass_delete');

        // Support - Contact Messages
        Route::prefix('support')->group(function () {
            Route::get('/messages', [AdminContactMessageController::class, 'index'])
                ->name('messages.index')
                ->middleware('permission:contact_messages.view');
            Route::get('/messages/{message}', [AdminContactMessageController::class, 'show'])
                ->name('messages.show')
                ->middleware('permission:contact_messages.show');
            Route::patch('/messages/{message}', [AdminContactMessageController::class, 'update'])
                ->name('messages.update')
                ->middleware('permission:contact_messages.reply');
            Route::post('/messages/{message}/reply', [AdminContactReplyController::class, 'store'])
                ->name('messages.reply')
                ->middleware('permission:contact_messages.reply');
        });

        // -------- Newsletters --------
        Route::delete('newsletters/destroy', [NewsletterController::class, 'massDestroy'])
            ->name('newsletters.massDestroy')
            ->middleware('permission:newsletters.mass_delete');

        Route::get('newsletters', [NewsletterController::class, 'index'])
            ->name('newsletters.index')
            ->middleware('permission:newsletters.view');

        Route::get('newsletters/create', [NewsletterController::class, 'create'])
            ->name('newsletters.create')
            ->middleware('permission:newsletters.create');

        Route::post('newsletters', [NewsletterController::class, 'store'])
            ->name('newsletters.store')
            ->middleware('permission:newsletters.create');

        Route::get('newsletters/{newsletter}/edit', [NewsletterController::class, 'edit'])
            ->whereNumber('newsletter')
            ->name('newsletters.edit')
            ->middleware('permission:newsletters.edit');

        Route::put('newsletters/{newsletter}', [NewsletterController::class, 'update'])
            ->whereNumber('newsletter')
            ->name('newsletters.update')
            ->middleware('permission:newsletters.edit');

        Route::get('newsletters/{newsletter}', [NewsletterController::class, 'show'])
            ->whereNumber('newsletter')
            ->name('newsletters.show')
            ->middleware('permission:newsletters.show');

        Route::delete('newsletters/{newsletter}', [NewsletterController::class, 'destroy'])
            ->whereNumber('newsletter')
            ->name('newsletters.destroy')
            ->middleware('permission:newsletters.delete');

        Route::get('newsletters/statistics', [NewsletterController::class, 'statistics'])
            ->name('newsletters.statistics')
            ->middleware('permission:newsletters.view');

        // -------- Campaigns --------
        Route::get('campaigns', [\App\Http\Controllers\Admin\CampaignController::class, 'index'])
            ->name('campaigns.index')
            ->middleware('permission:campaigns.view');

        Route::get('campaigns/create', [\App\Http\Controllers\Admin\CampaignController::class, 'create'])
            ->name('campaigns.create')
            ->middleware('permission:campaigns.create');

        Route::post('campaigns', [\App\Http\Controllers\Admin\CampaignController::class, 'store'])
            ->name('campaigns.store')
            ->middleware('permission:campaigns.create');

        Route::get('campaigns/{campaign}', [\App\Http\Controllers\Admin\CampaignController::class, 'show'])
            ->whereNumber('campaign')
            ->name('campaigns.show')
            ->middleware('permission:campaigns.show');

        Route::get('campaigns/{campaign}/edit', [\App\Http\Controllers\Admin\CampaignController::class, 'edit'])
            ->whereNumber('campaign')
            ->name('campaigns.edit')
            ->middleware('permission:campaigns.edit');

        Route::put('campaigns/{campaign}', [\App\Http\Controllers\Admin\CampaignController::class, 'update'])
            ->whereNumber('campaign')
            ->name('campaigns.update')
            ->middleware('permission:campaigns.edit');

        Route::delete('campaigns/{campaign}', [\App\Http\Controllers\Admin\CampaignController::class, 'destroy'])
            ->whereNumber('campaign')
            ->name('campaigns.destroy')
            ->middleware('permission:campaigns.delete');

        Route::post('campaigns/{campaign}/send', [\App\Http\Controllers\Admin\CampaignController::class, 'send'])
            ->whereNumber('campaign')
            ->name('campaigns.send')
            ->middleware('permission:campaigns.send');

        Route::post('campaigns/{campaign}/test', [\App\Http\Controllers\Admin\CampaignController::class, 'testSend'])
            ->whereNumber('campaign')
            ->name('campaigns.test')
            ->middleware('permission:campaigns.test');

        Route::get('campaigns/statistics', [\App\Http\Controllers\Admin\CampaignController::class, 'statistics'])
            ->name('campaigns.statistics')
            ->middleware('permission:campaigns.view');

        // Public unsubscribe and confirm routes
        Route::get('newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribeByToken'])
            ->name('newsletter.unsubscribe');

        Route::get('newsletter/confirm/{token}', [NewsletterController::class, 'confirmSubscription'])
            ->name('newsletter.confirm');
    });

Route::get('swagger', function () {
    return view('docs');
});
