<nav class="main-header navbar navbar-expand navbar-light border-bottom bg-white">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link"
               data-widget="pushmenu"
               href="#"><i class="bi bi-list"></i>
            </a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown user-menu">
            <a aria-expanded="false"
               class="nav-link dropdown-toggle"
               data-toggle="dropdown"
               href="#">
                <img alt="User Image"
                     class="user-image rounded-circle shadow"
                     src="{{ Auth::user()->avatar->url ?? 'https://ui-avatars.com/api/?name=' . Auth::user()->name }}" />
                <span class="d-none d-md-inline font-weight-bold">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg-right border-0 shadow-lg"
                style="max-width: 250px;">
                <li class="dropdown-item">
                    <a class="btn btn-link text-body"
                       href="{{ route('admin.profile.index') }}"><i class="bi bi-person mr-2"></i>Profile</a>
                </li>
                <li class="dropdown-item">
                    <a class="btn btn-link text-body"
                       href="{{ route('auth.logout') }}"><i class="bi bi-box-arrow-right mr-2"></i>Logout</a>
                </li>
            </ul>
        </li>
    </ul>
</nav>
