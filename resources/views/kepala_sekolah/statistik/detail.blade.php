@extends('layouts.app')

@section('title', 'Detail Statistik - ' . $guru->nama_lengkap)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Kepala Sekolah / Statistik /</span> {{ $guru->nama_lengkap }}
    </h4>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">{{ $guru->nama_lengkap }}</h5>
                            <p class="mb-0 text-muted">NIP: {{ $guru->nip }}</p>
                        </div>
                        <div>
                            <a href="{{ route('kepala_sekolah.statistik.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('kepala_sekolah.statistik.detail', $guru->id) }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Bulan</label>
                        <select name="bulan" class="form-select">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tahun</label>
                        <select name="tahun" class="form-select">
                            @for($i = date('Y'); $i >= date('Y') - 2; $i--)
                                <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
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

    {{-- <!-- Weekly Breakdown -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Breakdown Mingguan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Minggu Ke</th>
                            <th>Periode</th>
                            <th>Total Mengajar</th>
                            <th>Hadir</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weeklyData as $week)
                        <tr>
                            <td>{{ $week['week'] }}</td>
                            <td>{{ $week['start'] }} - {{ $week['end'] }}</td>
                            <td>{{ $week['total'] }}</td>
                            <td>{{ $week['hadir'] }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $week['persentase'] >= 80 ? 'success' : ($week['persentase'] >= 60 ? 'warning' : 'danger') }}" 
                                         style="width: {{ $week['persentase'] }}%">
                                        {{ $week['persentase'] }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Per Mata Pelajaran -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Statistik Per Mata Pelajaran</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th>Total Pertemuan</th>
                            <th>Hadir</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perMapelData as $mapel)
                        <tr>
                            <td>{{ $mapel['mata_pelajaran'] }}</td>
                            <td>{{ $mapel['total'] }}</td>
                            <td>{{ $mapel['hadir'] }}</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-{{ $mapel['persentase'] >= 80 ? 'success' : ($mapel['persentase'] >= 60 ? 'warning' : 'danger') }}" 
                                         style="width: {{ $mapel['persentase'] }}%">
                                        {{ $mapel['persentase'] }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div> --}}

    <!-- Detail Absensi -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Absensi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Jam Ke</th>
                            <th>Status</th>
                            <th>Jam Absen</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absensiData as $item)
                        <tr>
                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $item->tanggal->locale('id')->dayName }}</td>
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection