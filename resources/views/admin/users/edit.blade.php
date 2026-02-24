@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        @include('admin.partials.hero', [
            'badge' => 'User Management',
            'title' => 'Ubah Pengguna',
            'subtitle' => 'Perbarui profil, role, dan kredensial pengguna.',
        ])

        <x-admin.module class="mx-auto max-w-2xl" body-class="p-6">
            <form method="POST" action="{{ route('admin.users.update', $targetUser->id) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Nama</label>
                    <input name="name" value="{{ old('name', $targetUser->name) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $targetUser->email) }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Role</label>
                    <select name="role" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @php
                            $targetRole = $targetUser->role instanceof \App\Enums\Role ? $targetUser->role->value : $targetUser->role;
                        @endphp
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $targetRole) === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">New Password (Opsional)</label>
                    <input type="password" name="password" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Simpan</button>
                    <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Kembali</a>
                </div>
            </form>
        </x-admin.module>
    </div>
@endsection
