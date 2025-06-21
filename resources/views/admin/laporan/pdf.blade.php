<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
        }
        .header p {
            margin: 5px 0;
        }
        .meta {
            margin-bottom: 20px;
        }
        .meta table {
            width: 100%;
        }
        .meta table td {
            padding: 3px 0;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary table td, .summary table th {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
        }
        .summary table th {
            background-color: #f2f2f2;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data th, table.data td {
            border: 1px solid #ddd;
            padding: 5px;
        }
        table.data th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .status-hadir {
            color: green;
            font-weight: bold;
        }
        .status-tidak-hadir {
            color: red;
            font-weight: bold;
        }
        .status-izin {
            color: blue;
            font-weight: bold;
        }
        .status-sakit {
            color: orange;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            padding: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($tanggalMulai)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d-m-Y') }}</p>
    </div>
    
    <div class="meta">
        <table>
            <tr>
                <td width="25%">Tanggal Cetak</td>
                <td width="2%">:</td>
                <td>{{ date('d-m-Y H:i:s') }}</td>
            </tr>
            <tr>
                <td>Total Data</td>
                <td>:</td>
                <td>{{ $summary['total'] }}</td>
            </tr>
        </table>
    </div>
    
    <div class="summary">
        <table>
            <tr>
                <th>Status</th>
                <th>Jumlah</th>
                <th>Persentase</th>
            </tr>
            <tr>
                <td>Hadir</td>
                <td>{{ $summary['hadir'] }}</td>
                <td>{{ $summary['total'] > 0 ? round(($summary['hadir'] / $summary['total']) * 100, 2) : 0 }}%</td>
            </tr>
            <tr>
                <td>Tidak Hadir</td>
                <td>{{ $summary['tidak_hadir'] }}</td>
                <td>{{ $summary['total'] > 0 ? round(($summary['tidak_hadir'] / $summary['total']) * 100, 2) : 0 }}%</td>
            </tr>
            <tr>
                <td>Izin</td>
                <td>{{ $summary['izin'] }}</td>
                <td>{{ $summary['total'] > 0 ? round(($summary['izin'] / $summary['total']) * 100, 2) : 0 }}%</td>
            </tr>
            <tr>
                <td>Sakit</td>
                <td>{{ $summary['sakit'] }}</td>
                <td>{{ $summary['total'] > 0 ? round(($summary['sakit'] / $summary['total']) * 100, 2) : 0 }}%</td>
            </tr>
        </table>
    </div>
    
    <table class="data">
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
                    <td class="status-{{ $absensi->status }}">
                        {{ ucfirst(str_replace('_', ' ', $absensi->status)) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Tidak ada data absensi</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>Laporan Absensi Guru - Dicetak pada {{ date('d-m-Y H:i:s') }}</p>
    </div>
</body>
</html>