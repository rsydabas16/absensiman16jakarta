<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiGuruExport;

class LaporanController extends Controller
{
    public function index()
    {
        $guru = Guru::all();
        $kelas = Kelas::all();
        $mataPelajaran = MataPelajaran::all();
        
        return view('admin.laporan.index', compact('guru', 'kelas', 'mataPelajaran'));
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
        ];
        
        if ($request->format == 'web') {
            session([
                'laporan_data' => $data,
                'laporan_title' => $title,
                'laporan_summary' => $summary,
                'tanggal_mulai' => $tanggalMulai->format('Y-m-d'),
                'tanggal_akhir' => $tanggalAkhir->format('Y-m-d'),
            ]);
            
            return redirect()->route('admin.laporan.show');
        } elseif ($request->format == 'pdf') {
            $pdf = PDF::loadView('admin.laporan.pdf', compact('data', 'title', 'summary', 'tanggalMulai', 'tanggalAkhir'));
            return $pdf->download('laporan-absensi-' . date('Y-m-d') . '.pdf');
        } elseif ($request->format == 'excel') {
            return Excel::download(new AbsensiGuruExport($data, $title, $summary, $tanggalMulai, $tanggalAkhir), 'laporan-absensi-' . date('Y-m-d') . '.xlsx');
        }
        
        return redirect()->back()->with('error', 'Format laporan tidak valid');
    }
    
    public function show()
    {
        if (!session()->has('laporan_data')) {
            return redirect()->route('admin.laporan.index')->with('error', 'Tidak ada data laporan yang tersedia');
        }
        
        $data = session('laporan_data');
        $title = session('laporan_title');
        $summary = session('laporan_summary');
        $tanggalMulai = session('tanggal_mulai');
        $tanggalAkhir = session('tanggal_akhir');
        
        return view('admin.laporan.show', compact('data', 'title', 'summary', 'tanggalMulai', 'tanggalAkhir'));
    }
}



// app/Http/Controllers/KepalaSekolah/LaporanController.php

// namespace App\Http\Controllers\KepalaSekolah;

// use App\Http\Controllers\Controller;
// use App\Models\AbsensiGuru;
// use App\Models\Guru;
// use App\Models\Kelas;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;

// class LaporanController extends Controller
// {
//     public function index(Request $request)
//     {
//         $filter = [
//             'tanggal_mulai' => $request->tanggal_mulai ?? Carbon::now()->startOfMonth()->format('Y-m-d'),
//             'tanggal_akhir' => $request->tanggal_akhir ?? Carbon::now()->endOfMonth()->format('Y-m-d'),
//             'guru_id' => $request->guru_id,
//             'kelas_id' => $request->kelas_id,
//             'status' => $request->status,
//             'periode' => $request->periode ?? 'harian',
//         ];

//         // Get list for filters
//         $guruList = Guru::orderBy('nama_lengkap')->get();
//         $kelasList = Kelas::orderBy('nama_kelas')->get();

//         // Query absensi
//         $query = AbsensiGuru::with(['guru', 'jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
//             ->whereBetween('tanggal', [$filter['tanggal_mulai'], $filter['tanggal_akhir']]);

//         if ($filter['guru_id']) {
//             $query->where('guru_id', $filter['guru_id']);
//         }

//         if ($filter['kelas_id']) {
//             $query->whereHas('jadwalPelajaran', function($q) use ($filter) {
//                 $q->where('kelas_id', $filter['kelas_id']);
//             });
//         }

//         if ($filter['status']) {
//             $query->where('status', $filter['status']);
//         }

//         $absensiData = $query->orderBy('tanggal', 'desc')->get();

//         // Statistik
//         $statistik = $this->generateStatistik($absensiData);

//         // Data untuk chart
//         $chartData = $this->generateChartData($filter);

//         return view('kepala-sekolah.laporan.index', compact(
//             'filter', 'guruList', 'kelasList', 'absensiData', 'statistik', 'chartData'
//         ));
//     }

//     private function generateStatistik($absensiData)
//     {
//         $total = $absensiData->count();
        
//         if ($total === 0) {
//             return [
//                 'total' => 0,
//                 'hadir' => 0,
//                 'izin' => 0,
//                 'sakit' => 0,
//                 'alpa' => 0,
//                 'persentase_hadir' => 0,
//             ];
//         }

//         $hadir = $absensiData->where('status', 'hadir')->count();
//         $izin = $absensiData->where('status', 'izin')->count();
//         $sakit = $absensiData->where('status', 'sakit')->count();
//         $alpa = $absensiData->where('status', 'tidak_hadir')->count();

//         return [
//             'total' => $total,
//             'hadir' => $hadir,
//             'izin' => $izin,
//             'sakit' => $sakit,
//             'alpa' => $alpa,
//             'persentase_hadir' => round(($hadir / $total) * 100, 2),
//         ];
//     }

//     private function generateChartData($filter)
//     {
//         $startDate = Carbon::parse($filter['tanggal_mulai']);
//         $endDate = Carbon::parse($filter['tanggal_akhir']);
        
//         $query = AbsensiGuru::whereBetween('tanggal', [$startDate, $endDate]);

//         if ($filter['guru_id']) {
//             $query->where('guru_id', $filter['guru_id']);
//         }

//         if ($filter['kelas_id']) {
//             $query->whereHas('jadwalPelajaran', function($q) use ($filter) {
//                 $q->where('kelas_id', $filter['kelas_id']);
//             });
//         }

//         // Group by date for line chart
//         $data = $query->selectRaw('DATE(tanggal) as date, status, COUNT(*) as total')
//             ->groupBy('date', 'status')
//             ->orderBy('date')
//             ->get();

//         $labels = [];
//         $datasets = [
//             'hadir' => [],
//             'izin' => [],
//             'sakit' => [],
//             'alpa' => []
//         ];

//         // Generate date range
//         for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
//             $labels[] = $date->format('d/m');
            
//             $dayData = $data->where('date', $date->format('Y-m-d'));
            
//             $datasets['hadir'][] = $dayData->where('status', 'hadir')->first()->total ?? 0;
//             $datasets['izin'][] = $dayData->where('status', 'izin')->first()->total ?? 0;
//             $datasets['sakit'][] = $dayData->where('status', 'sakit')->first()->total ?? 0;
//             $datasets['alpa'][] = $dayData->where('status', 'tidak_hadir')->first()->total ?? 0;
//         }

//         return [
//             'labels' => $labels,
//             'datasets' => $datasets
//         ];
//     }

//     public function export(Request $request)
//     {
//         $filter = [
//             'tanggal_mulai' => $request->tanggal_mulai,
//             'tanggal_akhir' => $request->tanggal_akhir,
//             'guru_id' => $request->guru_id,
//             'kelas_id' => $request->kelas_id,
//             'status' => $request->status,
//         ];

//         $query = AbsensiGuru::with(['guru', 'jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran'])
//             ->whereBetween('tanggal', [$filter['tanggal_mulai'], $filter['tanggal_akhir']]);

//         if ($filter['guru_id']) {
//             $query->where('guru_id', $filter['guru_id']);
//         }

//         if ($filter['kelas_id']) {
//             $query->whereHas('jadwalPelajaran', function($q) use ($filter) {
//                 $q->where('kelas_id', $filter['kelas_id']);
//             });
//         }

//         if ($filter['status']) {
//             $query->where('status', $filter['status']);
//         }

//         $data = $query->orderBy('tanggal', 'desc')->get();

//         if ($request->format === 'excel') {
//             return $this->exportExcel($data, $filter);
//         } else {
//             return $this->exportPdf($data, $filter);
//         }
//     }
// }