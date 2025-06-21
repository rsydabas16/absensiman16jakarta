<!-- resources/views/exports/absensi-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Guru</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            font-weight: normal;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 5px 0;
        }
        .statistics {
            margin-bottom: 30px;
        }
        .statistics table {
            width: 100%;
            border-collapse: collapse;
        }
        .statistics th, .statistics td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .statistics th {
            background-color: #f4f4f4;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 12px;
        }
        .data-table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        .status-hadir { color: #28a745; }
        .status-izin { color: #17a2b8; }
        .status-sakit { color: #ffc107; }
        .status-alpa { color: #dc3545; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ABSENSI GURU</h1>
        <h2>Periode: {{ \Carbon\Carbon::parse($filters['tanggal_mulai'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['tanggal_akhir'])->format('d/m/Y') }}</h2>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>Tanggal Cetak:</strong></td>
                <td>{{ $tanggal_cetak }}</td>
            </tr>
            @if($filters['guru_id'])
            <tr>
                <td><strong>Guru:</strong></td>
                <td>{{ \App\Models\Guru::find($filters['guru_id'])->nama_lengkap }}</td>
            </tr>
            @endif
            @if($filters['kelas_id'])
            <tr>
                <td><strong>Kelas:</strong></td>
                <td>{{ \App\Models\Kelas::find($filters['kelas_id'])->nama_kelas }}</td>
            </tr>
            @endif
            @if($filters['status'])
            <tr>
                <td><strong>Status:</strong></td>
                <td>{{ ucfirst($filters['status']) }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="statistics">
        <h3>Statistik Kehadiran</h3>
        <table>
            <tr>
                <th>Total</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpa</th>
            </tr>
            <tr>
                <td>{{ $statistik['total'] }}</td>
                <td class="status-hadir">{{ $statistik['hadir'] }}</td>
                <td class="status-izin">{{ $statistik['izin'] }}</td>
                <td class="status-sakit">{{ $statistik['sakit'] }}</td>
                <td class="status-alpa">{{ $statistik['alpa'] }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Guru</th>
                <th>Mata Pelajaran</th>
                <th>Kelas</th>
                <th>Jam Ke</th>
                <th>Status</th>
                <th>Jam Absen</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                <td>{{ $item->tanggal->locale('id')->dayName }}</td>
                <td>{{ $item->guru->nama_lengkap }}</td>
                <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
                <td>{{ $item->jadwalPelajaran->jam_ke }}</td>
                <td class="status-{{ $item->status === 'tidak_hadir' ? 'alpa' : $item->status }}">
                    {{ ucfirst($item->status) }}
                </td>
                <td>{{ $item->jam_absen ?? '-' }}</td>
                <td>{{ $item->alasan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ $tanggal_cetak }}</p>
    </div>
</body>
</html>