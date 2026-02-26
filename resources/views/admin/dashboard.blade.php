@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            @include('admin.partials.hero', [
                'badge' => 'Admin Panel',
                'title' => 'Dashboard',
                'subtitle' => 'Ringkasan data produk, kategori, pengguna, dan aktivitas kunjungan publik.',
            ])
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape icon-md bg-primary text-white rounded me-3">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h2 class="h6 mb-0">Pengguna</h2>
                            <span class="h3 mb-0">{{ number_format($usersCount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape icon-md bg-tertiary text-white rounded me-3">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </div>
                        <div>
                            <h2 class="h6 mb-0">Kategori</h2>
                            <span class="h3 mb-0">{{ number_format($categoriesCount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape icon-md bg-success text-white rounded me-3">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div>
                            <h2 class="h6 mb-0">Produk</h2>
                            <span class="h3 mb-0">{{ number_format($productsCount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(! $trackerReady)
        <div class="alert alert-warning">
            Tabel pelacak belum tersedia. Jalankan migrasi terlebih dahulu: <code>php artisan migrate</code>.
        </div>
    @else
        <div class="row">
            <div class="col-12 col-md-4 mb-4">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="fs-6 fw-normal text-muted mb-1">Total Kunjungan</h2>
                        <span class="fs-3 fw-bold">{{ number_format($trackerSummary['totalVisits']) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-4">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="fs-6 fw-normal text-muted mb-1">Kunjungan Tamu</h2>
                        <span class="fs-3 fw-bold">{{ number_format($trackerSummary['guestVisits']) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-4">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="fs-6 fw-normal text-muted mb-1">Pengunjung Unik</h2>
                        <span class="fs-3 fw-bold">{{ number_format($trackerSummary['uniqueVisitors']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="fs-5 fw-bold mb-0">Rangkuman Grafik Kunjungan</h2>
                <a href="{{ route('admin.tracker.summary') }}" class="btn btn-sm btn-outline-primary">Lihat semua</a>
            </div>
            <div class="card-body">
                <div style="height: 320px;">
                    <canvas id="dashboardTrafficChart"></canvas>
                </div>
                <small class="text-muted d-block mt-2">Periode {{ $trackerDays }} hari terakhir.</small>
            </div>
        </div>

        <div class="card border-0 shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="fs-5 fw-bold mb-0">Daftar Kunjungan</h2>
                <a href="{{ route('admin.tracker.visits') }}" class="btn btn-sm btn-outline-primary">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-centered table-hover table-nowrap mb-0 rounded">
                    <thead class="thead-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Lokasi</th>
                            <th>Pengguna</th>
                            <th>IP</th>
                            <th>Metode</th>
                            <th>Peramban</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentVisits as $visit)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($visit->visited_at)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</td>
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
                                <td class="text-wrap" style="max-width: 360px;">{{ \Illuminate\Support\Str::limit((string) $visit->user_agent, 88) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">Belum ada data kunjungan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @if($trackerReady)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
        <script>
            (() => {
                const canvas = document.getElementById('dashboardTrafficChart');
                if (!canvas || typeof window.Chart === 'undefined') return;

                const labels = @json($trackerChartLabels);
                const visits = @json($trackerChartVisits);

                new window.Chart(canvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Total Kunjungan',
                                data: visits,
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13, 110, 253, 0.14)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                },
                            },
                        },
                    },
                });
            })();
        </script>
    @endif
@endpush
