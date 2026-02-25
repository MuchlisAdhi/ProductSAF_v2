@extends('layouts.admin')

@section('content')
    @include('admin.partials.hero', [
        'badge' => 'Category Management',
        'title' => 'Ubah Kategori',
        'subtitle' => 'Perbarui nama, ikon, dan urutan kategori.',
    ])

    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.update', $category->id) }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Urutan Nomor</label>
                    <input type="number" min="0" step="1" name="order_number" class="form-control" value="{{ old('order_number', $category->order_number) }}" required>
                </div>
                <div class="col-12">
                    @include('admin.categories.partials.icon-picker', [
                        'fieldId' => 'edit-category-icon',
                        'inputName' => 'icon',
                        'currentIcon' => old('icon', $category->icon),
                        'lucideIcons' => $lucideIcons,
                    ])
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>

    @include('admin.categories.partials.icon-modal', ['lucideIcons' => $lucideIcons])
@endsection
