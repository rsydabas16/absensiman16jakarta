<!-- resources/views/guru/absensi/index.blade.php -->


<!-- resources/views/guru/absensi/index.blade.php -->
@extends('layouts.app')

@section('title', 'Absensi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Guru /</span> Absensi
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Jadwal Mengajar Hari Ini - {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</h5>
        </div>
        <div class="card-body">
            @if($jadwalHariIni->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Tidak ada jadwal mengajar hari ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                <td>
                                    {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                </td>
                                <td>{{ $jadwal->mataPelajaran->nama_mata_pelajaran }}</td>
                                <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                <td>
                                    @if(in_array($jadwal->id, $absensiHariIni))
                                        @php
                                            $absensi = \App\Models\AbsensiGuru::where('jadwal_pelajaran_id', $jadwal->id)
                                                ->whereDate('tanggal', $tanggal)
                                                ->first();
                                            
                                            $badgeClasses = [
                                                'hadir' => 'success',
                                                'tidak_hadir' => 'danger',
                                                'izin' => 'info',
                                                'sakit' => 'warning',
                                                'dinas_luar' => 'primary',
                                                'cuti' => 'secondary'
                                            ];
                                            
                                            $statusLabels = [
                                                'hadir' => 'Hadir',
                                                'tidak_hadir' => 'Tidak Hadir',
                                                'izin' => 'Izin',
                                                'sakit' => 'Sakit',
                                                'dinas_luar' => 'Dinas Luar',
                                                'cuti' => 'Cuti'
                                            ];
                                        @endphp
                                        <span class="badge bg-label-{{ $badgeClasses[$absensi->status] ?? 'secondary' }}">
                                            {{ $statusLabels[$absensi->status] ?? ucfirst($absensi->status) }}
                                            @if($absensi->is_auto_alfa)
                                                <small>(Auto)</small>
                                            @endif
                                        </span>
                                    @else
                                        <span class="badge bg-label-secondary">Belum Absen</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!in_array($jadwal->id, $absensiHariIni))
                                        <a href="{{ route('guru.absensi.create', ['jadwal' => $jadwal->id]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bx bx-qr-scan me-1"></i> Absen
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bx bx-check me-1"></i> Sudah Absen
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection



{{-- @extends('layouts.app')

@section('title', 'Absensi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Guru /</span> Absensi
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Jadwal Mengajar Hari Ini - {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</h5>
        </div>
        <div class="card-body">
            @if($jadwalHariIni->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Tidak ada jadwal mengajar hari ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                <td>
                                    {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                </td>
                                <td>{{ $jadwal->mataPelajaran->nama_mata_pelajaran }}</td>
                                <td>{{ $jadwal->kelas->nama_kelas }}</td>
                                <td>
                                    @if(in_array($jadwal->id, $absensiHariIni))
                                        @php
                                            $absensi = \App\Models\AbsensiGuru::where('jadwal_pelajaran_id', $jadwal->id)
                                                ->whereDate('tanggal', $tanggal)
                                                ->first();
                                        @endphp
                                        <span class="badge bg-label-{{ $absensi->status === 'hadir' ? 'success' : ($absensi->status === 'izin' ? 'info' : ($absensi->status === 'sakit' ? 'warning' : 'danger')) }}">
                                            {{ ucfirst($absensi->status) }}
                                        </span>
                                    @else
                                        <span class="badge bg-label-secondary">Belum Absen</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!in_array($jadwal->id, $absensiHariIni))
                                        <a href="{{ route('guru.absensi.create', ['jadwal' => $jadwal->id]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bx bx-qr-scan me-1"></i> Absen
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bx bx-check me-1"></i> Sudah Absen
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection --}}