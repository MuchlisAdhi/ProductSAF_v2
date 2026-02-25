@extends('layouts.admin')

@section('content')
    @include('admin.partials.hero', [
        'badge' => 'Tracker',
        'title' => 'Summary',
        'subtitle' => 'Ringkasan kunjungan halaman publik berdasarkan periode.',
    ])

    @if(! $trackerReady)
        <div class="alert alert-warning">
            Tabel tracker belum tersedia. Jalankan migrasi terlebih dahulu: <code>php artisan migrate</code>.
        </div>
    @else
        <div class="card border-0 shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Periode</label>
                        <select name="days" class="form-select">
                            @foreach([7,14,30,60,90] as $period)
                                <option value="{{ $period }}" @selected($days === $period)>{{ $period }} hari terakhir</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Terapkan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12 col-md-4 mb-3">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="fs-6 fw-normal text-muted mb-1">Total Visits</h2>
                        <span class="fs-3 fw-bold">{{ number_format($summary['totalVisits']) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="fs-6 fw-normal text-muted mb-1">Guest Visits</h2>
                        <span class="fs-3 fw-bold">{{ number_format($summary['guestVisits']) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <div class="card border-0 shadow h-100">
                    <div class="card-body">
                        <h2 class="fs-6 fw-normal text-muted mb-1">Unique Visitors</h2>
                        <span class="fs-3 fw-bold">{{ number_format($summary['uniqueVisitors']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow mb-4">
            <div class="card-header">
                <h2 class="fs-5 fw-bold mb-0">Traffic Summary Chart</h2>
            </div>
            <div class="card-body">
                <canvas id="trackerSummaryChart" height="120"></canvas>
            </div>
        </div>

        <div class="card border-0 shadow mb-4">
            <div class="card-header">
                <h2 class="fs-5 fw-bold mb-0">Top Public Pages</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-centered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Path</th>
                            <th class="text-end">Visits</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPaths as $path)
                            <tr>
                                <td>{{ $path->path }}</td>
                                <td class="text-end">{{ number_format((int) $path->visits) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center py-4">Belum ada data kunjungan.</td>
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
                const canvas = document.getElementById('trackerSummaryChart');
                if (!canvas || typeof window.Chart === 'undefined') return;

                const labels = @json($chartLabels);
                const visits = @json($chartVisits);
                const guestVisits = @json($chartGuestVisits);

                new window.Chart(canvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Total Visits',
                                data: visits,
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13, 110, 253, 0.15)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.35,
                            },
                            {
                                label: 'Guest Visits',
                                data: guestVisits,
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.15)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.35,
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
