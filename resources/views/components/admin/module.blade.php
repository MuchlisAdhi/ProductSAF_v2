@props([
    'variant' => 'card',
    'bodyClass' => '',
    'headerClass' => '',
    'footerClass' => '',
])

@php
    $isTable = $variant === 'table';
    $wrapperClass = $isTable
        ? 'overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm'
        : 'rounded-2xl border border-slate-200 bg-white shadow-sm';
    $resolvedBodyClass = trim($bodyClass) !== ''
        ? trim($bodyClass)
        : ($isTable ? '' : 'p-5');
@endphp

<section {{ $attributes->class([$wrapperClass]) }}>
    @isset($header)
        <header class="border-b border-slate-200 bg-slate-50/60 px-4 py-3 sm:px-6 {{ $headerClass }}">
            {{ $header }}
        </header>
    @endisset

    @if($resolvedBodyClass !== '')
        <div class="{{ $resolvedBodyClass }}">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif

    @isset($footer)
        <div class="border-t border-slate-200 bg-slate-50/60 px-4 py-3 sm:px-6 {{ $footerClass }}">
            {{ $footer }}
        </div>
    @endisset
</section>
