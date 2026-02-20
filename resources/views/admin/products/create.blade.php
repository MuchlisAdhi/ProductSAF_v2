@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        @include('admin.partials.hero', [
            'badge' => 'Product Management',
            'title' => 'Create Product',
            'subtitle' => 'Form produk dengan upload gambar dan nutrisi dinamis.',
        ])

        @if($categories->count() === 0)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                No categories found. Create categories first.
            </div>
        @else
            @php
                $action = route('admin.products.store');
                $method = 'POST';
                $submitLabel = 'Save Product';
                $product = null;
                $nutritionRows = old('nutritions', [['label' => '', 'value' => '']]);
            @endphp
            @include('admin.products._form', compact('action', 'method', 'submitLabel', 'product', 'nutritionRows', 'categories', 'sackColors'))
        @endif
    </div>
@endsection
