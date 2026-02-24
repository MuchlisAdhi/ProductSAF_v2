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

<div class="space-y-2">
    <label class="mb-1 block text-xs font-semibold text-slate-700">Ikon</label>
    <div class="flex flex-wrap items-center gap-2">
        <button
            type="button"
            data-open-icon-picker
            data-target-input="{{ $fieldId }}"
            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100"
        >
            Pilih Lucide Ikon
        </button>
        <input
            id="{{ $fieldId }}"
            name="{{ $inputName }}"
            value="{{ $resolvedIcon }}"
            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm sm:max-w-lg"
            readonly
            required
        >
    </div>
    <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <div
            data-icon-preview
            data-for-input="{{ $fieldId }}"
            class="grid h-10 w-10 place-items-center overflow-hidden rounded-lg border border-slate-200 bg-white"
        >
            @if($currentComponent)
                <x-dynamic-component :component="$currentComponent" class="h-5 w-5 text-emerald-700" />
            @else
                <span class="text-[11px] font-semibold text-slate-500">N/A</span>
            @endif
        </div>
        <p class="text-xs text-slate-600">
            <span class="font-semibold text-slate-700" data-icon-label data-for-input="{{ $fieldId }}">{{ $currentLabel }}</span>
            <span class="ml-1">Nama ikonLucide.</span>
        </p>
    </div>
</div>
