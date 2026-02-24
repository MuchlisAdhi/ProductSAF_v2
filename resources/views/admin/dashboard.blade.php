@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        @include('admin.partials.hero', [
            'badge' => 'Admin Panel',
            'title' => 'Admin Dashboard',
            'subtitle' => 'Kelola users, kategori, dan produk dalam satu panel.',
        ])

        <section class="grid gap-3 sm:grid-cols-3">
            <x-admin.module>
                <p class="text-xs text-slate-500">Pengguna</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $usersCount }}</p>
            </x-admin.module>
            <x-admin.module>
                <p class="text-xs text-slate-500">Kategori</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $categoriesCount }}</p>
            </x-admin.module>
            <x-admin.module>
                <p class="text-xs text-slate-500">Produk</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $productsCount }}</p>
            </x-admin.module>
        </section>

        <x-admin.module>
            <h2 class="text-base font-semibold text-slate-900">Tindakan Cepat</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.products.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Kelola Produk</a>
                <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Buat Produk</a>
                <a href="{{ route('admin.categories.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Kelola Kategori</a>
                @php
                    $authRole = auth()->user()?->role;
                    $authRoleValue = $authRole instanceof \App\Enums\Role ? $authRole->value : (string) $authRole;
                @endphp
                @if($authRoleValue === 'SUPERADMIN')
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Kelola Pengguna</a>
                @endif
            </div>
        </x-admin.module>
    </div>
@endsection
