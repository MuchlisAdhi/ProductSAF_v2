@extends('layouts.admin')

@section('content')
    @include('admin.partials.hero', [
        'badge' => 'Product Management',
        'title' => 'Edit Produk',
        'subtitle' => 'Perbarui detail produk, nutrisi, dan gambar.',
    ])

    @if($categories->count() === 0)
        <div class="alert alert-warning">Belum ada kategori. Buat kategori terlebih dahulu.</div>
    @else
        @php
            $action = route('admin.products.update', $product->id);
            $method = 'PUT';
            $submitLabel = 'Simpan Perubahan';
            $nutritionRows = old(
                'nutritions',
                $product->nutritions->map(fn ($nutrition) => [
                    'label' => $nutrition->label,
                    'value' => $nutrition->value,
                ])->all() ?: [['label' => '', 'value' => '']]
            );
        @endphp
        @include('admin.products._form', compact('action', 'method', 'submitLabel', 'product', 'nutritionRows', 'categories', 'sackColors'))
    @endif
@endsection
