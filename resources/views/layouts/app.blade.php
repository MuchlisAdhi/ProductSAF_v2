<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $resolvedTitle = trim((string) ($metaTitle ?? $title ?? 'PT. Sidoagung Farm'));
        $resolvedDescription = trim((string) ($metaDescription ?? 'Katalog Produk'));
        $metaImagePath = trim((string) ($metaImage ?? '/images/og/saf-katalog-og.png'));
        $canonicalBaseUrl = rtrim((string) config('app.url', ''), '/');
        $requestUri = request()->getRequestUri();
        $resolvedUrl = trim((string) ($metaUrl ?? ($canonicalBaseUrl !== '' ? $canonicalBaseUrl.$requestUri : url()->full())));
        $resolvedImage = str_starts_with($metaImagePath, 'http://') || str_starts_with($metaImagePath, 'https://')
            ? $metaImagePath
            : ($canonicalBaseUrl !== '' ? $canonicalBaseUrl.'/'.ltrim($metaImagePath, '/') : url($metaImagePath));
        $resolvedTwitterCard = trim((string) ($twitterCard ?? 'summary_large_image'));
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1b5e20">
    <title>{{ $resolvedTitle }}</title>
    <meta name="description" content="{{ $resolvedDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PT. Sidoagung Farm">
    <meta property="og:title" content="{{ $resolvedTitle }}">
    <meta property="og:description" content="{{ $resolvedDescription }}">
    <meta property="og:url" content="{{ $resolvedUrl }}">
    <meta property="og:image" content="{{ $resolvedImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="{{ $resolvedTwitterCard }}">
    <meta name="twitter:title" content="{{ $resolvedTitle }}">
    <meta name="twitter:description" content="{{ $resolvedDescription }}">
    <meta name="twitter:image" content="{{ $resolvedImage }}">
    <link rel="canonical" href="{{ $resolvedUrl }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="serviceworker" href="/service-worker.js" scope="/">
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
    <div
        id="admin-hidden-login-menu"
        class="pointer-events-none fixed inset-0 z-[90] opacity-0 transition duration-200"
        aria-hidden="true"
    >
        <div id="admin-hidden-login-backdrop" class="absolute inset-0 bg-slate-900/40"></div>
        <div class="absolute inset-x-0 bottom-0 p-3 sm:p-4">
            <div class="mx-auto w-full max-w-md rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-900">Menu Admin</p>
                    <button
                        id="admin-hidden-login-close"
                        type="button"
                        class="rounded-lg px-2 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                        aria-label="Tutup menu admin"
                    >
                        Tutup
                    </button>
                </div>
                <p class="mb-3 text-xs text-slate-600">Akses form login untuk kelola kategori dan produk.</p>
                <a
                    id="admin-hidden-login-link"
                    href="{{ route('login', ['next' => '/admin']) }}"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600"
                >
                    Login Admin
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/preline@2.5.0/dist/preline.min.js"></script>
    <script>
        // Keep SW registration visible in HTML for analyzers that don't parse external JS.
        (() => {
            if (!('serviceWorker' in navigator)) return;
            window.__SAF_SW_BOOTSTRAP_INLINE__ = true;
            navigator.serviceWorker.register('/service-worker.js', { scope: '/' }).catch(() => null);
        })();
    </script>
    <script src="{{ asset('js/pwa-register.js') }}"></script>
    <script>
        (() => {
            const trigger = document.getElementById('public-brand-trigger');
            const hiddenMenu = document.getElementById('admin-hidden-login-menu');
            const hiddenMenuBackdrop = document.getElementById('admin-hidden-login-backdrop');
            const hiddenMenuClose = document.getElementById('admin-hidden-login-close');
            const hiddenMenuLink = document.getElementById('admin-hidden-login-link');
            if (!trigger || !hiddenMenu || !hiddenMenuBackdrop || !hiddenMenuClose || !hiddenMenuLink) return;
            const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
            const triggerPath = new URL(trigger.href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
            const isHomeTapUnlockContext = currentPath === triggerPath;

            const requiredTaps = 7;
            const tapWindowMs = 8000;
            const desktopMedia = '(min-width: 768px)';
            let tapCount = 0;
            let firstTapAt = 0;
            let bodyOverflowBeforeMenu = '';

            const isDesktop = () => window.matchMedia(desktopMedia).matches;

            const closeMenu = () => {
                hiddenMenu.classList.add('opacity-0', 'pointer-events-none');
                hiddenMenu.classList.remove('opacity-100');
                hiddenMenu.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = bodyOverflowBeforeMenu;
            };

            const openMenu = () => {
                bodyOverflowBeforeMenu = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                hiddenMenu.classList.remove('opacity-0', 'pointer-events-none');
                hiddenMenu.classList.add('opacity-100');
                hiddenMenu.setAttribute('aria-hidden', 'false');
            };

            trigger.addEventListener('click', (event) => {
                if (isHomeTapUnlockContext) {
                    // On home page, avoid reload so multi-tap counter can reach 7 taps.
                    event.preventDefault();
                }

                const now = Date.now();
                if (firstTapAt === 0 || now - firstTapAt > tapWindowMs) {
                    firstTapAt = now;
                    tapCount = 1;
                    return;
                }

                tapCount += 1;

                if (tapCount >= requiredTaps) {
                    if (isDesktop()) {
                        window.location.assign(hiddenMenuLink.href);
                    } else {
                        openMenu();
                    }
                    tapCount = 0;
                    firstTapAt = 0;
                }
            });

            hiddenMenuBackdrop.addEventListener('click', closeMenu);
            hiddenMenuClose.addEventListener('click', closeMenu);
            hiddenMenuLink.addEventListener('click', closeMenu);

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && hiddenMenu.getAttribute('aria-hidden') === 'false') {
                    closeMenu();
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
