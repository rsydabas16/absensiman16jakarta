<!-- resources/views/siswa/dashboard.blade.php -->
@extends('layouts.app')

@section('title', 'Dashboard Siswa')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Selamat Datang, {{ $siswa->nama_lengkap }}! 🎉</h5>
                        <p class="mb-4">
                            Anda adalah <span class="fw-bold">{{ $siswa->is_ketua_kelas ? 'Ketua Kelas' : 'Wakil Ketua Kelas' }}</span> 
                            {{ $siswa->kelas->nama_kelas }}. 
                            <br>
                            Hari ini: <span class="fw-bold">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                        </p>
                        <a href="{{ route('siswa.generate-qr.index') }}" class="btn btn-sm btn-outline-primary">Generate QR Code</a>
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
    <!-- Jadwal Hari Ini -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Jadwal Pelajaran Hari Ini</h5>
                <small class="text-muted">{{ \Carbon\Carbon::now()->locale('id')->dayName }}</small>
            </div>
            <div class="card-body">
                @if($jadwalHariIni->isEmpty())
                    <div class="alert alert-info mb-0">
                        <i class="bx bx-info-circle me-1"></i>
                        Tidak ada jadwal pelajaran hari ini.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Jam Ke</th>
                                    <th>Waktu</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru</th>
                                    <th>Status Guru</th>
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
                                    <td>{{ $jadwal->guru->nama_lengkap }}</td>
                                    <td>
                                        @if(isset($statusAbsensiGuru[$jadwal->id]))
                                            @php $absensi = $statusAbsensiGuru[$jadwal->id]; @endphp
                                            <span class="badge bg-label-{{ $absensi->status === 'hadir' ? 'success' : ($absensi->status === 'izin' ? 'info' : ($absensi->status === 'sakit' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($absensi->status) }}
                                            </span>
                                            @if($absensi->status !== 'hadir' && $absensi->tugas)
                                                <span class="badge bg-label-secondary ms-1">Ada Tugas</span>
                                            @endif
                                        @else
                                            <span class="badge bg-label-secondary">Belum Absen</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!isset($statusAbsensiGuru[$jadwal->id]))
                                            <a href="{{ route('siswa.generate-qr.index') }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bx bx-qr me-1"></i> Generate QR
                                            </a>
                                        @else
                                            @if($absensi->status === 'hadir')
                                                <a href="{{ route('siswa.materi.create', ['absensi_id' => $absensi->id]) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="bx bx-book-content me-1"></i> Isi Materi
                                                </a>
                                            @else
                                                <a href="{{ route('siswa.materi.tugas-guru') }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bx bx-task me-1"></i> Lihat Tugas
                                                </a>
                                            @endif
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
</div>
@endsection