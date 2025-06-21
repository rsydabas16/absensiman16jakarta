@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Selamat Datang, {{ $guru->nama_lengkap }}! 🎉</h5>
                        <p class="mb-4">
                            Hari ini adalah <span class="fw-bold">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>. 
                            Anda memiliki {{ $jadwalHariIni->count() }} jadwal mengajar hari ini.
                        </p>
                        <a href="{{ route('guru.absensi.index') }}" class="btn btn-sm btn-outline-primary">Mulai Absensi</a>
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

<div class="row">
    <!-- Statistik Absensi -->
    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/chart-success.png') }}" alt="chart success" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Hadir</span>
                <h3 class="card-title mb-2">{{ $totalHadir }}</h3>
                <small class="text-success fw-semibold">Bulan ini</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/wallet-info.png') }}" alt="wallet info" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Izin</span>
                <h3 class="card-title mb-2">{{ $totalIzin }}</h3>
                <small class="text-info fw-semibold">Bulan ini</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/paypal.png') }}" alt="paypal" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Sakit</span>
                <h3 class="card-title mb-2">{{ $totalSakit }}</h3>
                <small class="text-warning fw-semibold">Bulan ini</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/cc-warning.png') }}" alt="dinas luar" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Dinas Luar</span>
                <h3 class="card-title mb-2">{{ $totalDinasLuar }}</h3>
                <small class="text-primary fw-semibold">Bulan ini</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/chart.png') }}" alt="cuti" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Cuti</span>
                <h3 class="card-title mb-2">{{ $totalCuti }}</h3>
                <small class="text-secondary fw-semibold">Bulan ini</small>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-4 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/cc-primary.png') }}" alt="Credit Card" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Tidak Hadir</span>
                <h3 class="card-title mb-2">{{ $totalAlpa }}</h3>
                <small class="text-danger fw-semibold">Bulan ini</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Jadwal Hari Ini -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Jadwal Mengajar Hari Ini</h5>
            </div>
            <div class="card-body">
                @if($jadwalHariIni->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Jam Ke</th>
                                    <th>Waktu</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jadwalHariIni as $jadwal)
                                <tr>
                                    <td>{{ $jadwal->jam_ke }}</td>
                                    <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                                    <td>{{ $jadwal->mataPelajaran->nama_mata_pelajaran }}</td>
                                    <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                    <td>
                                        @php
                                            $absensi = $jadwal->absensiGuru()
                                                ->whereDate('tanggal', now()->toDateString())
                                                ->first();
                                        @endphp
                                        @if($absensi)
                                            @php
                                                $badgeClass = [
                                                    'hadir' => 'success',
                                                    'tidak_hadir' => 'danger',
                                                    'izin' => 'info',
                                                    'sakit' => 'warning',
                                                    'dinas_luar' => 'primary',
                                                    'cuti' => 'secondary'
                                                ][$absensi->status] ?? 'secondary';
                                                
                                                $statusLabel = [
                                                    'hadir' => 'Hadir',
                                                    'tidak_hadir' => 'Tidak Hadir',
                                                    'izin' => 'Izin',
                                                    'sakit' => 'Sakit',
                                                    'dinas_luar' => 'Dinas Luar',
                                                    'cuti' => 'Cuti'
                                                ][$absensi->status] ?? ucfirst($absensi->status);
                                            @endphp
                                            <span class="badge bg-label-{{ $badgeClass }}">
                                                {{ $statusLabel }}
                                                @if($absensi->is_auto_alfa)
                                                    <small>(Auto)</small>
                                                @endif
                                            </span>
                                        @else
                                            <span class="badge bg-label-warning">Belum Absen</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$absensi)
                                            <a href="{{ route('guru.absensi.create', ['jadwal' => $jadwal->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bx bx-qr-scan"></i> Absen
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bx bx-check"></i> Sudah Absen
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-1"></i>
                        Tidak ada jadwal mengajar hari ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection







{{-- @extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Selamat Datang, {{ $guru->nama_lengkap }}! 🎉</h5>
                        <p class="mb-4">
                            Hari ini adalah <span class="fw-bold">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>. 
                            Anda memiliki {{ $jadwalHariIni->count() }} jadwal mengajar hari ini.
                        </p>
                        <a href="{{ route('guru.absensi.index') }}" class="btn btn-sm btn-outline-primary">Mulai Absensi</a>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('template/img/illustrations/man-with-laptop-light.png') }}" 
                             height="140" alt="View Badge User" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Statistik Absensi -->
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/chart-success.png') }}" alt="chart success" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Hadir</span>
                <h3 class="card-title mb-2">{{ $totalHadir }}</h3>
                <small class="text-success fw-semibold">Bulan ini</small>
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
                <h3 class="card-title mb-2">{{ $totalIzin }}</h3>
                <small class="text-info fw-semibold">Bulan ini</small>
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
                <h3 class="card-title mb-2">{{ $totalSakit }}</h3>
                <small class="text-warning fw-semibold">Bulan ini</small>
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
                <span class="fw-semibold d-block mb-1">Tidak Hadir</span>
                <h3 class="card-title mb-2">{{ $totalAlpa }}</h3>
                <small class="text-danger fw-semibold">Bulan ini</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Jadwal Hari Ini -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Jadwal Mengajar Hari Ini</h5>
            </div>
            <div class="card-body">
                @if($jadwalHariIni->count() > 0)
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Jam Ke</th>
                                    <th>Waktu</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jadwalHariIni as $jadwal)
                                <tr>
                                    <td>{{ $jadwal->jam_ke }}</td>
                                    <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                                    <td>{{ $jadwal->mataPelajaran->nama_mata_pelajaran }}</td>
                                    <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                    <td>
                                        @php
                                            $absensi = $jadwal->absensiGuru()
                                                ->whereDate('tanggal', now()->toDateString())
                                                ->first();
                                        @endphp
                                        @if($absensi)
                                            <span class="badge bg-label-{{ $absensi->status === 'hadir' ? 'success' : 'danger' }}">
                                                {{ ucfirst($absensi->status) }}
                                            </span>
                                        @else
                                            <span class="badge bg-label-warning">Belum Absen</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$absensi)
                                            <a href="{{ route('guru.absensi.create', ['jadwal' => $jadwal->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bx bx-qr-scan"></i> Absen
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bx bx-check"></i> Sudah Absen
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-1"></i>
                        Tidak ada jadwal mengajar hari ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection --}}