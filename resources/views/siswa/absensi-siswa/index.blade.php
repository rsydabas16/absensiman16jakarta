@extends('layouts.app')

@section('title', 'Absensi Siswa Harian')

@push('styles')
<style>
.status-select {
    min-width: 120px;
}
.bulk-actions {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Siswa /</span> Absensi Siswa Harian
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Absensi Kelas {{ auth()->user()->siswa->kelas->nama_kelas }}</h5>
            <div>
                <span class="badge bg-label-primary">{{ \Carbon\Carbon::parse($tanggalHariIni)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
            </div>
        </div>
        <div class="card-body">
            @if($sudahAbsen)
                <div class="alert alert-success">
                    <i class="bx bx-check-circle me-1"></i>
                    Absensi untuk hari ini sudah dilakukan. Anda masih bisa mengubahnya jika diperlukan.
                </div>
            @endif

            <form action="{{ route('siswa.absensi-siswa.store') }}" method="POST" id="absensiForm">
                @csrf
                
                <!-- Bulk Actions -->
                <div class="bulk-actions">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-2">Aksi Massal:</h6>
                            <button type="button" class="btn btn-sm btn-success me-2" onclick="setAllStatus('hadir')">
                                <i class="bx bx-check-circle me-1"></i> Semua Hadir
                            </button>
                            <button type="button" class="btn btn-sm btn-info me-2" onclick="setAllStatus('izin')">
                                <i class="bx bx-info-circle me-1"></i> Semua Izin
                            </button>
                            <button type="button" class="btn btn-sm btn-warning me-2" onclick="setAllStatus('sakit')">
                                <i class="bx bx-heart me-1"></i> Semua Sakit
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="setAllStatus('alfa')">
                                <i class="bx bx-x-circle me-1"></i> Semua Alfa
                            </button>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="bx bx-reset me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th width="15%">Status</th>
                                <th width="25%">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($daftarSiswa as $siswa)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input siswa-checkbox" value="{{ $siswa->id }}">
                                </td>
                                <td>{{ $siswa->nisn }}</td>
                                <td>
                                    {{ $siswa->nama_lengkap }}
                                    @if($siswa->is_ketua_kelas)
                                        <span class="badge bg-label-primary ms-1">Ketua</span>
                                    @elseif($siswa->is_wakil_ketua)
                                        <span class="badge bg-label-info ms-1">Wakil</span>
                                    @endif
                                </td>
                                <td>
                                    <select name="absensi[{{ $siswa->id }}][status]" class="form-select status-select" required>
                                        <option value="hadir" {{ isset($absensiHariIni[$siswa->id]) && $absensiHariIni[$siswa->id]->status == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                        <option value="izin" {{ isset($absensiHariIni[$siswa->id]) && $absensiHariIni[$siswa->id]->status == 'izin' ? 'selected' : '' }}>Izin</option>
                                        <option value="sakit" {{ isset($absensiHariIni[$siswa->id]) && $absensiHariIni[$siswa->id]->status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                        <option value="alfa" {{ isset($absensiHariIni[$siswa->id]) && $absensiHariIni[$siswa->id]->status == 'alfa' ? 'selected' : '' }}>Alfa</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="absensi[{{ $siswa->id }}][keterangan]" class="form-control" 
                                           placeholder="Keterangan (opsional)" 
                                           value="{{ isset($absensiHariIni[$siswa->id]) ? $absensiHariIni[$siswa->id]->keterangan : '' }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Simpan Absensi
                    </button>
                    <a href="{{ route('siswa.absensi-siswa.rekap') }}" class="btn btn-info">
                        <i class="bx bx-bar-chart me-1"></i> Lihat Rekap
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select All functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.siswa-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Set all status function
function setAllStatus(status) {
    const selectedCheckboxes = document.querySelectorAll('.siswa-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        alert('Silakan pilih siswa terlebih dahulu dengan mencentang checkbox.');
        return;
    }
    
    selectedCheckboxes.forEach(checkbox => {
        const siswaId = checkbox.value;
        const statusSelect = document.querySelector(`select[name="absensi[${siswaId}][status]"]`);
        if (statusSelect) {
            statusSelect.value = status;
        }
    });
}

// Reset form function
function resetForm() {
    if (confirm('Apakah Anda yakin ingin mereset semua pilihan?')) {
        document.getElementById('absensiForm').reset();
        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.siswa-checkbox').forEach(cb => cb.checked = false);
    }
}

// Form validation
document.getElementById('absensiForm').addEventListener('submit', function(e) {
    const selects = document.querySelectorAll('select[name*="[status]"]');
    let isValid = true;
    
    selects.forEach(select => {
        if (!select.value) {
            isValid = false;
            select.classList.add('is-invalid');
        } else {
            select.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Mohon pilih status untuk semua siswa.');
    }
});
</script>
@endpush