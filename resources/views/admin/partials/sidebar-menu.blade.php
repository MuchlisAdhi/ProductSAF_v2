<ul class="nav flex-column pt-3 pt-md-0">
    <li class="nav-item mb-2">
        <a href="{{ route('admin.dashboard') }}" class="nav-link d-flex align-items-center">
            <span class="sidebar-icon">
                <img src="{{ asset('images/logo/saf-logo.png') }}" height="20" width="20" alt="Logo">
            </span>
            <span class="mt-1 ms-1 sidebar-text">Sidoagung Admin</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a href="{{ route('admin.dashboard') }}" class="nav-link d-flex align-items-center">
            <span class="sidebar-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="sidebar-text">Dashboard</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
        <a href="{{ route('admin.products.index') }}" class="nav-link d-flex align-items-center">
            <span class="sidebar-icon"><i class="bi bi-box-seam"></i></span>
            <span class="sidebar-text">Produk</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <a href="{{ route('admin.categories.index') }}" class="nav-link d-flex align-items-center">
            <span class="sidebar-icon"><i class="bi bi-grid-3x3-gap"></i></span>
            <span class="sidebar-text">Kategori</span>
        </a>
    </li>
    @if($canManageUsers)
        <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}" class="nav-link d-flex align-items-center">
                <span class="sidebar-icon"><i class="bi bi-people"></i></span>
                <span class="sidebar-text">Pengguna</span>
            </a>
        </li>
    @endif

    <li role="separator" class="dropdown-divider mt-4 mb-3 border-secondary"></li>
    <li class="nav-item">
        <span class="nav-link d-flex justify-content-between align-items-center text-uppercase small fw-bold text-secondary">Tracker</span>
    </li>
    <li class="nav-item {{ request()->routeIs('admin.tracker.summary') ? 'active' : '' }}">
        <a href="{{ route('admin.tracker.summary') }}" class="nav-link d-flex align-items-center">
            <span class="sidebar-icon"><i class="bi bi-bar-chart-line"></i></span>
            <span class="sidebar-text">Summary</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('admin.tracker.visits') ? 'active' : '' }}">
        <a href="{{ route('admin.tracker.visits') }}" class="nav-link d-flex align-items-center">
            <span class="sidebar-icon"><i class="bi bi-globe2"></i></span>
            <span class="sidebar-text">Visits</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('admin.tracker.users') ? 'active' : '' }}">
        <a href="{{ route('admin.tracker.users') }}" class="nav-link d-flex align-items-center">
            <span class="sidebar-icon"><i class="bi bi-person"></i></span>
            <span class="sidebar-text">Users (Guest)</span>
        </a>
    </li>

    <li role="separator" class="dropdown-divider mt-4 mb-3 border-secondary"></li>
    <li class="nav-item">
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="nav-link d-flex align-items-center">
            <span class="sidebar-icon"><i class="bi bi-box-arrow-up-right"></i></span>
            <span class="sidebar-text">Halaman Publik</span>
        </a>
    </li>
</ul>
