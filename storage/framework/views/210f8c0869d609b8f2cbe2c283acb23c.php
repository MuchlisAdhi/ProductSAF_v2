<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'color' => '',
    'class' => '',
    'variant' => 'solid',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'color' => '',
    'class' => '',
    'variant' => 'solid',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $value = trim((string) $color);
    $normalized = \Illuminate\Support\Str::lower($value);

    $tone = 'slate';
    if (in_array($normalized, ['merah', 'red'], true)) {
        $tone = 'red';
    } elseif (in_array($normalized, ['biru', 'blue'], true)) {
        $tone = 'blue';
    } elseif (in_array($normalized, ['hijau', 'green'], true)) {
        $tone = 'emerald';
    } elseif (in_array($normalized, ['orange', 'oranye'], true)) {
        $tone = 'orange';
    } elseif ($normalized === 'pink') {
        $tone = 'pink';
    }

    $isOutline = \Illuminate\Support\Str::lower((string) $variant) === 'outline';
    $outlineStyle = '';

    if ($isOutline) {
        $outlineStyle = match ($tone) {
            'red' => 'border-color:#ef4444;color:#dc2626;background-color:rgba(255,255,255,0.92);',
            'blue' => 'border-color:#3b82f6;color:#1d4ed8;background-color:rgba(255,255,255,0.92);',
            'emerald' => 'border-color:#10b981;color:#047857;background-color:rgba(255,255,255,0.92);',
            'orange' => 'border-color:#f97316;color:#c2410c;background-color:rgba(255,255,255,0.92);',
            'pink' => 'border-color:#ec4899;color:#be185d;background-color:rgba(255,255,255,0.92);',
            default => 'border-color:#64748b;color:#334155;background-color:rgba(255,255,255,0.92);',
        };
    }

    $badgeClass = match ($tone) {
        'red' => $isOutline ? 'border bg-white' : 'bg-red-100 text-red-700 ring-red-200',
        'blue' => $isOutline ? 'border bg-white' : 'bg-blue-100 text-blue-700 ring-blue-200',
        'emerald' => $isOutline ? 'border bg-white' : 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'orange' => $isOutline ? 'border bg-white' : 'bg-orange-100 text-orange-700 ring-orange-200',
        'pink' => $isOutline ? 'border bg-white' : 'bg-pink-100 text-pink-700 ring-pink-200',
        default => $isOutline ? 'border bg-white' : 'bg-slate-100 text-slate-700 ring-slate-200',
    };

    $ringClass = $isOutline ? '' : 'ring-1';
    $resolvedClass = trim((string) $class);
?>

<span
    <?php echo e($attributes->class("inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {$ringClass} {$badgeClass} {$resolvedClass}")); ?>

    <?php if($outlineStyle !== ''): ?> style="<?php echo e($outlineStyle); ?>" <?php endif; ?>
>
    <?php echo e($value !== '' ? $value : '-'); ?>

</span>
<?php /**PATH D:\productsaf-laravel\resources\views/components/sack-color-badge.blade.php ENDPATH**/ ?>