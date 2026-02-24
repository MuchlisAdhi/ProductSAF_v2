@php
    $selectedSackColor = match (\Illuminate\Support\Str::lower((string) old('sack_color', $product->sack_color ?? ''))) {
        'orange', 'oranye' => 'Oranye',
        'pink', 'merah muda' => 'Merah Muda',
        default => old('sack_color', $product->sack_color ?? ''),
    };
    $selectedCategoryId = old('category_id', $product->category_id ?? ($categories->first()->id ?? ''));
    $fieldClass = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-emerald-500/40 focus:outline-none focus:ring-2 focus:ring-amber-200/80';
    $initialImagePath = (string) ($product?->image?->system_path ?? '');
    $initialImageLabel = (string) ($product?->image?->original_file_name ?? 'Gambar saat ini');
    $removeImageFlag = old('remove_image', '0') === '1';
    $showInitialImage = $initialImagePath !== '' && ! $removeImageFlag;
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <x-admin.module variant="table">
        <x-slot:header>
            <h2 class="text-base font-semibold text-slate-900">Konfigurasi Produk</h2>
            <p class="text-xs text-slate-600">Isi identitas produk, kategori, dan gambar.</p>
        </x-slot:header>

        <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-6">
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Kode</label>
                <input name="code" value="{{ old('code', $product->code ?? '') }}" class="{{ $fieldClass }}" placeholder="SA 571 NS" required>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Feed Form</label>
                <input name="name" value="{{ old('name', $product->name ?? '') }}" class="{{ $fieldClass }}" placeholder="Pakan Starter Broiler" required>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-slate-700">Keterangan</label>
                <textarea name="description" rows="3" class="{{ $fieldClass }}" placeholder="Keterangan produk" required>{{ old('description', $product->description ?? '') }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Warna Karung</label>
                <select name="sack_color" class="{{ $fieldClass }}" required>
                    <option value="">Pilih warna karung</option>
                    @foreach($sackColors as $color)
                        <option value="{{ $color }}" @selected($selectedSackColor === $color)>{{ $color }}</option>
                    @endforeach
                    @if($selectedSackColor && !in_array($selectedSackColor, $sackColors, true))
                        <option value="{{ $selectedSackColor }}" selected>{{ $selectedSackColor }} (Legacy)</option>
                    @endif
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Kategori</label>
                <select name="category_id" class="{{ $fieldClass }}" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($selectedCategoryId === $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-xs font-semibold text-slate-700">Gambar Produk</label>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <label for="product-image-input" class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                            <x-lucide-upload class="h-4 w-4" />
                            Unggah gambar
                        </label>
                        <input id="product-image-input" type="file" name="image" accept="image/*" class="hidden">

                        <button
                            id="remove-image-button"
                            type="button"
                            class="{{ $showInitialImage ? '' : 'hidden' }} inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-red-50 hover:text-red-700"
                        >
                            <x-lucide-trash-2 class="h-4 w-4" />
                            Hapus gambar
                        </button>

                        <input id="remove-image-flag" type="hidden" name="remove_image" value="{{ $removeImageFlag ? '1' : '0' }}">
                    </div>

                    <div class="mt-3 flex items-center gap-3">
                        <div class="grid h-24 w-24 place-items-center overflow-hidden rounded-lg border border-slate-200 bg-white">
                            <img
                                id="product-image-preview"
                                src="{{ $showInitialImage ? $initialImagePath : '' }}"
                                alt="Product image preview"
                                data-initial-src="{{ $initialImagePath }}"
                                data-initial-label="{{ $initialImageLabel }}"
                                class="{{ $showInitialImage ? '' : 'hidden' }} h-full w-full object-cover"
                                loading="lazy"
                            >
                            <p id="product-image-empty-state" class="{{ $showInitialImage ? 'hidden' : '' }} px-2 text-center text-[11px] font-medium text-slate-500">Tidak ada gambar</p>
                        </div>
                        <div class="min-w-0">
                            <p id="product-image-name" class="truncate text-xs font-semibold text-slate-700">
                                {{ $showInitialImage ? $initialImageLabel : 'Tidak ada gambar yang diunggah.' }}
                            </p>
                            <p id="product-image-helper" class="text-xs text-slate-500">
                                JPG/PNG/WebP, max 10MB.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-admin.module>

    <x-admin.module variant="table">
        <x-slot:header>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Daftar Nutrisi</h2>
                    <p class="text-xs text-slate-600">Tambah satu atau lebih parameter nutrisi dalam format tabel.</p>
                </div>
                <button type="button" id="add-nutrition-row" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">
                    <x-lucide-plus class="h-4 w-4" />
                    Tambah Baris
                </button>
            </div>
        </x-slot:header>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-emerald-700 text-white">
                        <th class="w-16 px-3 py-2 text-left text-xs font-semibold">No</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold">Parameter</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold">Nilai</th>
                        <th class="w-20 px-3 py-2 text-center text-xs font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody id="nutrition-table-body">
                    @foreach($nutritionRows as $index => $nutrition)
                        <tr class="border-t border-slate-200 bg-white">
                            <td class="px-3 py-2 text-sm text-slate-600 row-number">{{ $index + 1 }}</td>
                            <td class="px-3 py-2">
                                <input name="nutritions[{{ $index }}][label]" value="{{ $nutrition['label'] }}" class="{{ $fieldClass }}" required>
                            </td>
                            <td class="px-3 py-2">
                                <input name="nutritions[{{ $index }}][value]" value="{{ $nutrition['value'] }}" class="{{ $fieldClass }}" required>
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" class="remove-nutrition-row inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-700" aria-label="Hapus baris nutrisi">
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.module>

    <div class="flex justify-end gap-2">
        <a href="{{ route('admin.products.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Kembali</a>
        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">{{ $submitLabel }}</button>
    </div>
</form>

<script>
    (() => {
        const tableBody = document.getElementById('nutrition-table-body');
        const addButton = document.getElementById('add-nutrition-row');
        const imageInput = document.getElementById('product-image-input');
        const imagePreview = document.getElementById('product-image-preview');
        const imageEmptyState = document.getElementById('product-image-empty-state');
        const imageName = document.getElementById('product-image-name');
        const imageHelper = document.getElementById('product-image-helper');
        const removeImageButton = document.getElementById('remove-image-button');
        const removeImageFlag = document.getElementById('remove-image-flag');

        if (!tableBody || !addButton) return;

        const refreshRows = () => {
            const rows = tableBody.querySelectorAll('tr');
            rows.forEach((row, index) => {
                const numberCell = row.querySelector('.row-number');
                if (numberCell) numberCell.textContent = String(index + 1);
                row.querySelectorAll('input').forEach((input) => {
                    input.name = input.name.replace(/nutritions\[\d+\]/, `nutritions[${index}]`);
                });
                const deleteButton = row.querySelector('.remove-nutrition-row');
                if (deleteButton) {
                    deleteButton.disabled = rows.length === 1;
                    deleteButton.classList.toggle('opacity-50', rows.length === 1);
                    deleteButton.classList.toggle('cursor-not-allowed', rows.length === 1);
                }
            });
        };

        addButton.addEventListener('click', () => {
            const templateRow = tableBody.querySelector('tr');
            if (!(templateRow instanceof HTMLTableRowElement)) return;

            const row = templateRow.cloneNode(true);
            if (!(row instanceof HTMLTableRowElement)) return;

            row.querySelectorAll('input').forEach((input) => {
                input.value = '';
            });
            tableBody.appendChild(row);
            refreshRows();
        });

        tableBody.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) return;
            const removeButton = target.closest('.remove-nutrition-row');
            if (!(removeButton instanceof HTMLButtonElement)) return;

            const rows = tableBody.querySelectorAll('tr');
            if (rows.length <= 1) return;
            const row = removeButton.closest('tr');
            if (row) row.remove();
            refreshRows();
        });

        refreshRows();

        if (!imageInput || !imagePreview || !imageEmptyState || !imageName || !imageHelper || !removeImageButton || !removeImageFlag) {
            return;
        }

        const hasInitialImage = String(imagePreview.dataset.initialSrc || '').trim() !== '';
        const initialImageSrc = String(imagePreview.dataset.initialSrc || '');
        const initialImageLabel = String(imagePreview.dataset.initialLabel || 'Gambar saat ini');
        let temporaryObjectUrl = null;

        const setRemoveButtonVisible = (visible) => {
            removeImageButton.classList.toggle('hidden', !visible);
        };

        const showImage = (src, label) => {
            imagePreview.src = src;
            imagePreview.classList.remove('hidden');
            imageEmptyState.classList.add('hidden');
            imageName.textContent = label;
            imageHelper.textContent = 'JPG/PNG/WebP, max 10MB.';
            setRemoveButtonVisible(true);
        };

        const showEmptyState = (label, helper, showRemoveButton = false) => {
            imagePreview.classList.add('hidden');
            imagePreview.removeAttribute('src');
            imageEmptyState.classList.remove('hidden');
            imageName.textContent = label;
            imageHelper.textContent = helper;
            setRemoveButtonVisible(showRemoveButton);
        };

        const releaseObjectUrl = () => {
            if (temporaryObjectUrl) {
                URL.revokeObjectURL(temporaryObjectUrl);
                temporaryObjectUrl = null;
            }
        };

        if (!imagePreview.classList.contains('hidden') && imagePreview.getAttribute('src')) {
            setRemoveButtonVisible(true);
        } else {
            showEmptyState('Belum ada gambar yang diunggah.', 'JPG/PNG/WebP, max 10MB.');
        }

        imageInput.addEventListener('change', () => {
            const file = imageInput.files && imageInput.files[0] ? imageInput.files[0] : null;
            if (!file) {
                if (hasInitialImage && removeImageFlag.value !== '1') {
                    showImage(initialImageSrc, initialImageLabel);
                } else {
                    showEmptyState('Belum ada gambar yang diunggah.', 'JPG/PNG/WebP, max 10MB.');
                }
                return;
            }

            releaseObjectUrl();
            temporaryObjectUrl = URL.createObjectURL(file);
            removeImageFlag.value = '0';
            showImage(temporaryObjectUrl, file.name);
        });

        removeImageButton.addEventListener('click', () => {
            imageInput.value = '';
            releaseObjectUrl();

            if (hasInitialImage) {
                removeImageFlag.value = '1';
                showEmptyState('Gambar akan dihapus saat menyimpan.', 'Klik Unggah untuk mengganti dengan gambar baru.');
            } else {
                removeImageFlag.value = '0';
                showEmptyState('Belum ada gambar yang diunggah.', 'JPG/PNG/WebP, max 10MB.');
            }
        });
    })();
</script>
