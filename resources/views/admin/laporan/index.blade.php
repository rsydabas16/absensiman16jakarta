@extends('layouts.app')

@section('title', 'Laporan Absensi Guru')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Laporan Absensi Guru</h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Filter Laporan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.laporan.generate') }}" method="GET">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Jenis Laporan</label>
                                <select class="form-select" id="jenis_laporan" name="jenis_laporan" required>
                                    <option value="" disabled selected>Pilih Jenis Laporan</option>
                                    <option value="guru">Berdasarkan Guru</option>
                                    <option value="kelas">Berdasarkan Kelas</option>
                                    <option value="mata_pelajaran">Berdasarkan Mata Pelajaran</option>
                                </select>
                            </div>
                            <div class="col-md-4 filter-item" id="filter_guru" style="display: none;">
                                <label class="form-label">Guru</label>
                                <select class="form-select" name="guru_id">
                                    <option value="">Semua Guru</option>
                                    @foreach($guru as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama_lengkap }} ({{ $g->nip }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 filter-item" id="filter_kelas" style="display: none;">
                                <label class="form-label">Kelas</label>
                                <select class="form-select" name="kelas_id">
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 filter-item" id="filter_mapel" style="display: none;">
                                <label class="form-label">Mata Pelajaran</label>
                                <select class="form-select" name="mata_pelajaran_id">
                                    <option value="">Semua Mata Pelajaran</option>
                                    @foreach($mataPelajaran as $mp)
                                    <option value="{{ $mp->id }}">{{ $mp->nama_mata_pelajaran }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="tanggal_mulai" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" name="tanggal_akhir" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Format Laporan</label>
                                <select class="form-select" name="format" required>
                                    <option value="web">Tampilkan di Web</option>
                                    <option value="pdf">Export PDF</option>
                                    <option value="excel">Export Excel</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-filter-alt me-1"></i> Generate Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisLaporanSelect = document.getElementById('jenis_laporan');
        const filterGuru = document.getElementById('filter_guru');
        const filterKelas = document.getElementById('filter_kelas');
        const filterMapel = document.getElementById('filter_mapel');
        
        jenisLaporanSelect.addEventListener('change', function() {
            // Sembunyikan semua filter terlebih dahulu
            filterGuru.style.display = 'none';
            filterKelas.style.display = 'none';
            filterMapel.style.display = 'none';
            
            // Tampilkan filter sesuai jenis laporan
            if (this.value === 'guru') {
                filterGuru.style.display = 'block';
            } else if (this.value === 'kelas') {
                filterKelas.style.display = 'block';
            } else if (this.value === 'mata_pelajaran') {
                filterMapel.style.display = 'block';
            }
        });
    });
</script>
@endpush
@endsection