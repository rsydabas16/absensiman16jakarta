<!-- resources/views/admin/mata-pelajaran/create.blade.php -->
@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Mata Pelajaran /</span> Tambah Mata Pelajaran
    </h4>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form Tambah Mata Pelajaran</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.mata-pelajaran.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Nama Mata Pelajaran</label>
                            <input type="text" class="form-control @error('nama_mata_pelajaran') is-invalid @enderror" 
                                   name="nama_mata_pelajaran" value="{{ old('nama_mata_pelajaran') }}" required
                                   placeholder="Contoh: Matematika, Bahasa Indonesia">
                            @error('nama_mata_pelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kode Mata Pelajaran</label>
                            <input type="text" class="form-control @error('kode_mapel') is-invalid @enderror" 
                                   name="kode_mapel" value="{{ old('kode_mapel') }}" required
                                   placeholder="Contoh: MTK, BIND, BING"
                                   maxlength="10">
                            @error('kode_mapel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Kode singkatan untuk mata pelajaran (maksimal 10 karakter)
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.mata-pelajaran.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection