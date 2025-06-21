<!-- resources/views/admin/kelas/create.blade.php -->
@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Kelas /</span> Tambah Kelas
    </h4>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form Tambah Kelas</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.kelas.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Tingkat</label>
                            <select name="tingkat" class="form-select @error('tingkat') is-invalid @enderror" required>
                                <option value="">-- Pilih Tingkat --</option>
                                <option value="X" {{ old('tingkat') == 'X' ? 'selected' : '' }}>X</option>
                                <option value="XI" {{ old('tingkat') == 'XI' ? 'selected' : '' }}>XI</option>
                                <option value="XII" {{ old('tingkat') == 'XII' ? 'selected' : '' }}>XII</option>
                            </select>
                            @error('tingkat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jurusan</label>
                            <input type="text" class="form-control @error('jurusan') is-invalid @enderror" 
                                   name="jurusan" value="{{ old('jurusan') }}" 
                                   placeholder="Contoh: IPA, IPS, Bahasa">
                            @error('jurusan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Kelas</label>
                            <input type="text" class="form-control @error('nama_kelas') is-invalid @enderror" 
                                   name="nama_kelas" value="{{ old('nama_kelas') }}" required
                                   placeholder="Contoh: 1, 2, 3">
                            @error('nama_kelas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Contoh nama kelas lengkap: X IPA 1, XI IPS 2, XII Bahasa 1
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection