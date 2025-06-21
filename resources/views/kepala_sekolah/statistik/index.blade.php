@extends('layouts.app')

@section('title', 'Statistik Guru')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Kepala Sekolah /</span> Statistik Guru
    </h4>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Statistik</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('kepala_sekolah.statistik.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Periode</label>
                        <select name="periode" class="form-select" onchange="toggleDateInputs(this.value)">
                            <option value="harian" {{ $periode == 'harian' ? 'selected' : '' }}>Harian</option>
                            <option value="bulanan" {{ $periode == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                             <option value="mingguan" {{ $periode == 'mingguan' ? 'selected' : '' }}>Pilih Tanggal</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3" id="tanggal-input" style="{{ $periode != 'harian' ? 'display:none' : '' }}">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" 
                               value="{{ request('tanggal', now()->toDateString()) }}">
                    </div>
                    
                    <div class="col-md-3" id="minggu-input" style="{{ $periode != 'mingguan' ? 'display:none' : '' }}">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" 
                               value="{{ request('start_date', now()->startOfWeek()->toDateString()) }}">
                    </div>
                    
                    <div class="col-md-3" id="minggu-input-end" style="{{ $periode != 'mingguan' ? 'display:none' : '' }}">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="end_date" 
                               value="{{ request('end_date', now()->endOfWeek()->toDateString()) }}">
                    </div>
                    
                    <div class="col-md-3" id="bulan-input" style="{{ $periode != 'bulanan' ? 'display:none' : '' }}">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="col-md-3" id="tahun-input" style="{{ $periode != 'bulanan' ? 'display:none' : '' }}">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select">
                            @for($i = date('Y'); $i >= date('Y') - 2; $i--)
                                <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search me-1"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik Guru -->
    <div class="row">
        @foreach($statistikGuru as $stat)
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $stat['guru']->nama_lengkap }}</h5>
                    <a href="{{ route('kepala_sekolah.statistik.detail', ['guru' => $stat['guru']->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" 
                       class="btn btn-sm btn-primary">
                        <i class="bx bx-detail me-1"></i> Detail
                    </a>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="chart-container">
                                <canvas id="chart-{{ $stat['guru']->id }}"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Mengajar:</span>
                                <strong>{{ $stat['total'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Hadir:</span>
                                <strong class="text-success">{{ $stat['hadir'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Izin:</span>
                                <strong class="text-info">{{ $stat['izin'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sakit:</span>
                                <strong class="text-warning">{{ $stat['sakit'] }}</strong>
                            </div>
                             <div class="d-flex justify-content-between mb-2">
                                <span>Dinas Luar:</span>
                                <strong style="color: #a10de0;">{{ $stat['dinas_luar'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Cuti:</span>
                                <strong  style="color: #cc7110;">{{ $stat['cuti'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Alpa:</span>
                                <strong class="text-danger">{{ $stat['alpa'] }}</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span>Persentase Kehadiran:</span>
                                <strong class="text-primary">{{ $stat['persentase_hadir'] }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate charts for each teacher
    @foreach($statistikGuru as $stat)
    new Chart(document.getElementById('chart-{{ $stat['guru']->id }}'), {
        type: 'doughnut',
        data: {
            labels: ['Hadir', 'Izin', 'Sakit','dinas luar','cuti', 'Alpa'],
            datasets: [{
                data: @json(array_column($stat['chart_data'], 'y')),
                backgroundColor: @json(array_column($stat['chart_data'], 'color'))
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    @endforeach
});

function toggleDateInputs(periode) {
    document.getElementById('tanggal-input').style.display = periode === 'harian' ? 'block' : 'none';
    document.getElementById('minggu-input').style.display = periode === 'mingguan' ? 'block' : 'none';
    document.getElementById('minggu-input-end').style.display = periode === 'mingguan' ? 'block' : 'none';
    document.getElementById('bulan-input').style.display = periode === 'bulanan' ? 'block' : 'none';
    document.getElementById('tahun-input').style.display = periode === 'bulanan' ? 'block' : 'none';
}
</script>
@endpush