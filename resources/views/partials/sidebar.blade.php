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
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.home') ? 'active' : '' }}"
                       href="{{ route('admin.home') }}">
                        <i class="nav-icon bi bi-speedometer2 mr-2"></i>
                        <p class="mb-0">Dashboard</p>
                    </a>
                </li>

                {{-- User --}}
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->is('admin/users') || request()->is('admin/users/*') ? 'active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <i class="nav-icon bi bi-people mr-2"></i>
                        <p class="mb-0">User</p>
                    </a>
                </li>

                {{-- Order (dengan badge count) --}}
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->is('admin/orders') || request()->is('admin/orders/*') ? 'active' : '' }}"
                       href="{{ route('admin.orders.index') }}">
                        <i class="nav-icon bi bi-box-seam mr-2"></i>
                        <p class="mb-0">Order</p>
                        @php $orders_count = \App\Models\Order::where(['status' => 'pending'])->count(); @endphp
                        @if ($orders_count > 0)
                            <span class="badge badge-light ml-auto">{{ $orders_count }}</span>
                        @endif
                    </a>
                </li>

                {{-- Shop (treeview) --}}
                <li
                    class="nav-item has-treeview
                   {{ request()->is('admin/product-categories*') ? 'menu-open' : '' }}
                   {{ request()->is('admin/product-brands*') ? 'menu-open' : '' }}
                   {{ request()->is('admin/products*') ? 'menu-open' : '' }}
                   {{ request()->is('admin/couriers*') ? 'menu-open' : '' }}
                   {{ request()->is('admin/coupons*') ? 'menu-open' : '' }}">
                    <a class="nav-link d-flex align-items-center nav-dropdown-toggle
                    {{ request()->is('admin/product-categories*') ? 'active' : '' }}
                    {{ request()->is('admin/product-brands*') ? 'active' : '' }}
                    {{ request()->is('admin/product*') ? 'active' : '' }}
                    {{ request()->is('admin/couriers*') ? 'active' : '' }}
                    {{ request()->is('admin/coupons*') ? 'active' : '' }}"
                       href="#">
                        <i class="nav-icon bi bi-shop mr-2"></i>
                        <p class="mb-0">Shop</p>
                        <i class="right bi bi-chevron-left ml-auto"></i> {{-- dorong ke kanan --}}
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/products') || request()->is('admin/products/*') ? 'active' : '' }}"
                               href="{{ route('admin.products.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">Product</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/product-categories') || request()->is('admin/product-categories/*') ? 'active' : '' }}"
                               href="{{ route('admin.product-categories.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">Category</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/product-brands') || request()->is('admin/product-brands/*') ? 'active' : '' }}"
                               href="{{ route('admin.product-brands.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">Brand</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/couriers') || request()->is('admin/couriers/*') ? 'active' : '' }}"
                               href="{{ route('admin.couriers.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">Courier</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/coupons') || request()->is('admin/coupons/*') ? 'active' : '' }}"
                               href="{{ route('admin.coupons.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">Coupon</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Payment Method (treeview) --}}
                <li
                    class="nav-item has-treeview {{ request()->is('admin/banks*') || request()->is('admin/ewallets*') ? 'menu-open' : '' }}">
                    <a class="nav-link d-flex align-items-center nav-dropdown-toggle {{ request()->is('admin/banks*') || request()->is('admin/ewallets*') ? 'active' : '' }}"
                       href="#">
                        <i class="nav-icon bi bi-wallet2 mr-2"></i>
                        <p class="mb-0">Payment Method</p>
                        <i class="right bi bi-chevron-left ml-auto"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/banks') || request()->is('admin/banks/*') ? 'active' : '' }}"
                               href="{{ route('admin.banks.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">Bank</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/ewallets') || request()->is('admin/ewallets/*') ? 'active' : '' }}"
                               href="{{ route('admin.ewallets.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">E-Wallet</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Banner --}}
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->is('admin/banners') || request()->is('admin/banners/*') ? 'active' : '' }}"
                       href="{{ route('admin.banners.index') }}">
                        <i class="nav-icon bi bi-card-image mr-2"></i>
                        <p class="mb-0">Banner</p>
                    </a>
                </li>

                {{-- Blog (treeview) --}}
                <li
                    class="nav-item {{ request()->is('admin/blogs') || request()->is('admin/blogs/*') || request()->is('admin/blog-categories') || request()->is('admin/blog-categories/*') || request()->is('admin/blog-tags') || request()->is('admin/blog-tags/*') ? 'menu-open' : '' }}">
                    <a class="nav-link d-flex align-items-center {{ request()->is('admin/blog-categories') || request()->is('admin/blog-categories/*') || request()->is('admin/blog-tags') || request()->is('admin/blog-tags/*') ? 'active' : '' }}"
                       href="#">
                        <i class="nav-icon bi bi-newspaper mr-2"></i>
                        <p class="mb-0">Blog</p>
                        <i class="right bi bi-chevron-left ml-auto"></i>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/blogs') || request()->is('admin/blogs/*') ? 'active' : '' }}"
                               href="{{ route('admin.blogs.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">Blog</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/blog-categories') || request()->is('admin/blog-categories/*') ? 'active' : '' }}"
                               href="{{ route('admin.blog-categories.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">Category</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->is('admin/blog-tags') || request()->is('admin/blog-tags/*') ? 'active' : '' }}"
                               href="{{ route('admin.blog-tags.index') }}">
                                <i class="nav-icon bi bi-circle mr-2"></i>
                                <p class="mb-0">Tag</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
</aside>
