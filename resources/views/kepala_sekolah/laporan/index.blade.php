@extends('layouts.app')

@section('title', 'Laporan Absensi')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 400px;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Kepala Sekolah /</span> Laporan Absensi
    </h4>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('kepala_sekolah.laporan.index') }}" method="GET" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="tanggal_mulai" 
                               value="{{ $filter['tanggal_mulai'] ?? now()->subDays(7)->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="tanggal_akhir" 
                              value="{{ $filter['tanggal_akhir'] ?? now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Guru</label>
                       <select name="guru_id" class="form-select">
                        <option value="">Semua Guru</option>
                        @foreach($guruList as $guru)
                            <option value="{{ $guru->id }}" {{ ($filter['guru_id'] ?? '') == $guru->id ? 'selected' : '' }}>
                                {{ $guru->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-select">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ $filter['kelas_id'] == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="hadir" {{ $filter['status'] == 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="izin" {{ $filter['status'] == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ $filter['status'] == 'sakit' ? 'selected' : '' }}>Sakit</option>
                              <option value="dinas_luar" {{ $filter['status'] == 'dinas_luar' ? 'selected' : '' }}>Dinas Luar</option>
                            <option value="cuti" {{ $filter['status'] == 'cuti' ? 'selected' : '' }}>Cuti</option>
                            <option value="tidak_hadir" {{ $filter['status'] == 'tidak_hadir' ? 'selected' : '' }}>Alpa</option>
                        </select>
                    </div>
                    {{-- <div class="col-md-3">
                        <label class="form-label">Periode</label>
                        <select name="periode" class="form-select">
                            <option value="harian" {{ $filter['periode'] == 'harian' ? 'selected' : '' }}>Harian</option>
                            <option value="mingguan" {{ $filter['periode'] == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                            <option value="bulanan" {{ $filter['periode'] == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                        </select>
                    </div> --}}
                    <div class="col-md-6">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search me-1"></i> Filter
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportData('excel')">
                                <i class="bx bx-download me-1"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-danger" onclick="exportData('pdf')">
                                <i class="bx bx-download me-1"></i> Export PDF
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title text-white">Total</h6>
                    <h3 class="mb-0">{{ $statistik['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title text-white">Hadir</h6>
                    <h3 class="mb-0">{{ $statistik['hadir'] }}</h3>
                    
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title text-white">Izin</h6>
                    <h3 class="mb-0">{{ $statistik['izin'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title text-white">Sakit</h6>
                    <h3 class="mb-0">{{ $statistik['sakit'] }}</h3>
                </div>
            </div>
        </div>
         <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title text-white">Dinas luar</h6>
                    <h3 class="mb-0">{{ $statistik['dinas_luar'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title text-white">Cuti</h6>
                    <h3 class="mb-0">{{ $statistik['cuti'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title text-white">Alpa</h6>
                    <h3 class="mb-0">{{ $statistik['alpa'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Grafik Absensi</h5>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="absensiChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabel Detail -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Absensi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Jam Ke</th>
                            <th>Status</th>
                            <th>Jam Absen</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensiData as $item)
                        <tr>
                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $item->tanggal->locale('id')->dayName }}</td>
                            <td>{{ $item->guru->nama_lengkap }}</td>
                            <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                            <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
                            <td>{{ $item->jadwalPelajaran->jam_ke }}</td>
                            <td>
                                <span class="badge bg-label-{{ $item->status === 'hadir' ? 'success' : ($item->status === 'izin' ? 'info' : ($item->status === 'sakit' ? 'warning' : 'danger')) }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->jam_absen ?? '-' }}</td>
                            <td>
                                @if($item->status !== 'hadir')
                                    <small>{{ $item->alasan }}</small>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data absensi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('absensiChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Hadir',
                    data: chartData.datasets.hadir,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Izin',
                    data: chartData.datasets.izin,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Sakit',
                    data: chartData.datasets.sakit,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Alpa',
                    data: chartData.datasets.alpa,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                },
                  {
                    label: 'Dinas Luar',
                    data: chartData.datasets.dinas_luar,
                    borderColor: '#a10de0',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
                },
                  {
                    label: 'Cuti',
                    data: chartData.datasets.cuti,
                    borderColor: '#cc7110',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4
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
                        stepSize: 1
                    }
                }
            }
        }
    });
});

function exportData(format) {
    const form = document.getElementById('filterForm');
    const action = form.action.replace('/laporan', '/laporan/export');
    
    // Create a temporary form for export
    const exportForm = document.createElement('form');
    exportForm.method = 'GET';
    exportForm.action = action;
    
    // Copy all form inputs
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = input.name;
        hiddenInput.value = input.value;
        exportForm.appendChild(hiddenInput);
    });
    
    // Add format input
    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = format;
    exportForm.appendChild(formatInput);
    
    document.body.appendChild(exportForm);
    exportForm.submit();
    document.body.removeChild(exportForm);
}
</script>
@endpush