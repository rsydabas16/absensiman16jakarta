<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Guru - {{ $guru->nama_lengkap }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        .info-guru {
            margin-bottom: 20px;
        }
        
        .info-guru table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-guru td {
            padding: 5px;
            border: none;
        }
        
        .info-guru .label {
            width: 150px;
            font-weight: bold;
        }
        
        .statistik {
            margin-bottom: 20px;
        }
        
        .statistik-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .statistik-table th,
        .statistik-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        .statistik-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        
        .status-hadir { background-color: #28a745; }
        .status-izin { background-color: #17a2b8; }
        .status-sakit { background-color: #ffc107; color: #333; }
        .status-alpa { background-color: #dc3545; }
        .status-dinas_luar { background-color: #6f42c1; }
        .status-cuti { background-color: #fd7e14; }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        
        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP ABSENSI GURU</h1>
        <h2>{{ $periodeInfo }}</h2>
    </div>

    <div class="info-guru">
        <table>
            <tr>
                <td class="label">Nama Guru</td>
                <td>: {{ $guru->nama_lengkap }}</td>
            </tr>
            <tr>
                <td class="label">NIP</td>
                <td>: {{ $guru->nip }}</td>
            </tr>
            <tr>
                <td class="label">Periode</td>
                <td>: {{ $periodeInfo }}</td>
            </tr>
            @if($kelasInfo)
            <tr>
                <td class="label">Kelas</td>
                <td>: {{ $kelasInfo }}</td>
            </tr>
            @endif
            @if($mataPelajaranInfo)
            <tr>
                <td class="label">Mata Pelajaran</td>
                <td>: {{ $mataPelajaranInfo }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td>: {{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <div class="statistik">
        <h3>STATISTIK ABSENSI</h3>
        <table class="statistik-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Jumlah</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Hadir</td>
                    <td>{{ $statistik['hadir'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['hadir'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Izin</td>
                    <td>{{ $statistik['izin'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['izin'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Sakit</td>
                    <td>{{ $statistik['sakit'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['sakit'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Dinas Luar</td>
                    <td>{{ $statistik['dinas_luar'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['dinas_luar'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Cuti</td>
                    <td>{{ $statistik['cuti'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['cuti'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Alpa</td>
                    <td>{{ $statistik['alpa'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['alpa'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>DETAIL ABSENSI ({{ $absensi->count() }} data)</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 10%">Hari</th>
                <th style="width: 8%">Jam Ke</th>
                <th style="width: 20%">Mata Pelajaran</th>
                <th style="width: 12%">Kelas</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Jam Absen</th>
                <th style="width: 13%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if($absensi->count() > 0)
                @foreach($absensi as $index => $item)
                <tr>
                    <td style="text-align: center">{{ $index + 1 }}</td>
                    <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $item->tanggal->locale('id')->dayName }}</td>
                    <td style="text-align: center">{{ $item->jadwalPelajaran->jam_ke }}</td>
                    <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                    <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
                    <td style="text-align: center">
                        <span class="status-badge status-{{ $item->status === 'tidak_hadir' ? 'alpa' : $item->status }}">
                            {{ $item->status === 'tidak_hadir' ? 'ALPA' : strtoupper($item->status) }}
                        </span>
                    </td>
                    <td style="text-align: center">{{ $item->jam_absen ?? '-' }}</td>
                    <td>
                        @if($item->status !== 'hadir')
                            {{ $item->alasan ?? '-' }}
                            @if($item->tugas)
                                <br><small><strong>Tugas:</strong> {{ Str::limit($item->tugas, 50) }}</small>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" class="no-data">Tidak ada data absensi untuk periode ini.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Halaman ini digenerate secara otomatis oleh sistem.</p>
    </div>
</body>
</html>









{{-- <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Guru - {{ $guru->nama_lengkap }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        .info-guru {
            margin-bottom: 20px;
        }
        
        .info-guru table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-guru td {
            padding: 5px;
            border: none;
        }
        
        .info-guru .label {
            width: 150px;
            font-weight: bold;
        }
        
        .statistik {
            margin-bottom: 20px;
        }
        
        .statistik-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .statistik-table th,
        .statistik-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        .statistik-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
        }
        
        .status-hadir { background-color: #28a745; }
        .status-izin { background-color: #17a2b8; }
        .status-sakit { background-color: #ffc107; color: #333; }
        .status-alpa { background-color: #dc3545; }
        
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
        }
        
        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP ABSENSI GURU</h1>
        <h2>{{ $namaBulan }} {{ $tahun }}</h2>
    </div>

    <div class="info-guru">
        <table>
            <tr>
                <td class="label">Nama Guru</td>
                <td>: {{ $guru->nama_lengkap }}</td>
            </tr>
            <tr>
                <td class="label">NIP</td>
                <td>: {{ $guru->nip }}</td>
            </tr>
            <tr>
                <td class="label">Periode</td>
                <td>: {{ $namaBulan }} {{ $tahun }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td>: {{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <div class="statistik">
        <h3>STATISTIK ABSENSI</h3>
        <table class="statistik-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Jumlah</th>
                    <th>Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Hadir</td>
                    <td>{{ $statistik['hadir'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['hadir'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Izin</td>
                    <td>{{ $statistik['izin'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['izin'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Sakit</td>
                    <td>{{ $statistik['sakit'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['sakit'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Alpa</td>
                    <td>{{ $statistik['alpa'] }}</td>
                    <td>{{ $totalData > 0 ? round(($statistik['alpa'] / $totalData) * 100, 1) : 0 }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h3>DETAIL ABSENSI</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%">No</th>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 10%">Hari</th>
                <th style="width: 8%">Jam Ke</th>
                <th style="width: 20%">Mata Pelajaran</th>
                <th style="width: 12%">Kelas</th>
                <th style="width: 10%">Status</th>
                <th style="width: 10%">Jam Absen</th>
                <th style="width: 10%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @if($absensi->count() > 0)
                @foreach($absensi as $index => $item)
                <tr>
                    <td style="text-align: center">{{ $index + 1 }}</td>
                    <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $item->tanggal->locale('id')->dayName }}</td>
                    <td style="text-align: center">{{ $item->jadwalPelajaran->jam_ke }}</td>
                    <td>{{ $item->jadwalPelajaran->mataPelajaran->nama_mata_pelajaran }}</td>
                    <td>{{ $item->jadwalPelajaran->kelas->nama_kelas }}</td>
                    <td style="text-align: center">
                        <span class="status-badge status-{{ $item->status === 'tidak_hadir' ? 'alpa' : $item->status }}">
                            {{ $item->status === 'tidak_hadir' ? 'ALPA' : strtoupper($item->status) }}
                        </span>
                    </td>
                    <td style="text-align: center">{{ $item->jam_absen ?? '-' }}</td>
                    <td>
                        @if($item->status !== 'hadir')
                            {{ $item->alasan ?? '-' }}
                            @if($item->tugas)
                                <br><small><strong>Tugas:</strong> {{ Str::limit($item->tugas, 50) }}</small>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9" class="no-data">Tidak ada data absensi untuk periode ini.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Halaman ini digenerate secara otomatis oleh sistem.</p>
    </div>
</body>
</html> --}}