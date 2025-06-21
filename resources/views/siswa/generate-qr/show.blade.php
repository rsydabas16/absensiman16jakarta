<!-- resources/views/siswa/generate-qr/show.blade.php -->
@extends('layouts.app')

@section('title', 'QR Code Absensi')

@push('styles')
<style>
    .qr-container {
        max-width: 400px;
        margin: 0 auto;
        text-align: center;
    }
    .countdown {
        font-size: 1.5rem;
        font-weight: bold;
        color: #dc3545;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Siswa /</span> QR Code Absensi
    </h4>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">QR Code untuk Absensi Guru</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading fw-bold mb-1">Informasi Jadwal:</h6>
                        <p class="mb-0">
                            <strong>Mata Pelajaran:</strong> {{ $jadwal->mataPelajaran->nama_mata_pelajaran }}<br>
                            <strong>Guru:</strong> {{ $jadwal->guru->nama_lengkap }}<br>
                            <strong>Jam ke:</strong> {{ $jadwal->jam_ke }} 
                            ({{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }})
                        </p>
                    </div>

                    <div class="qr-container">
                        <div id="qr-code-display">
                            {!! $qrImage !!}
                        </div>
                        
                        <div class="mt-3">
                            <p class="mb-2">QR Code akan expired dalam:</p>
                            <div class="countdown" id="countdown">15:00</div>
                        </div>
                        
                        <button class="btn btn-primary mt-3" id="regenerate-btn">
                            <i class="bx bx-refresh me-1"></i> Generate QR Baru
                        </button>
                        
                        <div class="mt-4">
                            <p class="text-muted small">
                                Tunjukkan QR Code ini kepada guru untuk melakukan absensi.
                                QR Code akan otomatis expired setelah 15 menit.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let timeLeft = 900; // 15 menit dalam detik
    const jadwalId = {{ $jadwal->id }};
    
    // Countdown timer
    const countdown = setInterval(() => {
        timeLeft--;
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        document.getElementById('countdown').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            clearInterval(countdown);
            document.getElementById('countdown').textContent = 'EXPIRED';
            document.getElementById('qr-code-display').innerHTML = 
                '<div class="alert alert-danger">QR Code telah expired. Silakan generate ulang.</div>';
        }
    }, 1000);
    
    // Regenerate QR
    document.getElementById('regenerate-btn').addEventListener('click', function() {
        fetch("{{ route('siswa.generate-qr.regenerate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ jadwal_id: jadwalId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update QR image
                document.getElementById('qr-code-display').innerHTML = 
                    '<img src="data:image/png;base64,' + data.qr_image + '" alt="QR Code">';
                
                // Reset timer
                timeLeft = 900;
                clearInterval(countdown);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal generate QR baru. Silakan coba lagi.');
        });
    });
</script>
@endpush