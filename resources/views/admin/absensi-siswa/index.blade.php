@extends('layouts.app')

@section('title', 'Laporan Absensi Siswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin /</span> Laporan Absensi Siswa
    </h4>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.absensi-siswa.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas_id">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="alfa" {{ request('status') == 'alfa' ? 'selected' : '' }}>Alfa</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.absensi-siswa.index') }}" class="btn btn-secondary">
                        <i class="bx bx-refresh me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-success rounded">
                                <i class="bx bx-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Hadir</span>
                    <h3 class="card-title mb-2">{{ number_format($totalHadir) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-info rounded">
                                <i class="bx bx-info-circle"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Izin</span>
                    <h3 class="card-title mb-2">{{ number_format($totalIzin) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="bx bx-heart"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Sakit</span>
                    <h3 class="card-title mb-2">{{ number_format($totalSakit) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-danger rounded">
                                <i class="bx bx-x-circle"></i>
                            </div>
                        </div>
                    </div>
                    <span class="fw-semibold d-block mb-1">Total Alfa</span>
                    <h3 class="card-title mb-2">{{ number_format($totalAlfla) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Absensi Siswa</h5>
            <div>
                <a href="{{ route('admin.absensi-siswa.export-excel', request()->all()) }}" class="btn btn-success btn-sm">
                    <i class="bx bx-download me-1"></i> Export Excel
                </a>
                <a href="{{ route('admin.absensi-siswa.export-pdf', request()->all()) }}" class="btn btn-danger btn-sm">
                    <i class="bx bx-download me-1"></i> Export PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($absensiSiswa->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                                <th>Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($absensiSiswa as $absensi)
                            <tr>
                                <td>{{ $absensi->tanggal->format('d/m/Y') }}</td>
                                <td>{{ $absensi->siswa->nisn }}</td>
                                <td>{{ $absensi->siswa->nama_lengkap }}</td>
                                <td>{{ $absensi->kelas->nama_kelas }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $absensi->status === 'hadir' ? 'success' : ($absensi->status === 'izin' ? 'info' : ($absensi->status === 'sakit' ? 'warning' : 'danger')) }}">
                                        {{ ucfirst($absensi->status) }}
                                    </span>
                                </td>
                                <td>{{ $absensi->keterangan ?? '-' }}</td>
                                <td>{{ $absensi->pencatat->nama_lengkap }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $absensiSiswa->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bx bx-search-alt-2 display-4 text-muted"></i>
                    <p class="mt-2 text-muted">Tidak ada data absensi siswa ditemukan.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection