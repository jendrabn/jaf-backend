<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a class="brand-link text-center"
       href="{{ route('admin.home') }}">
        <span class="brand-text font-weight-bold text-uppercase"
              style="letter-spacing: 0.15rem;">{{ config('app.name') }}</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-accordion="false"
                data-widget="treeview"
                role="menu">

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}"
                       href="{{ route('admin.home') }}">
                        <i class="nav-icon bi bi-speedometer2">
                        </i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('admin/users') || request()->is('admin/users/*') ? 'active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <i class="nav-icon bi bi-people"></i>
                        <p>
                            User
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('admin/orders') || request()->is('admin/orders/*') ? 'active' : '' }}"
                       href="{{ route('admin.orders.index') }}">
                        <i class="nav-icon bi bi-box-seam"></i>
                        <p>
                            Order
                            @php
                                $orders_count = \App\Models\Order::where(['status' => 'pending'])->count();
                            @endphp

                            @if ($orders_count > 0)
                                <span class="badge badge-light right">{{ $orders_count }}</span>
                            @endif
                        </p>
                    </a>
                </li>

                <li
                    class="nav-item has-treeview {{ request()->is('admin/product-categories*') ? 'menu-open' : '' }} {{ request()->is('admin/product-brands*') ? 'menu-open' : '' }} {{ request()->is('admin/products*') ? 'menu-open' : '' }} {{ request()->is('admin/couriers*') ? 'menu-open' : '' }} {{ request()->is('admin/coupons*') ? 'menu-open' : '' }}">
                    <a class="nav-link nav-dropdown-toggle {{ request()->is('admin/product-categories*') ? 'active' : '' }} {{ request()->is('admin/product-brands*') ? 'active' : '' }} {{ request()->is('admin/product*') ? 'active' : '' }} {{ request()->is('admin/couriers*') ? 'active' : '' }} {{ request()->is('admin/coupons*') ? 'active' : '' }} "
                       href="#">
                        <i class="nav-icon bi bi-shop"></i>
                        <p>
                            Shop
                            <i class="right bi bi-chevron-left nav-icon"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/products') || request()->is('admin/products/*') ? 'active' : '' }}"
                               href="{{ route('admin.products.index') }}">
                                <i class="nav-icon bi bi-circle">
                                </i>
                                <p>
                                    Product
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/product-categories') || request()->is('admin/product-categories/*') ? 'active' : '' }}"
                               href="{{ route('admin.product-categories.index') }}">
                                <i class="nav-icon bi bi-circle">
                                </i>
                                <p>
                                    Category
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/product-brands') || request()->is('admin/product-brands/*') ? 'active' : '' }}"
                               href="{{ route('admin.product-brands.index') }}">
                                <i class="nav-icon bi bi-circle">
                                </i>
                                <p>
                                    Brand
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/couriers') || request()->is('admin/couriers/*') ? 'active' : '' }}"
                               href="{{ route('admin.couriers.index') }}">
                                <i class="nav-icon bi bi-circle">
                                </i>
                                <p>
                                    Courier
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/coupons') || request()->is('admin/coupons/*') ? 'active' : '' }}"
                               href="{{ route('admin.coupons.index') }}">
                                <i class="nav-icon bi bi-circle">
                                </i>
                                <p>
                                    Coupon
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li
                    class="nav-item has-treeview {{ request()->is('admin/banks*') || request()->is('admin/ewallets*') ? 'menu-open' : '' }}">
                    <a class="nav-link nav-dropdown-toggle {{ request()->is('admin/banks*') || request()->is('admin/ewallets*') ? 'active' : '' }}"
                       href="#">
                        <i class="nav-icon bi bi-wallet"></i>
                        <p>
                            Payment Method
                            <i class="right bi bi-chevron-left nav-icon"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/banks') || request()->is('admin/banks/*') ? 'active' : '' }}"
                               href="{{ route('admin.banks.index') }}">
                                <i class="nav-icon bi bi-circle">
                                </i>
                                <p>
                                    Bank
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/ewallets') || request()->is('admin/ewallets/*') ? 'active' : '' }}"
                               href="{{ route('admin.ewallets.index') }}">
                                <i class="nav-icon bi bi-circle">
                                </i>
                                <p>
                                    E-Wallet
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('admin/banners') || request()->is('admin/banners/*') ? 'active' : '' }}"
                       href="{{ route('admin.banners.index') }}">
                        <i class="nav-icon bi bi-card-image">
                        </i>
                        <p>
                            Banners
                        </p>
                    </a>
                </li>

                <li
                    class="nav-item {{ request()->is('admin/blogs') || request()->is('admin/blogs/*') || request()->is('admin/blog-categories') || request()->is('admin/blog-categories/*') || request()->is('admin/blog-tags') || request()->is('admin/blog-tags/*') ? 'menu-open' : '' }}">
                    <a class="nav-link {{ request()->is('admin/blog-categories') || request()->is('admin/blog-categories/*') || request()->is('admin/blog-tags') || request()->is('admin/blog-tags/*') ? 'active' : '' }}"
                       href="#">
                        <i class="nav-icon bi bi-newspaper"></i>
                        <p>
                            Blog
                            <i class="right bi bi-chevron-left nav-icon"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/blogs') || request()->is('admin/blogs/*') ? 'active' : '' }}"
                               href="{{ route('admin.blogs.index') }}">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Post</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/blog-categories') || request()->is('admin/blog-categories/*') ? 'active' : '' }}"
                               href="{{ route('admin.blog-categories.index') }}">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Category</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/blog-tags') || request()->is('admin/blog-tags/*') ? 'active' : '' }}"
                               href="{{ route('admin.blog-tags.index') }}">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Tag</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
