@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Admin / Users /</span> Tambah User
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form Tambah User</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        
                        <!-- Basic Info -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Induk</label>
                                <input type="text" class="form-control @error('nomor_induk') is-invalid @enderror" 
                                       name="nomor_induk" value="{{ old('nomor_induk') }}" required>
                                @error('nomor_induk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                            {{ ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <!-- Guru Fields -->
                        <div id="guru-fields" style="display: none;">
                            <hr>
                            <h6 class="mb-3">Data Guru</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">NIP</label>
                                    <input type="text" class="form-control @error('nip') is-invalid @enderror" 
                                           name="nip" value="{{ old('nip') }}">
                                    @error('nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('guru_nama_lengkap') is-invalid @enderror" 
                                           name="guru_nama_lengkap" value="{{ old('guru_nama_lengkap') }}">
                                    @error('guru_nama_lengkap')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="guru_jenis_kelamin" class="form-select @error('guru_jenis_kelamin') is-invalid @enderror">
                                        <option value="">-- Pilih --</option>
                                        <option value="L" {{ old('guru_jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('guru_jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('guru_jenis_kelamin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">No. HP</label>
                                    <input type="text" class="form-control" name="guru_no_hp" value="{{ old('guru_no_hp') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control" name="guru_alamat" rows="1">{{ old('guru_alamat') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Siswa Fields -->
                        <div id="siswa-fields" style="display: none;">
                            <hr>
                            <h6 class="mb-3">Data Siswa</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">NISN</label>
                                    <input type="text" class="form-control @error('nisn') is-invalid @enderror" 
                                           name="nisn" value="{{ old('nisn') }}">
                                    @error('nisn')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('siswa_nama_lengkap') is-invalid @enderror" 
                                           name="siswa_nama_lengkap" value="{{ old('siswa_nama_lengkap') }}">
                                    @error('siswa_nama_lengkap')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Kelas</label>
                                    <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror">
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select name="siswa_jenis_kelamin" class="form-select @error('siswa_jenis_kelamin') is-invalid @enderror">
                                        <option value="">-- Pilih --</option>
                                        <option value="L" {{ old('siswa_jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('siswa_jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('siswa_jenis_kelamin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">No. HP</label>
                                    <input type="text" class="form-control" name="siswa_no_hp" value="{{ old('siswa_no_hp') }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control" name="siswa_alamat" rows="2">{{ old('siswa_alamat') }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jabatan</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_ketua_kelas" value="1" 
                                               {{ old('is_ketua_kelas') ? 'checked' : '' }}>
                                        <label class="form-check-label">Ketua Kelas</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_wakil_ketua" value="1" 
                                               {{ old('is_wakil_ketua') ? 'checked' : '' }}>
                                        <label class="form-check-label">Wakil Ketua Kelas</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.querySelector('select[name="role"]');
    const guruFields = document.getElementById('guru-fields');
    const siswaFields = document.getElementById('siswa-fields');
    
    function toggleFields() {
        const selectedRole = roleSelect.value;
        guruFields.style.display = selectedRole === 'guru' ? 'block' : 'none';
        siswaFields.style.display = selectedRole === 'siswa' ? 'block' : 'none';
    }
    
    roleSelect.addEventListener('change', toggleFields);
    toggleFields(); // Initial check
});
</script>
@endpush