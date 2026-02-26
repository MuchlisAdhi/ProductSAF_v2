@extends('layouts.admin')

@section('content')
    @include('admin.partials.hero', [
        'badge' => 'Tracker',
        'title' => 'Kunjungan',
        'subtitle' => 'Daftar detail kunjungan halaman publik.',
    ])

    @if(! $trackerReady)
        <div class="alert alert-warning">
            Tabel pelacak belum tersedia. Jalankan migrasi terlebih dahulu: <code>php artisan migrate</code>.
        </div>
    @else
        <div class="card border-0 shadow mb-4">
            <div class="card-body border-bottom">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Cari</label>
                        <input type="text" name="q" value="{{ $query }}" class="form-control" placeholder="Lokasi, IP, peramban, hash pengunjung">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jumlah Baris</label>
                        <select name="pageSize" class="form-select">
                            @foreach([10,25,50,100] as $size)
                                <option value="{{ $size }}" @selected($pageSize === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">Terapkan</button>
                        <a href="{{ route('admin.tracker.visits') }}" class="btn btn-outline-secondary">Setel Ulang</a>
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
                            <th>Lokasi</th>
                            <th>Pengguna</th>
                            <th>IP</th>
                            <th>Metode</th>
                            <th>Peramban</th>
                            <th>Hash Pengunjung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $visit)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($visit->visited_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</td>
                                <td><code>{{ $visit->path }}</code></td>
                                <td>
                                    @if($visit->is_guest)
                                        <span class="badge bg-warning text-dark">Tamu</span>
                                    @else
                                        <span class="badge bg-success">{{ $visit->user_name ?: 'Pengguna' }}</span>
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
