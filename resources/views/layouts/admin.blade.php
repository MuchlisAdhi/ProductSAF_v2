<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sidoagung Farm Admin' }}</title>
    <link rel="icon" href="{{ asset('images/logo/saf-logo-merah.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/logo/saf-logo-merah.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link type="text/css" href="{{ asset('vendor/volt/vendor/sweetalert2/dist/sweetalert2.min.css') }}" rel="stylesheet">
    <link type="text/css" href="{{ asset('vendor/volt/vendor/notyf/notyf.min.css') }}" rel="stylesheet">
    <link type="text/css" href="{{ asset('vendor/volt/css/volt.css') }}" rel="stylesheet">
    <style>
        :root {
            --admin-sidebar-width: 280px;
        }

        body {
            font-family: 'Nunito', sans-serif;
            overflow-x: hidden;
        }

        .admin-sidebar {
            width: var(--admin-sidebar-width);
            max-width: var(--admin-sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .admin-sidebar .sidebar-inner {
            width: 100%;
            min-height: 100%;
        }

        .admin-sidebar .nav-item .nav-link {
            border-radius: 0.6rem;
            color: rgba(255, 255, 255, 0.84);
            padding: 0.62rem 0.7rem;
            transition: all 0.18s ease;
        }

        .admin-sidebar .nav-item .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.12);
            transform: translateX(4px);
        }

        .admin-sidebar .nav-item.active > .nav-link {
            color: #ffffff;
            background: rgba(33, 128, 243, 0.45);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14);
        }

        .admin-sidebar .nav-item .sidebar-icon {
            width: 1.1rem;
            display: inline-flex;
            justify-content: center;
            margin-right: 0.55rem;
        }

        .admin-content {
            margin-left: var(--admin-sidebar-width);
            min-height: 100vh;
            padding: 1.25rem 1.25rem 0.85rem;
            background-color: #f5f8fb;
        }

        .admin-topbar {
            border: 0;
            box-shadow: 0 0.2rem 1rem rgba(43, 52, 69, 0.08);
        }

        .admin-content .card {
            border: 0;
            box-shadow: 0 0.2rem 1rem rgba(43, 52, 69, 0.08);
        }

        .admin-content .table > thead > tr > th {
            border-bottom: 0;
            color: #66799e;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.025em;
            text-transform: uppercase;
            background: #f5f7fb;
            white-space: nowrap;
        }

        .admin-content .table > :not(caption) > * > * {
            padding: 0.78rem 0.9rem;
            vertical-align: middle;
        }

        .admin-content .btn {
            font-weight: 600;
        }

        .admin-content .form-control,
        .admin-content .form-select,
        .admin-content .input-group-text {
            border-color: #d1d7e0;
        }

        .admin-content .form-control:focus,
        .admin-content .form-select:focus {
            border-color: #4d8cff;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
        }

        .admin-content .modal-content {
            border: 0;
            box-shadow: 0 1rem 2.2rem rgba(43, 52, 69, 0.22);
        }

        .account-avatar {
            width: 38px;
            height: 38px;
            object-fit: cover;
        }

        @media (max-width: 991.98px) {
            .admin-content {
                margin-left: 0;
                padding: 1rem 0.85rem 0.75rem;
            }
        }
    </style>
    @stack('head')
</head>
<body class="bg-soft">
    @php
        $authUser = auth()->user();
        $authRole = $authUser?->role;
        $authRoleValue = $authRole instanceof \App\Enums\Role ? $authRole->value : (string) $authRole;
        $canManageUsers = $authRoleValue === 'SUPERADMIN';
        $defaultAvatar = asset('images/default-avatar.svg');

        $resolveAvatarUrl = static function ($user, string $fallback): string {
            if (! $user) {
                return $fallback;
            }

            foreach (['profile_image_url', 'avatar_url', 'profile_photo_url', 'photo_url'] as $attribute) {
                $value = trim((string) data_get($user, $attribute, ''));
                if ($value !== '') {
                    return $value;
                }
            }

            foreach (['profile_image', 'avatar', 'photo', 'image'] as $attribute) {
                $value = trim((string) data_get($user, $attribute, ''));
                if ($value === '') {
                    continue;
                }

                if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, 'data:image/')) {
                    return $value;
                }

                return asset(ltrim($value, '/'));
            }

            return $fallback;
        };

        $avatarUrl = $resolveAvatarUrl($authUser, $defaultAvatar);
    @endphp

    <nav class="navbar navbar-dark navbar-theme-primary px-4 d-lg-none">
        <div class="container-fluid px-0">
            <a class="navbar-brand d-flex align-items-center me-lg-5" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/logo/saf-logo.png') }}" alt="Sidoagung logo" style="height: 30px; width: auto;">
                <span class="ms-2 fw-semibold">Sidoagung Admin</span>
            </a>
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenuMobile" aria-controls="sidebarMenuMobile" aria-label="Buka menu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <nav id="sidebarMenuDesktop" class="admin-sidebar sidebar d-none d-lg-block bg-gray-800 text-white" data-simplebar>
        <div class="sidebar-inner px-4 pt-3">
            @include('admin.partials.sidebar-menu')
        </div>
    </nav>

    <div class="offcanvas offcanvas-start bg-gray-800 text-white d-lg-none" tabindex="-1" id="sidebarMenuMobile" aria-labelledby="sidebarMenuMobileLabel" style="width: 300px;">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title" id="sidebarMenuMobileLabel">Menu Admin</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body p-3">
            @include('admin.partials.sidebar-menu', ['isMobile' => true])
        </div>
    </div>

    <main class="admin-content">
        <nav class="navbar navbar-top navbar-expand navbar-light bg-white rounded px-3 py-2 mb-4 admin-topbar">
            <div class="container-fluid px-0">
                <div class="d-flex justify-content-end align-items-center w-100">
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle p-0 d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="{{ $avatarUrl }}" class="avatar rounded-circle account-avatar" alt="Avatar {{ $authUser?->name ?? 'Account' }}">
                                <span class="d-none d-lg-inline-block ms-2 fw-semibold text-dark">
                                    {{ $authUser?->name ?? 'Account' }}
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end mt-2 py-0">
                                <li class="px-3 py-2 border-bottom">
                                    <p class="small text-muted mb-1">Signed in as</p>
                                    <p class="small fw-bold mb-0">{{ $authUser?->email ?? '-' }}</p>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item d-flex align-items-center text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        @include('partials.flash-bootstrap')
        @yield('content')
    </main>

    <script src="{{ asset('vendor/volt/vendor/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/onscreen/dist/on-screen.umd.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/nouislider/dist/nouislider.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/notyf/notyf.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/assets/js/volt.js') }}"></script>
    <script>
        (() => {
            if (typeof window.SmoothScroll === 'function') {
                new window.SmoothScroll('a[href*="#"]', {
                    speed: 450,
                    speedAsDuration: true,
                    offset: 72,
                });
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>
