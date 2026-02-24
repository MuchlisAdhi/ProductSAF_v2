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

    <div class="space-y-6">
        @include('admin.partials.hero', [
            'badge' => 'Category Management',
            'title' => 'Manajemen Kategori',
            'subtitle' => 'Buat, ubah, dan kelola urutan kategori produk.',
        ])

        <x-admin.module>
            <h2 class="text-base font-semibold text-slate-900">Buat Kategori</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Nama</label>
                    <input name="name" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" value="{{ old('name') }}" required>
                </div>
                <div class="sm:col-span-2">
                    @include('admin.categories.partials.icon-picker', [
                        'fieldId' => 'create-category-icon',
                        'inputName' => 'icon',
                        'currentIcon' => old('icon', $defaultIcon),
                        'lucideIcons' => $lucideIcons,
                    ])
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Urutan Nomor</label>
                    <input type="number" min="0" step="1" name="order_number" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" value="{{ old('order_number', 0) }}" required>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Buat Kategori</button>
                </div>
            </form>
        </x-admin.module>

        <x-admin.module variant="table">
            <x-slot:header>
                <h2 class="text-base font-semibold text-slate-900">Daftar Kategori</h2>
            </x-slot:header>

            <form method="GET" class="grid gap-3 border-b border-slate-200 bg-white p-4 sm:grid-cols-4 sm:p-6">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Cari</label>
                    <input type="text" name="q" value="{{ $query }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Cari nama kategori atau ikon...">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Ikon</label>
                    <select name="icon" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Semua Ikon</option>
                        @foreach($iconOptions as $icon)
                            @php
                                $normalizedIcon = $normalizeIcon((string) $icon);
                            @endphp
                            <option value="{{ $icon }}" @selected($iconFilter === $icon)>{{ $normalizedIcon !== '' ? $normalizedIcon : 'legacy icon' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <input type="hidden" name="page" value="1">
                    <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Terapkan Filter</button>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Rows</label>
                    <select name="pageSize" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @foreach([5,10,20,50,100] as $size)
                            <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-emerald-700 text-white">
                            <th class="px-3 py-2 text-left text-xs font-semibold">No</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Nama</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Urutan</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Ikon</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Produk</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $index => $category)
                            <tr class="border-t border-slate-200 bg-white">
                                <td class="px-3 py-2 text-sm text-slate-600">{{ ($categories->currentPage() - 1) * $categories->perPage() + $index + 1 }}</td>
                                <td class="px-3 py-2 text-sm font-medium text-slate-900">{{ $category->name }}</td>
                                <td class="px-3 py-2 text-sm text-slate-700">{{ $category->order_number }}</td>
                                <td class="px-3 py-2 text-sm text-slate-700">
                                    @php
                                        $resolvedIcon = (string) $iconLookup->get($normalizeIcon((string) $category->icon), $defaultIcon);
                                    @endphp
                                    <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-2.5 py-1">
                                        @include('partials.category-icon', [
                                            'icon' => $category->icon,
                                            'iconClass' => 'h-4 w-4 text-emerald-700',
                                        ])
                                        <span>{{ $resolvedIcon }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-sm text-slate-700">{{ $category->products_count }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100" aria-label="Ubah kategori {{ $category->name }}">
                                            <x-lucide-pencil class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-300 text-red-700 hover:bg-red-50" aria-label="Delete category {{ $category->name }}">
                                                <x-lucide-trash-2 class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-600">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-slot:footer>
                <div class="w-full">
                    {{ $categories->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
            </x-slot:footer>
        </x-admin.module>
    </div>

    @include('admin.categories.partials.icon-modal', ['lucideIcons' => $lucideIcons])
@endsection
