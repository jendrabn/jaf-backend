@php
    $shopActive =
        request()->routeIs('admin.products.*') ||
        request()->routeIs('admin.product-categories.*') ||
        request()->routeIs('admin.product-brands.*') ||
        request()->routeIs('admin.couriers.*') ||
        request()->routeIs('admin.coupons.*') ||
        request()->routeIs('admin.taxes.*');

    $paymentActive = request()->routeIs('admin.banks.*') || request()->routeIs('admin.ewallets.*');

    $blogActive =
        request()->routeIs('admin.blogs.*') ||
        request()->routeIs('admin.blog-categories.*') ||
        request()->routeIs('admin.blog-tags.*');

    $newsletterActive = request()->routeIs('admin.newsletters.*') || request()->routeIs('admin.campaigns.*');
@endphp

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a class="brand-link text-center"
       href="{{ route('admin.home') }}">
        <span class="brand-text font-weight-bold text-uppercase"
              style="letter-spacing:.15rem;">
            {{ config('app.name') }}
        </span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-accordion="false"
                data-widget="treeview"
                role="menu">

                {{-- Dashboard --}}
                @can('dashboard.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.home') ? 'active' : '' }}"
                           href="{{ route('admin.home') }}">
                            <i class="nav-icon bi bi-speedometer2 mr-2"></i>
                            <p class="mb-0">Dashboard</p>
                        </a>
                    </li>
                @endcan

                {{-- User --}}
                @can('users.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                           href="{{ route('admin.users.index') }}">
                            <i class="nav-icon bi bi-people mr-2"></i>
                            <p class="mb-0">User</p>
                        </a>
                    </li>
                @endcan

                {{-- User Role --}}
                @canany(['roles.view', 'permissions.view'])
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                           href="{{ route('admin.roles.index') }}">
                            <i class="nav-icon bi bi-shield-shaded mr-2"></i>
                            <p class="mb-0">Role & Permission</p>
                        </a>
                    </li>
                @endcanany

                {{-- Order (dengan badge count) --}}
                @can('orders.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                           href="{{ route('admin.orders.index') }}">
                            <i class="nav-icon bi bi-box-seam mr-2"></i>
                            <p class="mb-0">Order</p>
                            @php $orders_count = \App\Models\Order::where(['status' => 'pending'])->count(); @endphp
                            @if ($orders_count > 0)
                                <span class="badge badge-light ml-auto">{{ $orders_count }}</span>
                            @endif
                        </a>
                    </li>
                @endcan

                {{-- Shop (treeview) --}}
                @canany(['products.view', 'product_categories.view', 'product_brands.view', 'couriers.view',
                    'coupons.view', 'taxes.view'])
                    <li class="nav-item has-treeview {{ $shopActive ? 'menu-open' : '' }}">
                        <a class="nav-link d-flex align-items-center nav-dropdown-toggle {{ $shopActive ? 'active' : '' }}"
                           href="#">
                            <i class="nav-icon bi bi-shop mr-2"></i>
                            <p class="mb-0">Shop</p>
                            <i class="right bi bi-chevron-left ml-auto"></i>
                        </a>

                        <ul class="nav nav-treeview">
                            @can('products.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                                       href="{{ route('admin.products.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Product</p>
                                    </a>
                                </li>
                            @endcan

                            @can('product_categories.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.product-categories.*') ? 'active' : '' }}"
                                       href="{{ route('admin.product-categories.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Category</p>
                                    </a>
                                </li>
                            @endcan

                            @can('product_brands.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.product-brands.*') ? 'active' : '' }}"
                                       href="{{ route('admin.product-brands.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Brand</p>
                                    </a>
                                </li>
                            @endcan

                            @can('couriers.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.couriers.*') ? 'active' : '' }}"
                                       href="{{ route('admin.couriers.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Courier</p>
                                    </a>
                                </li>
                            @endcan

                            @can('coupons.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"
                                       href="{{ route('admin.coupons.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Coupon</p>
                                    </a>
                                </li>
                            @endcan

                            @can('taxes.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.taxes.*') ? 'active' : '' }}"
                                       href="{{ route('admin.taxes.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Tax</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                {{-- Payment Method (treeview) --}}
                @canany(['banks.view', 'ewallets.view'])
                    <li class="nav-item has-treeview {{ $paymentActive ? 'menu-open' : '' }}">
                        <a class="nav-link d-flex align-items-center nav-dropdown-toggle {{ $paymentActive ? 'active' : '' }}"
                           href="#">
                            <i class="nav-icon bi bi-credit-card mr-2"></i>
                            <p class="mb-0">Payment Method</p>
                            <i class="right bi bi-chevron-left ml-auto"></i>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('banks.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.banks.*') ? 'active' : '' }}"
                                       href="{{ route('admin.banks.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Bank</p>
                                    </a>
                                </li>
                            @endcan

                            @can('ewallets.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.ewallets.*') ? 'active' : '' }}"
                                       href="{{ route('admin.ewallets.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">E-Wallet</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                {{-- Banner --}}
                @can('banners.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}"
                           href="{{ route('admin.banners.index') }}">
                            <i class="nav-icon bi bi-card-image mr-2"></i>
                            <p class="mb-0">Banner</p>
                        </a>
                    </li>
                @endcan

                {{-- Blog (treeview) --}}
                @canany(['blogs.view', 'blog_categories.view', 'blog_tags.view'])
                    <li class="nav-item has-treeview {{ $blogActive ? 'menu-open' : '' }}">
                        <a class="nav-link d-flex align-items-center {{ $blogActive ? 'active' : '' }}"
                           href="#">
                            <i class="nav-icon bi bi-newspaper mr-2"></i>
                            <p class="mb-0">Blog</p>
                            <i class="right bi bi-chevron-left ml-auto"></i>
                        </a>
                        <ul class="nav nav-treeview">
                            @can('blogs.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}"
                                       href="{{ route('admin.blogs.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Blog</p>
                                    </a>
                                </li>
                            @endcan

                            @can('blog_categories.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}"
                                       href="{{ route('admin.blog-categories.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Category</p>
                                    </a>
                                </li>
                            @endcan

                            @can('blog_tags.view')
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.blog-tags.*') ? 'active' : '' }}"
                                       href="{{ route('admin.blog-tags.index') }}">
                                        <i class="nav-icon bi bi-circle mr-2"></i>
                                        <p class="mb-0">Tag</p>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                {{-- Support - Contact Messages --}}
                @can('contact_messages.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}"
                           href="{{ route('admin.messages.index') }}">
                            <i class="nav-icon bi bi-envelope mr-2"></i>
                            <p class="mb-0">Contact Messages</p>
                        </a>
                    </li>
                @endcan

                {{-- Audit Log --}}
                @can('audit_logs.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}"
                           href="{{ route('admin.audit-logs.index') }}">
                            <i class="nav-icon bi bi-clock-history mr-2"></i>
                            <p class="mb-0">Audit Log</p>
                        </a>
                    </li>
                @endcan

            </ul>
        </nav>
    </div>
</aside>
