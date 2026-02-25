@php
    $title = (string) ($title ?? 'Dashboard');
    $subtitle = (string) ($subtitle ?? 'Kelola data katalog produk.');
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <h1 class="h3 mb-1">{{ $title }}</h1>
        <p class="mb-0 text-muted">{{ $subtitle }}</p>
    </div>
</div>
