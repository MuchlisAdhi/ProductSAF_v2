@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        @include('admin.partials.hero', [
            'badge' => 'Product Management',
            'title' => 'Manajemen Produk',
            'subtitle' => 'Kelola data produk, filter, dan bulk action.',
        ])

        <x-admin.module variant="table">
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-900">Daftar Produk</h2>
                    <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Tambah Produk</a>
                </div>
            </x-slot:header>

            <form method="GET" class="grid gap-3 border-b border-slate-200 bg-white p-4 sm:grid-cols-5 sm:p-6">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Cari</label>
                    <input type="text" name="q" value="{{ $query }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Cari kode, nama, kategori...">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Kategori</label>
                    <select name="category" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Semua Kategori</option>
                        @foreach($categoryOptions as $category)
                            <option value="{{ $category->id }}" @selected($categoryFilter === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Warna Karung</label>
                    <select name="sackColor" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
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
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Urutkan Berdasarkan</label>
                    <select name="sort" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="latest" @selected($sort === 'latest')>Terbaru</option>
                        <option value="code_asc" @selected($sort === 'code_asc')>Kode A-Z</option>
                        <option value="code_desc" @selected($sort === 'code_desc')>Kode Z-A</option>
                        <option value="name_asc" @selected($sort === 'name_asc')>Nama A-Z</option>
                        <option value="name_desc" @selected($sort === 'name_desc')>Nama Z-A</option>
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Jumlah Baris</label>
                    <select name="pageSize" class="w-32 rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @foreach([5,10,20,50,100] as $size)
                            <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end justify-end">
                    <input type="hidden" name="page" value="1">
                    <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Terapkan Filter</button>
                </div>
            </form>

            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <button type="button" id="bulk-delete-button" class="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">Hapus Terpilih</button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-emerald-700 text-white">
                            <th class="w-10 px-3 py-2 text-left text-xs font-semibold"></th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">No</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Kode</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Nama</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Kategori</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Warna Kemasan</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Nutrisi</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Dibuat</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $index => $product)
                            <tr class="border-t border-slate-200 bg-white">
                                <td class="px-3 py-2">
                                    <input type="checkbox" value="{{ $product->id }}" class="bulk-product-checkbox h-4 w-4 rounded border-slate-300 text-emerald-700">
                                </td>
                                <td class="px-3 py-2 text-sm text-slate-600">{{ ($products->currentPage() - 1) * $products->perPage() + $index + 1 }}</td>
                                <td class="px-3 py-2 text-sm font-semibold text-emerald-700">{{ $product->code }}</td>
                                <td class="px-3 py-2 text-sm text-slate-900">{{ $product->name }}</td>
                                <td class="px-3 py-2 text-sm text-slate-700">{{ $product->category->name }}</td>
                                <td class="px-3 py-2 text-sm text-slate-700">
                                    <x-sack-color-badge :color="$product->sack_color" class="px-2 py-0.5" />
                                </td>
                                <td class="px-3 py-2 text-sm text-slate-700">{{ $product->nutritions_count }}</td>
                                <td class="px-3 py-2 text-sm text-slate-600">{{ optional($product->created_at)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100" aria-label="Ubah produk {{ $product->name }}">
                                            <x-lucide-pencil class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" onsubmit="return confirm('Hapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-300 text-red-700 hover:bg-red-50" aria-label="Hapus produk {{ $product->name }}">
                                                <x-lucide-trash-2 class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-sm text-slate-600">Tidak ada produk ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-slot:footer>
                <div class="w-full">
                    {{ $products->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
            </x-slot:footer>
        </x-admin.module>
    </div>

    <form id="bulk-delete-form" method="POST" action="{{ route('admin.products.bulk-delete') }}" class="hidden">
        @csrf
        <div id="bulk-delete-inputs"></div>
    </form>

    <script>
        (() => {
            const button = document.getElementById('bulk-delete-button');
            const form = document.getElementById('bulk-delete-form');
            const container = document.getElementById('bulk-delete-inputs');
            if (!button || !form || !container) return;

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
@endsection
