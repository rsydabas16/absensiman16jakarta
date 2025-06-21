@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Guru /</span> Rekap Absensi
    </h4>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Rekap</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('guru.rekap.index') }}" method="GET" class="row g-3">
                <!-- Filter Periode -->
                <div class="col-md-6">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" 
                           value="{{ $tanggalMulai }}" max="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" 
                           value="{{ $tanggalSelesai }}" max="{{ date('Y-m-d') }}" required>
                </div>

                <!-- Filter Kelas -->
                <div class="col-md-6">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasOptions as $kelas)
                            <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Mata Pelajaran -->
                <div class="col-md-6">
                    <label class="form-label">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" class="form-select">
                        <option value="">Semua Mata Pelajaran</option>
                        @foreach($mataPelajaranOptions as $mapel)
                            <option value="{{ $mapel->id }}" {{ $mataPelajaranId == $mapel->id ? 'selected' : '' }}>
                                {{ $mapel->nama_mata_pelajaran }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Action -->
                <div class="col-md-12">
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('guru.rekap.export', array_merge(request()->all())) }}" 
                           class="btn btn-success">
                            <i class="bx bx-download me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('guru.rekap.export-pdf', array_merge(request()->all())) }}" 
                           class="btn btn-danger">
                            <i class="bx bx-download me-1"></i> Export PDF
                        </a>
                        <a href="{{ route('guru.rekap.index') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh me-1"></i> Reset Filter
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Periode -->
    <div class="alert alert-info mb-4">
        <strong>Periode:</strong> {{ $periodeInfo }}
        @if($kelasInfo)
            <br><strong>Kelas:</strong> {{ $kelasInfo }}
        @endif
        @if($mataPelajaranInfo)
            <br><strong>Mata Pelajaran:</strong> {{ $mataPelajaranInfo }}
        @endif
    </div>

    <!-- Statistik -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Hadir</h5>
                    <h2 class="mb-0">{{ $statistik['hadir'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Izin</h5>
                    <h2 class="mb-0">{{ $statistik['izin'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Sakit</h5>
                    <h2 class="mb-0">{{ $statistik['sakit'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Dinas Luar</h5>
                    <h2 class="mb-0">{{ $statistik['dinas_luar'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Cuti</h5>
                    <h2 class="mb-0">{{ $statistik['cuti'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Alpa</h5>
                    <h2 class="mb-0">{{ $statistik['alpa'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rekap -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Absensi ({{ $absensi->count() }} data)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Jam Ke</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Jam Absen</th>
                            <th>Keterangan</th>
                            <th>Tugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensi as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $item->tanggal->locale('id')->dayName }}</td>
                            <td>{{ $item->jadwalPelajaran->jam_ke }}</td>
                            <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                            <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
                            <td>
                                <span class="badge bg-label-{{ $item->status === 'hadir' ? 'success' : ($item->status === 'izin' ? 'info' : ($item->status === 'sakit' ? 'warning' : 'danger')) }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->jam_absen ?? '-' }}</td>
                            <td>
                                @if($item->status !== 'hadir')
                                    <small>{{ $item->alasan }}</small>
                                    @if($item->tugas)
                                        <br>
                                        <span class="badge bg-label-secondary">Ada Tugas</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($item->tugas)
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#tugasModal{{ $item->id }}">
                                        <i class="bx bx-book-open me-1"></i> Lihat
                                    </button>
                                    
                                    <!-- Modal Tugas -->
                                    <div class="modal fade" id="tugasModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tugas {{ $item->tanggal->format('d/m/Y') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Mata Pelajaran:</strong> {{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</p>
                                                    <p><strong>Kelas:</strong> {{ $item->jadwalPelajaran->kelas->nama_kelas }}</p>
                                                    <hr>
                                                    <div>{{ $item->tugas }}</div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data absensi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validasi tanggal
    const tanggalMulai = document.querySelector('input[name="tanggal_mulai"]');
    const tanggalSelesai = document.querySelector('input[name="tanggal_selesai"]');
    
    tanggalMulai.addEventListener('change', function() {
        tanggalSelesai.min = this.value;
        if (tanggalSelesai.value && tanggalSelesai.value < this.value) {
            tanggalSelesai.value = this.value;
        }
    });
    
    tanggalSelesai.addEventListener('change', function() {
        if (this.value < tanggalMulai.value) {
            alert('Tanggal selesai tidak boleh lebih kecil dari tanggal mulai');
            this.value = tanggalMulai.value;
        }
    });
});
</script>
@endsection










{{-- @extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Guru /</span> Rekap Absensi
    </h4>


  
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Rekap</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('guru.rekap.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for($i = date('Y'); $i >= date('Y') - 2; $i--)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('guru.rekap.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}" 
                           class="btn btn-success">
                            <i class="bx bx-download me-1"></i> Export Excel
                        </a>
                        <a href="{{ route('guru.rekap.export-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" 
                           class="btn btn-danger">
                            <i class="bx bx-download me-1"></i> Export PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Hadir</h5>
                    <h2 class="mb-0">{{ $statistik['hadir'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Izin</h5>
                    <h2 class="mb-0">{{ $statistik['izin'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Sakit</h5>
                    <h2 class="mb-0">{{ $statistik['sakit'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Alpa</h5>
                    <h2 class="mb-0">{{ $statistik['alpa'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rekap -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Absensi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Jam Ke</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Jam Absen</th>
                            <th>Keterangan</th>
                            <th>Tugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensi as $item)
                        <tr>
                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $item->tanggal->locale('id')->dayName }}</td>
                            <td>{{ $item->jadwalPelajaran->jam_ke }}</td>
                            <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                            <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
                            <td>
                                <span class="badge bg-label-{{ $item->status === 'hadir' ? 'success' : ($item->status === 'izin' ? 'info' : ($item->status === 'sakit' ? 'warning' : 'danger')) }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->jam_absen ?? '-' }}</td>
                            <td>
                                @if($item->status !== 'hadir')
                                    <small>{{ $item->alasan }}</small>
                                    @if($item->tugas)
                                        <br>
                                        <span class="badge bg-label-secondary">Ada Tugas</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($item->tugas)
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#tugasModal{{ $item->id }}">
                                        <i class="bx bx-book-open me-1"></i> Lihat
                                    </button>
                                    
                                    <!-- Modal Tugas -->
                                    <div class="modal fade" id="tugasModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tugas {{ $item->tanggal->format('d/m/Y') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Mata Pelajaran:</strong> {{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</p>
                                                    <p><strong>Kelas:</strong> {{ $item->jadwalPelajaran->kelas->nama_kelas }}</p>
                                                    <hr>
                                                    <div>{{ $item->tugas }}</div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data absensi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection --}}




<!-- resources/views/guru/absensi/rekap.blade.php -->
{{-- @extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Guru /</span> Rekap Absensi
    </h4>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Rekap</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('guru.rekap.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for($i = date('Y'); $i >= date('Y') - 2; $i--)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('guru.rekap.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}" 
                           class="btn btn-success">
                            <i class="bx bx-download me-1"></i> Export
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Hadir</h5>
                    <h2 class="mb-0">{{ $statistik['hadir'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Izin</h5>
                    <h2 class="mb-0">{{ $statistik['izin'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Sakit</h5>
                    <h2 class="mb-0">{{ $statistik['sakit'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Alpa</h5>
                    <h2 class="mb-0">{{ $statistik['alpa'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rekap -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detail Absensi</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Jam Ke</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Jam Absen</th>
                            <th>Keterangan</th>
                            <th>Tugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensi as $item)
                        <tr>
                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $item->tanggal->locale('id')->dayName }}</td>
                            <td>{{ $item->jadwalPelajaran->jam_ke }}</td>
                            <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                            <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
                            <td>
                                <span class="badge bg-label-{{ $item->status === 'hadir' ? 'success' : ($item->status === 'izin' ? 'info' : ($item->status === 'sakit' ? 'warning' : 'danger')) }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->jam_absen ?? '-' }}</td>
                            <td>
                                @if($item->status !== 'hadir')
                                    <small>{{ $item->alasan }}</small>
                                    @if($item->tugas)
                                        <br>
                                        <span class="badge bg-label-secondary">Ada Tugas</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                              <td>
                @if($item->tugas)
                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#tugasModal{{ $item->id }}">
                        <i class="bx bx-book-open me-1"></i> Lihat
                    </button>
                    
                    <!-- Modal Tugas -->
                    <div class="modal fade" id="tugasModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Tugas {{ $item->tanggal->format('d/m/Y') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Mata Pelajaran:</strong> {{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</p>
                                    <p><strong>Kelas:</strong> {{ $item->jadwalPelajaran->kelas->nama_kelas }}</p>
                                    <hr>
                                    <div>{{ $item->tugas }}</div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    -
                @endif
            </td>
        
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data absensi.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
@endsection --}}



<!-- resources/views/guru/absensi/rekap.blade.php (update bagian detail) -->
<!-- ... kode lain tidak berubah ... -->

<!-- Di bagian tabel, tambahkan kolom untuk tugas -->

 {{-- <table class="table table-hover">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Hari</th>
            <th>Jam Ke</th>
            <th>Mata Pelajaran</th>
            <th>Kelas</th>
            <th>Status</th>
            <th>Jam Absen</th>
            <th>Keterangan</th>
            <th>Tugas</th>
        </tr>
    </thead>
    <tbody>
        @forelse($absensi as $item)
        <tr>
            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
            <td>{{ $item->tanggal->locale('id')->dayName }}</td>
            <td>{{ $item->jadwalPelajaran->jam_ke }}</td>
            <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
            <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
            <td>
                <span class="badge bg-label-{{ $item->status === 'hadir' ? 'success' : ($item->status === 'izin' ? 'info' : ($item->status === 'sakit' ? 'warning' : 'danger')) }}">
                    {{ ucfirst($item->status) }}
                </span>
            </td>
            <td>{{ $item->jam_absen ?? '-' }}</td>
            <td>
                @if($item->status !== 'hadir')
                    <small>{{ $item->alasan }}</small>
                @else
                    -
                @endif
            </td>
            <td>
                @if($item->tugas)
                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#tugasModal{{ $item->id }}">
                        <i class="bx bx-book-open me-1"></i> Lihat
                    </button>
                    
                    <!-- Modal Tugas -->
                    <div class="modal fade" id="tugasModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Tugas {{ $item->tanggal->format('d/m/Y') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Mata Pelajaran:</strong> {{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</p>
                                    <p><strong>Kelas:</strong> {{ $item->jadwalPelajaran->kelas->nama_kelas }}</p>
                                    <hr>
                                    <div>{{ $item->tugas }}</div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    -
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center">Tidak ada data absensi.</td>
        </tr>
        @endforelse
    </tbody>
</table>  --}}





 