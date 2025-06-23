<!-- resources/views/guru/absensi/create.blade.php -->
@extends('layouts.app')

@section('title', 'Form Absensi')

@push('styles')
<style>
    .qr-scanner-container {
        position: relative;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    #qr-reader {
        width: 100%;
    }
    .scan-region-highlight {
        border: 2px solid #00FF00 !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Guru /</span> Absensi
    </h4>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form Absensi</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading fw-bold mb-1">Informasi Jadwal:</h6>
                        <p class="mb-0">
                            <strong>Mata Pelajaran:</strong> {{ $jadwal->mataPelajaran->nama_mata_pelajaran }}<br>
                            <strong>Kelas:</strong> {{ $jadwal->kelas->nama_kelas }}<br>
                            <strong>Jam ke:</strong> {{ $jadwal->jam_ke }} ({{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }})
                        </p>
                    </div>

                    <!-- Tab navigation -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#scan-qr" aria-selected="true">
                                <i class="bx bx-qr-scan me-1"></i> Scan QR (Hadir)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tidak-hadir" aria-selected="false">
                                <i class="bx bx-x-circle me-1"></i> Tidak Hadir
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-4">
                        <!-- Tab Scan QR -->
                        <div class="tab-pane fade show active" id="scan-qr" role="tabpanel">
                            <div class="text-center mb-4">
                                <p>Silakan scan QR Code dari ketua/wakil ketua kelas</p>
                            </div>
                            
                            <div class="qr-scanner-container">
                                <div id="qr-reader"></div>
                            </div>

                            <div id="scan-result" class="mt-4 text-center" style="display: none;">
                                <div class="alert alert-success">
                                    <i class="bx bx-check-circle me-1"></i>
                                    <span id="scan-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Tidak Hadir -->
                        <div class="tab-pane fade" id="tidak-hadir" role="tabpanel">
                            <form action="{{ route('guru.absensi.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="jadwal_pelajaran_id" value="{{ $jadwal->id }}">
                                
                                <div class="mb-3">
                                    <label class="form-label">Status Kehadiran <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="izin">Izin</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="dinas_luar">Dinas Luar</option>
                                        <option value="cuti">Cuti</option>
                                        {{-- <option value="tidak_hadir">Tidak Hadir (Alpa)</option> --}}
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Alasan</label>
                                    <textarea name="alasan" class="form-control" rows="3" 
                                              placeholder="Opsional - Jelaskan alasan ketidakhadiran..."></textarea>
                                    {{-- <small class="text-muted">Field ini opsional untuk semua status kecuali Tidak Hadir (Alpa)</small> --}}
                                </div>

                                <div class="mb-3" id="tugas-container" >
                                    <label class="form-label">Tugas untuk Siswa <span class="text-danger">*</span></label>
                                    <textarea name="tugas" class="form-control" rows="5" 
                                              placeholder="Berikan tugas untuk siswa..."></textarea>
                                    {{-- <small class="text-muted">Wajib diisi untuk status Tidak Hadir (Alpa)</small> --}}
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-send me-1"></i> Kirim
                                </button>
                                <a href="{{ route('guru.absensi.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Batal
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    // QR Scanner
    const html5QrCode = new Html5Qrcode("qr-reader");
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        rememberLastUsedCamera: true,
    };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        (decodedText, decodedResult) => {
            // Stop scanning
            html5QrCode.stop();
            
            // Send to server
            $.ajax({
                url: "{{ route('guru.absensi.scan-qr') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    qr_code: decodedText,
                    jadwal_pelajaran_id: "{{ $jadwal->id }}"
                },
                success: function(response) {
                    $('#scan-result').show();
                    $('#scan-message').text('Absensi berhasil! Anda tercatat hadir.');
                    
                    setTimeout(() => {
                        window.location.href = "{{ route('guru.absensi.index') }}";
                    }, 2000);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.message || 'QR Code tidak valid!');
                    // Restart scanner
                    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanError);
                }
            });
        },
        (errorMessage) => {
            // Handle scan error
        }
    ).catch(err => {
        console.error(err);
        alert('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin kamera.');
    });
    


    // Form validation
    $('form').on('submit', function(e) {
        const status = $('select[name="status"]').val();
        const tugas = $('textarea[name="tugas"]').val().trim();
        
        if (status === 'tidak_hadir' && !tugas) {
            e.preventDefault();
            alert('Tugas untuk siswa wajib diisi untuk status Tidak Hadir (Alpa)');
            $('textarea[name="tugas"]').focus();
        }
    });
</script>
@endpush













{{-- @extends('layouts.app')

@section('title', 'Form Absensi')

@push('styles')
<style>
    .qr-scanner-container {
        position: relative;
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    #qr-reader {
        width: 100%;
    }
    .scan-region-highlight {
        border: 2px solid #00FF00 !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Guru /</span> Absensi
    </h4>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form Absensi</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading fw-bold mb-1">Informasi Jadwal:</h6>
                        <p class="mb-0">
                            <strong>Mata Pelajaran:</strong> {{ $jadwal->mataPelajaran->nama_mata_pelajaran }}<br>
                            <strong>Kelas:</strong> {{ $jadwal->kelas->nama_kelas }}<br>
                            <strong>Jam ke:</strong> {{ $jadwal->jam_ke }} ({{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }})
                        </p>
                    </div>

                    <!-- Tab navigation -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#scan-qr" aria-selected="true">
                                <i class="bx bx-qr-scan me-1"></i> Scan QR (Hadir)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tidak-hadir" aria-selected="false">
                                <i class="bx bx-x-circle me-1"></i> Tidak Hadir
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-4">
                        <!-- Tab Scan QR -->
                        <div class="tab-pane fade show active" id="scan-qr" role="tabpanel">
                            <div class="text-center mb-4">
                                <p>Silakan scan QR Code dari ketua/wakil ketua kelas</p>
                            </div>
                            
                            <div class="qr-scanner-container">
                                <div id="qr-reader"></div>
                            </div>

                            <div id="scan-result" class="mt-4 text-center" style="display: none;">
                                <div class="alert alert-success">
                                    <i class="bx bx-check-circle me-1"></i>
                                    <span id="scan-message"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Tidak Hadir -->
                        <div class="tab-pane fade" id="tidak-hadir" role="tabpanel">
                            <form action="{{ route('guru.absensi.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="jadwal_pelajaran_id" value="{{ $jadwal->id }}">
                                
                                <div class="mb-3">
                                    <label class="form-label">Status Kehadiran</label>
                                    <select name="status" class="form-select" required>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="izin">Izin</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="tidak_hadir">Tidak Hadir (Alpa)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Alasan</label>
                                    <textarea name="alasan" class="form-control" rows="3" required></textarea>
                                </div>

                                <div class="mb-3" id="tugas-container" style="display: none;">
                                    <label class="form-label">Tugas untuk Siswa</label>
                                    <textarea name="tugas" class="form-control" rows="5" 
                                              placeholder="Berikan tugas untuk siswa..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-send me-1"></i> Kirim
                                </button>
                                <a href="{{ route('guru.absensi.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Batal
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    // QR Scanner
    const html5QrCode = new Html5Qrcode("qr-reader");
    
    const config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        rememberLastUsedCamera: true,
    };
    
    html5QrCode.start(
        { facingMode: "environment" },
        config,
        (decodedText, decodedResult) => {
            // Stop scanning
            html5QrCode.stop();
            
            // Send to server
            $.ajax({
                url: "{{ secure_url('guru.absensi.scan-qr') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    qr_code: decodedText,
                    jadwal_pelajaran_id: "{{ $jadwal->id }}"
                },
                success: function(response) {
                    $('#scan-result').show();
                    $('#scan-message').text('Absensi berhasil! Anda tercatat hadir.');
                    
                    setTimeout(() => {
                        window.location.href = "{{ route('guru.absensi.index') }}";
                    }, 2000);
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.message || 'QR Code tidak valid!');
                    // Restart scanner
                    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanError);
                }
            });
        },
        (errorMessage) => {
            // Handle scan error
        }
    ).catch(err => {
        console.error(err);
        alert('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin kamera.');
    });
    
    // Show/hide tugas field
    $('select[name="status"]').on('change', function() {
        if ($(this).val() === 'tidak_hadir') {
            $('#tugas-container').show();
            $('textarea[name="tugas"]').attr('required', true);
        } else {
            $('#tugas-container').hide();
            $('textarea[name="tugas"]').attr('required', false);
        }
    });
</script>
@endpush --}}
