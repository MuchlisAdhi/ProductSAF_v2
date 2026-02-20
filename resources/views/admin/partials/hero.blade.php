@php
    $badge = (string) ($badge ?? 'Admin Panel');
    $title = (string) ($title ?? 'Admin Dashboard');
    $subtitle = (string) ($subtitle ?? 'Kelola data katalog produk.');
@endphp

<section class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-emerald-50 via-white to-amber-50 p-6 shadow-sm sm:p-8">
    <p class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-slate-800">{{ $badge }}</p>
    <h1 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1>
    <p class="mt-2 max-w-2xl text-sm text-slate-600 sm:text-base">{{ $subtitle }}</p>
</section>
