<!-- resources/views/siswa/materi/create.blade.php -->
@extends('layouts.app')

@section('title', 'Isi Materi Pembelajaran')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Siswa /</span> Materi Pembelajaran
    </h4>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Form Materi Pembelajaran</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading fw-bold mb-1">Informasi Pelajaran:</h6>
                        <p class="mb-0">
                            <strong>Mata Pelajaran:</strong> {{ $absensi->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}<br>
                            <strong>Guru:</strong> {{ $absensi->jadwalPelajaran->guru->nama_lengkap }}<br>
                            <strong>Tanggal:</strong> {{ $absensi->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}<br>
                            <strong>Jam:</strong> {{ \Carbon\Carbon::parse($absensi->jadwalPelajaran->jam_mulai)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($absensi->jadwalPelajaran->jam_selesai)->format('H:i') }}
                        </p>
                    </div>

                    <form action="{{ route('siswa.materi.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="absensi_guru_id" value="{{ $absensi->id }}">
                        
                        <div class="mb-3">
                            <label class="form-label">Materi yang Dipelajari Hari Ini</label>
                            <textarea name="materi" class="form-control @error('materi') is-invalid @enderror" 
                                      rows="8" placeholder="Tuliskan materi yang dipelajari hari ini..." required>{{ old('materi') }}</textarea>
                            @error('materi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimal 10 karakter. Jelaskan secara singkat materi yang dipelajari.</div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-send me-1"></i> Simpan Materi
                            </button>
                            <a href="{{ route('siswa.materi.index') }}" class="btn btn-secondary">
                                <i class="bx bx-x me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection