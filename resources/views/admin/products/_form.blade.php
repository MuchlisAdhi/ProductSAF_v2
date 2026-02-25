@php
    $selectedSackColor = match (\Illuminate\Support\Str::lower((string) old('sack_color', $product->sack_color ?? ''))) {
        'orange', 'oranye' => 'Oranye',
        'pink', 'merah muda' => 'Merah Muda',
        default => old('sack_color', $product->sack_color ?? ''),
    };
    $selectedCategoryId = old('category_id', $product->category_id ?? ($categories->first()->id ?? ''));
    $initialImagePath = (string) ($product?->image?->system_path ?? '');
    $initialImageLabel = (string) ($product?->image?->original_file_name ?? 'Gambar saat ini');
    $removeImageFlag = old('remove_image', '0') === '1';
    $showInitialImage = $initialImagePath !== '' && ! $removeImageFlag;
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="card border-0 shadow mb-4">
        <div class="card-header">
            <h2 class="fs-5 fw-bold mb-0">Konfigurasi Produk</h2>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Kode</label>
                    <input name="code" value="{{ old('code', $product->code ?? '') }}" class="form-control" placeholder="SA 571 NS" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Feed Form</label>
                    <input name="name" value="{{ old('name', $product->name ?? '') }}" class="form-control" placeholder="Pakan Starter Broiler" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Keterangan</label>
                    <textarea name="description" rows="3" class="form-control" placeholder="Keterangan produk" required>{{ old('description', $product->description ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Warna Karung</label>
                    <select name="sack_color" class="form-select" required>
                        <option value="">Pilih warna karung</option>
                        @foreach($sackColors as $color)
                            <option value="{{ $color }}" @selected($selectedSackColor === $color)>{{ $color }}</option>
                        @endforeach
                        @if($selectedSackColor && !in_array($selectedSackColor, $sackColors, true))
                            <option value="{{ $selectedSackColor }}" selected>{{ $selectedSackColor }} (Legacy)</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected($selectedCategoryId === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Gambar Produk</label>
                    <div class="border rounded p-3 bg-light">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <label for="product-image-input" class="btn btn-sm btn-primary mb-0">
                                <i class="bi bi-upload me-1"></i>Unggah gambar
                            </label>
                            <input id="product-image-input" type="file" name="image" accept="image/*" class="d-none">

                            <button
                                id="remove-image-button"
                                type="button"
                                class="btn btn-sm btn-outline-danger {{ $showInitialImage ? '' : 'd-none' }}"
                            >
                                <i class="bi bi-trash me-1"></i>Hapus gambar
                            </button>

                            <input id="remove-image-flag" type="hidden" name="remove_image" value="{{ $removeImageFlag ? '1' : '0' }}">
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-3">
                            <div class="border rounded p-2 bg-white d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                <img
                                    id="product-image-preview"
                                    src="{{ $showInitialImage ? $initialImagePath : '' }}"
                                    alt="Product image preview"
                                    data-initial-src="{{ $initialImagePath }}"
                                    data-initial-label="{{ $initialImageLabel }}"
                                    class="{{ $showInitialImage ? '' : 'd-none' }} img-fluid"
                                    style="max-height: 100%; object-fit: cover;"
                                    loading="lazy"
                                >
                                <p id="product-image-empty-state" class="{{ $showInitialImage ? 'd-none' : '' }} small text-muted mb-0 text-center">Tidak ada gambar</p>
                            </div>
                            <div>
                                <p id="product-image-name" class="mb-1 fw-semibold">
                                    {{ $showInitialImage ? $initialImageLabel : 'Tidak ada gambar yang diunggah.' }}
                                </p>
                                <p id="product-image-helper" class="small text-muted mb-0">JPG/PNG/WebP, max 10MB.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fs-5 fw-bold mb-0">Daftar Nutrisi</h2>
                <small class="text-muted">Tambah satu atau lebih parameter nutrisi.</small>
            </div>
            <button type="button" id="add-nutrition-row" class="btn btn-sm btn-outline-primary">
                Tambah Baris
            </button>
        </div>
        <div class="table-responsive">
            <table class="table mb-0" id="nutrition-table">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 72px;">No</th>
                        <th>Parameter</th>
                        <th>Nilai</th>
                        <th class="text-center" style="width: 88px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="nutrition-table-body">
                    @foreach($nutritionRows as $index => $nutrition)
                        <tr>
                            <td class="row-number">{{ $index + 1 }}</td>
                            <td>
                                <input name="nutritions[{{ $index }}][label]" value="{{ $nutrition['label'] }}" class="form-control" required>
                            </td>
                            <td>
                                <input name="nutritions[{{ $index }}][value]" value="{{ $nutrition['value'] }}" class="form-control" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="remove-nutrition-row btn btn-sm btn-outline-danger" aria-label="Hapus baris nutrisi">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Kembali</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
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
                removeImageButton.classList.toggle('d-none', !visible);
            };

            const showImage = (src, label) => {
                imagePreview.src = src;
                imagePreview.classList.remove('d-none');
                imageEmptyState.classList.add('d-none');
                imageName.textContent = label;
                imageHelper.textContent = 'JPG/PNG/WebP, max 10MB.';
                setRemoveButtonVisible(true);
            };

            const showEmptyState = (label, helper, showRemoveButton = false) => {
                imagePreview.classList.add('d-none');
                imagePreview.removeAttribute('src');
                imageEmptyState.classList.remove('d-none');
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

            if (!imagePreview.classList.contains('d-none') && imagePreview.getAttribute('src')) {
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
@endpush
