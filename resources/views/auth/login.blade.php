@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold text-slate-900">Masuk ke Akun Anda</h1>
        <!-- <p class="mt-1 text-sm text-slate-600">Use seeded account: <span class="font-semibold">admin@sidoagung.com</span> / <span class="font-semibold">password123</span></p> -->

        <form method="POST" action="{{ route('login.submit') }}" class="mt-5 space-y-4">
            @csrf
            <input type="hidden" name="next" value="{{ $nextPath }}">
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    required
                />
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-700">Password</label>
                <input
                    type="password"
                    name="password"
                    class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    required
                />
            </div>

            <button type="submit" class="w-full rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                Sign In
            </button>
        </form>
    </div>
@endsection
