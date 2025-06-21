@extends('layouts.app')

@section('title', 'Hasil Laporan Absensi Guru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Laporan Absensi /</span> Hasil Laporan
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $title }}</h5>
                    <div>
                        <a href="{{ route('admin.laporan.generate', [
                            'jenis_laporan' => request()->session()->get('jenis_laporan'),
                            'guru_id' => request()->session()->get('guru_id'),
                            'kelas_id' => request()->session()->get('kelas_id'),
                            'mata_pelajaran_id' => request()->session()->get('mata_pelajaran_id'),
                            'tanggal_mulai' => $tanggalMulai,
                            'tanggal_akhir' => $tanggalAkhir,
                            'format' => 'pdf'
                        ]) }}" class="btn btn-danger">
                            <i class="bx bxs-file-pdf me-1"></i> Export PDF
                        </a>
                        <a href="{{ route('admin.laporan.generate', [
                            'jenis_laporan' => request()->session()->get('jenis_laporan'),
                            'guru_id' => request()->session()->get('guru_id'),
                            'kelas_id' => request()->session()->get('kelas_id'),
                            'mata_pelajaran_id' => request()->session()->get('mata_pelajaran_id'),
                            'tanggal_mulai' => $tanggalMulai,
                            'tanggal_akhir' => $tanggalAkhir,
                            'format' => 'excel'
                        ]) }}" class="btn btn-success">
                            <i class="bx bxs-file-excel me-1"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-0">Periode: {{ \Carbon\Carbon::parse($tanggalMulai)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d-m-Y') }}</p>
                            </div>
                            <div>
                                <p class="mb-0">Total Data: {{ $summary['total'] }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Hadir</h5>
                                    <h2 class="mb-0">{{ $summary['hadir'] }}</h2>
                                    <p class="mb-0">{{ $summary['total'] > 0 ? round(($summary['hadir'] / $summary['total']) * 100, 2) : 0 }}%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Tidak Hadir</h5>
                                    <h2 class="mb-0">{{ $summary['tidak_hadir'] }}</h2>
                                    <p class="mb-0">{{ $summary['total'] > 0 ? round(($summary['tidak_hadir'] / $summary['total']) * 100, 2) : 0 }}%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Izin</h5>
                                    <h2 class="mb-0">{{ $summary['izin'] }}</h2>
                                    <p class="mb-0">{{ $summary['total'] > 0 ? round(($summary['izin'] / $summary['total']) * 100, 2) : 0 }}%</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title text-white">Sakit</h5>
                                    <h2 class="mb-0">{{ $summary['sakit'] }}</h2>
                                    <p class="mb-0">{{ $summary['total'] > 0 ? round(($summary['sakit'] / $summary['total']) * 100, 2) : 0 }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Guru</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Jam Ke</th>
                                    <th>Jam</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $index => $absensi)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->format('d-m-Y') }}</td>
                                        <td>{{ $absensi->guru->nama_lengkap }}</td>
                                        <td>{{ $absensi->jadwalPelajaran->kelas->nama_kelas }}</td>
                                        <td>{{ $absensi->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                                        <td>{{ $absensi->jadwalPelajaran->jam_ke }}</td>
                                        <td>{{ \Carbon\Carbon::parse($absensi->jadwalPelajaran->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($absensi->jadwalPelajaran->jam_selesai)->format('H:i') }}</td>
                                        <td>
                                            @if($absensi->status == 'hadir')
                                                <span class="badge bg-success">Hadir</span>
                                            @elseif($absensi->status == 'tidak_hadir')
                                                <span class="badge bg-danger">Tidak Hadir</span>
                                            @elseif($absensi->status == 'izin')
                                                <span class="badge bg-info">Izin</span>
                                            @elseif($absensi->status == 'sakit')
                                                <span class="badge bg-warning">Sakit</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data absensi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection