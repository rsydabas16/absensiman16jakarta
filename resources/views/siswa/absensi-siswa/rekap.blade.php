@extends('layouts.app')

@section('title', 'Rekap Absensi Siswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Siswa /</span> Rekap Absensi Siswa
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Rekap Absensi 30 Hari Terakhir - Kelas {{ auth()->user()->siswa->kelas->nama_kelas }}</h5>
            <a href="{{ route('siswa.absensi-siswa.index') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-plus me-1"></i> Absensi Hari Ini
            </a>
        </div>
        <div class="card-body">
            @if($rekapAbsensi->count() > 0)
                @foreach($rekapAbsensi as $tanggal => $absensiPerTanggal)
                <div class="card border mb-3">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</h6>
                            <div>
                                @php
                                    $hadir = $absensiPerTanggal->where('status', 'hadir')->count();
                                    $izin = $absensiPerTanggal->where('status', 'izin')->count();
                                    $sakit = $absensiPerTanggal->where('status', 'sakit')->count();
                                    $alfa = $absensiPerTanggal->where('status', 'alfa')->count();
                                @endphp
                                <span class="badge bg-success me-1">H: {{ $hadir }}</span>
                                <span class="badge bg-info me-1">I: {{ $izin }}</span>
                                <span class="badge bg-warning me-1">S: {{ $sakit }}</span>
                                <span class="badge bg-danger">A: {{ $alfa }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($absensiPerTanggal as $absensi)
                                    <tr>
                                        <td>{{ $absensi->siswa->nisn }}</td>
                                        <td>{{ $absensi->siswa->nama_lengkap }}</td>
                                        <td>
                                            <span class="badge bg-label-{{ $absensi->status === 'hadir' ? 'success' : ($absensi->status === 'izin' ? 'info' : ($absensi->status === 'sakit' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($absensi->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $absensi->keterangan ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <i class="bx bx-calendar-x display-4 text-muted"></i>
                    <p class="mt-2 text-muted">Belum ada data absensi siswa dalam 30 hari terakhir.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection