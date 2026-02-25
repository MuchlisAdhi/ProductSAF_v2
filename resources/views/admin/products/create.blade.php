@extends('layouts.admin')

@section('content')
    @include('admin.partials.hero', [
        'badge' => 'Product Management',
        'title' => 'Tambah Produk',
        'subtitle' => 'Form produk dengan upload gambar dan nutrisi dinamis.',
    ])

    @if($categories->count() === 0)
        <div class="alert alert-warning">Belum ada kategori. Buat kategori terlebih dahulu.</div>
    @else
        @php
            $action = route('admin.products.store');
            $method = 'POST';
            $submitLabel = 'Simpan Produk';
            $product = null;
            $nutritionRows = old('nutritions', [['label' => '', 'value' => '']]);
        @endphp
        @include('admin.products._form', compact('action', 'method', 'submitLabel', 'product', 'nutritionRows', 'categories', 'sackColors'))
    @endif
@endsection
