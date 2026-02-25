@extends('layouts.admin')

@section('content')
    @include('admin.partials.hero', [
        'badge' => 'Tracker',
        'title' => 'Visits',
        'subtitle' => 'Daftar detail kunjungan halaman publik.',
    ])

    @if(! $trackerReady)
        <div class="alert alert-warning">
            Tabel tracker belum tersedia. Jalankan migrasi terlebih dahulu: <code>php artisan migrate</code>.
        </div>
    @else
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
                            @foreach([10,25,50,100] as $size)
                                <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">Terapkan</button>
                        <a href="{{ route('admin.tracker.visits') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
            <div class="card-body py-2">
                <small class="text-muted">Menampilkan {{ $filteredCount }} dari {{ $totalCount }} kunjungan</small>
            </div>
            <div class="table-responsive">
                <table class="table table-centered table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Path</th>
                            <th>User</th>
                            <th>IP</th>
                            <th>Method</th>
                            <th>Agent</th>
                            <th>Visitor Hash</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $visit)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($visit->visited_at)->format('d/m/Y H:i:s') }}</td>
                                <td><code>{{ $visit->path }}</code></td>
                                <td>
                                    @if($visit->is_guest)
                                        <span class="badge bg-warning text-dark">Guest</span>
                                    @else
                                        <span class="badge bg-success">{{ $visit->user_name ?: 'User' }}</span>
                                    @endif
                                </td>
                                <td>{{ $visit->ip_address ?: '-' }}</td>
                                <td>{{ $visit->method }}</td>
                                <td class="text-wrap" style="max-width: 360px;">{{ \Illuminate\Support\Str::limit((string) $visit->user_agent, 90) }}</td>
                                <td><code>{{ \Illuminate\Support\Str::limit($visit->visitor_hash, 18, '...') }}</code></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Belum ada data kunjungan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer px-3 border-0 d-flex justify-content-end">
                {{ $visits->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
@endsection
