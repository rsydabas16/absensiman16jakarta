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
            margin-bottom: 5px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary-box {
            display: inline-block;
            width: 18%;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            margin-right: 1%;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        th {
            background-color: #f2f2f2;
        }
        .page-break {
            page-break-after: always;
        }
        .status-hadir {
            color: #28a745;
        }
        .status-izin {
            color: #17a2b8;
        }
        .status-sakit {
            color: #ffc107;
        }
        .status-alpa {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Periode: {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalAkhir->format('d/m/Y') }}</p>
    </div>
    
    <div class="summary">
        <div class="summary-box">
            <h4>Total</h4>
            <h3>{{ $summary['total'] }}</h3>
        </div>
        <div class="summary-box">
            <h4>Hadir</h4>
            <h3 class="status-hadir">{{ $summary['hadir'] }}</h3>
            <small>{{ round(($summary['hadir'] / max($summary['total'], 1)) * 100, 2) }}%</small>
        </div>
        <div class="summary-box">
            <h4>Izin</h4>
            <h3 class="status-izin">{{ $summary['izin'] }}</h3>
        </div>
        <div class="summary-box">
            <h4>Sakit</h4>
            <h3 class="status-sakit">{{ $summary['sakit'] }}</h3>
        </div>
        <div class="summary-box">
            <h4>Alpa</h4>
            <h3 class="status-alpa">{{ $summary['alpa'] }}</h3>
        </div>
    </div>
    
    <div class="table-data">
        <table>
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
                @forelse($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $item->tanggal->locale('id')->dayName }}</td>
                    <td>{{ $item->guru->nama_lengkap }}</td>
                    <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                    <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
                    <td>{{ $item->jadwalPelajaran->jam_ke }}</td>
                    <td class="status-{{ $item->status === 'tidak_hadir' ? 'alpa' : $item->status }}">
                        {{ ucfirst($item->status === 'tidak_hadir' ? 'Alpa' : $item->status) }}
                    </td>
                    <td>{{ $item->jam_absen ?? '-' }}</td>
                    <td>
                        @if($item->status !== 'hadir')
                            {{ $item->alasan ?? '-' }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center;">Tidak ada data absensi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 30px; text-align: right;">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>