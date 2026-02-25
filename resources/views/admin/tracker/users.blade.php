@extends('layouts.admin')

@section('content')
    @include('admin.partials.hero', [
        'badge' => 'Tracker',
        'title' => 'Users (Guest)',
        'subtitle' => 'Ringkasan visitor guest yang mengakses halaman publik.',
    ])

    @if(! $trackerReady)
        <div class="alert alert-warning">
            Tabel tracker belum tersedia. Jalankan migrasi terlebih dahulu: <code>php artisan migrate</code>.
        </div>
    @else
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="fs-6 fw-normal text-muted mb-1">Unique Guests</h2>
                        <span class="fs-3 fw-bold">{{ number_format($uniqueGuestCount) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="fs-6 fw-normal text-muted mb-1">Total Guest Visits</h2>
                        <span class="fs-3 fw-bold">{{ number_format($totalGuestVisits) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow mb-4">
            <div class="card-body border-bottom">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Cari</label>
                        <input type="text" name="q" value="{{ $query }}" class="form-control" placeholder="Path, IP, user agent, visitor hash">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Rows</label>
                        <select name="pageSize" class="form-select">
                            @foreach([10,20,50,100] as $size)
                                <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">Terapkan</button>
                        <a href="{{ route('admin.tracker.users') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th>Visitor Hash</th>
                            <th>IP Address</th>
                            <th>First Seen</th>
                            <th>Last Seen</th>
                            <th class="text-end">Visits</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guests as $guest)
                            <tr>
                                <td><code>{{ \Illuminate\Support\Str::limit((string) $guest->visitor_hash, 22, '...') }}</code></td>
                                <td>{{ $guest->ip_address ?: '-' }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($guest->first_seen)->format('d/m/Y H:i:s') }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($guest->last_seen)->format('d/m/Y H:i:s') }}</td>
                                <td class="text-end fw-semibold">{{ number_format((int) $guest->visits_count) }}</td>
                                <td class="text-wrap" style="max-width: 380px;">{{ \Illuminate\Support\Str::limit((string) $guest->user_agent, 110) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Belum ada data guest visitor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer px-3 border-0 d-flex justify-content-end">
                {{ $guests->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
@endsection
