@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        @include('admin.partials.hero', [
            'badge' => 'User Management',
            'title' => 'Manajemen Pengguna',
            'subtitle' => 'Kelola akun admin dan role akses sistem.',
        ])

        <x-admin.module>
            <h2 class="text-base font-semibold text-slate-900">Tambah Pengguna</h2>
            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Nama</label>
                    <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Role</label>
                    <select name="role" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', 'USER') === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Password</label>
                    <input type="password" name="password" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">Tambah Pengguna</button>
                </div>
            </form>
        </x-admin.module>

        <x-admin.module variant="table">
            <x-slot:header>
                <h2 class="text-base font-semibold text-slate-900">Daftar Pengguna</h2>
            </x-slot:header>

            <form method="GET" class="grid gap-3 border-b border-slate-200 bg-white p-4 sm:grid-cols-4 sm:p-6">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Cari</label>
                    <input type="text" name="q" value="{{ $query }}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Cari nama atau email...">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Role</label>
                    <select name="role" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Semua Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected($roleFilter === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800">Terapkan Filter</button>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-700">Jumlah Baris</label>
                    <select name="pageSize" onchange="this.form.submit()" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        @foreach([5,10,20,50,100] as $size)
                            <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-emerald-700 text-white">
                            <th class="px-3 py-2 text-left text-xs font-semibold">No</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Nama</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Email</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Role</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold">Created</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr class="border-t border-slate-200 bg-white">
                                <td class="px-3 py-2 text-sm text-slate-600">{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</td>
                                <td class="px-3 py-2 text-sm text-slate-900">
                                    {{ $user->name }}
                                    @if($user->id === $currentUserId)
                                        <span class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">Anda</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-sm text-slate-700">{{ $user->email }}</td>
                                <td class="px-3 py-2 text-sm text-slate-700">{{ $user->role instanceof \App\Enums\Role ? $user->role->value : $user->role }}</td>
                                <td class="px-3 py-2 text-sm text-slate-600">{{ optional($user->created_at)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100" aria-label="Edit user {{ $user->name }}">
                                            <x-lucide-pencil class="h-4 w-4" />
                                        </a>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Hapus pengguna ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" @disabled($user->id === $currentUserId) class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-300 text-red-700 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50" aria-label="Delete user {{ $user->name }}">
                                                <x-lucide-trash-2 class="h-4 w-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-600">Tidak ada pengguna yang ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-slot:footer>
                <div class="w-full">
                    {{ $users->onEachSide(1)->links('vendor.pagination.custom') }}
                </div>
            </x-slot:footer>
        </x-admin.module>
    </div>
@endsection
