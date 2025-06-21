@extends('layouts.partials.app')

@section('title', 'My Profile')

@section('content')
@push('styles')
<style>
    .profile-avatar {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 4px solid #f8f9fa;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        margin: 0 auto 20px;
        display: block;
    }
    
    .profile-info-card {
        text-align: center;
        padding: 20px;
    }
    
    .profile-name {
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .profile-role {
        margin-bottom: 16px;
        color: #6c757d;
    }
    
    .profile-badges {
        margin-top: 12px;
    }
</style>
@endpush
<div class="row">
    <div class="col-12">
        <div class="card mb-6">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">My Profile</h5>
                <div>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="bx bx-edit me-1"></i>
                        Edit Profile
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">


                    <!-- Profile Picture -->
<div class="col-xl-4 col-lg-5 col-md-5">
    <div class="profile-info-card">
        @if($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded-circle profile-avatar">
        @else
            <img src="{{ asset('template/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle profile-avatar">
        @endif
        
        <h5 class="profile-name">{{ $user->name }}</h5>
        <p class="profile-role">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
        
        @if($user->role === 'siswa' && $profileData)
            <div class="profile-badges">
                @if($profileData->is_ketua_kelas)
                    <span class="badge bg-primary">Ketua Kelas</span>
                @elseif($profileData->is_wakil_ketua)
                    <span class="badge bg-info">Wakil Ketua Kelas</span>
                @endif
            </div>
        @endif
    </div>
</div>

                    <!-- Profile Picture -->
                    {{-- <div class="col-xl-4 col-lg-5 col-md-5">
                        <div class="d-flex justify-content-center">
                            <div class="card-body text-center">
                                <div class="avatar avatar-xl mb-5">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                    @else
                                        <img src="{{ asset('template/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                    @endif
                                </div>
                                <h5 class="mb-1 ">{{ $user->name }}</h5>
                                <p class="text-muted mb-5">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
                                @if($user->role === 'siswa' && $profileData)
                                    @if($profileData->is_ketua_kelas)
                                        <span class="badge bg-primary mt-2">Ketua Kelas</span>
                                    @elseif($profileData->is_wakil_ketua)
                                        <span class="badge bg-info mt-2">Wakil Ketua Kelas</span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div> --}}

                    <!-- Profile Information -->
                    <div class="col-xl-8 col-lg-7 col-md-7">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted text-uppercase mb-4">Informasi Akun</h6>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                                    <label>Nama</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" value="{{ $user->email }}" readonly>
                                    <label>Email</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" value="{{ $user->nomor_induk }}" readonly>
                                    <label>Nomor Induk</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}" readonly>
                                    <label>Role</label>
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
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" value="{{ $profileData->nama_lengkap ?? '-' }}" readonly>
                                            <label>Nama Lengkap</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" value="{{ $profileData->jenis_kelamin ?? '-' }}" readonly>
                                            <label>Jenis Kelamin</label>
                                        </div>
                                    </div>
                                @elseif($user->role === 'siswa')
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" value="{{ $profileData->nisn ?? '-' }}" readonly>
                                            <label>NISN</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" value="{{ $profileData->nama_lengkap ?? '-' }}" readonly>
                                            <label>Nama Lengkap</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" value="{{ $profileData->kelas->nama_kelas ?? '-' }}" readonly>
                                            <label>Kelas</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating form-floating-outline">
                                            <input type="text" class="form-control" value="{{ $profileData->jenis_kelamin ?? '-' }}" readonly>
                                            <label>Jenis Kelamin</label>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <input type="text" class="form-control" value="{{ $profileData->no_hp ?? '-' }}" readonly>
                                        <label>No. HP</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating form-floating-outline">
                                        <textarea class="form-control" style="height: 60px;" readonly>{{ $profileData->alamat ?? '-' }}</textarea>
                                        <label>Alamat</label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Account Info -->
                        <div class="row mt-6">
                            <div class="col-12">
                                <h6 class="text-muted text-uppercase mb-4">Informasi Akun</h6>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" value="{{ $user->created_at->format('d/m/Y H:i') }}" readonly>
                                    <label>Tanggal Dibuat</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating form-floating-outline">
                                    <input type="text" class="form-control" value="{{ $user->updated_at->format('d/m/Y H:i') }}" readonly>
                                    <label>Terakhir Diupdate</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Card -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ubah Password</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" name="current_password" required>
                                <label for="current_password">Password Saat Ini</label>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                        
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                <label for="password">Password Baru</label>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating form-floating-outline">
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                                <label for="password_confirmation">Konfirmasi Password Baru</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-lock me-1"></i>
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection