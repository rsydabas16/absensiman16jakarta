@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Selamat Datang, {{ auth()->user()->name }}! 🎉</h5>
                        <p class="mb-4">
                            Anda login sebagai <span class="fw-bold">Administrator</span>. 
                            Kelola semua data master dan monitoring sistem dari dashboard ini.
                        </p>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Kelola User</a>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('template/img/illustrations/man-with-laptop.png') }}" 
                             height="140" alt="View Badge User" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Total Users -->
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/chart-success.png') }}" alt="chart success" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Total Users</span>
                <h3 class="card-title mb-2">{{ $total_users }}</h3>
                <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> Aktif</small>
            </div>
        </div>
    </div>

    <!-- Total Guru -->
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/wallet-info.png') }}" alt="wallet info" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Total Guru</span>
                <h3 class="card-title mb-2">{{ $total_guru }}</h3>
                <small class="text-info fw-semibold">Guru Terdaftar</small>
            </div>
        </div>
    </div>

    <!-- Total Siswa -->
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/cc-primary.png') }}" alt="Credit Card" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Total Siswa</span>
                <h3 class="card-title mb-2">{{ $total_siswa }}</h3>
                <small class="text-primary fw-semibold">Siswa Aktif</small>
            </div>
        </div>
    </div>

    <!-- Total Kelas -->
    <div class="col-lg-3 col-md-6 col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('template/img/icons/unicons/paypal.png') }}" alt="paypal" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Total Kelas</span>
                <h3 class="card-title mb-2">{{ $total_kelas }}</h3>
                <small class="text-warning fw-semibold">Kelas Tersedia</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Users -->
    <div class="col-md-6 col-lg-12 order-2 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">User Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>Nomor Induk</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Terdaftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_users as $user)
                            <tr>
                                <td>{{ $user->nomor_induk }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'guru' ? 'primary' : ($user->role === 'siswa' ? 'success' : 'info')) }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection