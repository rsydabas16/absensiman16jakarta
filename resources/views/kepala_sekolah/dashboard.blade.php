@extends('layouts.app')

@section('title', 'Dashboard Kepala Sekolah')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Selamat Datang, {{ auth()->user()->name }}! 🎉</h5>
                        <p class="mb-4">
                            Dashboard monitoring kehadiran guru. 
                            Tanggal: <span class="fw-bold">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                        </p>
                        <a href="{{ route('kepala_sekolah.laporan.index') }}" class="btn btn-sm btn-outline-primary">Lihat Laporan</a>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('template/img/illustrations/man-with-laptop.png') }}" 
                             height="140" alt="View Badge User" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Hari Ini -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/chart-success.png') }}" alt="chart success" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Hadir Hari Ini</span>
             <h3 class="card-title mb-2 text-success">{{ $statistikHariIni['hadir'] }}</h3>
                <small class="text-success fw-semibold">Guru Hadir</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/wallet-info.png') }}" alt="wallet info" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Izin</span>
               <h3 class="card-title mb-2 text-info">{{ $statistikHariIni['izin'] }}</h3>
                <small class="text-info fw-semibold">Guru Izin</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/paypal.png') }}" alt="paypal" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Sakit</span>
             <h3 class="card-title mb-2 text-warning">{{ $statistikHariIni['sakit'] }}</h3>
                <small class="text-warning fw-semibold">Guru Sakit</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/paypal.png') }}" alt="paypal" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Dinas Luar</span>
             <h3 class="card-title mb-2 text-warning">{{ $statistikHariIni['dinas_luar'] }}</h3>
                <small class="text-warning fw-semibold">Guru Dinas Luar</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/paypal.png') }}" alt="paypal" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Cuti</span>
             <h3 class="card-title mb-2 text-warning">{{ $statistikHariIni['cuti'] }}</h3>
                <small class="text-warning fw-semibold">Guru Cuti</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/cc-primary.png') }}" alt="Credit Card" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Alpa</span>
              <h3 class="card-title mb-2 text-danger">{{ $statistikHariIni['tidak_hadir'] }}</h3>
                <small class="text-danger fw-semibold">Tanpa Keterangan</small>
            </div>
        </div>
    </div>
</div>


{{-- <div class="row">
    <!-- Grafik Kehadiran Mingguan -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tren Kehadiran 7 Hari Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Guru Terbaik -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top 5 Guru Terbaik Bulan Ini</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>Guru</th>
                                <th class="text-end">Hadir</th>
                            </tr>
                        </thead>
                        <tbody>
                          @foreach($topGuru as $guru)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ substr($guru->nama_lengkap, 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            {{ $guru->nama_lengkap }}
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-label-success">{{ $guru->hadir }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendChart').getContext('2d');
    const data = @json($chartDataMinggu);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => item.tanggal),
            datasets: [{
                label: 'Jumlah Hadir',
                data: data.map(item => item.total),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush