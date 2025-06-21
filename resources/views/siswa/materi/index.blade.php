<!-- resources/views/siswa/materi/index.blade.php -->
@extends('layouts.app')

@section('title', 'Materi Pembelajaran')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Siswa /</span> Materi Pembelajaran
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Materi Pembelajaran Hari Ini</h5>
        </div>
        <div class="card-body">
            @if($jadwalHariIni->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Tidak ada pelajaran yang perlu diisi materinya hari ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jam Ke</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru</th>
                                <th>Status Materi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwalHariIni as $jadwal)
                            @php
                                $absensi = $jadwal->absensiGuru()
                                    ->whereDate('tanggal', now()->toDateString())
                                    ->first();
                            @endphp
                            <tr>
                                <td>{{ $jadwal->jam_ke }}</td>
                                <td>{{ $jadwal->mataPelajaran->nama_mata_pelajaran }}</td>
                                <td>{{ $jadwal->guru->nama_lengkap }}</td>
                                <td>
                                    @if(in_array($absensi->id, $materiTerisi))
                                        <span class="badge bg-label-success">
                                            <i class="bx bx-check-circle me-1"></i> Sudah Diisi
                                        </span>
                                    @else
                                        <span class="badge bg-label-warning">
                                            <i class="bx bx-time me-1"></i> Belum Diisi
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!in_array($absensi->id, $materiTerisi))
                                        <a href="{{ route('siswa.materi.create', ['absensi_id' => $absensi->id]) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bx bx-edit me-1"></i> Isi Materi
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bx bx-check me-1"></i> Sudah Diisi
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