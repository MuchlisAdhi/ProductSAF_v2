<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sidoagung Farm Product Catalog' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-100 via-slate-100 to-emerald-50/40 text-slate-900">
    <header class="sticky top-0 z-40 border-b border-white/20 bg-[#1b5e20] text-white shadow-sm">
        @php
            $currentRole = auth()->user()?->role;
            $roleValue = $currentRole instanceof \App\Enums\Role ? $currentRole->value : (string) $currentRole;
            $canAccessAdminNav = in_array($roleValue, ['SUPERADMIN', 'ADMIN'], true);
        @endphp
        <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Sidoagung Farm Home">
                <img
                    src="{{ asset('images/logo/logo-sidoagung-merah.png') }}"
                    alt="Logo Sidoagung"
                    class="h-20 w-14 object-contain"
                >
                <div class="leading-tight">
                    <div class="text-sm font-semibold text-white">Sidoagung Farm</div>
                    <div class="text-xs text-white/80">Product Catalog</div>
                </div>
            </a>

            <nav class="flex items-center gap-1 text-sm">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Beranda</a>
                <a href="{{ route('home') }}#katalog" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Katalog</a>
                <a href="{{ route('products.index') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Produk</a>
                @auth
                    @if($canAccessAdminNav)
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Admin</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="border-t border-white/20 bg-[#1b5e20] text-white">
        <div class="mx-auto max-w-6xl px-4 py-8 text-xs text-white sm:px-6">
            &copy; {{ now()->year }} Sidoagung Farm - Product Catalog
        </div>
    </footer>
</body>
</html>
