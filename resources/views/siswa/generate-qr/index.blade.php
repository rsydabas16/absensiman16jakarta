<!-- resources/views/siswa/generate-qr/index.blade.php -->
@extends('layouts.app')

@section('title', 'Generate QR Code')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Siswa /</span> Generate QR Code
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Jadwal Pelajaran - {{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</h5>
        </div>
        <div class="card-body">
            @if($jadwalHariIni->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Tidak ada jadwal pelajaran hari ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jam Ke</th>
                                <th>Waktu</th>
                                <th>Mata Pelajaran</th>
                                <th>Guru</th>
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
                                <td>{{ $jadwal->guru->nama_lengkap }}</td>
                                <td>
                                    @if(isset($statusAbsensi[$jadwal->id]))
                                        <span class="badge bg-label-success">
                                            <i class="bx bx-check-circle me-1"></i> Sudah Absen
                                        </span>
                                    @else
                                        <span class="badge bg-label-warning">
                                            <i class="bx bx-time me-1"></i> Belum Absen
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!isset($statusAbsensi[$jadwal->id]))
                                        @php
                                            $now = \Carbon\Carbon::now();
                                            $jamMulai = \Carbon\Carbon::parse($jadwal->jam_mulai);
                                            $jamSelesai = \Carbon\Carbon::parse($jadwal->jam_selesai);
                                            $bisaGenerate = $now->between($jamMulai->subMinutes(1), $jamSelesai);
                                        @endphp
                                        
                                        @if($bisaGenerate)
                                            <a href="{{ route('siswa.generate-qr.create', ['jadwal_id' => $jadwal->id]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bx bx-qr me-1"></i> Generate QR
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                <i class="bx bx-time me-1"></i> Belum Waktunya
                                            </button>
                                        @endif
                                    @else
                                        <button class="btn btn-sm btn-success" disabled>
                                            <i class="bx bx-check me-1"></i> Sudah Absen
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="bx bx-info-circle me-1"></i>
                    <strong>Catatan:</strong> QR Code hanya bisa di-generate 15 menit sebelum jadwal dimulai hingga jadwal selesai.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection