@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        @include('admin.partials.hero', [
            'badge' => 'Category Management',
            'title' => 'Edit Category',
            'subtitle' => 'Perbarui nama, icon, dan urutan kategori.',
        ])

        <x-admin.module class="mx-auto max-w-2xl" body-class="p-6">
            <form method="POST" action="{{ route('admin.categories.update', $category->id) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Name</label>
                    <input name="name" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" value="{{ old('name', $category->name) }}" required>
                </div>
                @include('admin.categories.partials.icon-picker', [
                    'fieldId' => 'edit-category-icon',
                    'inputName' => 'icon',
                    'currentIcon' => old('icon', $category->icon),
                    'lucideIcons' => $lucideIcons,
                ])
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Order Number</label>
                    <input type="number" min="0" step="1" name="order_number" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" value="{{ old('order_number', $category->order_number) }}" required>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Save</button>
                    <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Back</a>
                </div>
            </form>
        </x-admin.module>
    </div>

    @include('admin.categories.partials.icon-modal', ['lucideIcons' => $lucideIcons])
@endsection
