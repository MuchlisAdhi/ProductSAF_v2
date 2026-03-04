<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1b5e20">
    <title>{{ $title ?? 'Sidoagung Farm Katalog Produk' }}</title>
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" href="{{ asset('images/logo/saf-logo-merah.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('images/logo/saf-logo-merah.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Kaushan+Script&display=swap" rel="stylesheet">
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
        <div class="mx-auto flex max-w-6xl flex-col gap-1.5 px-3 py-2 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <a
                id="public-brand-trigger"
                href="{{ route('home') }}"
                class="flex min-w-0 items-center gap-2.5 sm:gap-3.5"
                aria-label="Sidoagung Farm Home"
            >
                <img
                    src="{{ asset('images/logo/saf-logo.png') }}"
                    alt="Logo Sidoagung"
                    class="h-11 w-11 shrink-0 object-contain sm:h-14 sm:w-12"
                >
                <div class="min-w-0">
                    <div class="brand-title text-[1.45rem] leading-[1.04] tracking-[0.022em] text-white sm:text-[2rem]">PT. Sidoagung Farm</div>
                    <div class="brand-tagline mt-0.5 text-[0.98rem] leading-[1.06] tracking-[0.014em] text-white/90 sm:text-[1.2rem]">Menjadi Tuan Rumah Di Negeri Sendiri</div>
                </div>
            </a>

            <nav class="flex w-full flex-wrap items-center justify-center gap-1.5 text-[1rem] sm:w-auto sm:flex-nowrap sm:justify-end sm:text-[1.28rem]">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-1.5 font-semibold text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50 sm:px-3.5 sm:py-2">Beranda</a>
                <a href="{{ route('home') }}#katalog" class="rounded-lg px-3 py-1.5 font-semibold text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50 sm:px-3.5 sm:py-2">Katalog</a>
                <a href="{{ route('products.index') }}" class="rounded-lg px-3 py-1.5 font-semibold text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50 sm:px-3.5 sm:py-2">Produk</a>
                @auth
                    @if($canAccessAdminNav)
                        <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-1.5 font-semibold text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50 sm:px-3.5 sm:py-2">Admin</a>
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
            &copy; {{ now()->year }} Sidoagung Farm - Katalog Produk
        </div>
    </footer>
    <a
        id="admin-hidden-login"
        href="{{ route('login', ['next' => '/admin']) }}"
        class="pointer-events-none fixed bottom-4 left-4 z-50 rounded-full border border-emerald-200 bg-white/95 px-3 py-2 text-xs font-semibold text-emerald-800 shadow-lg opacity-0 transition-all duration-300 hover:bg-emerald-50"
        aria-label="Admin login"
        title="Admin login"
    >
        Admin Login
    </a>
    <script src="https://cdn.jsdelivr.net/npm/preline@2.5.0/dist/preline.min.js"></script>
    <script src="{{ asset('js/pwa-register.js') }}"></script>
    <script>
        (() => {
            const trigger = document.getElementById('public-brand-trigger');
            const hiddenButton = document.getElementById('admin-hidden-login');
            if (!trigger || !hiddenButton) return;

            const requiredTaps = 7;
            const tapWindowMs = 8000;
            const visibleMs = 15000;
            let tapCount = 0;
            let firstTapAt = 0;
            let hideTimer = null;

            const hideButton = () => {
                hiddenButton.classList.add('opacity-0', 'pointer-events-none');
                hiddenButton.classList.remove('opacity-100');
            };

            const showButton = () => {
                hiddenButton.classList.remove('opacity-0', 'pointer-events-none');
                hiddenButton.classList.add('opacity-100');

                if (hideTimer) {
                    window.clearTimeout(hideTimer);
                }

                hideTimer = window.setTimeout(() => {
                    hideButton();
                }, visibleMs);
            };

            trigger.addEventListener('click', () => {
                const now = Date.now();
                if (firstTapAt === 0 || now - firstTapAt > tapWindowMs) {
                    firstTapAt = now;
                    tapCount = 1;
                    return;
                }

                tapCount += 1;

                if (tapCount >= requiredTaps) {
                    showButton();
                    tapCount = 0;
                    firstTapAt = 0;
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
