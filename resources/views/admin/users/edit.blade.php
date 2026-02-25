@extends('layouts.admin')

@section('content')
    @include('admin.partials.hero', [
        'badge' => 'User Management',
        'title' => 'Ubah Pengguna',
        'subtitle' => 'Perbarui profil, role, dan kredensial pengguna.',
    ])

    <div class="card border-0 shadow mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $targetUser->id) }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-6">
                    <label class="form-label">Nama</label>
                    <input name="name" value="{{ old('name', $targetUser->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $targetUser->email) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        @php
                            $targetRole = $targetUser->role instanceof \App\Enums\Role ? $targetUser->role->value : $targetUser->role;
                        @endphp
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', $targetRole) === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password Baru (Opsional)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection
