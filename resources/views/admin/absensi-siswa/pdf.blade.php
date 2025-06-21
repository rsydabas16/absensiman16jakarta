<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Siswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .status-hadir { color: #28a745; font-weight: bold; }
        .status-izin { color: #17a2b8; font-weight: bold; }
        .status-sakit { color: #ffc107; font-weight: bold; }
        .status-alfa { color: #dc3545; font-weight: bold; }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ABSENSI SISWA</h1>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y - HH:mm') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="10%">Tanggal</th>
                <th width="12%">NISN</th>
                <th width="20%">Nama Siswa</th>
                <th width="10%">Kelas</th>
                <th width="8%">Status</th>
                <th width="25%">Keterangan</th>
                <th width="10%">Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensiSiswa as $index => $absensi)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $absensi->tanggal->format('d/m/Y') }}</td>
                <td>{{ $absensi->siswa->nisn }}</td>
                <td>{{ $absensi->siswa->nama_lengkap }}</td>
                <td>{{ $absensi->kelas->nama_kelas }}</td>
                <td class="status-{{ $absensi->status }}">{{ ucfirst($absensi->status) }}</td>
                <td>{{ $absensi->keterangan ?? '-' }}</td>
                <td>{{ $absensi->pencatat->nama_lengkap }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada data absensi</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total Data: {{ $absensiSiswa->count() }} record</p>
    </div>
</body>
</html>