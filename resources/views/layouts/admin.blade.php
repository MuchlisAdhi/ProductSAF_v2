<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sidoagung Farm Admin' }}</title>
    <link rel="icon" href="{{ asset('images/logo/saf-logo-merah.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/logo/saf-logo-merah.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gradient-to-b from-slate-100 via-slate-100 to-emerald-50/40 text-slate-900">
    <header class="sticky top-0 z-40 border-b border-white/20 bg-[#1b5e20] text-white shadow-sm">
        <div class="mx-auto flex min-h-14 max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-2 sm:px-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3" aria-label="Sidoagung Admin Home">
                <img
                    src="{{ asset('images/logo/logo-sidoagung-merah.png') }}"
                    alt="Logo Sidoagung"
                    class="h-20 w-14 object-contain"
                >
                <div class="leading-tight">
                    <div class="text-sm font-semibold text-white">Sidoagung Farm</div>
                    <div class="text-xs text-white/80">Admin Dashboard</div>
                </div>
            </a>
            <div class="flex flex-wrap items-center gap-1 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Dashboard</a>
                <a href="{{ route('admin.products.index') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Products</a>
                <a href="{{ route('admin.categories.index') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Categories</a>
                @php
                    $authRole = auth()->user()?->role;
                    $authRoleValue = $authRole instanceof \App\Enums\Role ? $authRole->value : (string) $authRole;
                @endphp
                @if($authRoleValue === 'SUPERADMIN')
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Users</a>
                @endif
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Public</a>
                @if(!empty($roleLabel))
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">{{ $roleLabel }}</span>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-white/40 px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="flex-1">
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>

    <footer class="mt-auto border-t border-white/20 bg-[#1b5e20] text-white">
        <div class="mx-auto max-w-6xl px-4 py-8 text-xs text-white sm:px-6">
            &copy; {{ now()->year }} Sidoagung Farm - Product Catalog
        </div>
    </footer>
</body>
</html>
