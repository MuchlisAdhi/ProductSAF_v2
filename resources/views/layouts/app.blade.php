<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sidoagung Farm Product Catalog' }}</title>
    <link rel="icon" href="{{ asset('images/logo/saf-logo-merah.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/logo/saf-logo-merah.ico') }}" type="image/x-icon">
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gradient-to-b from-slate-100 via-slate-100 to-emerald-50/40 text-slate-900">
    <header class="sticky top-0 z-40 border-b border-white/20 bg-[#1b5e20] text-white shadow-sm">
        @php
            $currentRole = auth()->user()?->role;
            $roleValue = $currentRole instanceof \App\Enums\Role ? $currentRole->value : (string) $currentRole;
            $canAccessAdminNav = in_array($roleValue, ['SUPERADMIN', 'ADMIN'], true);
        @endphp
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-3 py-2 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2 sm:gap-3" aria-label="Sidoagung Farm Home">
                <img
                    src="{{ asset('images/logo/saf-logo.png') }}"
                    alt="Logo Sidoagung"
                    class="h-10 w-10 shrink-0 object-contain sm:h-12 sm:w-10"
                >
                <div class="min-w-0 leading-tight">
                    <div class="text-sm font-semibold text-white sm:text-base">PT. Sidoagung Farm</div>
                    <div class="text-[11px] text-white/80 sm:text-xs">Menjadi Tuan Rumah Di Negeri Sendiri</div>
                </div>
            </a>

            <nav class="flex w-full flex-wrap items-center justify-center gap-1 text-xs sm:w-auto sm:flex-nowrap sm:justify-end sm:text-sm">
                <a href="{{ route('home') }}" class="rounded-lg px-2.5 py-1.5 font-semibold text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50 sm:px-3 sm:py-2">Beranda</a>
                <a href="{{ route('home') }}#katalog" class="rounded-lg px-2.5 py-1.5 font-semibold text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50 sm:px-3 sm:py-2">Katalog</a>
                <a href="{{ route('products.index') }}" class="rounded-lg px-2.5 py-1.5 font-semibold text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50 sm:px-3 sm:py-2">Produk</a>
                @auth
                    @if($canAccessAdminNav)
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-2.5 py-1.5 font-semibold text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50 sm:px-3 sm:py-2">Admin</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="flex-1">
        <div class="catalog-main mx-auto max-w-6xl px-4 py-4 sm:px-6 sm:py-6">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>

    <footer class="mt-auto border-t border-white/20 bg-[#1b5e20] text-white">
        <div class="catalog-footer mx-auto max-w-6xl px-4 py-5 text-xs text-white sm:px-6">
            &copy; {{ now()->year }} Sidoagung Farm - Product Catalog
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/preline@2.5.0/dist/preline.min.js"></script>
    @stack('scripts')
</body>
</html>
