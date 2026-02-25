@extends('layouts.auth')

@section('content')
    <div class="text-center text-md-center mb-4 mt-md-0">
        <img src="{{ asset('images/logo/saf-logo.png') }}" alt="Sidoagung Farm" class="mb-3" style="height: 68px; width: auto;">
        <h1 class="mb-0 h3">Admin Login</h1>
        <p class="text-muted mb-0">Masuk ke dashboard Admin</p>
    </div>

    <form method="POST" action="{{ route('login.submit') }}" class="mt-4">
        @csrf
        <input type="hidden" name="next" value="{{ $nextPath }}">

        <div class="form-group mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control"
                    placeholder="admin@sidoagung.com"
                    required
                    autofocus
                >
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="********"
                    required
                >
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                Sign in
            </button>
        </div>
    </form>
@endsection
