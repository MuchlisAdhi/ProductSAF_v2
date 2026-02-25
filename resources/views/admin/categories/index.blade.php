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
        'title' => 'Manajemen Kategori',
        'subtitle' => 'Lihat, filter, dan kelola kategori produk.',
    ])

    <div class="card border-0 shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="fs-5 fw-bold mb-0">Daftar Kategori</h2>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary">
                Tambah Kategori
            </a>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="q" value="{{ $query }}" class="form-control" placeholder="Nama kategori atau ikon">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ikon</label>
                    <select name="icon" class="form-select">
                        <option value="">Semua Ikon</option>
                        @foreach($iconOptions as $icon)
                            @php
                                $normalizedIcon = $normalizeIcon((string) $icon);
                            @endphp
                            <option value="{{ $icon }}" @selected($iconFilter === $icon)>{{ $normalizedIcon !== '' ? $normalizedIcon : 'legacy icon' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Rows</label>
                    <select name="pageSize" class="form-select">
                        @foreach([5,10,20,50,100] as $size)
                            <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <input type="hidden" name="page" value="1">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-centered table-nowrap mb-0 rounded">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Urutan</th>
                        <th>Ikon</th>
                        <th>Produk</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $category)
                        <tr>
                            <td>{{ ($categories->currentPage() - 1) * $categories->perPage() + $index + 1 }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->order_number }}</td>
                            <td>
                                @php
                                    $resolvedIcon = (string) $iconLookup->get($normalizeIcon((string) $category->icon), $defaultIcon);
                                    $resolvedIconUrl = route('admin.lucide-icons.svg', ['name' => $resolvedIcon]);
                                @endphp
                                <span class="d-inline-flex align-items-center gap-2 px-2 py-1 rounded-pill border bg-light text-dark">
                                    <img
                                        src="{{ $resolvedIconUrl }}"
                                        alt="{{ $resolvedIcon }}"
                                        width="16"
                                        height="16"
                                        class="flex-shrink-0"
                                        loading="lazy"
                                    >
                                    <span class="small text-lowercase">{{ $resolvedIcon }}</span>
                                </span>
                            </td>
                            <td>{{ $category->products_count }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" class="d-inline-block" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">Tidak ada kategori ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer px-3 border-0 d-flex flex-column flex-lg-row justify-content-between align-items-center">
            <small class="fw-normal small mb-3 mb-lg-0">Menampilkan {{ $filteredCount }} dari {{ $totalCount }} kategori</small>
            {{ $categories->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>

@endsection
