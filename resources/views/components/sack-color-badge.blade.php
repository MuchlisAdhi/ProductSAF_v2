@props([
    'color' => '',
    'class' => '',
    'variant' => 'solid',
])

@php
    $value = trim((string) $color);
    $normalized = \Illuminate\Support\Str::lower($value);
    $displayValue = match ($normalized) {
        'orange', 'oranye' => 'Oranye',
        'pink', 'merah muda' => 'Merah Muda',
        default => $value,
    };

    $tone = 'gray';
    if (in_array($normalized, ['merah', 'red'], true)) {
        $tone = 'red';
    } elseif (in_array($normalized, ['biru', 'blue'], true)) {
        $tone = 'blue';
    } elseif (in_array($normalized, ['hijau', 'green'], true)) {
        $tone = 'emerald';
    } elseif (in_array($normalized, ['orange', 'oranye'], true)) {
        $tone = 'orange';
    } elseif (in_array($normalized, ['pink', 'merah muda'], true)) {
        $tone = 'pink';
    }

    $isOutline = \Illuminate\Support\Str::lower((string) $variant) === 'outline';
    [$background, $textColor, $borderColor] = match ($tone) {
        'red' => ['#fee2e2', '#b91c1c', '#ef4444'],
        'blue' => ['#dbeafe', '#1d4ed8', '#3b82f6'],
        'emerald' => ['#d1fae5', '#047857', '#10b981'],
        'orange' => ['#ffedd5', '#c2410c', '#f97316'],
        'pink' => ['#fce7f3', '#be185d', '#ec4899'],
        default => ['#e5e7eb', '#374151', '#9ca3af'],
    };

    $style = $isOutline
        ? "border:1px solid {$borderColor};background:#fff;color:{$textColor};"
        : "background:{$background};color:{$textColor};border:1px solid rgba(0,0,0,.05);";

    $resolvedClass = trim((string) $class);
@endphp

<span
    {{ $attributes->class("badge rounded-pill fw-semibold {$resolvedClass}") }}
    style="{{ $style }}"
>
    {{ $displayValue !== '' ? $displayValue : '-' }}
</span>
