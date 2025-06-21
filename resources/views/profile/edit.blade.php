@extends('layouts.partials.app')

@section('title', 'Edit Profile')

@push('styles')
<style>
    .avatar-upload {
        position: relative;
        max-width: 150px;
        margin: 0 auto;
    }
    
    .avatar-upload .avatar-edit {
        position: absolute;
        right: 12px;
        z-index: 1;
        top: 10px;
    }
    
    .avatar-upload .avatar-edit input {
        display: none;
    }
    
    .avatar-upload .avatar-edit label {
        display: inline-block;
        width: 34px;
        height: 34px;
        margin-bottom: 0;
        border-radius: 100%;
        background: #FFFFFF;
        border: 1px solid transparent;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.12);
        cursor: pointer;
        font-weight: normal;
        transition: all 0.2s ease-in-out;
    }
    
    .avatar-upload .avatar-edit label:hover {
        background: #f1f1f1;
        border-color: #d6d6d6;
    }
    
    .avatar-upload .avatar-edit label:after {
        content: "\270E";
        font-family: 'boxicons';
        color: #757575;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 16px;
    }
    
    .avatar-upload .avatar-preview {
        width: 150px;
        height: 150px;
        position: relative;
        border-radius: 100%;
        border: 6px solid #F8F8F8;
        box-shadow: 0px 2px 4px 0px rgba(0, 0, 0, 0.1);
    }
    
    .avatar-upload .avatar-preview > div {
        width: 100%;
        height: 100%;
        border-radius: 100%;
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Edit Profile</h5>
                <div>
                    <a href="{{ route('profile.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i>
                        Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Profile Picture Upload -->
                        <div class="col-xl-4 col-lg-5 col-md-5">
                            <div class="card-body text-center">
                                <div class="avatar-upload">
                                    <div class="avatar-edit">
                                        <input type="file" id="imageUpload" name="avatar" accept=".png, .jpg, .jpeg" />
                                        <label for="imageUpload"></label>
                                    </div>
                                    <div class="avatar-preview">
                                        <div id="imagePreview" style="background-image: url('{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('template/img/avatars/1.png') }}');">
                                        </div>
                                    </div>
                                </div>
                                @error('avatar')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                                <h5 class="mt-4 mb-1">{{ $user->name }}</h5>
                                <p class="text-muted mb-0">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
                                <small class="text-muted">Format: JPG, PNG. Max: 2MB</small>
                            </div>
                        </div>

                        <!-- Profile Form -->
                        <div class="col-xl-8 col-lg-7 col-md-7">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-muted text-uppercase mb-4">Informasi Akun</h6>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        <label for="name">Nama</label>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        <label for="email">Email</label>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $user->nomor_induk }}" readonly>
                                        <label>Nomor Induk</label>
                                        <small class="text-muted">Field ini tidak dapat diubah</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}" readonly>
                                        <label>Role</label>
                                        <small class="text-muted">Field ini tidak dapat diubah</small>
                                    </div>
                                </div>
                            </div>

                            @if($profileData)
                                <div class="row mt-6">
                                    <div class="col-12">
                                        <h6 class="text-muted text-uppercase mb-4">
                                            @if($user->role === 'guru')
                                                Informasi Guru
                                            @elseif($user->role === 'siswa')
                                                Informasi Siswa
                                            @endif
                                        </h6>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    @if($user->role === 'guru')
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" value="{{ $profileData->nip ?? '-' }}" readonly>
                                                <label>NIP</label>
                                                <small class="text-muted">Field ini tidak dapat diubah</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                                       id="nama_lengkap" name="nama_lengkap" 
                                                       value="{{ old('nama_lengkap', $profileData->nama_lengkap) }}" required>
                                                <label for="nama_lengkap">Nama Lengkap</label>
                                                @error('nama_lengkap')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" value="{{ $profileData->jenis_kelamin ?? '-' }}" readonly>
                                                <label>Jenis Kelamin</label>
                                                <small class="text-muted">Field ini tidak dapat diubah</small>
                                            </div>
                                        </div>
                                    @elseif($user->role === 'siswa')
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" value="{{ $profileData->nisn ?? '-' }}" readonly>
                                                <label>NISN</label>
                                                <small class="text-muted">Field ini tidak dapat diubah</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" 
                                                       id="nama_lengkap" name="nama_lengkap" 
                                                       value="{{ old('nama_lengkap', $profileData->nama_lengkap) }}" required>
                                                <label for="nama_lengkap">Nama Lengkap</label>
                                                @error('nama_lengkap')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" value="{{ $profileData->kelas->nama_kelas ?? '-' }}" readonly>
                                                <label>Kelas</label>
                                                <small class="text-muted">Field ini tidak dapat diubah</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating form-floating-outline">
                                                <input type="text" class="form-control" value="{{ $profileData->jenis_kelamin ?? '-' }}" readonly>
                                                <label>Jenis Kelamin</label>
                                                <small class="text-muted">Field ini tidak dapat diubah</small>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control @error('no_hp') is-invalid @enderror" 
                                                   id="no_hp" name="no_hp" 
                                                   value="{{ old('no_hp', $profileData->no_hp) }}">
                                            <label for="no_hp">No. HP</label>
                                            @error('no_hp')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                                      id="alamat" name="alamat" style="height: 60px;">{{ old('alamat', $profileData->alamat) }}</textarea>
                                            <label for="alamat">Alamat</label>
                                            @error('alamat')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>
                                    Simpan Perubahan
                                </button>
                                <a href="{{ route('profile.index') }}" class="btn btn-secondary ms-2">
                                    <i class="bx bx-x me-1"></i>
                                    Batal
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $("#imageUpload").change(function() {
        readURL(this);
    });
    
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').css('background-image', 'url('+e.target.result +')');
                $('#imagePreview').hide();
                $('#imagePreview').fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
});
</script>
@endpush