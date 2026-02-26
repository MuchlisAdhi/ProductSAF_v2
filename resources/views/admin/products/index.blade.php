@extends('layouts.admin')

@section('content')
    @include('admin.partials.hero', [
        'badge' => 'Product Management',
        'title' => 'Manajemen Produk',
        'subtitle' => 'Kelola produk dan filter data katalog.',
    ])

    <div class="card border-0 shadow mb-4">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h2 class="fs-5 fw-bold mb-0">Daftar Produk</h2>
            <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary">
                Tambah Produk
            </a>
        </div>
        <div class="card-body border-bottom">
            <form method="GET" class="row g-3">
                <div class="col-12 col-lg-3">
                    <label class="form-label">Cari</label>
                    <input type="text" name="q" value="{{ $query }}" class="form-control" placeholder="Kode, nama, kategori">
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($categoryOptions as $category)
                            <option value="{{ $category->id }}" @selected($categoryFilter === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label">Warna Karung</label>
                    <select name="sackColor" class="form-select">
                        <option value="">Semua Warna</option>
                        @foreach($sackColorOptions as $color)
                            @php
                                $colorLabel = match (\Illuminate\Support\Str::lower((string) $color)) {
                                    'orange', 'oranye' => 'Oranye',
                                    'pink', 'merah muda' => 'Merah Muda',
                                    default => $color,
                                };
                            @endphp
                            <option value="{{ $color }}" @selected($sackColorFilter === $color)>{{ $colorLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label">Urutkan</label>
                    <select name="sort" class="form-select">
                        <option value="latest" @selected($sort === 'latest')>Terbaru</option>
                        <option value="code_asc" @selected($sort === 'code_asc')>Kode A-Z</option>
                        <option value="code_desc" @selected($sort === 'code_desc')>Kode Z-A</option>
                        <option value="name_asc" @selected($sort === 'name_asc')>Nama A-Z</option>
                        <option value="name_desc" @selected($sort === 'name_desc')>Nama Z-A</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-1">
                    <label class="form-label">Rows</label>
                    <select name="pageSize" class="form-select">
                        @foreach([5,10,20,50,100] as $size)
                            <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-lg-2 d-flex align-items-end justify-content-lg-end gap-2">
                    <input type="hidden" name="page" value="1">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="card-body border-bottom d-flex justify-content-between align-items-center">
            <small class="text-muted">Menampilkan {{ $filteredCount }} dari {{ $totalCount }} produk</small>
            <button type="button" id="bulk-delete-button" class="btn btn-sm btn-danger">
                <i class="bi bi-trash me-1"></i>Hapus Terpilih
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-centered table-nowrap mb-0 rounded">
                <thead class="thead-light">
                    <tr>
                        <th class="border-0">
                            <input type="checkbox" id="check-all-products">
                        </th>
                        <th class="border-0">No</th>
                        <th class="border-0">Kode</th>
                        <th class="border-0">Nama</th>
                        <th class="border-0">Kategori</th>
                        <th class="border-0">Warna</th>
                        <th class="border-0">Nutrisi</th>
                        <th class="border-0">Dibuat</th>
                        <th class="border-0 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $index => $product)
                        <tr>
                            <td>
                                <input type="checkbox" value="{{ $product->id }}" class="bulk-product-checkbox">
                            </td>
                            <td>{{ ($products->currentPage() - 1) * $products->perPage() + $index + 1 }}</td>
                            <td><span class="fw-bold text-primary">{{ $product->code }}</span></td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td><x-sack-color-badge :color="$product->sack_color" class="px-2 py-1" /></td>
                            <td>{{ $product->nutritions_count }}</td>
                            <td>{{ $product->created_at?->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" class="d-inline-block" onsubmit="return confirm('Hapus produk ini?')">
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
                            <td colspan="9" class="text-center py-4">Tidak ada produk ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer px-3 border-0 d-flex flex-column flex-lg-row justify-content-between align-items-center">
            <small class="fw-normal small mb-3 mb-lg-0">Halaman {{ $products->currentPage() }} dari {{ $products->lastPage() }}</small>
            {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <form id="bulk-delete-form" method="POST" action="{{ route('admin.products.bulk-delete') }}" class="d-none">
        @csrf
        <div id="bulk-delete-inputs"></div>
    </form>
@endsection

@push('scripts')
    <script>
        (() => {
            const button = document.getElementById('bulk-delete-button');
            const form = document.getElementById('bulk-delete-form');
            const container = document.getElementById('bulk-delete-inputs');
            const checkAll = document.getElementById('check-all-products');
            if (!button || !form || !container) return;

            checkAll?.addEventListener('change', () => {
                document.querySelectorAll('.bulk-product-checkbox').forEach((checkbox) => {
                    checkbox.checked = checkAll.checked;
                });
            });

            button.addEventListener('click', () => {
                const checked = Array.from(document.querySelectorAll('.bulk-product-checkbox:checked'));
                if (checked.length === 0) {
                    alert('Pilih setidaknya satu produk untuk dihapus.');
                    return;
                }
                if (!confirm('Hapus produk yang dipilih?')) return;

                container.innerHTML = '';
                checked.forEach((checkbox) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = checkbox.value;
                    container.appendChild(input);
                });
                form.submit();
            });
        })();
    </script>
@endpush
