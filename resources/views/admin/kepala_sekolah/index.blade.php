@extends('layouts.kepala_sekolah')

@section('title', 'Dashboard Kepala Sekolah')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Selamat Datang, {{ Auth::user()->name }}! 🎉</h5>
                            <p class="mb-4">Anda memiliki <span class="fw-bold">{{ $totalGuru }}</span> guru yang terdaftar dalam sistem. Berikut adalah ringkasan absensi guru hari ini.</p>

                            <a href="{{ route('kepala-sekolah.laporan.index') }}" class="btn btn-sm btn-outline-primary">Lihat Detail Laporan</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-3 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="chart success" class="rounded">
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Absensi Hari Ini</span>
                    <h3 class="card-title mb-2">{{ $statistikHariIni['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <img src="{{ asset('assets/img/icons/unicons/cc-success.png') }}" alt="Credit Card" class="rounded">
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Hadir</span>
                    <h3 class="card-title text-success mb-2">{{ $statistikHariIni['hadir'] }}</h3>
                    <small class="text-success fw-semibold">
                        {{ $statistikHariIni['total'] > 0 ? round(($statistikHariIni['hadir'] / $statistikHariIni['total']) * 100, 2) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <img src="{{ asset('assets/img/icons/unicons/cc-warning.png') }}" alt="Credit Card" class="rounded">
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Izin / Sakit</span>
                    <h3 class="card-title text-warning mb-2">{{ $statistikHariIni['izin'] + $statistikHariIni['sakit'] }}</h3>
                    <small class="text-warning fw-semibold">
                        {{ $statistikHariIni['total'] > 0 ? round((($statistikHariIni['izin'] + $statistikHariIni['sakit']) / $statistikHariIni['total']) * 100, 2) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <img src="{{ asset('assets/img/icons/unicons/cc-danger.png') }}" alt="Credit Card" class="rounded">
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Tidak Hadir</span>
                    <h3 class="card-title text-danger mb-2">{{ $statistikHariIni['tidak_hadir'] }}</h3>
                    <small class="text-danger fw-semibold">
                        {{ $statistikHariIni['total'] > 0 ? round(($statistikHariIni['tidak_hadir'] / $statistikHariIni['total']) * 100, 2) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Chart Absensi Mingguan -->
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Absensi Mingguan</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="mingguan" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="mingguan">
                            <a class="dropdown-item" href="{{ route('kepala-sekolah.dashboard.filter', ['filter' => 'mingguan']) }}">Lihat Detail</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chartMingguan" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart Absensi Bulanan -->
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Absensi Bulanan</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="bulanan" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="bulanan">
                            <a class="dropdown-item" href="{{ route('kepala-sekolah.dashboard.filter', ['filter' => 'bulanan']) }}">Lihat Detail</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chartBulanan" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top 5 Guru dengan Kehadiran Terbaik -->
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Top 5 Guru dengan Kehadiran Terbaik</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Guru</th>
                                    <th>Total Absensi</th>
                                    <th>Hadir</th>
                                    <th>Persentase</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topGuru as $guru)
                                <tr>
                                    <td>{{ $guru->nama_lengkap }}</td>
                                    <td>{{ $guru->total_absensi }}</td>
                                    <td>{{ $guru->hadir }}</td>
                                    <td>
                                        {{ $guru->total_absensi > 0 ? round(($guru->hadir / $guru->total_absensi) * 100, 2) : 0 }}%
                                    </td>
                                    <td>
                                        <a href="{{ route('kepala-sekolah.laporan.guru-statistik', ['id' => $guru->id]) }}" class="btn btn-sm btn-primary">Detail</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kehadiran Per Kelas Hari Ini -->
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Kehadiran Per Kelas Hari Ini</h5>
                    <div class="dropdown">
                        <button class="btn p-0" type="button" id="perKelas" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="perKelas">
                            <a class="dropdown-item" href="{{ route('kepala-sekolah.dashboard.filter', ['filter' => 'harian']) }}">Lihat Detail</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th>Total</th>
                                    <th>Hadir</th>
                                    <th>Tidak Hadir</th>
                                    <th>Izin/Sakit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kehadiranPerKelas as $kelas)
                                <tr>
                                    <td>{{ $kelas->nama_kelas }}</td>
                                    <td>{{ $kelas->total }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $kelas->hadir }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $kelas->tidak_hadir }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">{{ $kelas->izin + $kelas->sakit }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data absensi hari ini</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart Mingguan
        const ctxMingguan = document.getElementById('chartMingguan').getContext('2d');
        const chartMingguan = new Chart(ctxMingguan, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartDataMinggu['labels']) !!},
                datasets: [
                    {
                        label: 'Hadir',
                        data: {!! json_encode($chartDataMinggu['hadir']) !!},
                        backgroundColor: '#28a745',
                        borderColor: '#28a745',
                        borderWidth: 1
                    },
                    {
                        label: 'Tidak Hadir',
                        data: {!! json_encode($chartDataMinggu['tidak_hadir']) !!},
                        backgroundColor: '#dc3545',
                        borderColor: '#dc3545',
                        borderWidth: 1
                    },
                    {
                        label: 'Izin',
                        data: {!! json_encode($chartDataMinggu['izin']) !!},
                        backgroundColor: '#17a2b8',
                        borderColor: '#17a2b8',
                        borderWidth: 1
                    },
                    {
                        label: 'Sakit',
                        data: {!! json_encode($chartDataMinggu['sakit']) !!},
                        backgroundColor: '#ffc107',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Chart Bulanan
        const ctxBulanan = document.getElementById('chartBulanan').getContext('2d');
        const chartBulanan = new Chart(ctxBulanan, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartDataBulan['labels']) !!},
                datasets: [
                    {
                        label: 'Hadir',
                        data: {!! json_encode($chartDataBulan['hadir']) !!},
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderColor: '#28a745',
                        borderWidth: 2,
                        tension: 0.1
                    },
                    {
                        label: 'Tidak Hadir',
                        data: {!! json_encode($chartDataBulan['tidak_hadir']) !!},
                        backgroundColor: 'rgba(220, 53, 69, 0.2)',
                        borderColor: '#dc3545',
                        borderWidth: 2,
                        tension: 0.1
                    },
                    {
                        label: 'Izin',
                        data: {!! json_encode($chartDataBulan['izin']) !!},
                        backgroundColor: 'rgba(23, 162, 184, 0.2)',
                        borderColor: '#17a2b8',
                        borderWidth: 2,
                        tension: 0.1
                    },
                   