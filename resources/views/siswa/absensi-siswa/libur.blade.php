@extends('layouts.app')

@section('title', 'Hari Libur')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Siswa /</span> Absensi Siswa
    </h4>

    <div class="card">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="bx bx-calendar-x display-1 text-warning"></i>
            </div>
            <h3 class="mb-3">Hari Libur</h3>
            <h5 class="text-muted mb-4">{{ $hariLibur->keterangan }}</h5>
            <p class="text-muted">
                Hari ini adalah hari libur, jadi tidak ada absensi siswa yang dilakukan.
            </p>
            <div class="mt-4">
                <a href="{{ route('siswa.dashboard') }}" class="btn btn-primary">
                    <i class="bx bx-home me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection