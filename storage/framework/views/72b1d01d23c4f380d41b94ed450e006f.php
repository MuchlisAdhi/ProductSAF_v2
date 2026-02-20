<?php
    use BladeUI\Icons\Exceptions\SvgNotFound;

    $iconValue = trim((string) ($icon ?? ''));
    $resolvedClass = (string) ($iconClass ?? ($imgClass ?? 'h-5 w-5 text-emerald-700'));

    $normalizeIcon = static function (string $value): string {
        $normalized = \Illuminate\Support\Str::of(trim($value))
            ->replace(['_', '.'], '-')
            ->kebab()
            ->lower()
            ->toString();

        return $normalized !== '' ? $normalized : 'box';
    };

    $resolvedIcon = $normalizeIcon($iconValue);

    try {
        $iconSvg = svg('lucide-'.$resolvedIcon, $resolvedClass)->toHtml();
    } catch (SvgNotFound) {
        $iconSvg = svg('lucide-box', $resolvedClass)->toHtml();
    }
?>

<?php echo $iconSvg; ?>

<?php /**PATH D:\productsaf-laravel\resources\views/partials/category-icon.blade.php ENDPATH**/ ?>