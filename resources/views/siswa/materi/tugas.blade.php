<!-- resources/views/siswa/materi/tugas.blade.php -->
@extends('layouts.app')

@section('title', 'Tugas dari Guru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Siswa /</span> Tugas dari Guru
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Tugas (7 Hari Terakhir)</h5>
        </div>
        <div class="card-body">
            @if($tugasList->isEmpty())
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-1"></i>
                    Tidak ada tugas dari guru yang tidak hadir dalam 7 hari terakhir.
                </div>
            @else
                @foreach($tugasList as $tugas)
                <div class="card mb-3 border-{{ $tugas->status === 'sakit' ? 'warning' : 'info' }}">
                    <div class="card-header bg-{{ $tugas->status === 'sakit' ? 'warning' : 'info' }} bg-opacity-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $tugas->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</h6>
                                <small class="text-muted">
                                    {{ $tugas->jadwalPelajaran->guru->nama_lengkap }} - 
                                    {{ $tugas->tanggal->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                </small>
                            </div>
                            <span class="badge bg-label-{{ $tugas->status === 'sakit' ? 'warning' : 'info' }}">
                                {{ ucfirst($tugas->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Alasan:</strong> {{ $tugas->alasan }}
                        </div>
                        <div class="border-top pt-2">
                            <strong>Tugas:</strong>
                            <p class="mb-0 mt-1">{{ $tugas->tugas }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection