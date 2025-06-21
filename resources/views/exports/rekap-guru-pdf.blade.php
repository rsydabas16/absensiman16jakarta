<!-- resources/views/exports/rekap-guru-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi - {{ $guru->nama_lengkap }}</title>
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
            border: 1px solid #ddd;
            padding: 15px;
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
            padding: 10px;
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
        .status-hadir { color: #28a745; font-weight: bold; }
        .status-izin { color: #17a2b8; font-weight: bold; }
        .status-sakit { color: #ffc107; font-weight: bold; }
        .status-alpa { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP ABSENSI GURU</h1>
        <h2>Bulan: {{ $bulan }} {{ $tahun }}</h2>
    </div>

    <div class="info">
        <table>
            <tr>
                <td width="30%"><strong>Nama Guru:</strong></td>
                <td>{{ $guru->nama_lengkap }}</td>
            </tr>
            <tr>
                <td><strong>NIP:</strong></td>
                <td>{{ $guru->nip }}</td>
            </tr>
            <tr>
                <td><strong>Tanggal Cetak:</strong></td>
                <td>{{ $tanggal_cetak }}</td>
            </tr>
        </table>
    </div>

    <div class="statistics">
        <h3>Statistik Kehadiran</h3>
        <table>
            <tr>
                <th>Status</th>
                <th>Jumlah</th>
                <th>Persentase</th>
            </tr>
            <tr>
                <td>Hadir</td>
                <td class="status-hadir">{{ $statistik['hadir'] }}</td>
                <td>{{ $data->count() > 0 ? round(($statistik['hadir'] / $data->count()) * 100, 2) : 0 }}%</td>
            </tr>
            <tr>
                <td>Izin</td>
                <td class="status-izin">{{ $statistik['izin'] }}</td>
                <td>{{ $data->count() > 0 ? round(($statistik['izin'] / $data->count()) * 100, 2) : 0 }}%</td>
            </tr>
            <tr>
                <td>Sakit</td>
                <td class="status-sakit">{{ $statistik['sakit'] }}</td>
                <td>{{ $data->count() > 0 ? round(($statistik['sakit'] / $data->count()) * 100, 2) : 0 }}%</td>
            </tr>
            <tr>
                <td>Alpa</td>
                <td class="status-alpa">{{ $statistik['alpa'] }}</td>
                <td>{{ $data->count() > 0 ? round(($statistik['alpa'] / $data->count()) * 100, 2) : 0 }}%</td>
            </tr>
            <tr style="background-color: #f4f4f4; font-weight: bold;">
                <td>TOTAL</td>
                <td>{{ $data->count() }}</td>
                <td>100%</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
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
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                <td>{{ $item->tanggal->locale('id')->dayName }}</td>
                <td>{{ $item->jadwalPelajaran->jam_ke }}</td>
                <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
                <td class="status-{{ $item->status === 'tidak_hadir' ? 'alpa' : $item->status }}">
                    {{ ucfirst($item->status) }}
                </td>
                <td>{{ $item->jam_absen ?? '-' }}</td>
                <td>
                    @if($item->status !== 'hadir')
                        {{ $item->alasan }}
                        @if($item->tugas)
                            <br><strong>Tugas:</strong> {{ Str::limit($item->tugas, 50) }}
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ $tanggal_cetak }}</p>
    </div>
</body>
</html>