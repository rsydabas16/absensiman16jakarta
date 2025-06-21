<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiGuruExport;



class LaporanController extends Controller
{
    public function index(Request $request)
{
    // Tentukan filter default
    $filter = [
        'tanggal_mulai' => $request->tanggal_mulai ?? now()->subDays(7)->format('Y-m-d'),
        'tanggal_akhir' => $request->tanggal_akhir ?? now()->format('Y-m-d'),
        'guru_id' => $request->guru_id ?? '',
        'kelas_id' => $request->kelas_id ?? '',
        'status' => $request->status ?? '',
        'periode' => $request->periode ?? 'harian',
    ];
    
    // Ambil data untuk dropdown
    $guruList = Guru::orderBy('nama_lengkap')->get();
    $kelasList = Kelas::orderBy('nama_kelas')->get();
    
    // Query absensi berdasarkan filter
    $query = AbsensiGuru::with(['guru', 'jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
        ->whereBetween('tanggal', [$filter['tanggal_mulai'], $filter['tanggal_akhir']])
        ->orderBy('tanggal', 'desc');
    
    // Filter berdasarkan guru
    if (!empty($filter['guru_id'])) {
        $query->where('guru_id', $filter['guru_id']);
    }
    
    // Filter berdasarkan kelas
    if (!empty($filter['kelas_id'])) {
        $query->whereHas('jadwalPelajaran', function($q) use ($filter) {
            $q->where('kelas_id', $filter['kelas_id']);
        });
    }
    
    // Filter berdasarkan status
    if (!empty($filter['status'])) {
        $query->where('status', $filter['status']);
    }
    
    $absensiData = $query->get();
    
    // Hitung statistik
    $statistik = [
        'total' => $absensiData->count(),
        'hadir' => $absensiData->where('status', 'hadir')->count(),
        'izin' => $absensiData->where('status', 'izin')->count(),
        'sakit' => $absensiData->where('status', 'sakit')->count(),
        'dinas_luar' => $absensiData->where('status', 'dinas_luar')->count(),
        'cuti' => $absensiData->where('status', 'cuti')->count(),
        'alpa' => $absensiData->where('status', 'tidak_hadir')->count(),
        
    ];
    
    // Buat data untuk chart berdasarkan periode yang dipilih
    $chartData = $this->generateChartData($absensiData, $filter['periode'], $filter['tanggal_mulai'], $filter['tanggal_akhir']);
    
    return view('kepala_sekolah.laporan.index', compact(
        'filter', 'guruList', 'kelasList', 'absensiData', 'statistik', 'chartData'
    ));
}

private function generateChartData($absensiData, $periode, $tanggalMulai, $tanggalAkhir)
{
    $startDate = Carbon::parse($tanggalMulai);
    $endDate = Carbon::parse($tanggalAkhir);
    $labels = [];
    $datasets = [
        'hadir' => [],
        'izin' => [],
        'sakit' => [],
        'alpa' => [],
        'dinas_luar' => [],
        'cuti' => []
    ];
    
    if ($periode == 'harian') {
        for ($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
            $currentDate = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            
            $dayData = $absensiData->filter(function($item) use ($currentDate) {
                return $item->tanggal->format('Y-m-d') == $currentDate;
            });
            
            $datasets['hadir'][] = $dayData->where('status', 'hadir')->count();
            $datasets['izin'][] = $dayData->where('status', 'izin')->count();
            $datasets['sakit'][] = $dayData->where('status', 'sakit')->count();
            $datasets['alpa'][] = $dayData->where('status', 'tidak_hadir')->count();
             $datasets['dinas_luar'][] = $dayData->where('status', 'dinas_luar')->count();
            $datasets['cuti'][] = $dayData->where('status', 'cuti')->count();
        }
    } elseif ($periode == 'mingguan') {
        // Logika untuk data mingguan
        // ...
    } elseif ($periode == 'bulanan') {
        // Logika untuk data bulanan
        // ...
    }
    
    return [
        'labels' => $labels,
        'datasets' => $datasets
    ];
}
    
    public function generate(Request $request)
    {
        $request->validate([
            'jenis_laporan' => 'required|in:guru,kelas,mata_pelajaran',
            'tanggal_mulai' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);
        
        $tanggalMulai = Carbon::parse($request->tanggal_mulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir)->endOfDay();
        
        $data = [];
        $title = '';
        
        if ($request->jenis_laporan == 'guru') {
            $title = 'Laporan Absensi Per Guru';
            
            $query = AbsensiGuru::with(['guru', 'jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                ->orderBy('tanggal', 'desc');
            
            if ($request->filled('guru_id')) {
                $query->where('guru_id', $request->guru_id);
                $guru = Guru::find($request->guru_id);
                $title .= ' - ' . $guru->nama_lengkap;
            }
            
            $data = $query->get();
        } elseif ($request->jenis_laporan == 'kelas') {
            $title = 'Laporan Absensi Per Kelas';
            
            $query = AbsensiGuru::with(['guru', 'jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
                ->whereHas('jadwalPelajaran', function ($query) use ($request) {
                    if ($request->filled('kelas_id')) {
                        $query->where('kelas_id', $request->kelas_id);
                    }
                })
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                ->orderBy('tanggal', 'desc');
            
            if ($request->filled('kelas_id')) {
                $kelas = Kelas::find($request->kelas_id);
                $title .= ' - ' . $kelas->nama_kelas;
            }
            
            $data = $query->get();
        } elseif ($request->jenis_laporan == 'mata_pelajaran') {
            $title = 'Laporan Absensi Per Mata Pelajaran';
            
            $query = AbsensiGuru::with(['guru', 'jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
                ->whereHas('jadwalPelajaran', function ($query) use ($request) {
                    if ($request->filled('mata_pelajaran_id')) {
                        $query->where('mata_pelajaran_id', $request->mata_pelajaran_id);
                    }
                })
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir])
                ->orderBy('tanggal', 'desc');
            
            if ($request->filled('mata_pelajaran_id')) {
                $mapel = MataPelajaran::find($request->mata_pelajaran_id);
                $title .= ' - ' . $mapel->nama_mata_pelajaran;
            }
            
            $data = $query->get();
        }
        
        $summary = [
            'total' => $data->count(),
            'hadir' => $data->where('status', 'hadir')->count(),
            'tidak_hadir' => $data->where('status', 'tidak_hadir')->count(),
            'izin' => $data->where('status', 'izin')->count(),
            'sakit' => $data->where('status', 'sakit')->count(),
            'dinas_luar' => $data->where('status', 'dinas_luar')->count(),
            'cuti' => $data->where('status', 'cuti')->count(),
        ];
        
        if ($request->format == 'web') {
            session([
                'laporan_data' => $data,
                'laporan_title' => $title,
                'laporan_summary' => $summary,
                'tanggal_mulai' => $tanggalMulai->format('Y-m-d'),
                'tanggal_akhir' => $tanggalAkhir->format('Y-m-d'),
            ]);
            
            return redirect()->route('kepala_sekolah.laporan.show');
        } elseif ($request->format == 'pdf') {
            $pdf = PDF::loadView('kepala_sekolah.laporan.pdf', compact('data', 'title', 'summary', 'tanggalMulai', 'tanggalAkhir'));
            return $pdf->download('laporan-absensi-' . date('Y-m-d') . '.pdf');
        } elseif ($request->format == 'excel') {
            return Excel::download(new AbsensiGuruExport($data, $title, $summary, $tanggalMulai, $tanggalAkhir), 'laporan-absensi-' . date('Y-m-d') . '.xlsx');
        }
        
        return redirect()->back()->with('error', 'Format laporan tidak valid');
    }
    
    public function show()
    {
        if (!session()->has('laporan_data')) {
            return redirect()->route('kepala_sekolah.laporan.index')->with('error', 'Tidak ada data laporan yang tersedia');
        }
        
        $data = session('laporan_data');
        $title = session('laporan_title');
        $summary = session('laporan_summary');
        $tanggalMulai = session('tanggal_mulai');
        $tanggalAkhir = session('tanggal_akhir');
        
        return view('kepala_sekolah.laporan.show', compact('data', 'title', 'summary', 'tanggalMulai', 'tanggalAkhir'));
    }
    
    public function guruStatistik($id)
    {
        $guru = Guru::findOrFail($id);
        
        // Default filter - bulan ini
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        // Mengambil data absensi guru
        $absensi = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
            ->where('guru_id', $id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Menghitung statistik
        $statistik = [
            'total' => $absensi->count(),
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'tidak_hadir' => $absensi->where('status', 'tidak_hadir')->count(),
            'izin' => $absensi->where('status', 'izin')->count(),
            'sakit' => $absensi->where('status', 'sakit')->count(),
            'dinas_luar' => $absensi->where('status', 'dinas_luar')->count(),
            'cuti' => $absensi->where('status', 'cuti')->count(),
        ];
        
        // Data untuk chart
        $chartData = [
            'labels' => ['Hadir', 'Tidak Hadir', 'Izin', 'Sakit','Dinas Luar','Cuti'],
            'data' => [
                $statistik['hadir'],
                $statistik['tidak_hadir'],
                $statistik['izin'],
                $statistik['sakit'],
                 $statistik['dinas_luar'],
                $statistik['cuti']
            ],
            'colors' => ['#28a745', '#dc3545', '#17a2b8', '#ffc107','#a10de0','#cc7110']
        ];
        
        return view('kepala_sekolah.laporan.guru_statistik', compact('guru', 'absensi', 'statistik', 'chartData', 'startDate', 'endDate'));
    }
    
    public function filterGuruStatistik(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);
        
        $filter = $request->filter ?? 'bulanan';
        $startDate = null;
        $endDate = null;
        
        if ($filter == 'harian') {
            $date = $request->date ?? Carbon::today()->format('Y-m-d');
            $startDate = Carbon::parse($date)->startOfDay();
            $endDate = Carbon::parse($date)->endOfDay();
        } elseif ($filter == 'mingguan') {
            $weekStart = $request->week_start ?? Carbon::now()->startOfWeek()->format('Y-m-d');
            $startDate = Carbon::parse($weekStart)->startOfDay();
            $endDate = Carbon::parse($weekStart)->addDays(6)->endOfDay();
        } elseif ($filter == 'bulanan') {
            $month = $request->month ?? Carbon::now()->format('Y-m');
            $startDate = Carbon::parse($month . '-01')->startOfDay();
            $endDate = Carbon::parse($month . '-01')->endOfMonth()->endOfDay();
        }
        
        // Mengambil data absensi guru
        $absensi = AbsensiGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
            ->where('guru_id', $id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Menghitung statistik
        $statistik = [
            'total' => $absensi->count(),
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'tidak_hadir' => $absensi->where('status', 'tidak_hadir')->count(),
            'izin' => $absensi->where('status', 'izin')->count(),
            'sakit' => $absensi->where('status', 'sakit')->count(),
            'dinas_luar' => $absensi->where('status', 'dinas_luar')->count(),
            'cuti' => $absensi->where('status', 'cuti')->count(),
        ];
        
        // Data untuk chart
        $chartData = [
            'labels' => ['Hadir', 'Tidak Hadir', 'Izin', 'Sakit','Dinas Luar','Cuti'],
            'data' => [
                $statistik['hadir'],
                $statistik['tidak_hadir'],
                $statistik['izin'],
                $statistik['sakit'],
                 $statistik['dinas_luar'],
                $statistik['cuti']
            ],
            'colors' => ['#28a745', '#dc3545', '#17a2b8', '#ffc107','#a10de0','#cc7110']
        ];
        
        return view('kepala_sekolah.laporan.guru_statistik', compact('guru', 'absensi', 'statistik', 'chartData', 'startDate', 'endDate', 'filter'));
    }

    public function export(Request $request)
{
     // Tentukan filter dari request
    $filter = [
        'tanggal_mulai' => $request->tanggal_mulai ?? now()->subDays(7)->format('Y-m-d'),
        'tanggal_akhir' => $request->tanggal_akhir ?? now()->format('Y-m-d'),
        'guru_id' => $request->guru_id ?? '',
        'kelas_id' => $request->kelas_id ?? '',
        'status' => $request->status ?? '',
        'periode' => $request->periode ?? 'harian',
    ];
    
    // Query absensi berdasarkan filter
    $query = AbsensiGuru::with(['guru', 'jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
        ->whereBetween('tanggal', [$filter['tanggal_mulai'], $filter['tanggal_akhir']])
        ->orderBy('tanggal', 'desc');
    
    // Filter berdasarkan guru
    if (!empty($filter['guru_id'])) {
        $query->where('guru_id', $filter['guru_id']);
    }
    
    // Filter berdasarkan kelas
    if (!empty($filter['kelas_id'])) {
        $query->whereHas('jadwalPelajaran', function($q) use ($filter) {
            $q->where('kelas_id', $filter['kelas_id']);
        });
    }
    
    // Filter berdasarkan status
    if (!empty($filter['status'])) {
        $query->where('status', $filter['status']);
    }
    
    $absensiData = $query->get();
    
    // Hitung statistik
    $statistik = [
        'total' => $absensiData->count(),
        'hadir' => $absensiData->where('status', 'hadir')->count(),
        'izin' => $absensiData->where('status', 'izin')->count(),
        'sakit' => $absensiData->where('status', 'sakit')->count(),
        'alpa' => $absensiData->where('status', 'tidak_hadir')->count(),
        'dinas_luar' => $absensiData->where('status', 'dinas_luar')->count(),
        'cuti' => $absensiData->where('status', 'cuti')->count(),
    ];
    
    $title = 'Laporan Absensi Guru';
    
    if ($request->format == 'pdf') {
        $pdf = PDF::loadView('kepala_sekolah.laporan.pdf', [
            'data' => $absensiData, 
            'title' => $title, 
            'summary' => $statistik, 
            'tanggalMulai' => Carbon::parse($filter['tanggal_mulai']), 
            'tanggalAkhir' => Carbon::parse($filter['tanggal_akhir'])
        ]);
        return $pdf->download('laporan-absensi-' . date('Y-m-d') . '.pdf');
    } elseif ($request->format == 'excel') {
        return Excel::download(
            new AbsensiGuruExport($absensiData, $title, $statistik, 
                Carbon::parse($filter['tanggal_mulai']), 
                Carbon::parse($filter['tanggal_akhir'])
            ), 
            'laporan-absensi-' . date('Y-m-d') . '.xlsx'
        );
    }
    
    return redirect()->back()->with('error', 'Format laporan tidak valid');
}
}