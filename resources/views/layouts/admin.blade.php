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
            <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-2.5 sm:gap-3.5" aria-label="Sidoagung Admin Home">
                <img
                    src="{{ asset('images/logo/saf-logo.png') }}"
                    alt="Logo Sidoagung"
                    class="h-11 w-11 shrink-0 object-contain sm:h-14 sm:w-12"
                >
                <div class="min-w-0">
                    <div class="text-[1.45rem] leading-[1.04] tracking-[0.022em] text-white sm:text-[2rem]" style="font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;">PT. Sidoagung Farm</div>
                    <div class="mt-0.5 text-[0.98rem] leading-[1.06] tracking-[0.014em] text-white/90 sm:text-[1.2rem]" style="font-family: 'Brush Script MT', 'Segoe Script', cursive;">Menjadi Tuan Rumah Di Negeri Sendiri</div>
                </div>
            </a>
            <div class="flex flex-wrap items-center gap-1 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Dashboard</a>
                <a href="{{ route('admin.products.index') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Produk</a>
                <a href="{{ route('admin.categories.index') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Kategori</a>
                @php
                    $authRole = auth()->user()?->role;
                    $authRoleValue = $authRole instanceof \App\Enums\Role ? $authRole->value : (string) $authRole;
                @endphp
                @if($authRoleValue === 'SUPERADMIN')
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Pengguna</a>
                @endif
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 font-medium text-white hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white/50">Publik</a>
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
            &copy; {{ now()->year }} Sidoagung Farm - Katalog Produk
        </div>
    </footer>
</body>
</html>
