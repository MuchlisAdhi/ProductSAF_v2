@php
    $fieldId = (string) ($fieldId ?? 'icon');
    $inputName = (string) ($inputName ?? 'icon');

    $normalizeIcon = static function (?string $value): string {
        $normalized = \Illuminate\Support\Str::of((string) $value)
            ->trim()
            ->replace(['_', '.'], '-')
            ->kebab()
            ->lower()
            ->toString();

        return $normalized;
    };

    $iconOptions = collect($lucideIcons ?? [])
        ->map(fn ($iconName) => $normalizeIcon((string) $iconName))
        ->filter()
        ->unique()
        ->values();

    $defaultIcon = $iconOptions->firstWhere(fn ($icon) => $icon === 'box') ?? ((string) $iconOptions->first() ?: 'box');
    $currentIcon = trim((string) ($currentIcon ?? ''));
    $normalizedCurrentIcon = $normalizeIcon($currentIcon);
    $resolvedIcon = $iconOptions->contains($normalizedCurrentIcon) ? $normalizedCurrentIcon : $defaultIcon;
    $currentLabel = $resolvedIcon !== '' ? $resolvedIcon : 'no icon selected';
    $currentComponent = $resolvedIcon !== '' ? 'lucide-'.$resolvedIcon : null;
@endphp

<div>
    <label class="form-label">Ikon</label>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button
            type="button"
            data-open-icon-picker
            data-target-input="{{ $fieldId }}"
            class="btn btn-outline-secondary"
        >
            Pilih Lucide Icon
        </button>
        <input
            id="{{ $fieldId }}"
            name="{{ $inputName }}"
            value="{{ $resolvedIcon }}"
            class="form-control"
            style="max-width: 420px;"
            readonly
            required
        >
    </div>
    <div class="d-flex align-items-center gap-2 p-2 mt-2 border rounded bg-light">
        <div
            data-icon-preview
            data-for-input="{{ $fieldId }}"
            class="d-flex align-items-center justify-content-center border rounded bg-white"
            style="width: 40px; height: 40px;"
        >
            @if($currentComponent)
                <x-dynamic-component :component="$currentComponent" class="text-success" style="width:18px;height:18px;" />
            @else
                <span class="small text-muted">N/A</span>
            @endif
        </div>
        <p class="small mb-0 text-muted">
            <span class="fw-semibold text-dark" data-icon-label data-for-input="{{ $fieldId }}">{{ $currentLabel }}</span>
            <span class="ms-1">Nama ikon Lucide.</span>
        </p>
    </div>
</div>
