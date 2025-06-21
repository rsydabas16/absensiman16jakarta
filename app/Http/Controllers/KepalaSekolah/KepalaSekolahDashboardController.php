<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\JadwalPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KepalaSekolahDashboardController extends Controller
{
    public function index()
    {
        // Total guru dan jumlah absensi hari ini
        $totalGuru = Guru::count();
        $today = Carbon::today();
        
        // Mendapatkan data absensi hari ini
        $absensiHariIni = AbsensiGuru::where('tanggal', $today)->get();
        
        // Menghitung statistik absensi hari ini
        $statistikHariIni = [
            'total' => $absensiHariIni->count(),
            'hadir' => $absensiHariIni->where('status', 'hadir')->count(),
            'tidak_hadir' => $absensiHariIni->where('status', 'tidak_hadir')->count(),
            'izin' => $absensiHariIni->where('status', 'izin')->count(),
            'sakit' => $absensiHariIni->where('status', 'sakit')->count(),
            'dinas_luar' => $absensiHariIni->where('status', 'dinas_luar')->count(),
            'cuti' => $absensiHariIni->where('status', 'cuti')->count(),
        ];
        
        // Mendapatkan data kehadiran per kelas untuk hari ini
        $kehadiranPerKelas = DB::table('absensi_guru')
            ->join('jadwal_pelajaran', 'absensi_guru.jadwal_pelajaran_id', '=', 'jadwal_pelajaran.id')
            ->join('kelas', 'jadwal_pelajaran.kelas_id', '=', 'kelas.id')
            ->where('absensi_guru.tanggal', $today)
            ->select('kelas.nama_kelas', 
                DB::raw('count(*) as total'),
                DB::raw('sum(case when absensi_guru.status = "hadir" then 1 else 0 end) as hadir'),
                DB::raw('sum(case when absensi_guru.status = "tidak_hadir" then 1 else 0 end) as tidak_hadir'),
                DB::raw('sum(case when absensi_guru.status = "izin" then 1 else 0 end) as izin'),
                DB::raw('sum(case when absensi_guru.status = "sakit" then 1 else 0 end) as sakit'),
                DB::raw('sum(case when absensi_guru.status = "dinas_luar" then 1 else 0 end) as dinas_luar'),
                DB::raw('sum(case when absensi_guru.status = "cuti" then 1 else 0 end) as cuti')
            )
            ->groupBy('kelas.nama_kelas')
            ->get();
        
        // Get data for chart - mingguan
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        $absensiMingguIni = DB::table('absensi_guru')
            ->whereBetween('tanggal', [$startOfWeek, $endOfWeek])
            ->select(
                DB::raw('DATE(tanggal) as tanggal'),
                DB::raw('count(*) as total'),
                DB::raw('sum(case when status = "hadir" then 1 else 0 end) as hadir'),
                DB::raw('sum(case when status = "tidak_hadir" then 1 else 0 end) as tidak_hadir'),
                DB::raw('sum(case when status = "izin" then 1 else 0 end) as izin'),
                DB::raw('sum(case when status = "sakit" then 1 else 0 end) as sakit'),
                DB::raw('sum(case when absensi_guru.status = "dinas_luar" then 1 else 0 end) as dinas_luar'),
                DB::raw('sum(case when absensi_guru.status = "cuti" then 1 else 0 end) as cuti')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
        
        $chartDataMinggu = [
            'labels' => [],
            'hadir' => [],
            'tidak_hadir' => [],
            'izin' => [],
            'sakit' => [],
            'dinas_luar' => [],
            'cuti' => [],
        ];
        
        // Loop melalui setiap hari dalam seminggu
        for ($date = clone $startOfWeek; $date <= $endOfWeek; $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');
            $data = $absensiMingguIni->where('tanggal', $formattedDate)->first();
            
            $chartDataMinggu['labels'][] = $date->format('D, d/m');
            $chartDataMinggu['hadir'][] = $data ? $data->hadir : 0;
            $chartDataMinggu['tidak_hadir'][] = $data ? $data->tidak_hadir : 0;
            $chartDataMinggu['izin'][] = $data ? $data->izin : 0;
            $chartDataMinggu['sakit'][] = $data ? $data->sakit : 0;
            $chartDataMinggu['dinas_luar'][] = $data ? $data->sakit : 0;
            $chartDataMinggu['cuti'][] = $data ? $data->sakit : 0;
        }
        
        // Get data for chart - bulanan
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $absensiBulanIni = DB::table('absensi_guru')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->select(
                DB::raw('DATE(tanggal) as tanggal'),
                DB::raw('count(*) as total'),
                DB::raw('sum(case when status = "hadir" then 1 else 0 end) as hadir'),
                DB::raw('sum(case when status = "tidak_hadir" then 1 else 0 end) as tidak_hadir'),
                DB::raw('sum(case when status = "izin" then 1 else 0 end) as izin'),
                DB::raw('sum(case when status = "sakit" then 1 else 0 end) as sakit'),
                DB::raw('sum(case when absensi_guru.status = "dinas_luar" then 1 else 0 end) as dinas_luar'),
                DB::raw('sum(case when absensi_guru.status = "cuti" then 1 else 0 end) as cuti')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
            
        $chartDataBulan = [
            'labels' => [],
            'hadir' => [],
            'tidak_hadir' => [],
            'izin' => [],
            'sakit' => [],
             'dinas_luar' => [],
            'cuti' => [],
        ];
        
        // Group data by week for monthly chart to avoid crowding
        $weeklyData = [];
        $currentWeek = 1;
        
        for ($date = clone $startOfMonth; $date <= $endOfMonth; $date->addDay()) {
            $weekNumber = ceil($date->day / 7);
            
            if (!isset($weeklyData[$weekNumber])) {
                $weeklyData[$weekNumber] = [
                    'hadir' => 0,
                    'tidak_hadir' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'dinas_luar' => 0,
                    'cuti' => 0,
                ];
            }
            
            $formattedDate = $date->format('Y-m-d');
            $data = $absensiBulanIni->where('tanggal', $formattedDate)->first();
            
            if ($data) {
                $weeklyData[$weekNumber]['hadir'] += $data->hadir;
                $weeklyData[$weekNumber]['tidak_hadir'] += $data->tidak_hadir;
                $weeklyData[$weekNumber]['izin'] += $data->izin;
                $weeklyData[$weekNumber]['sakit'] += $data->sakit;
                $weeklyData[$weekNumber]['dinas_luar'] += $data->izin;
                $weeklyData[$weekNumber]['cuti'] += $data->sakit;
                
                
            }
        }
        
        foreach ($weeklyData as $week => $data) {
            $chartDataBulan['labels'][] = "Minggu " . $week;
            $chartDataBulan['hadir'][] = $data['hadir'];
            $chartDataBulan['tidak_hadir'][] = $data['tidak_hadir'];
            $chartDataBulan['izin'][] = $data['izin'];
            $chartDataBulan['sakit'][] = $data['sakit'];
              $chartDataBulan['dinas_luar'][] = $data['dinas_luar'];
            $chartDataBulan['cuti'][] = $data['cuti'];
        }
        
        // Get data for top 5 guru dengan kehadiran tertinggi
        $topGuru = DB::table('absensi_guru')
            ->join('guru', 'absensi_guru.guru_id', '=', 'guru.id')
            ->select(
                'guru.nama_lengkap',
                DB::raw('count(*) as total_absensi'),
                DB::raw('sum(case when absensi_guru.status = "hadir" then 1 else 0 end) as hadir'),
                DB::raw('sum(case when absensi_guru.status = "tidak_hadir" then 1 else 0 end) as tidak_hadir'),
                DB::raw('sum(case when absensi_guru.status = "izin" then 1 else 0 end) as izin'),
                DB::raw('sum(case when absensi_guru.status = "sakit" then 1 else 0 end) as sakit')
            )
            ->groupBy('guru.nama_lengkap')
            ->orderByRaw('hadir DESC, total_absensi DESC')
            ->limit(5)
            ->get();
        
       return view('kepala_sekolah.dashboard', compact(
            'totalGuru', 
            'statistikHariIni', 
            'kehadiranPerKelas', 
            'chartDataMinggu',
            'chartDataBulan',
            'topGuru'
        ));
    }
    
    public function filterData(Request $request)
    {
        $filter = $request->filter ?? 'harian';
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
        
        // Mengambil data guru untuk dropdown filter
        $guru = Guru::all();
        
        // Jika ada filter guru
        $guruId = $request->guru_id ?? null;
        
        // Query dasar
        $query = DB::table('absensi_guru')
            ->join('guru', 'absensi_guru.guru_id', '=', 'guru.id')
            ->join('jadwal_pelajaran', 'absensi_guru.jadwal_pelajaran_id', '=', 'jadwal_pelajaran.id')
            ->join('mata_pelajaran', 'jadwal_pelajaran.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->join('kelas', 'jadwal_pelajaran.kelas_id', '=', 'kelas.id')
            ->whereBetween('absensi_guru.tanggal', [$startDate, $endDate]);
        
        // Jika ada filter guru
        if ($guruId) {
            $query->where('absensi_guru.guru_id', $guruId);
        }
        
        // Mengambil data absensi sesuai filter
        $absensi = $query->select(
                'absensi_guru.*',
                'guru.nama_lengkap as nama_guru',
                'mata_pelajaran.nama_mata_pelajaran',
                'kelas.nama_kelas',
                'jadwal_pelajaran.jam_ke',
                'jadwal_pelajaran.jam_mulai',
                'jadwal_pelajaran.jam_selesai'
            )
            ->orderBy('absensi_guru.tanggal', 'desc')
            ->get();
        
        // Menghitung statistik absensi
        $statistik = [
            'total' => $absensi->count(),
            'hadir' => $absensi->where('status', 'hadir')->count(),
            'tidak_hadir' => $absensi->where('status', 'tidak_hadir')->count(),
            'izin' => $absensi->where('status', 'izin')->count(),
            'sakit' => $absensi->where('status', 'sakit')->count(),
        ];
        
        // Jika ada filter guru, ambil data untuk chart pie
        $chartData = null;
        if ($guruId) {
            $chartData = [
                'labels' => ['Hadir', 'Tidak Hadir', 'Izin', 'Sakit'],
                'data' => [
                    $statistik['hadir'],
                    $statistik['tidak_hadir'],
                    $statistik['izin'],
                    $statistik['sakit']
                ],
                'colors' => ['#28a745', '#dc3545', '#17a2b8', '#ffc107']
            ];
        }
        
       return view('kepala_sekolah.dashboard', compact(
            'filter', 
            'startDate', 
            'endDate', 
            'absensi', 
            'statistik', 
            'guru', 
            'guruId',
            'chartData'
        ));
    }
}
// app/Http/Controllers/KepalaSekolah/KepalaSekolahDashboardController.php

// namespace App\Http\Controllers\KepalaSekolah;

// use App\Http\Controllers\Controller;
// use App\Models\AbsensiGuru;
// use App\Models\Guru;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\DB;

// class KepalaSekolahDashboardController extends Controller
// {
//     public function index()
//     {
//         $tanggalHariIni = Carbon::now()->toDateString();
        
//         // Statistik hari ini
//         $statistikHariIni = AbsensiGuru::where('tanggal', $tanggalHariIni)
//             ->selectRaw('status, count(*) as total')
//             ->groupBy('status')
//             ->pluck('total', 'status')
//             ->toArray();
            
//         // Top 5 guru dengan kehadiran terbaik bulan ini
//         $bulanIni = Carbon::now()->month;
//         $tahunIni = Carbon::now()->year;
        
//         $guruTerbaik = Guru::withCount(['absensi as hadir_count' => function ($query) use ($bulanIni, $tahunIni) {
//                 $query->whereMonth('tanggal', $bulanIni)
//                     ->whereYear('tanggal', $tahunIni)
//                     ->where('status', 'hadir');
//             }])
//             ->orderBy('hadir_count', 'desc')
//             ->take(5)
//             ->get();
            
//         // Grafik kehadiran mingguan
//         $grafikMingguan = $this->getGrafikMingguan();
        
//         $data = [
//             'hadirHariIni' => $statistikHariIni['hadir'] ?? 0,
//             'izinHariIni' => $statistikHariIni['izin'] ?? 0,
//             'sakitHariIni' => $statistikHariIni['sakit'] ?? 0,
//             'alpaHariIni' => $statistikHariIni['tidak_hadir'] ?? 0,
//             'guruTerbaik' => $guruTerbaik,
//             'grafikMingguan' => $grafikMingguan,
//         ];

//         return view('kepala-sekolah.dashboard', $data);
//     }
    
//     private function getGrafikMingguan()
//     {
//         $endDate = Carbon::now();
//         $startDate = $endDate->copy()->subDays(6);
        
//         $data = AbsensiGuru::whereBetween('tanggal', [$startDate, $endDate])
//             ->where('status', 'hadir')
//             ->selectRaw('DATE(tanggal) as tanggal, count(*) as total')
//             ->groupBy('tanggal')
//             ->orderBy('tanggal')
//             ->get();
            
//         $result = [];
//         for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
//             $tanggal = $date->format('Y-m-d');
//             $total = $data->firstWhere('tanggal', $tanggal)?->total ?? 0;
            
//             $result[] = [
//                 'tanggal' => $date->locale('id')->dayName,
//                 'total' => $total
//             ];
//         }
        
//         return $result;
//     }
// }