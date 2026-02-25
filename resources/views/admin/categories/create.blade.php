@extends('layouts.admin')

@section('content')
    @php
        $normalizeIcon = static function (?string $value): string {
            return \Illuminate\Support\Str::of((string) $value)
                ->trim()
                ->replace(['_', '.'], '-')
                ->kebab()
                ->lower()
                ->toString();
        };

        $iconLookup = collect($lucideIcons)
            ->map(fn ($iconName) => $normalizeIcon((string) $iconName))
            ->filter()
            ->unique()
            ->mapWithKeys(fn ($iconName) => [$iconName => $iconName]);

        $defaultIcon = (string) $iconLookup->get('box', (string) $iconLookup->first() ?: 'box');
    @endphp

    @include('admin.partials.hero', [
        'badge' => 'Category Management',
        'title' => 'Tambah Kategori',
        'subtitle' => 'Buat kategori baru untuk katalog produk.',
    ])

    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.store') }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Urutan Nomor</label>
                    <input type="number" min="0" step="1" name="order_number" class="form-control" value="{{ old('order_number', 0) }}" required>
                </div>
                <div class="col-12">
                    @include('admin.categories.partials.icon-picker', [
                        'fieldId' => 'create-category-icon',
                        'inputName' => 'icon',
                        'currentIcon' => old('icon', $defaultIcon),
                        'lucideIcons' => $lucideIcons,
                    ])
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Buat Kategori</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    @include('admin.categories.partials.icon-modal', ['lucideIcons' => $lucideIcons])
@endsection
